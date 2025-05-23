<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'hesabix:update',
    description: 'Updates the software by pulling changes from GitHub, clearing cache, updating the database, and building the frontend.'
)]
class UpdateSoftwareCommand extends Command
{
    private LoggerInterface $logger;
    private LockFactory $lockFactory;
    private ParameterBagInterface $params;
    private string $rootDir;
    private string $appDir;
    private string $archiveDir;
    private string $backupDir;
    private string $stateFile;
    private string $env;
    private string $webUIDir;

    public function __construct(LoggerInterface $logger, LockFactory $lockFactory, ParameterBagInterface $params)
    {
        $this->logger = $logger;
        $this->lockFactory = $lockFactory;
        $this->params = $params;
        $this->appDir = dirname(__DIR__, 2);
        $this->rootDir = dirname($this->appDir);
        $this->archiveDir = $this->rootDir . '/hesabixArchive';
        $this->backupDir = $this->rootDir . '/hesabixBackup';
        $this->webUIDir = $this->rootDir . '/webUI';
        $envConfig = file_exists($this->appDir . '/.env.local.php') ? require $this->appDir . '/.env.local.php' : [];
        $this->env = $envConfig['APP_ENV'] ?? getenv('APP_ENV') ?: 'prod';
        $this->logger->info("Environment detected: " . $this->env);
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('state-file', InputArgument::OPTIONAL, 'Path to the state file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lock = $this->lockFactory->createLock('hesabix-update', 3600);
        if (!$lock->acquire()) {
            $this->writeOutput($output, '<error>Another update process is running. Please try again later.</error>');
            $this->logger->warning('Update attempt stopped due to an existing lock.');
            return Command::FAILURE;
        }

        $this->stateFile = $input->getArgument('state-file') ?? $this->backupDir . '/update_state_' . Uuid::uuid4() . '.json';
        if (!file_exists(dirname($this->stateFile))) {
            mkdir(dirname($this->stateFile), 0755, true);
        }

        if (!file_exists($this->stateFile)) {
            file_put_contents($this->stateFile, json_encode([
                'uuid' => Uuid::uuid4()->toString(),
                'log' => '',
                'completedSteps' => [],
            ]));
        }

        $uuid = json_decode(file_get_contents($this->stateFile), true)['uuid'];

        $this->logger->info("Starting software update with UUID: $uuid in {$this->env} mode");
        $this->writeOutput($output, "Starting software update (UUID: $uuid) in {$this->env} mode");

        $state = $this->loadState($uuid);
        $state['log'] = $state['log'] ?? '';

        if ($this->isUpToDate()) {
            $this->writeOutput($output, '<info>Software is already up to date with the remote repository.</info>');
            $this->logger->info('No update needed, software is up to date.');
            $state['log'] .= "No update needed, software is up to date.\n";
            $state['completedSteps'] = ['post_update_test'];
            $this->saveState($uuid, $state, $output, 'No update needed');
            $lock->release();
            return Command::SUCCESS;
        }

        $state['completedSteps'] = $state['completedSteps'] ?? [];

        $gitHeadBefore = null;
        $cacheBackup = null;
        $dbBackup = null;
        $archiveBackup = null;

        try {
            if (!in_array('pre_checks', $state['completedSteps'])) {
                $this->preUpdateChecks($output);
                $state['completedSteps'][] = 'pre_checks';
                $this->saveState($uuid, $state, $output, 'Pre-update checks completed');
            }

            if (!in_array('archive_backup', $state['completedSteps'])) {
                $this->writeOutput($output, 'Backing up hesabixArchive...');
                $archiveBackupDir = $this->backupDir . '/' . Uuid::uuid4();
                if (!is_dir($archiveBackupDir)) {
                    mkdir($archiveBackupDir, 0755, true);
                }
                $archiveBackup = $archiveBackupDir . '/hesabixArchive_backup_' . time() . '.tar';
                $this->runProcess(['tar', '-cf', $archiveBackup, '-C', $this->rootDir, 'hesabixArchive'], $this->rootDir, $output, 3);
                $state['archiveBackup'] = $archiveBackup;
                $archiveHashBefore = $this->getDirectoryHash($this->archiveDir);
                $state['archiveHashBefore'] = $archiveHashBefore;
                $state['completedSteps'][] = 'archive_backup';
                $this->saveState($uuid, $state, $output, 'hesabixArchive backup completed');
            } else {
                $archiveBackup = $state['archiveBackup'];
                $archiveHashBefore = $state['archiveHashBefore'];
            }

            if (!in_array('git_pull', $state['completedSteps'])) {
                $this->writeOutput($output, 'Setting up tracking for master branch...');
                $this->runProcess(['git', 'branch', '--set-upstream-to=origin/master', 'master'], $this->rootDir, $output, 1);
                $this->writeOutput($output, 'Fetching latest changes from GitHub...');
                $gitHeadBefore = $this->getCurrentGitHead();
                $this->runProcess(['git', 'pull'], $this->rootDir, $output, 3);
                $state['gitHeadBefore'] = $gitHeadBefore;
                $state['completedSteps'][] = 'git_pull';
                $this->saveState($uuid, $state, $output, 'Git changes fetched');
            } else {
                $gitHeadBefore = $state['gitHeadBefore'];
            }

            if (!in_array('composer_install', $state['completedSteps'])) {
                $this->writeOutput($output, 'Installing dependencies...');
                $composerCommand = ['composer', 'install', '--optimize-autoloader'];
                if ($this->env !== 'dev') {
                    $composerCommand[] = '--no-dev';
                }
                $this->runProcess($composerCommand, $this->appDir, $output, 3, true);
                $state['completedSteps'][] = 'composer_install';
                $this->saveState($uuid, $state, $output, 'Dependencies installed');
            }

            if (!in_array('composer_install_core', $state['completedSteps'])) {
                $this->writeOutput($output, 'Installing dependencies in hesabixCore...');
                $composerCommand = ['composer', 'install', '--optimize-autoloader'];
                if ($this->env !== 'dev') {
                    $composerCommand[] = '--no-dev';
                }
                $this->runProcess($composerCommand, $this->rootDir . '/hesabixCore', $output, 3, true);
                $state['completedSteps'][] = 'composer_install_core';
                $this->saveState($uuid, $state, $output, 'Dependencies installed in hesabixCore');
            }

            if (!in_array('cache_clear', $state['completedSteps'])) {
                $this->writeOutput($output, 'Clearing cache...');
                $cacheBackupDir = $this->backupDir . '/' . Uuid::uuid4();
                if (!is_dir($cacheBackupDir)) {
                    mkdir($cacheBackupDir, 0755, true);
                }
                $cacheBackup = $cacheBackupDir . '/cache_backup_' . time();
                $this->runProcess(['cp', '-r', $this->appDir . '/var/cache', $cacheBackup], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
                $state['cacheBackup'] = $cacheBackup;
                $this->runProcess(['php', 'bin/console', 'cache:clear', "--env={$this->env}"], $this->appDir, $output, 3);
                $state['completedSteps'][] = 'cache_clear';
                $this->saveState($uuid, $state, $output, 'Cache cleared');
            } else {
                $cacheBackup = $state['cacheBackup'];
            }

            if (!in_array('db_update', $state['completedSteps'])) {
                $this->writeOutput($output, 'Updating database schema...');
                $dbBackupDir = $this->backupDir . '/' . Uuid::uuid4();
                if (!is_dir($dbBackupDir)) {
                    mkdir($dbBackupDir, 0755, true);
                }
                $dbBackup = $dbBackupDir . '/db_backup_' . time() . '.sql';
                $this->backupDatabaseToFile($dbBackup, $output);
                $state['dbBackup'] = $dbBackup;
                $this->runProcess(['php', 'bin/console', 'doctrine:schema:update', '--force', '--no-interaction'], $this->appDir, $output, 3);
                $state['completedSteps'][] = 'db_update';
                $this->saveState($uuid, $state, $output, 'Database schema updated');
            } else {
                $dbBackup = $state['dbBackup'];
            }

            if (!in_array('archive_check', $state['completedSteps'])) {
                $archiveHashAfter = $this->getDirectoryHash($this->archiveDir);
                if ($archiveHashBefore !== $archiveHashAfter) {
                    $this->writeOutput($output, 'hesabixArchive has changed, restoring from backup...');
                    $this->restoreArchive($archiveBackup);
                    $this->writeOutput($output, 'hesabixArchive restored successfully.');
                } else {
                    $this->writeOutput($output, 'hesabixArchive unchanged, no restoration needed.');
                }
                $state['completedSteps'][] = 'archive_check';
                $this->saveState($uuid, $state, $output, 'Archive check completed');
            }

            if (!in_array('npm_install', $state['completedSteps'])) {
                if (!is_dir($this->webUIDir)) {
                    throw new \RuntimeException('Frontend directory webUI does not exist at: ' . $this->webUIDir);
                }
                $this->writeOutput($output, 'Installing npm packages in webUI...');
                $this->runProcess(['npm', 'install'], $this->webUIDir, $output, 3);
                $state['completedSteps'][] = 'npm_install';
                $this->saveState($uuid, $state, $output, 'npm packages installed in webUI');
            }

            if (!in_array('npm_build', $state['completedSteps'])) {
                $this->writeOutput($output, 'Building frontend in webUI...');
                $this->runProcess(['npm', 'run', 'build-only'], $this->webUIDir, $output, 3);
                $state['completedSteps'][] = 'npm_build';
                $this->saveState($uuid, $state, $output, 'Frontend built in webUI');
            }

            if (!in_array('post_update_test', $state['completedSteps'])) {
                $this->writeOutput($output, 'Running post-update tests...');
                $this->postUpdateChecks($output);
                $state['completedSteps'][] = 'post_update_test';
                $this->saveState($uuid, $state, $output, 'Post-update tests completed');
            }

            $commitHash = $this->getCurrentVersion();
            $this->writeOutput($output, "Software updated to commit $commitHash");
            $state['commit_hash'] = $commitHash;

            $this->logger->info('Software update completed successfully!');
            $this->writeOutput($output, '<info>Software update completed successfully!</info>');
            $this->saveState($uuid, $state, $output, 'Update completed successfully');

            $this->writeOutput($output, 'Cleaning up all temporary directories...');
            $this->cleanupAllTempDirectories();

            $lock->release();
            return Command::SUCCESS;
        } catch (ProcessFailedException $e) {
            $errorMessage = $e->getProcess()->getErrorOutput() ?: $e->getMessage();
            $this->logger->error('Process failed: ' . $errorMessage);
            $this->writeOutput($output, '<error>Process error: ' . $errorMessage . '</error>');
            $this->rollback($gitHeadBefore, $cacheBackup, $dbBackup, $archiveBackup, $output);
            $state['error'] = $errorMessage;
            $this->saveState($uuid, $state, $output, 'Process failed and rollback performed');
            $lock->release();
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->logger->error('Update failed: ' . $e->getMessage());
            $this->writeOutput($output, '<error>Error: ' . $e->getMessage() . '</error>');
            $this->rollback($gitHeadBefore, $cacheBackup, $dbBackup, $archiveBackup, $output);
            $state['error'] = $e->getMessage();
            $this->saveState($uuid, $state, $output, 'Update failed and rollback performed');
            $lock->release();
            return Command::FAILURE;
        } finally {
            $lock->release();
        }
    }

    private function writeOutput(OutputInterface $output, string $message): void
    {
        $output->writeln($message);
        if (ob_get_level() > 0) {
            ob_flush();
            flush();
        }
    }

    private function runProcess(array $command, string $workingDir, OutputInterface $output, int $retries = 3, bool $isComposer = false): void
    {
        $attempt = 0;
        while ($attempt < $retries) {
            try {
                $env = [];
                if ($isComposer) {
                    $env = [
                        'COMPOSER_ALLOW_SUPERUSER' => '1',
                        'HOME' => '/var/www',
                        'COMPOSER_HOME' => '/var/www/.composer',
                    ];
                }

                $process = new Process($command, $workingDir, $env);
                $process->setTimeout(3600);
                if ($output->isVerbose()) {
                    $process->mustRun(function ($type, $buffer) use ($output) {
                        $this->writeOutput($output, $buffer);
                    });
                } else {
                    $process->mustRun();
                    $this->writeOutput($output, $process->getOutput());
                }
                $this->logger->info('Command executed successfully: ' . implode(' ', $command));
                return;
            } catch (ProcessFailedException $e) {
                $attempt++;
                $errorMessage = $e->getProcess()->getErrorOutput() ?: $e->getMessage();
                $this->logger->warning("Attempt $attempt failed for " . implode(' ', $command) . ": $errorMessage");
                $this->writeOutput($output, "<comment>Attempt $attempt failed: $errorMessage</comment>");
                if ($attempt === $retries) {
                    throw new \RuntimeException('Command "' . implode(' ', $command) . '" failed after ' . $retries . ' attempts: ' . $errorMessage);
                }
                sleep(5);
            }
        }
    }

    private function getCurrentVersion(): string
    {
        return $this->getCurrentGitHead();
    }

    private function getCurrentGitHead(): string
    {
        $process = new Process(['git', 'rev-parse', 'HEAD'], $this->rootDir);
        $process->run();
        if (!$process->isSuccessful()) {
            $this->logger->warning('Failed to get current Git HEAD: ' . $process->getErrorOutput());
            return 'unknown';
        }
        return trim($process->getOutput());
    }

    private function isUpToDate(): bool
    {
        try {
            $this->runProcess(['git', 'fetch', 'origin'], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
            $process = new Process(['git', 'status', '-uno'], $this->rootDir);
            $process->run();
            $status = $process->getOutput();
            return strpos($status, 'Your branch is up to date') !== false;
        } catch (\Exception $e) {
            $this->logger->warning('Failed to check if software is up to date: ' . $e->getMessage());
            return false;
        }
    }

    private function backupDatabaseToFile(string $backupFile, OutputInterface $output): void
    {
        $backupDir = dirname($backupFile);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $envConfig = file_exists($this->appDir . '/.env.local.php') ? require $this->appDir . '/.env.local.php' : [];
        $dbUrl = $envConfig['DATABASE_URL'] ?? getenv('DATABASE_URL');
        if (!$dbUrl) {
            throw new \RuntimeException('Could not determine DATABASE_URL from .env.local.php or environment.');
        }

        $urlParts = parse_url($dbUrl);
        $dbHost = $urlParts['host'] ?? 'localhost';
        $dbUser = $urlParts['user'] ?? 'root';
        $dbPass = $urlParts['pass'] ?? '';
        $dbName = ltrim($urlParts['path'] ?? '', '/');
        $dbScheme = $urlParts['scheme'] ?? 'mysql';

        if (in_array($dbScheme, ['mysql', 'mariadb'])) {
            $command = [
                'mysqldump',
                '-h', $dbHost,
                '-u', $dbUser,
                '-p' . $dbPass,
                $dbName,
                '--result-file=' . $backupFile
            ];
        } elseif ($dbScheme === 'pgsql') {
            $command = [
                'pg_dump',
                '-h', $dbHost,
                '-U', $dbUser,
                '-d', $dbName,
                '--no-owner',
                '--no-privileges',
                '-f', $backupFile
            ];
            if ($dbPass) {
                putenv("PGPASSWORD=$dbPass");
            }
        } else {
            throw new \RuntimeException("Unsupported database scheme: $dbScheme.");
        }

        $process = new Process($command, $this->rootDir);
        $process->setTimeout(3600);
        $process->mustRun();

        if (!file_exists($backupFile) || filesize($backupFile) === 0) {
            throw new \RuntimeException('Database backup creation failed.');
        }
        $this->logger->info("Database backup created at: $backupFile (scheme: $dbScheme)");
    }

    private function restoreArchive(string $backupFile): void
    {
        $this->runProcess(['rm', '-rf', $this->archiveDir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        $this->runProcess(['mkdir', $this->archiveDir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        $this->runProcess(['tar', '-xf', $backupFile, '-C', $this->rootDir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        if (!is_dir($this->archiveDir)) {
            throw new \RuntimeException('Restoration of hesabixArchive from tar backup failed.');
        }
    }

    private function getDirectoryHash(string $dir): string
    {
        if (!is_dir($dir)) {
            return '';
        }
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }
        sort($files);
        $hash = '';
        foreach ($files as $file) {
            $hash .= md5_file($file);
        }
        return md5($hash);
    }

    private function rollback(?string $gitHeadBefore, ?string $cacheBackup, ?string $dbBackup, ?string $archiveBackup, OutputInterface $output): void
    {
        $this->writeOutput($output, 'Rolling back changes...');

        if ($gitHeadBefore) {
            try {
                $this->runProcess(['git', 'reset', '--hard', $gitHeadBefore], $this->rootDir, $output);
                $this->logger->info('Git rolled back to ' . $gitHeadBefore);
            } catch (\Exception $e) {
                $this->logger->error('Git rollback failed: ' . $e->getMessage());
            }
        }

        if ($cacheBackup) {
            try {
                $this->runProcess(['rm', '-rf', $this->appDir . '/var/cache'], $this->rootDir, $output);
                $this->runProcess(['cp', '-r', $cacheBackup, $this->appDir . '/var/cache'], $this->rootDir, $output);
                $this->logger->info('Cache restored');
            } catch (\Exception $e) {
                $this->logger->error('Cache rollback failed: ' . $e->getMessage());
            }
        }

        if ($dbBackup) {
            try {
                $envConfig = file_exists($this->appDir . '/.env.local.php') ? require $this->appDir . '/.env.local.php' : [];
                $dbUrl = $envConfig['DATABASE_URL'] ?? getenv('DATABASE_URL');
                if (!$dbUrl) {
                    throw new \RuntimeException('Could not determine DATABASE_URL for rollback.');
                }

                $urlParts = parse_url($dbUrl);
                $dbHost = $urlParts['host'] ?? 'localhost';
                $dbUser = $urlParts['user'] ?? 'root';
                $dbPass = $urlParts['pass'] ?? '';
                $dbName = ltrim($urlParts['path'] ?? '', '/');
                $dbScheme = $urlParts['scheme'] ?? 'mysql';

                if (in_array($dbScheme, ['mysql', 'mariadb'])) {
                    $command = [
                        'mysql',
                        '-h', $dbHost,
                        '-u', $dbUser,
                        '-p' . $dbPass,
                        $dbName
                    ];
                    $process = new Process($command, $this->rootDir);
                    $process->setInput(file_get_contents($dbBackup));
                } elseif ($dbScheme === 'pgsql') {
                    $command = [
                        'psql',
                        '-h', $dbHost,
                        '-U', $dbUser,
                        '-d', $dbName,
                        '-f', $dbBackup
                    ];
                    if ($dbPass) {
                        putenv("PGPASSWORD=$dbPass");
                    }
                    $process = new Process($command, $this->rootDir);
                } else {
                    throw new \RuntimeException("Unsupported database scheme for rollback: $dbScheme.");
                }

                $process->setTimeout(3600);
                $process->mustRun();
                $this->logger->info('Database restored');
            } catch (\Exception $e) {
                $this->logger->error('Database rollback failed: ' . $e->getMessage());
            }
        }

        if ($archiveBackup) {
            try {
                $this->restoreArchive($archiveBackup);
                $this->logger->info('hesabixArchive restored');
            } catch (\Exception $e) {
                $this->logger->error('Archive rollback failed: ' . $e->getMessage());
            }
        }
    }

    private function cleanupAllTempDirectories(): void
    {
        $directories = glob($this->backupDir . '/*', GLOB_ONLYDIR);
        $protectedDirs = ['databasefiles', 'versions'];

        foreach ($directories as $dir) {
            $dirName = basename($dir);
            if (!in_array($dirName, $protectedDirs)) {
                $this->logger->info("Removing temporary directory: $dir");
                $this->runProcess(['rm', '-rf', $dir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
            }
        }
    }

    private function loadState(string $uuid): array
    {
        if (file_exists($this->stateFile)) {
            $state = json_decode(file_get_contents($this->stateFile), true);
            if ($state && $state['uuid'] === $uuid) {
                return $state;
            }
        }
        return ['uuid' => $uuid, 'log' => '', 'completedSteps' => []];
    }

    private function saveState(string $uuid, array $state, OutputInterface $output, string $message): void
    {
        $state['uuid'] = $uuid;
        $state['log'] = ($state['log'] ?? '') . ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL ? $message . "\n" : '');
        file_put_contents($this->stateFile, json_encode($state, JSON_PRETTY_PRINT));
        $this->logger->debug('State saved at: ' . $this->stateFile);
    }

    private function preUpdateChecks(OutputInterface $output): void
    {
        $this->writeOutput($output, 'Running pre-update checks...');
        $this->runProcess(['git', '--version'], $this->rootDir, $output, 1);
        $this->runProcess(['composer', '--version'], $this->rootDir, $output, 1, true);
        $this->runProcess(['php', '-v'], $this->rootDir, $output, 1);
        $this->runProcess(['npm', '--version'], $this->rootDir, $output, 1);

        $process = new Process(['whoami'], $this->rootDir);
        $process->run();
        $user = trim($process->getOutput());
        if ($user === 'root') {
            $this->writeOutput($output, '<warning>Running the command as root can be risky. It is recommended to use a non-root user.</warning>');
            $this->logger->warning('Command executed as root user.');
        }

        $this->writeOutput($output, 'Pre-update checks completed successfully.');
    }

    private function postUpdateChecks(OutputInterface $output): void
    {
        $this->writeOutput($output, 'Running post-update checks...');
        
        // Clear cache completely to avoid stale cache issues
        $this->writeOutput($output, 'Clearing all caches for the current environment...');
        $this->runProcess(['rm', '-rf', $this->appDir . '/var/cache/*'], $this->appDir, new \Symfony\Component\Console\Output\NullOutput());
        $this->runProcess(['php', 'bin/console', 'cache:clear', "--env={$this->env}"], $this->appDir, $output, 3);
        
        // Clear cache pools
        $this->runProcess(['php', 'bin/console', 'cache:pool:clear', 'cache.global_clearer'], $this->appDir, $output, 1);
        
        $this->writeOutput($output, 'Post-update checks completed successfully.');
    }
}
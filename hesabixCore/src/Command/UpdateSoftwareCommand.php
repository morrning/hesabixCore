<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
    description: 'Updates the software by pulling from GitHub, clearing cache, and updating the database.'
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

    public function __construct(LoggerInterface $logger, LockFactory $lockFactory, ParameterBagInterface $params)
    {
        $this->logger = $logger;
        $this->lockFactory = $lockFactory;
        $this->params = $params;
        $this->appDir = dirname(__DIR__, 2);
        $this->rootDir = dirname($this->appDir);
        $this->archiveDir = $this->rootDir . '/hesabixArchive';
        $this->backupDir = $this->rootDir . '/hesabixBackup';
        $this->stateFile = $this->backupDir . '/' . Uuid::uuid4() . '/update_state.json';
        $this->env = getenv('APP_ENV') ?: 'prod';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lock = $this->lockFactory->createLock('software-update', 3600);
        if (!$lock->acquire()) {
            $this->writeOutput($output, '<error>Another update process is currently running. Please try again later.</error>');
            $this->logger->warning('Update attempt blocked due to existing lock.');
            return Command::FAILURE;
        }

        $uuid = Uuid::uuid4()->toString();
        $this->logger->info("Starting software update with UUID: $uuid in {$this->env} mode");
        $this->writeOutput($output, "Starting software update (UUID: $uuid)...");

        if ($this->isUpToDate()) {
            $this->writeOutput($output, '<info>The software is already up to date with the remote repository.</info>');
            $this->logger->info('No update needed, software is up to date.');
            $lock->release();
            return Command::SUCCESS;
        }

        $stateDir = dirname($this->stateFile);
        if (!is_dir($stateDir)) {
            $this->logger->debug("Creating state directory: $stateDir");
            if (!mkdir($stateDir, 0755, true) && !is_dir($stateDir)) {
                throw new \RuntimeException("Failed to create state directory: $stateDir");
            }
        }

        $state = $this->loadState($uuid);
        $state['log'] = $state['log'] ?? '';
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
                    $this->logger->debug("Creating archive backup directory: $archiveBackupDir");
                    if (!mkdir($archiveBackupDir, 0755, true) && !is_dir($archiveBackupDir)) {
                        throw new \RuntimeException("Failed to create archive backup directory: $archiveBackupDir");
                    }
                }
                $archiveBackup = $archiveBackupDir . '/hesabixArchive_backup_' . time() . '.tar';
                $this->runProcess(['tar', '-cf', $archiveBackup, '-C', $this->rootDir, 'hesabixArchive'], $this->rootDir, $output, 3);
                $state['archiveBackup'] = $archiveBackup;
                $archiveHashBefore = $this->getDirectoryHash($this->archiveDir);
                $state['archiveHashBefore'] = $archiveHashBefore;
                $state['completedSteps'][] = 'archive_backup';
                $this->saveState($uuid, $state, $output, 'hesabixArchive backed up');
            } else {
                $archiveBackup = $state['archiveBackup'];
                $archiveHashBefore = $state['archiveHashBefore'];
            }

            if (!in_array('git_pull', $state['completedSteps'])) {
                $this->writeOutput($output, 'Pulling latest changes from GitHub...');
                $gitHeadBefore = $this->getCurrentGitHead();
                $this->runProcess(['git', 'pull'], $this->rootDir, $output, 3);
                $state['gitHeadBefore'] = $gitHeadBefore;
                $state['completedSteps'][] = 'git_pull';
                $this->saveState($uuid, $state, $output, 'Git pull completed');
            } else {
                $gitHeadBefore = $state['gitHeadBefore'];
            }

            if (!in_array('composer_install', $state['completedSteps'])) {
                $this->writeOutput($output, 'Installing dependencies...');
                $composerCommand = ['composer', 'install', '--optimize-autoloader'];
                if ($this->env !== 'dev') {
                    $composerCommand[] = '--no-dev';
                }
                $this->runProcess($composerCommand, $this->appDir, $output, 3);
                $state['completedSteps'][] = 'composer_install';
                $this->saveState($uuid, $state, $output, 'Dependencies installed');
            }

            if (!in_array('cache_clear', $state['completedSteps'])) {
                $this->writeOutput($output, 'Clearing cache...');
                $cacheBackupDir = $this->backupDir . '/' . Uuid::uuid4();
                if (!is_dir($cacheBackupDir)) {
                    $this->logger->debug("Creating cache backup directory: $cacheBackupDir");
                    if (!mkdir($cacheBackupDir, 0755, true) && !is_dir($cacheBackupDir)) {
                        throw new \RuntimeException("Failed to create cache backup directory: $cacheBackupDir");
                    }
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
                    $this->logger->debug("Creating database backup directory: $dbBackupDir");
                    if (!mkdir($dbBackupDir, 0755, true) && !is_dir($dbBackupDir)) {
                        throw new \RuntimeException("Failed to create database backup directory: $dbBackupDir");
                    }
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
                    $this->writeOutput($output, 'hesabixArchive unchanged, no restore needed.');
                }
                $state['completedSteps'][] = 'archive_check';
                $this->saveState($uuid, $state, $output, 'Archive check completed');
            }

            if (!in_array('post_update_test', $state['completedSteps'])) {
                $this->writeOutput($output, 'Running post-update tests...');
                $this->postUpdateChecks($output);
                $state['completedSteps'][] = 'post_update_test';
                $this->saveState($uuid, $state, $output, 'Post-update tests completed');
            }

            $commitHash = $this->getCurrentVersion();
            $this->writeOutput($output, "Software updated to commit: $commitHash");
            $state['commit_hash'] = $commitHash;

            $this->logger->info('Software update completed successfully!');
            $this->writeOutput($output, '<info>Software update completed successfully!</info>');
            $this->saveState($uuid, $state, $output, 'Update completed successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Update failed: ' . $e->getMessage());
            $this->writeOutput($output, '<error>An error occurred: ' . $e->getMessage() . '</error>');
            $this->rollback($gitHeadBefore, $cacheBackup, $dbBackup, $archiveBackup, $output);
            $this->writeOutput($output, '<comment>Update process aborted and rolled back.</comment>');
            $state['error'] = $e->getMessage();
            $this->saveState($uuid, $state, $output, 'Update failed and rolled back');
            return Command::FAILURE;
        } finally {
            $this->cleanupBackups($cacheBackup, $dbBackup, $archiveBackup);
            $lock->release();
            $stateDir = dirname($this->stateFile);
            if (is_dir($stateDir)) {
                $this->logger->info('Cleaning up state directory: ' . $stateDir);
                $this->runProcess(['rm', '-rf', $stateDir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
            }
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

    private function runProcess(array $command, string $workingDir, OutputInterface $output, int $retries = 3): void
    {
        $attempt = 0;
        while ($attempt < $retries) {
            try {
                $process = new Process($command, $workingDir);
                $process->setTimeout(3600);
                if ($output->isVerbose()) {
                    $process->mustRun(function ($type, $buffer) use ($output) {
                        $this->writeOutput($output, $buffer);
                    });
                } else {
                    $process->mustRun();
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

    private function isUpToDate(): bool
    {
        try {
            $localHeadProcess = new Process(['git', 'rev-parse', 'HEAD'], $this->rootDir);
            $localHeadProcess->run();
            if (!$localHeadProcess->isSuccessful()) {
                $this->logger->warning('Failed to get local Git HEAD: ' . $localHeadProcess->getErrorOutput());
                return false;
            }
            $localHead = trim($localHeadProcess->getOutput());

            $remoteHeadProcess = new Process(['git', 'ls-remote', 'origin', 'HEAD'], $this->rootDir);
            $remoteHeadProcess->run();
            if (!$remoteHeadProcess->isSuccessful()) {
                $this->logger->warning('Failed to get remote Git HEAD: ' . $remoteHeadProcess->getErrorOutput());
                return false;
            }
            $remoteOutput = explode("\t", trim($remoteHeadProcess->getOutput()));
            $remoteHead = $remoteOutput[0] ?? '';

            return $localHead === $remoteHead;
        } catch (\Exception $e) {
            $this->logger->warning('Error checking Git status: ' . $e->getMessage());
            return false;
        }
    }

    private function preUpdateChecks(OutputInterface $output): void
    {
        $this->writeOutput($output, 'Running pre-update checks...');
        $this->runProcess(['git', 'fetch'], $this->rootDir, $output);
        $this->writeOutput($output, 'Git repository accessible.');
        $this->runProcess(['php', 'bin/console', 'doctrine:query:sql', 'SELECT 1'], $this->appDir, $output);
        $this->writeOutput($output, 'Database connection OK.');
    }

    private function postUpdateChecks(OutputInterface $output): void
    {
        $this->writeOutput($output, 'Running post-update tests...');
        $this->runProcess(['php', 'bin/console', 'cache:warmup', "--env={$this->env}"], $this->appDir, $output);
        $this->writeOutput($output, 'Application tested and warmed up successfully.');
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

    private function backupDatabaseToFile(string $backupFile, OutputInterface $output): void
    {
        $backupDir = dirname($backupFile);
        if (!is_dir($backupDir)) {
            $this->logger->debug("Creating database backup directory: $backupDir");
            if (!mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
                throw new \RuntimeException("Failed to create database backup directory: $backupDir");
            }
        }

        $dbUrl = $this->params->get('database_url') ?? getenv('DATABASE_URL');
        if (!$dbUrl) {
            throw new \RuntimeException('Could not determine DATABASE_URL.');
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
            throw new \RuntimeException('Failed to create database backup.');
        }
        $this->logger->info("Database backup created at: $backupFile (scheme: $dbScheme)");
    }

    private function restoreArchive(string $backupFile): void
    {
        $this->runProcess(['rm', '-rf', $this->archiveDir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        $this->runProcess(['mkdir', $this->archiveDir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        $this->runProcess(['tar', '-xf', $backupFile, '-C', $this->rootDir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        if (!is_dir($this->archiveDir)) {
            throw new \RuntimeException('Failed to restore hesabixArchive from tar backup.');
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
                $this->logger->info('Cache rolled back');
            } catch (\Exception $e) {
                $this->logger->error('Cache rollback failed: ' . $e->getMessage());
            }
        }

        if ($dbBackup) {
            try {
                $dbUrl = $this->params->get('database_url') ?? getenv('DATABASE_URL');
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
                $this->logger->info('Database rolled back');
            } catch (\Exception $e) {
                $this->logger->error('Database rollback failed: ' . $e->getMessage());
            }
        }

        if ($archiveBackup) {
            try {
                $this->restoreArchive($archiveBackup);
                $this->logger->info('hesabixArchive rolled back');
            } catch (\Exception $e) {
                $this->logger->error('Archive rollback failed: ' . $e->getMessage());
            }
        }
    }

    private function cleanupBackups(?string $cacheBackup, ?string $dbBackup, ?string $archiveBackup): void
    {
        if ($cacheBackup && is_dir($cacheBackup)) {
            $this->runProcess(['rm', '-rf', $cacheBackup], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        }
        if ($dbBackup && file_exists($dbBackup)) {
            unlink($dbBackup);
        }
        if ($archiveBackup && file_exists($archiveBackup)) {
            unlink($archiveBackup);
        }
    }

    private function loadState(string $uuid): array
    {
        $file = $this->stateFile;
        if (file_exists($file)) {
            $state = json_decode(file_get_contents($file), true);
            if ($state['uuid'] === $uuid) {
                return $state;
            }
        }
        return ['uuid' => $uuid, 'log' => '', 'completedSteps' => []];
    }

    private function saveState(string $uuid, array $state, OutputInterface $output, string $message): void
    {
        $state['uuid'] = $uuid;
        $state['log'] .= $output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL ? $message . "\n" : '';
        $stateDir = dirname($this->stateFile);
        if (!is_dir($stateDir)) {
            $this->logger->debug("Creating state directory: $stateDir");
            if (!mkdir($stateDir, 0755, true) && !is_dir($stateDir)) {
                throw new \RuntimeException("Failed to create state directory: $stateDir");
            }
        }
        file_put_contents($this->stateFile, json_encode($state, JSON_PRETTY_PRINT));
        $this->logger->debug('State saved to ' . $this->stateFile);
    }
}
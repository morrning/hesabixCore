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

#[AsCommand(
    name: 'app:update-software',
    description: 'Updates the software by pulling from GitHub, clearing cache, and updating the database.'
)]
class UpdateSoftwareCommand extends Command
{
    private LoggerInterface $logger;
    private LockFactory $lockFactory;
    private string $rootDir;
    private string $appDir;
    private string $archiveDir;
    private string $backupDir;
    private string $stateFile;

    public function __construct(LoggerInterface $logger, LockFactory $lockFactory)
    {
        $this->logger = $logger;
        $this->lockFactory = $lockFactory;
        $this->appDir = dirname(__DIR__, 2); // src/Command -> hesabixCore
        $this->rootDir = dirname($this->appDir); // hesabixCore -> parent dir
        $this->archiveDir = $this->rootDir . '/hesabixArchive';
        $this->backupDir = $this->rootDir . '/../backup';
        $this->stateFile = $this->backupDir . '/update_state.json';
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
        $this->logger->info("Starting software update with UUID: $uuid");
        $this->writeOutput($output, "Starting software update (UUID: $uuid)...");

        // چک کردن اینکه آیا آپدیت لازم است
        if ($this->isUpToDate()) {
            $this->writeOutput($output, '<info>The software is already up to date with the remote repository.</info>');
            $this->logger->info('No update needed, software is up to date.');
            $lock->release();
            return Command::SUCCESS;
        }

        // ادامه فرآیند فقط در صورتی که آپدیت لازم باشه
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        $state = $this->loadState($uuid);
        $state['log'] = $state['log'] ?? '';
        $state['completedSteps'] = $state['completedSteps'] ?? [];

        $gitHeadBefore = null;
        $cacheBackup = null;
        $dbBackup = null;
        $archiveBackup = null;

        try {
            // بقیه کد بدون تغییر ...
        } catch (\Exception $e) {
            // بقیه کد بدون تغییر ...
        } finally {
            $this->cleanupBackups($cacheBackup, $dbBackup, $archiveBackup);
            $lock->release();
            if (file_exists($this->stateFile)) {
                unlink($this->stateFile);
            }
        }
    }

    // متد جدید برای چک کردن به‌روز بودن
    private function isUpToDate(): bool
    {
        // گرفتن HEAD فعلی مخزن محلی
        $localHeadProcess = new Process(['git', 'rev-parse', 'HEAD'], $this->rootDir);
        $localHeadProcess->run();
        if (!$localHeadProcess->isSuccessful()) {
            throw new \RuntimeException('Failed to get local Git HEAD: ' . $localHeadProcess->getErrorOutput());
        }
        $localHead = trim($localHeadProcess->getOutput());

        // گرفتن HEAD مخزن ریموت
        $remoteHeadProcess = new Process(['git', 'ls-remote', 'origin', 'HEAD'], $this->rootDir);
        $remoteHeadProcess->run();
        if (!$remoteHeadProcess->isSuccessful()) {
            throw new \RuntimeException('Failed to get remote Git HEAD: ' . $remoteHeadProcess->getErrorOutput());
        }
        $remoteOutput = explode("\t", trim($remoteHeadProcess->getOutput()));
        $remoteHead = $remoteOutput[0] ?? '';

        // مقایسه HEAD محلی و ریموت
        return $localHead === $remoteHead;
    }

    private function writeOutput(OutputInterface $output, string $message): void
    {
        $output->writeln($message);
        if (PHP_SAPI === 'cli' && $output instanceof \Symfony\Component\Console\Output\StreamOutput && $output->getStream()) {
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
                $process->mustRun(function ($type, $buffer) use ($output) {
                    $this->writeOutput($output, $buffer);
                });
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

    private function preUpdateChecks(OutputInterface $output): void
    {
        $this->writeOutput($output, 'Running pre-update checks...');
        $this->runProcess(['git', 'fetch'], $this->rootDir, $output);
        $this->writeOutput($output, 'Git repository accessible.');
        $this->runProcess(['php', 'bin/console', 'doctrine:query:sql', 'SELECT 1'], $this->appDir, $output);
        $this->writeOutput($output, 'Database connection OK.');
    }

    private function postUpdateTest(OutputInterface $output): void
    {
        $this->writeOutput($output, 'Running post-update tests...');
        $this->runProcess(['php', 'bin/console', 'cache:warmup', '--env=prod'], $this->appDir, $output);
        $this->writeOutput($output, 'Application tested and warmed up successfully.');
    }

    private function getPackageVersion(): string
    {
        $packageJson = json_decode(file_get_contents($this->appDir . '/package.json'), true);
        return $packageJson['version'] ?? 'unknown';
    }

    private function getCurrentGitHead(): string
    {
        $process = new Process(['git', 'rev-parse', 'HEAD'], $this->rootDir);
        $process->run();
        return trim($process->getOutput());
    }

    private function backupCache(string $cacheDir): string
    {
        $backupDir = $this->backupDir . '/cache_backup_' . time();
        $this->runProcess(['cp', '-r', $cacheDir, $backupDir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        return $backupDir;
    }

    private function backupDatabase(): string
    {
        $backupFile = $this->backupDir . '/db_backup_' . time() . '.sql';
        $this->runProcess(['php', 'bin/console', 'dbal:database:dump', '--file=' . $backupFile], $this->appDir, new \Symfony\Component\Console\Output\NullOutput());
        return $backupFile;
    }

    private function backupArchive(): string
    {
        $tarFile = $this->backupDir . '/hesabixArchive_backup_' . time() . '.tar';
        $this->runProcess(['tar', '-cf', $tarFile, '-C', $this->rootDir, 'hesabixArchive'], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        if (!file_exists($tarFile)) {
            throw new \RuntimeException('Failed to create tar backup of hesabixArchive.');
        }
        return $tarFile;
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
                $this->runProcess(['php', 'bin/console', 'dbal:database:import', $dbBackup], $this->appDir, $output);
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

    private function saveState(string $uuid, array $state): void
    {
        $state['uuid'] = $uuid;
        $state['log'] .= $this->getOutput()->getOutput() . "\n";
        file_put_contents($this->stateFile, json_encode($state));
    }
}
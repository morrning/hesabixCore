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
    description: 'نرم‌افزار را با دریافت تغییرات از GitHub، پاک کردن کش، به‌روزرسانی دیتابیس و ساخت فرانت‌اند به‌روز می‌کند.'
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
        $this->logger->info("محیط شناسایی شد: " . $this->env);
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('state-file', InputArgument::OPTIONAL, 'مسیر فایل وضعیت');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lock = $this->lockFactory->createLock('hesabix-update', 3600);
        if (!$lock->acquire()) {
            $this->writeOutput($output, '<error>یک فرآیند به‌روزرسانی دیگر در حال اجراست. لطفاً بعداً تلاش کنید.</error>');
            $this->logger->warning('تلاش برای به‌روزرسانی به دلیل قفل موجود متوقف شد.');
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

        $this->logger->info("شروع به‌روزرسانی نرم‌افزار با UUID: $uuid در حالت {$this->env}");
        $this->writeOutput($output, "شروع به‌روزرسانی نرم‌افزار (UUID: $uuid) در حالت {$this->env}");

        $state = $this->loadState($uuid);
        $state['log'] = $state['log'] ?? '';

        if ($this->isUpToDate()) {
            $this->writeOutput($output, '<info>نرم‌افزار از قبل با مخزن ریموت به‌روز است.</info>');
            $this->logger->info('به‌روزرسانی لازم نیست، نرم‌افزار به‌روز است.');
            $state['log'] .= "به‌روزرسانی لازم نیست، نرم‌افزار به‌روز است.\n";
            $state['completedSteps'] = ['post_update_test'];
            $this->saveState($uuid, $state, $output, 'به‌روزرسانی لازم نیست');
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
                $this->saveState($uuid, $state, $output, 'بررسی‌های پیش از به‌روزرسانی تکمیل شد');
            }

            if (!in_array('archive_backup', $state['completedSteps'])) {
                $this->writeOutput($output, 'در حال بکاپ‌گیری از hesabixArchive...');
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
                $this->saveState($uuid, $state, $output, 'بکاپ hesabixArchive انجام شد');
            } else {
                $archiveBackup = $state['archiveBackup'];
                $archiveHashBefore = $state['archiveHashBefore'];
            }

            if (!in_array('git_pull', $state['completedSteps'])) {
                $this->writeOutput($output, 'تنظیم ردیابی برای شاخه master...');
                $this->runProcess(['git', 'branch', '--set-upstream-to=origin/master', 'master'], $this->rootDir, $output, 1);
                $this->writeOutput($output, 'دریافت آخرین تغییرات از GitHub...');
                $gitHeadBefore = $this->getCurrentGitHead();
                $this->runProcess(['git', 'pull'], $this->rootDir, $output, 3);
                $state['gitHeadBefore'] = $gitHeadBefore;
                $state['completedSteps'][] = 'git_pull';
                $this->saveState($uuid, $state, $output, 'دریافت تغییرات Git تکمیل شد');
            } else {
                $gitHeadBefore = $state['gitHeadBefore'];
            }

            if (!in_array('composer_install', $state['completedSteps'])) {
                $this->writeOutput($output, 'نصب وابستگی‌ها...');
                $composerCommand = ['composer', 'install', '--optimize-autoloader'];
                if ($this->env !== 'dev') {
                    $composerCommand[] = '--no-dev';
                }
                $this->runProcess($composerCommand, $this->appDir, $output, 3, true);
                $state['completedSteps'][] = 'composer_install';
                $this->saveState($uuid, $state, $output, 'وابستگی‌ها نصب شدند');
            }

            if (!in_array('composer_install_core', $state['completedSteps'])) {
                $this->writeOutput($output, 'نصب وابستگی‌ها در hesabixCore...');
                $composerCommand = ['composer', 'install', '--optimize-autoloader'];
                if ($this->env !== 'dev') {
                    $composerCommand[] = '--no-dev';
                }
                $this->runProcess($composerCommand, $this->rootDir . '/hesabixCore', $output, 3, true);
                $state['completedSteps'][] = 'composer_install_core';
                $this->saveState($uuid, $state, $output, 'وابستگی‌ها در hesabixCore نصب شدند');
            }

            if (!in_array('cache_clear', $state['completedSteps'])) {
                $this->writeOutput($output, 'پاک کردن کش...');
                $cacheBackupDir = $this->backupDir . '/' . Uuid::uuid4();
                if (!is_dir($cacheBackupDir)) {
                    mkdir($cacheBackupDir, 0755, true);
                }
                $cacheBackup = $cacheBackupDir . '/cache_backup_' . time();
                $this->runProcess(['cp', '-r', $this->appDir . '/var/cache', $cacheBackup], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
                $state['cacheBackup'] = $cacheBackup;
                $this->runProcess(['php', 'bin/console', 'cache:clear', "--env={$this->env}"], $this->appDir, $output, 3);
                $state['completedSteps'][] = 'cache_clear';
                $this->saveState($uuid, $state, $output, 'کش پاک شد');
            } else {
                $cacheBackup = $state['cacheBackup'];
            }

            if (!in_array('db_update', $state['completedSteps'])) {
                $this->writeOutput($output, 'به‌روزرسانی طرح دیتابیس...');
                $dbBackupDir = $this->backupDir . '/' . Uuid::uuid4();
                if (!is_dir($dbBackupDir)) {
                    mkdir($dbBackupDir, 0755, true);
                }
                $dbBackup = $dbBackupDir . '/db_backup_' . time() . '.sql';
                $this->backupDatabaseToFile($dbBackup, $output);
                $state['dbBackup'] = $dbBackup;
                $this->runProcess(['php', 'bin/console', 'doctrine:schema:update', '--force', '--no-interaction'], $this->appDir, $output, 3);
                $state['completedSteps'][] = 'db_update';
                $this->saveState($uuid, $state, $output, 'طرح دیتابیس به‌روز شد');
            } else {
                $dbBackup = $state['dbBackup'];
            }

            if (!in_array('archive_check', $state['completedSteps'])) {
                $archiveHashAfter = $this->getDirectoryHash($this->archiveDir);
                if ($archiveHashBefore !== $archiveHashAfter) {
                    $this->writeOutput($output, 'آرشیو hesabix تغییر کرده، بازگردانی از بکاپ...');
                    $this->restoreArchive($archiveBackup);
                    $this->writeOutput($output, 'آرشیو hesabix با موفقیت بازگردانی شد.');
                } else {
                    $this->writeOutput($output, 'آرشیو hesabix بدون تغییر، نیازی به بازگردانی نیست.');
                }
                $state['completedSteps'][] = 'archive_check';
                $this->saveState($uuid, $state, $output, 'بررسی آرشیو تکمیل شد');
            }

            if (!in_array('npm_install', $state['completedSteps'])) {
                if (!is_dir($this->webUIDir)) {
                    throw new \RuntimeException('پوشه فرانت‌اند webUI در مسیر وجود ندارد: ' . $this->webUIDir);
                }
                $this->writeOutput($output, 'نصب پکیج‌های npm در webUI...');
                $this->runProcess(['npm', 'install'], $this->webUIDir, $output, 3);
                $state['completedSteps'][] = 'npm_install';
                $this->saveState($uuid, $state, $output, 'پکیج‌های npm در webUI نصب شدند');
            }

            if (!in_array('npm_build', $state['completedSteps'])) {
                $this->writeOutput($output, 'ساخت فرانت‌اند در webUI...');
                $this->runProcess(['npm', 'run', 'build'], $this->webUIDir, $output, 3);
                $state['completedSteps'][] = 'npm_build';
                $this->saveState($uuid, $state, $output, 'فرانت‌اند در webUI ساخته شد');
            }

            if (!in_array('post_update_test', $state['completedSteps'])) {
                $this->writeOutput($output, 'اجرای تست‌های پس از به‌روزرسانی...');
                $this->postUpdateChecks($output);
                $state['completedSteps'][] = 'post_update_test';
                $this->saveState($uuid, $state, $output, 'تست‌های پس از به‌روزرسانی تکمیل شد');
            }

            $commitHash = $this->getCurrentVersion();
            $this->writeOutput($output, "نرم‌افزار به کمیت $commitHash به‌روز شد");
            $state['commit_hash'] = $commitHash;

            $this->logger->info('به‌روزرسانی نرم‌افزار با موفقیت انجام شد!');
            $this->writeOutput($output, '<info>به‌روزرسانی نرم‌افزار با موفقیت انجام شد!</info>');
            $this->saveState($uuid, $state, $output, 'به‌روزرسانی با موفقیت تکمیل شد');

            $this->writeOutput($output, 'پاکسازی تمام پوشه‌های موقت...');
            $this->cleanupAllTempDirectories();

            $lock->release();
            return Command::SUCCESS;
        } catch (ProcessFailedException $e) {
            $errorMessage = $e->getProcess()->getErrorOutput() ?: $e->getMessage();
            $this->logger->error('فرآیند ناموفق بود: ' . $errorMessage);
            $this->writeOutput($output, '<error>خطا در فرآیند: ' . $errorMessage . '</error>');
            $this->rollback($gitHeadBefore, $cacheBackup, $dbBackup, $archiveBackup, $output);
            $state['error'] = $errorMessage;
            $this->saveState($uuid, $state, $output, 'فرآیند ناموفق بود و بازگردانی انجام شد');
            $lock->release();
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->logger->error('به‌روزرسانی ناموفق بود: ' . $e->getMessage());
            $this->writeOutput($output, '<error>خطا: ' . $e->getMessage() . '</error>');
            $this->rollback($gitHeadBefore, $cacheBackup, $dbBackup, $archiveBackup, $output);
            $state['error'] = $e->getMessage();
            $this->saveState($uuid, $state, $output, 'به‌روزرسانی ناموفق بود و بازگردانی انجام شد');
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
                $this->logger->info('دستور با موفقیت اجرا شد: ' . implode(' ', $command));
                return;
            } catch (ProcessFailedException $e) {
                $attempt++;
                $errorMessage = $e->getProcess()->getErrorOutput() ?: $e->getMessage();
                $this->logger->warning("تلاش $attempt ناموفق برای " . implode(' ', $command) . ": $errorMessage");
                $this->writeOutput($output, "<comment>تلاش $attempt ناموفق: $errorMessage</comment>");
                if ($attempt === $retries) {
                    throw new \RuntimeException('دستور "' . implode(' ', $command) . '" پس از ' . $retries . ' تلاش ناموفق بود: ' . $errorMessage);
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
            $this->logger->warning('دریافت HEAD فعلی Git ناموفق بود: ' . $process->getErrorOutput());
            return 'ناشناخته';
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
            $this->logger->warning('بررسی به‌روز بودن نرم‌افزار ناموفق بود: ' . $e->getMessage());
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
            throw new \RuntimeException('تعیین DATABASE_URL از .env.local.php یا محیط ممکن نبود.');
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
            throw new \RuntimeException("طرح دیتابیس پشتیبانی‌نشده: $dbScheme.");
        }

        $process = new Process($command, $this->rootDir);
        $process->setTimeout(3600);
        $process->mustRun();

        if (!file_exists($backupFile) || filesize($backupFile) === 0) {
            throw new \RuntimeException('ایجاد بکاپ دیتابیس ناموفق بود.');
        }
        $this->logger->info("بکاپ دیتابیس در مسیر ایجاد شد: $backupFile (طرح: $dbScheme)");
    }

    private function restoreArchive(string $backupFile): void
    {
        $this->runProcess(['rm', '-rf', $this->archiveDir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        $this->runProcess(['mkdir', $this->archiveDir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        $this->runProcess(['tar', '-xf', $backupFile, '-C', $this->rootDir], $this->rootDir, new \Symfony\Component\Console\Output\NullOutput());
        if (!is_dir($this->archiveDir)) {
            throw new \RuntimeException('بازگردانی hesabixArchive از بکاپ tar ناموفق بود.');
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
        $this->writeOutput($output, 'بازگردانی تغییرات...');

        if ($gitHeadBefore) {
            try {
                $this->runProcess(['git', 'reset', '--hard', $gitHeadBefore], $this->rootDir, $output);
                $this->logger->info('Git به ' . $gitHeadBefore . ' بازگردانی شد');
            } catch (\Exception $e) {
                $this->logger->error('بازگردانی Git ناموفق بود: ' . $e->getMessage());
            }
        }

        if ($cacheBackup) {
            try {
                $this->runProcess(['rm', '-rf', $this->appDir . '/var/cache'], $this->rootDir, $output);
                $this->runProcess(['cp', '-r', $cacheBackup, $this->appDir . '/var/cache'], $this->rootDir, $output);
                $this->logger->info('کش بازگردانی شد');
            } catch (\Exception $e) {
                $this->logger->error('بازگردانی کش ناموفق بود: ' . $e->getMessage());
            }
        }

        if ($dbBackup) {
            try {
                $envConfig = file_exists($this->appDir . '/.env.local.php') ? require $this->appDir . '/.env.local.php' : [];
                $dbUrl = $envConfig['DATABASE_URL'] ?? getenv('DATABASE_URL');
                if (!$dbUrl) {
                    throw new \RuntimeException('تعیین DATABASE_URL برای بازگردانی ممکن نبود.');
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
                    throw new \RuntimeException("طرح دیتابیس پشتیبانی‌نشده برای بازگردانی: $dbScheme.");
                }

                $process->setTimeout(3600);
                $process->mustRun();
                $this->logger->info('دیتابیس بازگردانی شد');
            } catch (\Exception $e) {
                $this->logger->error('بازگردانی دیتابیس ناموفق بود: ' . $e->getMessage());
            }
        }

        if ($archiveBackup) {
            try {
                $this->restoreArchive($archiveBackup);
                $this->logger->info('آرشیو hesabix بازگردانی شد');
            } catch (\Exception $e) {
                $this->logger->error('بازگردانی آرشیو ناموفق بود: ' . $e->getMessage());
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
                $this->logger->info("حذف پوشه موقت: $dir");
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
        $this->logger->debug('وضعیت در مسیر ذخیره شد: ' . $this->stateFile);
    }

    private function preUpdateChecks(OutputInterface $output): void
    {
        $this->writeOutput($output, 'اجرای بررسی‌های پیش از به‌روزرسانی...');
        $this->runProcess(['git', '--version'], $this->rootDir, $output, 1);
        $this->runProcess(['composer', '--version'], $this->rootDir, $output, 1, true);
        $this->runProcess(['php', '-v'], $this->rootDir, $output, 1);
        $this->runProcess(['npm', '--version'], $this->rootDir, $output, 1);

        $process = new Process(['whoami'], $this->rootDir);
        $process->run();
        $user = trim($process->getOutput());
        if ($user === 'root') {
            $this->writeOutput($output, '<warning>اجرای دستور به‌عنوان root ممکن است خطرناک باشد. توصیه می‌شود از کاربر غیر-root استفاده کنید.</warning>');
            $this->logger->warning('دستور به‌عنوان کاربر root اجرا شده است.');
        }

        $this->writeOutput($output, 'بررسی‌های پیش از به‌روزرسانی با موفقیت تکمیل شد.');
    }

    private function postUpdateChecks(OutputInterface $output): void
    {
        $this->writeOutput($output, 'اجرای بررسی‌های پس از به‌روزرسانی...');
        $this->runProcess(['php', 'bin/console', 'cache:pool:clear', 'cache.global_clearer'], $this->appDir, $output, 1);
        $this->writeOutput($output, 'بررسی‌های پس از به‌روزرسانی با موفقیت تکمیل شد.');
    }
}
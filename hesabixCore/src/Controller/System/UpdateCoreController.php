<?php

namespace App\Controller\System;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;

final class UpdateCoreController extends AbstractController
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    #[Route('/api/admin/updatecore/run', name: 'api_admin_updatecore_run', methods: ['POST'])]
    public function api_admin_updatecore_run(): JsonResponse
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $uuid = uniqid();
        $stateFile = $projectDir . '/../backup/update_state_' . $uuid . '.json';

        if (!file_exists(dirname($stateFile))) {
            mkdir(dirname($stateFile), 0755, true);
        }

        file_put_contents($stateFile, json_encode([
            'uuid' => $uuid,
            'log' => 'Update process started at ' . date('Y-m-d H:i:s') . "\n",
            'completedSteps' => [],
        ]));

        $env = array_merge($_SERVER, [
            'HOME' => '/var/www',
            'COMPOSER_HOME' => '/var/www/.composer',
        ]);

        $process = new Process(['php', 'bin/console', 'hesabix:update', $stateFile], $projectDir, $env);
        $process->setTimeout(3600);
        $process->run(function ($type, $buffer) use ($stateFile) {
            $state = json_decode(file_get_contents($stateFile), true) ?? ['uuid' => uniqid(), 'log' => ''];
            $state['log'] .= $buffer;
            file_put_contents($stateFile, json_encode($state));
        });

        $state = json_decode(file_get_contents($stateFile), true) ?? ['uuid' => $uuid, 'log' => ''];

        if (!$process->isSuccessful()) {
            $state['error'] = $process->getErrorOutput() ?: 'Unknown error';
            file_put_contents($stateFile, json_encode($state));
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Update process failed: ' . $state['error'],
                'uuid' => $uuid,
            ], 500);
        }

        $state['log'] .= $process->getOutput() . $process->getErrorOutput();
        file_put_contents($stateFile, json_encode($state));

        return new JsonResponse([
            'status' => 'started',
            'message' => 'Update process started',
            'uuid' => $uuid,
        ]);
    }

    #[Route('/api/admin/updatecore/status', name: 'api_admin_updatecore_status', methods: ['GET'])]
    public function api_admin_updatecore_status(Request $request): JsonResponse
    {
        $uuid = $request->query->get('uuid');
        if (!$uuid) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'UUID is required',
                'output' => '',
            ], 400);
        }

        $stateFile = $this->getParameter('kernel.project_dir') . '/../backup/update_state_' . $uuid . '.json';

        if (!file_exists($stateFile)) {
            return new JsonResponse([
                'status' => 'idle',
                'message' => 'No update process is currently running',
                'output' => '',
            ]);
        }

        $state = json_decode(file_get_contents($stateFile), true) ?? ['log' => ''];
        $output = $state['log'] ?? '';

        $isRunning = !isset($state['error']) &&
            !in_array('post_update_test', $state['completedSteps'] ?? []) &&
            !str_contains($output, 'No update needed') &&
            !str_contains($output, 'Software update completed successfully');

        if ($state['error'] ?? false) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Update failed: ' . $state['error'],
                'output' => $output,
            ]);
        }

        if (!$isRunning) {
            $backupDir = $this->getParameter('kernel.project_dir') . '/../backup';
            $stateFiles = glob($backupDir . '/update_state_*.json');
            foreach ($stateFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Update completed successfully',
                'output' => $output,
                'commit_hash' => $state['commit_hash'] ?? 'unknown',
            ]);
        }

        return new JsonResponse([
            'status' => 'running',
            'message' => 'Update is in progress',
            'output' => $output,
        ]);
    }

    #[Route('/api/admin/updatecore/stream', name: 'api_admin_updatecore_stream', methods: ['GET'])]
    public function api_admin_updatecore_stream(Request $request): StreamedResponse|JsonResponse
    {
        $uuid = $request->query->get('uuid');
        if (!$uuid) {
            return new JsonResponse(['status' => 'error', 'message' => 'UUID is required'], 400);
        }

        $stateFile = $this->getParameter('kernel.project_dir') . '/../backup/update_state_' . $uuid . '.json';

        return new StreamedResponse(function () use ($stateFile) {
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');

            while (true) {
                if (!file_exists($stateFile)) {
                    echo "data: " . json_encode(['status' => 'idle', 'output' => '']) . "\n\n";
                    ob_flush();
                    flush();
                    break;
                }

                $state = json_decode(file_get_contents($stateFile), true) ?? ['log' => ''];
                $output = $state['log'] ?? '';

                $isRunning = !isset($state['error']) &&
                    !in_array('post_update_test', $state['completedSteps'] ?? []);

                $status = $state['error'] ? 'error' : ($isRunning ? 'running' : 'success');
                echo "data: " . json_encode(['status' => $status, 'output' => $output]) . "\n\n";
                ob_flush();
                flush();

                if (!$isRunning) {
                    break;
                }

                sleep(1);
            }
        });
    }

    #[Route('/api/admin/updatecore/commits', name: 'api_admin_updatecore_commits', methods: ['GET'])]
    public function api_admin_updatecore_commits(): JsonResponse
    {
        $projectDir = $this->getParameter('kernel.project_dir');

        $currentProcess = new Process(['git', 'rev-parse', 'HEAD'], $projectDir);
        $currentProcess->run();
        $currentCommit = $currentProcess->isSuccessful() ? trim($currentProcess->getOutput()) : 'unknown';

        $targetProcess = new Process(['git', 'ls-remote', 'origin', 'HEAD'], $projectDir);
        $targetProcess->run();
        $targetOutput = $targetProcess->isSuccessful() ? explode("\t", trim($targetProcess->getOutput()))[0] : 'unknown';

        return new JsonResponse([
            'currentCommit' => $currentCommit,
            'targetCommit' => $targetOutput,
        ]);
    }

    #[Route('/api/admin/updatecore/system-info', name: 'api_admin_updatecore_system_info', methods: ['GET'])]
    public function api_admin_updatecore_system_info(): JsonResponse
    {
        $osName = php_uname('s');
        $osRelease = php_uname('r');
        $osVersion = php_uname('v');
        $osMachine = php_uname('m');

        $cpuInfo = 'Unknown';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cpuInfo = shell_exec('wmic cpu get caption') ?? 'Unknown';
        } else {
            $cpuInfo = shell_exec('cat /proc/cpuinfo | grep "model name" | head -n 1') ?? 'Unknown';
            $cpuInfo = str_replace('model name	: ', '', trim($cpuInfo));
        }

        $distroName = 'Unknown';
        $distroVersion = 'Unknown';
        if (strtoupper(PHP_OS) === 'LINUX') {
            $distroInfo = shell_exec('cat /etc/os-release | grep -E "^(NAME|VERSION)="') ?? '';
            if ($distroInfo) {
                preg_match('/NAME="([^"]+)"/', $distroInfo, $nameMatch);
                preg_match('/VERSION="([^"]+)"/', $distroInfo, $versionMatch);
                $distroName = $nameMatch[1] ?? 'Unknown';
                $distroVersion = $versionMatch[1] ?? 'Unknown';
            }
        }

        $webServer = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

        $dbVersion = 'Unknown';
        $dbName = 'Unknown';
        try {
            $dbParams = $this->connection->getParams();
            $dbName = $dbParams['driver'] ?? 'Unknown';

            if (str_contains($dbName, 'mysql')) {
                $dbVersion = $this->connection->fetchOne('SELECT VERSION()');
            } elseif (str_contains($dbName, 'pgsql')) {
                $dbVersion = $this->connection->fetchOne('SHOW server_version');
            } elseif (str_contains($dbName, 'sqlite')) {
                $dbVersion = $this->connection->fetchOne('SELECT sqlite_version()');
            } else {
                $dbVersion = 'Unsupported database type';
            }
        } catch (\Exception $e) {
            $dbVersion = 'Error fetching DB version: ' . $e->getMessage();
            $dbName = 'Error fetching DB name';
        }

        return new JsonResponse([
            'osName' => $osName,
            'osRelease' => $osRelease,
            'osVersion' => $osVersion,
            'osMachine' => $osMachine,
            'cpuInfo' => $cpuInfo,
            'distroName' => $distroName,
            'distroVersion' => $distroVersion,
            'webServer' => $webServer,
            'dbName' => $dbName,
            'dbVersion' => $dbVersion,
        ]);
    }

    #[Route('/api/admin/updatecore/clear-cache', name: 'api_admin_updatecore_clear_cache', methods: ['POST'])]
    public function api_admin_updatecore_clear_cache(): JsonResponse
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $env = $this->getParameter('kernel.environment');

        $process = new Process(['php', 'bin/console', 'cache:clear', "--env=$env"], $projectDir);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to clear cache: ' . $process->getErrorOutput(),
            ], 500);
        }

        return new JsonResponse([
            'status' => 'success',
            'output' => $process->getOutput(),
        ]);
    }

    #[Route('/api/admin/updatecore/change-env', name: 'api_admin_updatecore_change_env', methods: ['POST'])]
    public function api_admin_updatecore_change_env(Request $request): JsonResponse
    {
        $newEnv = $request->getPayload()->get('env');

        if (!$newEnv || !in_array($newEnv, ['dev', 'prod'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid environment',
                'output' => 'خطا: محیط نامعتبر است',
                'debug' => 'Received env: ' . var_export($newEnv, true)
            ], 400);
        }

        $projectDir = $this->getParameter('kernel.project_dir');
        $envFile = $projectDir . '/.env.local.php';
        $composerLock = $projectDir . '/composer.lock';
        $output = '';

        $envConfig = file_exists($envFile) ? require $envFile : [];
        $envConfig['APP_ENV'] = $newEnv;
        file_put_contents($envFile, '<?php return ' . var_export($envConfig, true) . ';');
        $output .= "حالت به $newEnv تغییر کرد\n";

        if (file_exists($composerLock)) {
            unlink($composerLock);
            $output .= "فایل composer.lock حذف شد\n";
        }

        $env = [
            'HOME' => sys_get_temp_dir(),
            'COMPOSER_HOME' => sys_get_temp_dir() . '/composer'
        ];

        $composerCheck = new Process(['composer', '--version'], $projectDir, $env);
        $composerCheck->run();
        if (!$composerCheck->isSuccessful()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Composer is not installed',
                'output' => $output . "خطا: Composer روی سرور نصب نیست. لطفاً Composer را نصب کنید.\n" . $composerCheck->getErrorOutput()
            ], 500);
        }
        $output .= "Composer نسخه " . trim($composerCheck->getOutput()) . " تشخیص داده شد\n";

        $composerCommand = ['composer', 'install', '--optimize-autoloader'];
        if ($newEnv !== 'dev') {
            $composerCommand[] = '--no-dev';
            $composerCommand[] = '--no-scripts';
        }
        $composerProcess = new Process($composerCommand, $projectDir, $env);
        $composerProcess->setTimeout(600);
        $composerProcess->run();
        if (!$composerProcess->isSuccessful()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to update dependencies',
                'output' => $output . "خطا در به‌روزرسانی وابستگی‌ها: " . $composerProcess->getErrorOutput(),
            ], 500);
        }
        $output .= "وابستگی‌ها با موفقیت به‌روزرسانی شدند\n" . $composerProcess->getOutput();

        $cacheProcess = new Process(['php', 'bin/console', 'cache:clear', "--env=$newEnv"], $projectDir, $env);
        $cacheProcess->setTimeout(300);
        $cacheProcess->run();
        if (!$cacheProcess->isSuccessful()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to clear cache',
                'output' => $output . "خطا در پاک کردن کش: " . $cacheProcess->getErrorOutput(),
            ], 500);
        }
        $output .= "کش با موفقیت پاک شد\n" . $cacheProcess->getOutput();

        return new JsonResponse([
            'status' => 'success',
            'message' => "حالت به $newEnv تغییر کرد، وابستگی‌ها به‌روزرسانی شدند و کش پاک شد",
            'output' => $output,
        ]);
    }

    #[Route('/api/admin/updatecore/current-env', name: 'api_admin_updatecore_current_env', methods: ['GET'])]
    public function api_admin_updatecore_current_env(): JsonResponse
    {
        $env = $this->getParameter('kernel.environment');
        return new JsonResponse(['env' => $env]);
    }

    #[Route('/api/admin/updatecore/system-logs', name: 'api_admin_updatecore_system_logs', methods: ['GET'])]
    public function api_admin_updatecore_system_logs(): JsonResponse
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $env = $this->getParameter('kernel.environment');
        $logFile = "$projectDir/var/log/$env.log";

        try {
            if ($env === 'prod') {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'نمایش لاگ‌ها در محیط prod پشتیبانی نمی‌شود (به php://stderr ارسال می‌شود)',
                ], 400);
            }

            if (file_exists($logFile)) {
                $logs = file_get_contents($logFile);
                return new JsonResponse([
                    'status' => 'success',
                    'logs' => $logs ?: 'لاگ‌ها خالی هستند',
                ]);
            } else {
                return new JsonResponse([
                    'status' => 'success',
                    'logs' => 'فایل لاگ وجود ندارد',
                ]);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'خطا در دریافت لاگ‌ها: ' . $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/api/admin/updatecore/clear-logs', name: 'api_admin_updatecore_clear_logs', methods: ['POST'])]
    public function api_admin_updatecore_clear_logs(): JsonResponse
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $env = $this->getParameter('kernel.environment');
        $logFile = "$projectDir/var/log/$env.log";

        try {
            if ($env === 'prod') {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'پاک کردن لاگ‌ها در محیط prod پشتیبانی نمی‌شود (به php://stderr ارسال می‌شود)',
                ], 400);
            }

            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'لاگ‌ها با موفقیت پاک شدند',
                ]);
            } else {
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'فایلی برای پاک کردن وجود نداشت',
                ]);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'خطا در پاک کردن لاگ‌ها: ' . $e->getMessage(),
            ], 500);
        }
    }
}
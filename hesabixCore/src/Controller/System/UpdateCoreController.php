<?php

namespace App\Controller\System;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

        $process = new Process(['php', 'bin/console', 'hesabix:update', $stateFile], $projectDir);
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
        // اطلاعات سیستم‌عامل
        $osName = php_uname('s');
        $osRelease = php_uname('r');
        $osVersion = php_uname('v');
        $osMachine = php_uname('m');

        // اطلاعات پردازنده
        $cpuInfo = 'Unknown';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cpuInfo = shell_exec('wmic cpu get caption') ?? 'Unknown';
        } else {
            $cpuInfo = shell_exec('cat /proc/cpuinfo | grep "model name" | head -n 1') ?? 'Unknown';
            $cpuInfo = str_replace('model name	: ', '', trim($cpuInfo));
        }

        // اطلاعات توزیع لینوکس
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

        // اطلاعات وب‌سرور
        $webServer = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

        // اطلاعات بانک اطلاعاتی
        $dbVersion = 'Unknown';
        $dbName = 'Unknown';
        try {
            $dbParams = $this->connection->getParams();
            $dbName = $dbParams['driver'] ?? 'Unknown';

            // گرفتن نسخه بانک اطلاعاتی با کوئری SQL
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
}
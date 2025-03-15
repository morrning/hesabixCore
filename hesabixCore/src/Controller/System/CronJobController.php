<?php

namespace App\Controller\System;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Bundle\FrameworkBundle\Console\Application; // استفاده از Application درست
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Psr\Log\LoggerInterface;

class CronJobController extends AbstractController
{
    private KernelInterface $kernel;
    private LoggerInterface $logger;

    public function __construct(KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    #[Route('/api/admin/cron/add-autoupdate-tickets', name: 'add_autoupdate_tickets_cron', methods: ['POST'])]
    public function addCronJob(Request $request): JsonResponse
    {
        $this->logger->info('Starting addCronJob', ['interval' => $request->request->get('interval')]);
        
        $interval = (int)$request->request->get('interval', 24);
        if ($interval < 1) {
            $this->logger->warning('Invalid interval provided', ['interval' => $interval]);
            return new JsonResponse(['status' => 'error', 'message' => 'Interval must be at least 1 hour'], 400);
        }

        $projectDir = $this->kernel->getProjectDir();
        $phpBinary = PHP_BINARY;
        $command = "$phpBinary $projectDir/bin/console hesabix:autoupdate-tickets";
        $cronJob = "0 */$interval * * * $command >> $projectDir/var/log/cron.log 2>&1";

        $process = new Process(['crontab', '-l']);
        $process->run();
        $existingCron = $process->isSuccessful() ? $process->getOutput() : '';

        if (strpos($existingCron, $command) !== false) {
            $this->logger->info('Cron job already exists', ['command' => $command]);
            return new JsonResponse([
                'status' => 'info',
                'message' => 'Cron job for hesabix:autoupdate-tickets is already set.',
                'interval' => $this->extractInterval($existingCron, $command),
            ]);
        }

        $newCron = $existingCron . PHP_EOL . $cronJob;
        $process = new Process(['bash', '-c', "echo '$newCron' | crontab -"]);
        $process->run();

        if ($process->isSuccessful()) {
            $this->logger->info('Cron job added successfully', ['cronJob' => $cronJob]);
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Cron job for hesabix:autoupdate-tickets added successfully.',
                'interval' => $interval,
            ]);
        }

        $this->logger->error('Failed to add cron job', ['error' => $process->getErrorOutput()]);
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Failed to add cron job: ' . $process->getErrorOutput(),
        ], 500);
    }

    #[Route('/api/admin/cron/check-autoupdate-tickets', name: 'check_autoupdate_tickets_cron', methods: ['GET'])]
    public function checkCronJob(): JsonResponse
    {
        $this->logger->info('Checking cron job status');
        
        $projectDir = $this->kernel->getProjectDir();
        $phpBinary = PHP_BINARY;
        $command = "$phpBinary $projectDir/bin/console hesabix:autoupdate-tickets";

        $process = new Process(['crontab', '-l']);
        $process->run();

        if ($process->isSuccessful()) {
            $output = $process->getOutput();
            if (strpos($output, $command) !== false) {
                $interval = $this->extractInterval($output, $command);
                $this->logger->info('Cron job found', ['interval' => $interval]);
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Cron job for hesabix:autoupdate-tickets is set.',
                    'enabled' => true,
                    'interval' => $interval,
                ]);
            }
        }

        $this->logger->info('Cron job not found');
        return new JsonResponse([
            'status' => 'warning',
            'message' => 'Cron job for hesabix:autoupdate-tickets is not set.',
            'enabled' => false,
            'interval' => null,
        ]);
    }

    #[Route('/api/admin/cron/run-autoupdate-tickets', name: 'run_autoupdate_tickets', methods: ['POST'])]
    public function runAutoUpdateTickets(): JsonResponse
    {
        $this->logger->info('Starting runAutoUpdateTickets');

        try {
            // استفاده از Application از FrameworkBundle
            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $this->logger->debug('Running command: hesabix:autoupdate-tickets');
            $input = new ArrayInput([
                'command' => 'hesabix:autoupdate-tickets',
            ]);

            $output = new BufferedOutput();
            $exitCode = $application->run($input, $output);
            $commandOutput = $output->fetch();

            $this->logger->debug('Command output', ['output' => $commandOutput, 'exitCode' => $exitCode]);

            if ($exitCode === 0) {
                preg_match('/Total (\d+) support tickets updated successfully/', $commandOutput, $matches);
                $updatedCount = isset($matches[1]) ? (int)$matches[1] : 0;
                $this->logger->info('Tickets updated successfully', ['updatedCount' => $updatedCount]);
                return new JsonResponse([
                    'status' => 'success',
                    'message' => "بررسی تیکت‌ها با موفقیت انجام شد. $updatedCount تیکت تغییر وضعیت دادند.",
                    'updatedCount' => $updatedCount,
                ]);
            }

            $this->logger->error('Command failed', ['exitCode' => $exitCode, 'output' => $commandOutput]);
            return new JsonResponse([
                'status' => 'error',
                'message' => 'خطا در اجرای بررسی تیکت‌ها: ' . $commandOutput,
            ], 500);
        } catch (\Exception $e) {
            $this->logger->error('Exception occurred while running command', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return new JsonResponse([
                'status' => 'error',
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/api/admin/cron/remove-autoupdate-tickets', name: 'remove_autoupdate_tickets_cron', methods: ['POST'])]
    public function removeCronJob(): JsonResponse
    {
        $this->logger->info('Starting removeCronJob');

        $projectDir = $this->kernel->getProjectDir();
        $phpBinary = PHP_BINARY;
        $command = "$phpBinary $projectDir/bin/console hesabix:autoupdate-tickets";

        $process = new Process(['crontab', '-l']);
        $process->run();
        $existingCron = $process->isSuccessful() ? $process->getOutput() : '';

        if (strpos($existingCron, $command) === false) {
            $this->logger->info('Cron job not found for removal', ['command' => $command]);
            return new JsonResponse([
                'status' => 'info',
                'message' => 'Cron job for hesabix:autoupdate-tickets is not set.',
            ]);
        }

        $lines = explode(PHP_EOL, $existingCron);
        $newCron = '';
        foreach ($lines as $line) {
            if (strpos($line, $command) === false) {
                $newCron .= $line . PHP_EOL;
            }
        }
        $newCron = trim($newCron);

        $process = new Process(['bash', '-c', "echo '$newCron' | crontab -"]);
        $process->run();

        if ($process->isSuccessful()) {
            $this->logger->info('Cron job removed successfully', ['command' => $command]);
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Cron job for hesabix:autoupdate-tickets removed successfully.',
            ]);
        }

        $this->logger->error('Failed to remove cron job', ['error' => $process->getErrorOutput()]);
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Failed to remove cron job: ' . $process->getErrorOutput(),
        ], 500);
    }

    private function extractInterval(string $cronContent, string $command): ?int
    {
        $lines = explode(PHP_EOL, $cronContent);
        foreach ($lines as $line) {
            if (strpos($line, $command) !== false) {
                $parts = preg_split('/\s+/', trim($line));
                if (isset($parts[1]) && preg_match('/^\*\/(\d+)$/', $parts[1], $matches)) {
                    return (int)$matches[1];
                }
            }
        }
        return null;
    }
}
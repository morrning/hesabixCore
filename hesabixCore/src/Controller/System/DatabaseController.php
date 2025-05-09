<?php

namespace App\Controller\System;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\registryMGR;

final class DatabaseController extends AbstractController
{
    private string $backupPath;
    private registryMGR $registryMGR;
    private EntityManagerInterface $entityManager;

    public function __construct(registryMGR $registryMGR, EntityManagerInterface $entityManager)
    {
        $this->registryMGR = $registryMGR;
        $this->entityManager = $entityManager;
        $this->backupPath = dirname(__DIR__, 2) . '/hesabixBackup/versions';
    }

    #[Route('/api/admin/database/backup/info', name: 'app_admin_database_backup_info', methods: ['GET'])]
    public function getBackupInfo(): JsonResponse
    {
        try {
            $lastBackup = $this->getLastBackupInfo('local');
            $lastFtpBackup = $this->getLastBackupInfo('ftp');

            return $this->json([
                'result' => 1,
                'lastBackup' => $lastBackup,
                'lastFtpBackup' => $lastFtpBackup
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'result' => 0,
                'message' => 'خطا در دریافت اطلاعات پشتیبان: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/admin/database/backup/create', name: 'app_admin_database_backup_create', methods: ['POST'])]
    public function app_admin_database_backup_create(): JsonResponse
    {
        try {
            // ایجاد پوشه‌های مورد نیاز
            $this->ensureBackupDirectoriesExist();

            // ایجاد نام فایل با timestamp
            $filename = 'Hesabix-' . time() . '.sql';
            $filepath = $this->backupPath . '/' . $filename;

            // دریافت تنظیمات دیتابیس از EntityManager
            $connection = $this->entityManager->getConnection();
            $params = $connection->getParams();
            
            $dbName = $params['dbname'];
            $dbUser = $params['user'];
            $dbPass = $params['password'];
            $dbHost = $params['host'];
            $dbPort = $params['port'] ?? '3306';

            // دستور mysqldump
            $command = sprintf(
                'mysqldump -h %s -P %s -u %s -p%s %s > %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );

            // اجرای دستور
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('خطا در اجرای دستور mysqldump: ' . implode("\n", $output));
            }

            // ذخیره اطلاعات آخرین پشتیبان
            $this->updateLastBackupInfo('local', $filename);

            return $this->json([
                'result' => 1,
                'filename' => $filename,
                'message' => 'پشتیبان با موفقیت ایجاد شد'
            ]);
        } catch (\Exception $e) {
        return $this->json([
            'result' => 0,
                'message' => 'خطا در ایجاد پشتیبان: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/admin/database/backup/create-and-upload', name: 'app_admin_database_backup_create_and_upload', methods: ['POST'])]
    public function createAndUploadToFtp(): JsonResponse
    {
        try {
            // ایجاد پشتیبان محلی
            $backupResponse = $this->app_admin_database_backup_create();
            $backupData = json_decode($backupResponse->getContent(), true);

            if ($backupData['result'] !== 1) {
                throw new \Exception($backupData['message']);
            }

            $filename = $backupData['filename'];
            $filepath = $this->backupPath . '/' . $filename;

            // بررسی وجود فایل و دسترسی‌های آن
            if (!file_exists($filepath)) {
                throw new \Exception("فایل پشتیبان در مسیر {$filepath} یافت نشد");
            }

            if (!is_readable($filepath)) {
                throw new \Exception("عدم دسترسی به فایل پشتیبان در مسیر {$filepath}");
            }

            // دریافت تنظیمات FTP
            $ftpEnabled = filter_var($this->registryMGR->get('system_settings', 'ftp_enabled'), FILTER_VALIDATE_BOOLEAN);
            if (!$ftpEnabled) {
                throw new \Exception('اتصال FTP غیرفعال است');
            }

            $ftpHost = $this->registryMGR->get('system_settings', 'ftp_host');
            $ftpPort = $this->registryMGR->get('system_settings', 'ftp_port');
            $ftpUsername = $this->registryMGR->get('system_settings', 'ftp_username');
            $ftpPassword = $this->registryMGR->get('system_settings', 'ftp_password');
            $ftpPath = $this->registryMGR->get('system_settings', 'ftp_path');

            // اتصال به FTP
            $ftp = ftp_connect($ftpHost, (int)$ftpPort, 30);
            if (!$ftp) {
                throw new \Exception('خطا در اتصال به سرور FTP');
            }

            // ورود به FTP
            if (!ftp_login($ftp, $ftpUsername, $ftpPassword)) {
                ftp_close($ftp);
                throw new \Exception('خطا در ورود به سرور FTP');
            }

            // فعال کردن حالت غیرفعال
            ftp_pasv($ftp, true);

            // دریافت مسیر home کاربر
            $homeDir = ftp_pwd($ftp);
            if ($homeDir === false) {
                ftp_close($ftp);
                throw new \Exception('خطا در دریافت مسیر home کاربر FTP');
            }

            // تنظیم مسیر نهایی نسبت به home
            $remotePath = rtrim($homeDir, '/') . '/' . ltrim($ftpPath, '/') . '/' . $filename;
            $remoteDir = dirname($remotePath);

            // بررسی دسترسی نوشتن در مسیر
            $testFile = 'test_' . time() . '.txt';
            if (!@ftp_put($ftp, $testFile, 'test', FTP_ASCII)) {
                ftp_close($ftp);
                throw new \Exception('کاربر FTP دسترسی نوشتن ندارد');
            }
            ftp_delete($ftp, $testFile);

            // ایجاد مسیر در صورت عدم وجود
            $this->createFtpDirectory($ftp, $remoteDir);

            // تغییر به مسیر مورد نظر
            if (!@ftp_chdir($ftp, $remoteDir)) {
                ftp_close($ftp);
                throw new \Exception("خطا در تغییر به مسیر {$remoteDir} در سرور FTP");
            }

            // آپلود فایل
            if (!ftp_put($ftp, basename($remotePath), $filepath, FTP_BINARY)) {
                $error = error_get_last();
                ftp_close($ftp);
                throw new \Exception('خطا در آپلود فایل به سرور FTP: ' . ($error['message'] ?? 'خطای نامشخص'));
            }

            ftp_close($ftp);

            // ذخیره اطلاعات آخرین پشتیبان FTP
            $this->updateLastBackupInfo('ftp', $filename);

            return $this->json([
                'result' => 1,
                'filename' => $filename,
                'message' => 'پشتیبان با موفقیت ایجاد و به سرور FTP ارسال شد'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'result' => 0,
                'message' => 'خطا در ایجاد و ارسال پشتیبان: ' . $e->getMessage()
            ], 500);
        }
    }

    private function ensureBackupDirectoriesExist(): void
    {
        $directories = [
            dirname($this->backupPath),
            $this->backupPath
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new \Exception("خطا در ایجاد پوشه {$dir}");
                }
            }
        }
    }

    private function getLastBackupInfo(string $type): ?string
    {
        $key = $type === 'ftp' ? 'last_ftp_backup' : 'last_backup';
        return $this->registryMGR->get('system_settings', $key);
    }

    private function updateLastBackupInfo(string $type, string $filename): void
    {
        $key = $type === 'ftp' ? 'last_ftp_backup' : 'last_backup';
        $this->registryMGR->update('system_settings', $key, $filename);
    }

    private function createFtpDirectory($ftp, $dir): void
    {
        // اگر مسیر ریشه است، نیازی به ایجاد نیست
        if ($dir === '/' || $dir === '.') {
            return;
        }

        // بررسی وجود مسیر
        if (@ftp_chdir($ftp, $dir)) {
            ftp_chdir($ftp, '/');
            return;
        }

        // ایجاد مسیر والد
        $parent = dirname($dir);
        $this->createFtpDirectory($ftp, $parent);

        // ایجاد مسیر فعلی
        $folder = basename($dir);
        if (!@ftp_mkdir($ftp, $folder)) {
            throw new \Exception("خطا در ایجاد پوشه {$folder} در سرور FTP. لطفاً دسترسی‌های کاربر FTP را بررسی کنید.");
        }
    }
}
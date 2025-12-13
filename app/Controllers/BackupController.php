<?php
/**
 * نظام النسخ الاحتياطي
 */

namespace App\Controllers;

use Core\Controller;

class BackupController extends Controller
{
    private string $backupDir;

    public function __construct()
    {
        parent::__construct();
        $this->backupDir = dirname(dirname(__DIR__)) . '/storage/backups';
        
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * صفحة النسخ الاحتياطي
     */
    public function index(): void
    {
        $backups = $this->getBackupsList();
        
        $this->view('settings/backup', [
            'title' => 'النسخ الاحتياطي',
            'backups' => $backups
        ]);
    }

    /**
     * إنشاء نسخة احتياطية
     */
    public function create(): void
    {
        $dbPath = dirname(dirname(__DIR__)) . '/database/database.sqlite';
        
        if (!file_exists($dbPath)) {
            $_SESSION['flash']['error'] = 'قاعدة البيانات غير موجودة';
            header('Location: ' . url('/settings/backup'));
            exit;
        }

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sqlite';
        $backupPath = $this->backupDir . '/' . $filename;

        if (copy($dbPath, $backupPath)) {
            $_SESSION['flash']['success'] = 'تم إنشاء النسخة الاحتياطية بنجاح';
        } else {
            $_SESSION['flash']['error'] = 'فشل في إنشاء النسخة الاحتياطية';
        }

        header('Location: ' . url('/settings/backup'));
        exit;
    }

    /**
     * تحميل نسخة احتياطية
     */
    public function download(string $filename): void
    {
        $filepath = $this->backupDir . '/' . basename($filename);

        if (!file_exists($filepath)) {
            $_SESSION['flash']['error'] = 'الملف غير موجود';
            header('Location: ' . url('/settings/backup'));
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    /**
     * استعادة نسخة احتياطية
     */
    public function restore(): void
    {
        if (!isset($_FILES['backup']) || $_FILES['backup']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash']['error'] = 'يرجى اختيار ملف النسخة الاحتياطية';
            header('Location: ' . url('/settings/backup'));
            exit;
        }

        $uploadedFile = $_FILES['backup']['tmp_name'];
        $dbPath = dirname(dirname(__DIR__)) . '/database/database.sqlite';

        // إنشاء نسخة من الحالية قبل الاستعادة
        $safeBackup = $this->backupDir . '/pre_restore_' . date('Y-m-d_H-i-s') . '.sqlite';
        copy($dbPath, $safeBackup);

        // استعادة النسخة
        if (copy($uploadedFile, $dbPath)) {
            $_SESSION['flash']['success'] = 'تم استعادة النسخة الاحتياطية بنجاح';
        } else {
            $_SESSION['flash']['error'] = 'فشل في استعادة النسخة الاحتياطية';
        }

        header('Location: ' . url('/settings/backup'));
        exit;
    }

    /**
     * حذف نسخة احتياطية
     */
    public function delete(string $filename): void
    {
        $filepath = $this->backupDir . '/' . basename($filename);

        if (file_exists($filepath) && unlink($filepath)) {
            $_SESSION['flash']['success'] = 'تم حذف النسخة الاحتياطية';
        } else {
            $_SESSION['flash']['error'] = 'فشل في حذف النسخة';
        }

        header('Location: ' . url('/settings/backup'));
        exit;
    }

    /**
     * الحصول على قائمة النسخ الاحتياطية
     */
    private function getBackupsList(): array
    {
        $files = glob($this->backupDir . '/*.sqlite');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'date' => filemtime($file)
            ];
        }

        // ترتيب تنازلي
        usort($backups, fn($a, $b) => $b['date'] - $a['date']);

        return $backups;
    }
}

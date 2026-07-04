<?php

namespace App\Services;

use App\Config\Config;

class MockMailLogger
{
    /**
     * Append a mock email entry to storage/logs/mail.log.
     * Ensures the log directory is writable by the web server (Apache/XAMPP).
     */
    public static function log(string $to, string $subject, string $body): bool
    {
        $logFile = Config::getInstance()->get('mail.log_path');
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Allow Apache/XAMPP (daemon/nobody) to append in local development
        if (!is_writable($logDir)) {
            @chmod($logDir, 0777);
        }

        if (file_exists($logFile) && !is_writable($logFile)) {
            @chmod($logFile, 0666);
        }

        $timestamp = date('Y-m-d H:i:s');
        $divider = str_repeat('-', 50) . "\n";
        $logContent = "Timestamp: {$timestamp}\nTo: {$to}\nSubject: {$subject}\nMessage:\n{$body}\n" . $divider;

        $written = @file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);

        if ($written === false) {
            error_log("[Elze.eg MockMail] Failed to write to {$logFile}. OTP/email for {$to}: {$body}");
            return false;
        }

        return true;
    }

    /**
     * Append pre-formatted content (e.g. order invoices) without re-wrapping headers.
     */
    public static function appendRaw(string $content): bool
    {
        $logFile = Config::getInstance()->get('mail.log_path');
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        if (!is_writable($logDir)) {
            @chmod($logDir, 0777);
        }

        if (file_exists($logFile) && !is_writable($logFile)) {
            @chmod($logFile, 0666);
        }

        $written = @file_put_contents($logFile, $content, FILE_APPEND | LOCK_EX);

        if ($written === false) {
            error_log("[Elze.eg MockMail] Failed to append to {$logFile}");
            return false;
        }

        return true;
    }
}

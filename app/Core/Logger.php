<?php
namespace App\Core;

class Logger {
    private static $logFile;
    
    public static function init($logFile) {
        self::$logFile = $logFile;
        
        // Create log directory if it doesn't exist
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        // Create log file if it doesn't exist
        if (!file_exists($logFile)) {
            touch($logFile);
            chmod($logFile, 0666);
        }
    }
    
    public static function log($message, $level = 'INFO') {
        if (!self::$logFile) {
            throw new \Exception('Logger not initialized');
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }
    
    public static function info($message) {
        self::log($message, 'INFO');
    }
    
    public static function error($message) {
        self::log($message, 'ERROR');
    }
    
    public static function debug($message) {
        self::log($message, 'DEBUG');
    }
}
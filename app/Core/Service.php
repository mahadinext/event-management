<?php

namespace App\Core;

class Service {
    protected function setFlashMessage($message, $type = 'success') {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }

    protected function logInfo($message, array $context = []) {
        $this->log('INFO', $message, $context);
    }
    
    protected function logWarning($message, array $context = []) {
        $this->log('WARNING', $message, $context);
    }
    
    protected function logError($message, array $context = []) {
        $this->log('ERROR', $message, $context);
    }
    
    protected function logCritical($message, array $context = []) {
        $this->log('CRITICAL', $message, $context);
    }

    protected function logDebug($message, array $context = []) {
        $this->log('DEBUG', $message, $context);
    }
    
    protected function log($level, $message, array $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $logFile = __DIR__ . '/../../logs/site.log';
        $contextString = !empty($context) ? json_encode($context) : '';
        
        $logMessage = sprintf(
            "[%s] %s: %s %s\n",
            $timestamp,
            $level,
            $message,
            $contextString
        );
        
        // Ensure logs directory exists
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
<?php
namespace App\Core;
use App\Core\View;

class Controller {
    protected $conn;
    protected $view;
    
    public function __construct() {
        require_once(__DIR__ . '/../../config/database.php');
        $this->conn = $conn;
        $this->view = new View();
    }
    
    protected function view($viewPath, $data = []) {
        // Extract data to make variables available in view
        if (!empty($data)) {
            extract($data);
        }
    
        // Start output buffering
        ob_start();
    
        // Build the full path to the view file
        $viewFile = __DIR__ . '/../../views/' . $viewPath . '.php';
        
        // Check if the view file exists
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$viewPath}.php");
        }
        
        // Include the view file
        require_once($viewFile);
        
        // Get the contents
        $content = ob_get_clean();
        
        // Echo the content directly
        echo $content;
        
        return true;
    }
    
    protected function redirect($path) {
        header('Location: ' . $path);
        exit();
    }
    
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
<?php

namespace App\Middleware;

use Exception;
class CsrfMiddleware {
    public function handle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['_token']) || $_POST['_token'] !== $_SESSION['_token']) {
                throw new Exception('CSRF token validation failed');
            }
        }
        
        // Generate new CSRF token if not exists
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
    }
}
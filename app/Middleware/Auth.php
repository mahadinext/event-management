<?php

namespace App\Middleware;

use App\Models\User;
class Auth {
    public static function check() {
        if (!isset($_SESSION['user_id'])) {
            // Store intended URL
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            }

            header('Location: /views/auth/login.php');
            exit();
        }
        return true;
    }

    public static function user() {
        if (isset($_SESSION['user_id'])) {
            require_once(__DIR__ . '/../../config/database.php');
            require_once(__DIR__ . '/../models/User.php');
            return new User($conn, $_SESSION['user_id']);
        }
        return null;
    }

    public static function checkPermission($permission) {
        $user = self::user();
        if (!$user || !$user->hasPermission($permission)) {
            header('Location: /403.php');
            exit();
        }
        return true;
    }

    public static function checkRole($role) {
        $user = self::user();
        if (!$user || !$user->hasRole($role)) {
            header('Location: /403.php');
            exit();
        }
        return true;
    }
}
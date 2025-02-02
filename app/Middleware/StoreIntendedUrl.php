<?php

namespace App\Middleware;
class StoreIntendedUrl {
    public static function handle() {
        if (!isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        }
    }
}
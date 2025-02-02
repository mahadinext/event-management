<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Logger;
use App\Core\Router;
// use Exception;

// Your routing code

// Initialize logger
Logger::init(__DIR__ . '/logs/site.log');
// Logger::info("Application started");

session_start();

// Require the router and other core files
// require_once(__DIR__ . '/app/core/Router.php');

// Create router instance
$router = new Router();

// Include routes
require_once(__DIR__ . '/routes/web.php');

// Get current URI and method
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Debug logging
// Logger::info("Request: $method $uri");

// Dispatch router
try {
    $router->dispatch($uri, $method);
} catch (Exception $e) {
    Logger::error("Error: " . $e->getMessage());
    Logger::error("Stack trace: " . $e->getTraceAsString());
    require_once(__DIR__ . '/views/errors/404.php');
}
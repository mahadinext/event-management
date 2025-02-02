<?php
namespace App\Core;

use Exception;
use App\Core\Logger;

class Router {
    private $routes = [];
    private $params = [];
    private $middleware = [];
    private $notFoundCallback;
    private $prefix = '';
    private $namespace = '';
    
    public function group($attributes, $callback) {
        // Store the previous prefix
        $previousPrefix = $this->prefix;
        $previousNamespace = $this->namespace;
        
        // Add new prefix to existing prefix
        if (isset($attributes['prefix'])) {
            $this->prefix = $previousPrefix . '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['namespace'])) {
            $this->namespace = $attributes['namespace'];
        }
        
        // Execute the callback, which will add routes
        $callback($this);
        
        // Restore the previous prefix
        $this->prefix = $previousPrefix;
        $this->namespace = $previousNamespace;
    }

    public function get($path, $handler, $middleware = []) {
        $path = $this->prefix . '/' . trim($path, '/');
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post($path, $handler, $middleware = []) {
        $path = $this->prefix . '/' . trim($path, '/');
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    public function put($path, $handler, $middleware = []) {
        $path = $this->prefix . '/' . trim($path, '/');
        $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    public function delete($path, $handler, $middleware = []) {
        $path = $this->prefix . '/' . trim($path, '/');
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function notFound($callback) {
        $this->notFoundCallback = $callback;
    }
    
    private function addRoute($method, $path, $handler, $middleware) {
        // Convert path parameters to regex pattern
        $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";
        
        $this->routes[$method][$pattern] = [
            'handler' => $handler,
            'middleware' => $middleware,
            'namespace' => $this->namespace
        ];
    }
    
    public function dispatch($uri, $method) {
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Handle PUT and DELETE methods via POST
        if ($method === 'POST') {
            if (isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
            }
        }
        
        foreach ($this->routes[$method] ?? [] as $pattern => $route) {
            if (preg_match($pattern, $uri, $matches)) {
                // Extract named parameters
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $this->params[$key] = $value;
                    }
                }
                
                // Run middleware
                foreach ($route['middleware'] as $middleware) {
                    $middlewareClass = $middleware . 'Middleware';
                    $middlewareFile = __DIR__ . "/../middleware/{$middlewareClass}.php";
                    
                    if (file_exists($middlewareFile)) {
                        require_once($middlewareFile);
                        $middlewareInstance = new $middlewareClass();
                        $middlewareInstance->handle();
                    }
                }
                
                $this->namespace = $route['namespace'];

                return $this->callHandler($route['handler']);
            }
        }
        
        // No route found
        header("HTTP/1.0 404 Not Found");
        require_once(__DIR__ . '/../../views/errors/404.php');
        exit();
    }
    
    private function callHandler($handler) {
        // Logger::debug("Calling handler: $handler");
        
        if (!strpos($handler, '@')) {
            Logger::error("Router::callHandler() Invalid handler format: $handler");
            throw new Exception("Router::callHandler() Invalid handler format: $handler");
        }
        
        list($controller, $method) = explode('@', $handler);
        
        // Add Controller suffix if not present
        if (!str_ends_with($controller, 'Controller')) {
            $controller .= 'Controller';
        }
        // Logger::debug("prefix: ", [$this->prefix]);

        // Determine base folder based on current URL path
        $baseFolder = '';
        $baseNamespace = 'App\\Controllers\\';
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Logger::debug("Current Path: " . $currentPath);
        
        if (strpos($currentPath, '/admin') === 0) {
            $baseFolder = 'admin/';
            $baseNamespace .= 'Admin\\';
        } else if (strpos($currentPath, '/attendee') === 0) {
            $baseFolder = 'attendee/';
            $baseNamespace .= 'Attendee\\';
        } else if (strpos($currentPath, '/api') === 0) {
            $baseFolder = 'api/';
            $baseNamespace .= 'Api\\';
        }
        
        // Determine the controller path based on the controller name
        // For example: DashboardController -> dashboard/DashboardController.php
        // $controllerPath = strtolower(str_replace('Controller', '', $controller));
        // For example: EventAttendeeController -> event-attendee/EventAttendeeController.php
        // $controllerPath = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', str_replace('Controller', '', $controller)));
        // For example: EventAttendeeController -> event/attendee/EventAttendeeController.php
        $controllerPath = preg_replace('/([a-z])([A-Z])/', '$1/$2', str_replace('Controller', '', $controller));
        $controllerFile = __DIR__ . "/../controllers/{$baseFolder}{$controllerPath}/{$controller}.php";

        // Ensure we don't have double slashes
        $controllerPath = trim(str_replace('//', '/', $controllerPath), '/');
        
        // Logger::debug("Looking for controller file: $controllerFile");
        
        if (!file_exists($controllerFile)) {
            Logger::error("Router::callHandler() Controller file not found: $controllerFile");
            throw new Exception("Router::callHandler() Controller file not found: $controllerFile");
        }
        
        require_once($controllerFile);
        
        $controllerClass = $controller;
        
        // Build the full namespaced class name
        $controllerNamespace = $baseNamespace;
        $pathParts = explode('/', trim($controllerPath, '/'));
        foreach ($pathParts as $part) {
            $controllerNamespace .= ucfirst(str_replace('-', '', ucwords($part, '-'))) . '\\';
        }
        // Logger::debug("Controller Namespace: " . $controllerNamespace);
        $controllerClass = $controllerNamespace . $controller;
        
        // Remove any double backslashes
        $controllerClass = str_replace('\\\\', '\\', $controllerClass);
        
        // Logger::debug("Attempting to load controller class: " . $controllerClass);

        // if ($this->namespace) {
        //     $controllerClass = $this->namespace . '\\' . $controller;
        //     Logger::debug("Full controller class: " . $controllerClass);
        // }
        
        if (!class_exists($controllerClass)) {
            Logger::error("Router::callHandler() Controller class not found: $controllerClass");
            throw new Exception("Router::callHandler() Controller class not found: $controllerClass");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            Logger::error("Router::callHandler() Method $method not found in controller $controllerClass");
            throw new Exception("Router::callHandler() Method $method not found in controller $controllerClass");
        }
        
        // Logger::info("Executing: $controllerClass@$method");
        return call_user_func_array([$controller, $method], $this->params);
    }
}
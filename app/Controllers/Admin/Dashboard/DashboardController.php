<?php

namespace App\Controllers\Admin\Dashboard;

use App\Core\Controller;
use App\Core\Logger;
use App\Services\AdminDashboardService;
use Exception;

class DashboardController extends Controller {
    private $adminDashboardService;
    
    public function __construct() {
        parent::__construct();
        $this->adminDashboardService = new AdminDashboardService($this->conn);
    }
    
    public function index() {
        try {
            $stats = $this->adminDashboardService->getDashboardStats();

            // Pass data to view
            return $this->view('dashboard', [
                'pageTitle' => 'Dashboard',
                'currentPage' => 'dashboard',
                'userName' => $_SESSION['user_name'] ?? 'Guest',
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            Logger::error("DashboardController@index Error: " . $e->getMessage());
            throw $e;
        }
    }
}
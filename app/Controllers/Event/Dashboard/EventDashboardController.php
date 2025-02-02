<?php

namespace App\Controllers\Event\Dashboard;

use App\Core\Controller;
use App\Core\Logger;
use App\Services\EventDashboardService;
use App\Constants\EventConstants;
use Exception;
class EventDashboardController extends Controller {
    private $eventDashboardService;
    
    public function __construct() {
        parent::__construct();
        $this->eventDashboardService = new EventDashboardService($this->conn);
    }
    
    public function index() {
        try {
            // Get parameters
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 9; // Show 9 cards per page
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'event_date';
            $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
            
            // Get filters
            $filters = [
                'status' => isset($_GET['status']) ? $_GET['status'] : EventConstants::STATUS_ACTIVE,
                'event_type' => isset($_GET['event_type']) ? $_GET['event_type'] : '',
                'registration_type' => isset($_GET['registration_type']) ? $_GET['registration_type'] : '',
                'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
                'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : '',
                'search' => isset($_GET['search']) ? $_GET['search'] : ''
            ];
            
            // Get events
            $events = $this->eventDashboardService->filterEvents($page, $limit, $sort, $order, $filters);
            $total = $this->eventDashboardService->getTotalEvents($filters);
            $total_pages = ceil($total / $limit);
            
            return $this->view('event-dashboard/index', [
                'pageTitle' => 'Event Dashboard',
                'currentPage' => 'event-dashboard',
                'events' => $events,
                'page' => $page,
                'total_pages' => $total_pages,
                'sort' => $sort,
                'order' => $order,
                'filters' => $filters
            ]);
            
        } catch (Exception $e) {
            Logger::error("EventDashboardController@index", ["Error" => $e, "Message" => $e->getMessage()]);
            $_SESSION['error'] = "Error loading events";
            header('Location: /');
            exit;
        }
    }

    public function register() {    
        try {
            // Get POST data
            $eventId = $_POST['event_id'] ?? null;
            
            // Validate event ID
            if (!$eventId) {
                throw new Exception("Event ID is required");
            }
            
            // Prepare data array
            $data = [
                'name' => $_POST['name'] ?? null,
                'email' => $_POST['email'] ?? null,
                'phone' => $_POST['phone'] ?? null
            ];
            
            // Register for event using service
            $this->eventDashboardService->registerForEvent($eventId, $data);
            
            // Return success response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Successfully registered for the event!'
            ]);
            exit;
            
        } catch (Exception $e) {
            Logger::error("EventDashboardController@register", ["Error" => $e->getMessage()]);
            
            // Return error response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}

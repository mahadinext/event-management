<?php

namespace App\Controllers\Admin\Event;

use App\Core\Controller;
use App\Core\Logger;
use App\Services\EventService;
use App\Constants\EventConstants;
use Exception;

class EventController extends Controller {
    private $eventService;
    
    public function __construct() {
        parent::__construct();
        // Check if user has permission to manage events
        // Auth::checkPermission('manage-events');
        $this->eventService = new EventService($this->conn);
    }
    
    /*
    * Display all events
    */
    public function index() {
        // Get parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'event_date';
        $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
        
        // Get filters
        $filters = [
            'status' => isset($_GET['status']) ? $_GET['status'] : '',
            'event_type' => isset($_GET['event_type']) ? $_GET['event_type'] : '',
            'registration_type' => isset($_GET['registration_type']) ? $_GET['registration_type'] : '',
            'event_date_from' => isset($_GET['event_date_from']) ? $_GET['event_date_from'] : '',
            'event_date_to' => isset($_GET['event_date_to']) ? $_GET['event_date_to'] : ''
        ];
        
        // Get events using service
        $events = $this->eventService->filterEvents($page, $limit, $sort, $order, $filters);
        
        // Calculate total pages
        $total_events = $this->eventService->getTotalEvents($filters);
        $total_pages = ceil($total_events / $limit);
        
        // Pass data to view
        return $this->view('events/index', [
            'pageTitle' => 'Events',
            'currentPage' => 'events',
            'events' => $events,
            'page' => $page,
            'total_pages' => $total_pages,
            'sort' => $sort,
            'order' => $order,
            'filters' => $filters
        ]);
    }

    /*
    * Create a new event
    */
    public function create() {
        try {
            return $this->view('events/create', [
                'pageTitle' => 'Create Event',
                'currentPage' => 'events'
            ]);
        } catch (Exception $e) {
            Logger::error("EventController@create Error creating event: " . $e->getMessage());
            return $this->view('errors/500', [
                'pageTitle' => 'Error',
                'currentPage' => 'error'
            ]);
        }
    }

    /*
    * Store a new event
    */
    public function store() {
        try {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'event_date' => $_POST['event_date'] ?? '',
                'registration_deadline' => $_POST['registration_deadline'] ?? '',
                'max_attendees' => $_POST['max_attendees'] ?? 0,
                'event_location' => $_POST['event_location'] ?? '',
                'status' => $_POST['status'] ?? EventConstants::STATUS_ACTIVE,
                'registration_type' => $_POST['registration_type'] ?? EventConstants::REGISTRATION_USER_ONLY,
                'event_type' => $_POST['event_type'] ?? EventConstants::EVENT_TYPE_FREE,
                'ticket_price' => $_POST['ticket_price'] ?? 0.00,
                'created_by' => $_SESSION['user_id'] ?? null
            ];
            Logger::debug("EventController@store data: " . json_encode($data));
    
            // Validate required fields
            $required = ['name', 'description', 'event_date', 'registration_deadline', 'max_attendees', 'event_location', 'status', 'registration_type', 'event_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("EventController@store $field is required");
                }
            }
    
            // Create event
            $eventId = $this->eventService->createEvent($data);
            Logger::debug("EventController@store eventId: " . $eventId);
    
            if ($eventId) {
                $_SESSION['admin_event_crud_success'] = 'Event created successfully';
                header('Location: /admin/events');
                exit;
            } else {
                throw new Exception('EventController@store Failed to create event');
            }
    
        } catch (Exception $e) {
            Logger::error("EventController@store Error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['old'] = $_POST;
            header('Location: /admin/events/create');
            exit;
        }
    }
    
    /*
    * Edit an event
    * @param int $id
    */
    public function edit($id) {
        try {
            $event = $this->eventService->getEvent($id);
            
            if (!$event) {
                throw new Exception('Event not found');
            }
    
            return $this->view('events/edit', [
                'pageTitle' => 'Edit Event',
                'currentPage' => 'events',
                'event' => $event
            ]);
    
        } catch (Exception $e) {
            Logger::error("EventController@edit Error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: /admin/events');
            exit;
        }
    }
    
    /*
    * Update an event
    * @param int $id
    */
    public function update($id) {
        try {
            // Validate request
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'event_date' => $_POST['event_date'] ?? '',
                'registration_deadline' => $_POST['registration_deadline'] ?? '',
                'max_attendees' => $_POST['max_attendees'] ?? 0,
                'event_location' => $_POST['event_location'] ?? '',
                'status' => $_POST['status'] ?? EventConstants::STATUS_ACTIVE,
                'registration_type' => $_POST['registration_type'] ?? EventConstants::REGISTRATION_USER_ONLY,
                'event_type' => $_POST['event_type'] ?? EventConstants::EVENT_TYPE_FREE,
                'ticket_price' => $_POST['ticket_price'] ?? 0.00,
                'updated_by' => $_SESSION['user_id'] ?? null
            ];
    
            // Validate required fields
            $required = ['name', 'description', 'event_date', 'registration_deadline', 'max_attendees', 'event_location', 'status', 'registration_type', 'event_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("$field is required");
                }
            }
    
            // Update event
            $success = $this->eventService->updateEvent($id, $data);
            Logger::debug("EventController@update success: " . $success);
    
            if ($success) {
                $_SESSION['admin_event_crud_success'] = 'Event updated successfully';
                header('Location: /admin/events');
                exit;
            } else {
                throw new Exception('Failed to update event');
            }
    
        } catch (Exception $e) {
            Logger::error("EventController@update Error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['old'] = $_POST;
            header("Location: /admin/events/edit/$id");
            exit;
        }
    }

    /**
     * Delete an event
     * @param int $id
     * @return void
     */
    public function delete($id) {
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not authenticated');
            }

            $success = $this->eventService->deleteEvent($id, $_SESSION['user_id']);
            
            if ($success) {
                $_SESSION['admin_event_crud_success'] = 'Event deleted successfully';
            } else {
                $_SESSION['admin_event_crud_error'] = 'Failed to delete event';
            }

            header('Location: /admin/events');
            exit;
        } catch (Exception $e) {
            Logger::error("EventController@delete Error: " . $e->getMessage());
            $_SESSION['admin_event_crud_error'] = $e->getMessage();
        
            header('Location: /admin/events');
            exit;
        }
    }

    /**
     * Restore a deleted event
     * @param int $id
     * @return void
     */
    public function restore($id) {
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not authenticated');
            }

            $success = $this->eventService->restoreEvent($id, $_SESSION['user_id']);
            
            if ($success) {
                $_SESSION['admin_event_crud_success'] = 'Event restored successfully';
            } else {
                $_SESSION['admin_event_crud_error'] = 'Failed to restore event';
            }

            header('Location: /admin/events');
            exit;
        } catch (Exception $e) {
            Logger::error("EventController@restore Error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
        
            header('Location: /admin/events');
            exit;
        }
    }
}

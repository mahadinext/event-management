<?php

namespace App\Controllers\Admin\Event\Attendee;

use App\Core\Controller;
use App\Core\Logger;
use App\Services\EventAttendeeService;
use Exception;

class EventAttendeeController extends Controller {
    private $attendeeService;
    
    public function __construct() {
        parent::__construct();
        // Auth::checkAdmin();
        $this->attendeeService = new EventAttendeeService($this->conn);
    }
    
    public function index() {
        try {
            // Get parameters
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 10;
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'registration_date';
            $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
            
            // Get filters
            $filters = [
                'event_id' => isset($_GET['event_id']) ? $_GET['event_id'] : '',
                'registration_type' => isset($_GET['registration_type']) ? $_GET['registration_type'] : '',
                'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
                'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : ''
            ];
            
            // Get registrations
            $registrations = $this->attendeeService->getAllRegistrations($page, $limit, $sort, $order, $filters);
            $total = $this->attendeeService->getTotalRegistrations($filters);
            $total_pages = ceil($total / $limit);
            
            // Get events for filter dropdown
            $events = $this->attendeeService->getActiveEvents();
            
            return $this->view('event-attendees/index', [
                'pageTitle' => 'Event Attendees',
                'currentPage' => 'event-attendees',
                'registrations' => $registrations,
                'events' => $events,
                'page' => $page,
                'total_pages' => $total_pages,
                'sort' => $sort,
                'order' => $order,
                'filters' => $filters
            ]);
            
        } catch (Exception $e) {
            Logger::error("EventAttendeeController@index Error: " . $e->getMessage());
            $_SESSION['error'] = "Error loading registrations";
            header('Location: /admin/dashboard');
            exit;
        }
    }
    
    public function exportCsv() {
        try {
            // Get filters
            $filters = [
                'event_id' => isset($_GET['event_id']) ? $_GET['event_id'] : '',
                'registration_type' => isset($_GET['registration_type']) ? $_GET['registration_type'] : '',
                'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
                'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : ''
            ];
            
            $registrations = $this->attendeeService->getAllRegistrationsForExport($filters);
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="event_registrations_' . date('Y-m-d') . '.csv"');
            
            // Create CSV file
            $output = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($output, [
                'ID',
                'Event',
                'Attendee Name',
                'Email',
                'Phone',
                'Registration Type',
                'Registration Date',
                'Created At',
                'Updated At'
            ]);
            
            // Add data
            foreach ($registrations as $key => $registration) {
                fputcsv($output, [
                    ++$key,
                    $registration['event_name'],
                    $registration['attendee_name'],
                    $registration['attendee_email'],
                    $registration['attendee_phone'],
                    $registration['registration_type'],
                    $registration['registration_date'],
                    $registration['created_at'],
                    $registration['updated_at']
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            Logger::error("EventAttendeeController@exportCsv Error: " . $e->getMessage());
            $_SESSION['error'] = "Error exporting registrations";
            header('Location: /admin/event-attendees');
            exit;
        }
    }
}
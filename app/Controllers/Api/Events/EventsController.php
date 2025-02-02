<?php

namespace App\Controllers\Api\Events;

use App\Core\Controller;
use App\Core\Logger;
use App\Constants\EventConstants;
use Exception;

class EventsController extends Controller {    
    public function __construct() {
        parent::__construct();        
        // Set JSON response headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
    }
    
    /**
     * Get list of active events
     */
    public function index() {
        try {
            $query = "SELECT 
                        e.*,
                        COUNT(DISTINCT er.id) as total_registrations,
                        (e.max_attendees - COUNT(DISTINCT er.id)) as available_spots
                     FROM events e
                     LEFT JOIN event_registrations er ON e.id = er.event_id
                     WHERE e.status = 1 
                     AND e.deleted_at IS NULL
                     GROUP BY e.id
                     ORDER BY e.event_date ASC";
            
            $result = mysqli_query($this->conn, $query);
            if (!$result) {
                throw new Exception(mysqli_error($this->conn));
            }
            
            $events = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $events[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'event_date' => $row['event_date'],
                    'max_attendees' => (int)$row['max_attendees'],
                    'total_registrations' => (int)$row['total_registrations'],
                    'available_spots' => (int)$row['available_spots']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $events
            ]);
            
        } catch (Exception $e) {
            Logger::error("EventApiController@index Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching events',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get single event details by ID
     * @param int $id
     */
    public function show($id) {
        try {
            // Validate ID
            if (!$id || !is_numeric($id)) {
                throw new Exception("Invalid event ID");
            }
            
            // Get event details
            $event = $this->getEventDetails($id);
            
            if (!$event) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Event not found'
                ]);
                return;
            }

            // Format event data with text representations
            $eventData = $this->formatEventDetailsData($event);
        
            echo json_encode([
                'success' => true,
                'data' => $eventData
            ]);
        } catch (Exception $e) {
            Logger::error("EventApiController@show Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching event details',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get event details including registration stats
     * @param int $id
     */
    private function getEventDetails($id) {
        try {
            $query = "SELECT
                    e.id,
                    e.name,
                    e.description,
                    e.event_date,
                    e.registration_deadline,
                    e.event_location,
                    e.max_attendees,
                    e.status,
                    e.registration_type,
                    e.event_type,
                    COUNT(DISTINCT er.id) as total_registrations,
                    COUNT(DISTINCT CASE WHEN er.user_id IS NOT NULL THEN er.id END) as user_registrations,
                    COUNT(DISTINCT CASE WHEN er.user_id IS NULL THEN er.id END) as guest_registrations,
                    (e.max_attendees - COUNT(DISTINCT er.id)) as available_spots
                    FROM events e
                    LEFT JOIN event_registrations er ON e.id = er.event_id
                    WHERE e.id = ? AND e.deleted_at IS NULL
                    GROUP BY e.id";
            
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $event = mysqli_fetch_assoc($result);
            
            mysqli_stmt_close($stmt);
            
            return $event;
        } catch (Exception $e) {
            Logger::error("EventApiController@getEventDetails Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Format event details data
     * @param array $event
     * @return array
     */
    private function formatEventDetailsData($event) {
        try {
            return [
                'id' => (int)$event['id'],
                'name' => $event['name'],
                'description' => $event['description'],
                'event_date' => $event['event_date'],
                'event_location' => $event['event_location'],
                'registration_deadline' => $event['registration_deadline'],
                'max_attendees' => (int)$event['max_attendees'],
                'status' => [
                    'id' => (int)$event['status'],
                    'text' => EventConstants::STATUS_LABELS[$event['status']] ?? 'Unknown'
                ],
                'registration_type' => [
                    'id' => (int)$event['registration_type'],
                    'text' => EventConstants::REGISTRATION_TYPE_LABELS[$event['registration_type']] ?? 'Unknown'
                ],
                'event_type' => [
                    'id' => (int)$event['event_type'],
                    'text' => EventConstants::EVENT_TYPE_LABELS[$event['event_type']] ?? 'Unknown'
                ],
                'registration_stats' => [
                    'total_registrations' => (int)$event['total_registrations'],
                    'user_registrations' => (int)$event['user_registrations'],
                    'guest_registrations' => (int)$event['guest_registrations'],
                    'available_spots' => (int)$event['available_spots']
                ]
            ];
        } catch (Exception $e) {
            Logger::error("EventApiController@formatEventDetailsData Error: " . $e->getMessage());
            throw $e;
        }
    }
}
<?php

namespace App\Services;

use App\Core\Logger;
use Exception;
use App\Constants\EventConstants;

class EventDashboardService {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Filter events with pagination and search
     * @param int $page
     * @param int $limit
     * @param string $sort
     * @param string $order
     * @param array $filters
     * @return array
     */
    public function filterEvents($page, $limit, $sort, $order, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;

            // Base query with registration count
            $query = "SELECT e.*, 
                        COUNT(DISTINCT er.id) as registrations,
                        (e.max_attendees - COUNT(DISTINCT er.id)) as available_spots
                    FROM events e 
                    LEFT JOIN event_registrations er ON e.id = er.event_id";

            // Get filter conditions
            $filterData = $this->buildFilterConditions($filters);
            
            if (!empty($filterData['conditions'])) {
                $query .= " WHERE " . implode(' AND ', $filterData['conditions']);
            }

            // Group by to handle the COUNT
            $query .= " GROUP BY e.id";

            // Validate and sanitize sort column
            $allowed_sort_columns = ['name', 'event_date', 'created_at', 'status', 'event_type'];
            $sort = in_array($sort, $allowed_sort_columns) ? $sort : 'event_date';
            
            // Validate order
            $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
            
            $query .= " ORDER BY e." . $sort . " " . $order;
            $query .= " LIMIT ? OFFSET ?";
            
            // Add limit and offset to params
            $filterData['params'][] = $limit;
            $filterData['params'][] = $offset;
            $filterData['types'] .= "ii";

            return $this->executeQuery($query, $filterData['types'], $filterData['params']);
        } catch (Exception $e) {
            Logger::error("EventService@filterEvents Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build common filter conditions
     * @param array $filters
     * @return array
     */
    private function buildFilterConditions($filters = [])
    {
        try {
            $where_conditions = [];
            $params = [];
            $types = "";

            // Always exclude deleted events for public view
            $where_conditions[] = "e.deleted_at IS NULL";
            $where_conditions[] = "e.status = " . EventConstants::STATUS_ACTIVE;

            // Status filter (default to active for public view)
            if (isset($filters['status'])) {
                $where_conditions[] = "e.status = ?";
                $params[] = $filters['status'];
                $types .= "i";
            }

            // Event type filter
            if (!empty($filters['event_type'])) {
                $where_conditions[] = "e.event_type = ?";
                $params[] = $filters['event_type'];
                $types .= "i";
            }

            // Registration type filter
            if (!empty($filters['registration_type'])) {
                $where_conditions[] = "e.registration_type = ?";
                $params[] = $filters['registration_type'];
                $types .= "i";
            }

            // Date range filter
            if (!empty($filters['date_from'])) {
                $where_conditions[] = "e.event_date >= ?";
                $params[] = $filters['date_from'];
                $types .= "s";
            }

            if (!empty($filters['date_to'])) {
                $where_conditions[] = "e.event_date <= ?";
                $params[] = $filters['date_to'];
                $types .= "s";
            }

            // Search filter
            if (!empty($filters['search'])) {
                $search = '%' . $filters['search'] . '%';
                $where_conditions[] = "(e.name LIKE ? OR e.description LIKE ? OR e.event_location LIKE ?)";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $types .= "sss";
            }

            return [
                'conditions' => $where_conditions,
                'params' => $params,
                'types' => $types
            ];
        } catch (Exception $e) {
            Logger::error("EventDashboardService@buildFilterConditions Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute a database query
     * @param string $query
     * @param string $types
     * @param array $params
     * @param bool $singleRow
     * @return array|mixed
     */
    private function executeQuery($query, $types = "", $params = [], $singleRow = false)
    {
        try {
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($this->conn));
            }

            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
            }

            $result = mysqli_stmt_get_result($stmt);
            
            if ($singleRow) {
                $data = mysqli_fetch_assoc($result);
            } else {
                $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
            }

            mysqli_stmt_close($stmt);
            return $data;
        } catch (Exception $e) {
            Logger::error("EventDashboardService@executeQuery Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get total number of events with filters
     * @param array $filters
     * @return int
     */
    public function getTotalEvents($filters = [])
    {
        try {
            $query = "SELECT COUNT(DISTINCT e.id) as total FROM events e";

            // Get filter conditions
            $filterData = $this->buildFilterConditions($filters);

            if (!empty($filterData['conditions'])) {
                $query .= " WHERE " . implode(' AND ', $filterData['conditions']);
            }

            // Execute query and get single row result
            $result = $this->executeQuery($query, $filterData['types'], $filterData['params'], true);
            
            return (int)($result['total'] ?? 0);
        } catch (Exception $e) {
            Logger::error("EventService@getTotalEvents Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register for an event
     * @param int $eventId
     * @param array $data
     * @return bool
     */
    public function registerForEvent($eventId, $data) {
        try {
            $isLoggedIn = isset($_SESSION['user_id']);
            
            // Check if event exists and has capacity
            $this->validateEventCapacity($eventId);
            
            // Check for existing registration
            if ($isLoggedIn) {
                $this->validateUserRegistration($eventId, $_SESSION['user_id']);
            } else {
                $this->validateGuestRegistration($eventId, $data);
            }
            
            // Create registration record
            return $this->createRegistration($eventId, $data, $isLoggedIn);
            
        } catch (Exception $e) {
            Logger::error("EventDashboardService@registerForEvent Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Validate event capacity
     * @param int $eventId
     * @return void
     */
    private function validateEventCapacity($eventId) {
        try {
            $query = "SELECT e.id, e.max_attendees, e.registration_deadline, COUNT(er.id) as current_registrations 
                    FROM events e 
                    LEFT JOIN event_registrations er ON e.id = er.event_id 
                    WHERE e.id = ? 
                    AND e.deleted_at IS NULL
                    GROUP BY e.id";
            
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Database error: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $eventId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error checking event capacity: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $eventData = mysqli_fetch_assoc($result);
            
            mysqli_stmt_close($stmt);
            
            if (!$eventData) {
                throw new Exception("Event not found");
            }

            // Check if registration deadline has passed
            if (!empty($eventData['registration_deadline'])) {
                $deadline = strtotime($eventData['registration_deadline']);
                $now = time();
                
                if ($now > $deadline) {
                    throw new Exception("Registration deadline has passed for this event");
                }
            }
            
            if ($eventData['current_registrations'] >= $eventData['max_attendees']) {
                throw new Exception("Sorry, this event has reached maximum capacity");
            }
        } catch (Exception $e) {
            Logger::error("EventDashboardService@validateEventCapacity Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Validate user registration
     * @param int $eventId
     * @param int $userId
     * @return void
     */
    private function validateUserRegistration($eventId, $userId) {
        try {
            $query = "SELECT id FROM event_registrations 
                    WHERE event_id = ? AND user_id = ?";
            
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Database error: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($stmt, "ii", $eventId, $userId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error checking registration: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                throw new Exception("You have already registered for this event");
            }
            
            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            Logger::error("EventDashboardService@validateUserRegistration Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate guest registration
     * @param int $eventId
     * @param array $data
     * @return void
     */
    private function validateGuestRegistration($eventId, $data) {
        try {
            // Validate required fields
            if (empty($data['name']) || empty($data['email']) || empty($data['phone'])) {
                throw new Exception("Name, Email and Phone are required for guest registration");
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            
            // Validate phone format
            if (!preg_match('/^[0-9]{11}$/', $data['phone'])) {
                throw new Exception("Invalid phone number format");
            }
            
            // Check for existing guest registration
            $query = "SELECT id FROM event_registrations 
                    WHERE event_id = ? AND guest_email = ?";
            
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Database error: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($stmt, "is", $eventId, $data['email']);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error checking registration: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                throw new Exception("This email has already been used to register for this event");
            }
            
            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            Logger::error("EventDashboardService@validateGuestRegistration Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create registration record
     * @param int $eventId
     * @param array $data
     * @param bool $isLoggedIn
     * @return bool
     */
    private function createRegistration($eventId, $data, $isLoggedIn) {
        try {
            $query = "INSERT INTO event_registrations 
                 (event_id, user_id, guest_name, guest_email, guest_phone) 
                 VALUES (?, ?, ?, ?, ?)";
                 
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Database error: " . mysqli_error($this->conn));
            }
            
            // Set parameters based on login status
            $userId = $isLoggedIn ? $_SESSION['user_id'] : null;
            $guestName = !$isLoggedIn ? $data['name'] : null;
            $guestEmail = !$isLoggedIn ? $data['email'] : null;
            $guestPhone = $data['phone'];
            
            mysqli_stmt_bind_param($stmt, "iisss", 
                $eventId, 
                $userId, 
                $guestName, 
                $guestEmail, 
                $guestPhone
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Registration failed: " . mysqli_stmt_error($stmt));
            }
            
            mysqli_stmt_close($stmt);
        
            return true;
        } catch (Exception $e) {
            Logger::error("EventDashboardService@createRegistration Error: " . $e->getMessage());
            throw $e;
        }
    }
}

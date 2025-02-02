<?php
namespace App\Services;

use App\Constants\EventConstants;
use App\Core\Logger;
use Exception;

class EventService {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /*
    * Filter events
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
    
            // Build base query
            $query = "SELECT e.*, COUNT(DISTINCT er.id) as registrations 
                     FROM events e 
                     LEFT JOIN event_registrations er ON e.id = er.event_id";
    
            // Start WHERE clause
            $where_conditions = ["e.deleted_at IS NULL"];
            $params = [];
            $types = "";
    
            // Add filters
            if (!empty($filters['status'])) {
                $where_conditions[] = "e.status = ?";
                $params[] = $filters['status'];
                $types .= "i";
            }
    
            if (!empty($filters['event_type'])) {
                $where_conditions[] = "e.event_type = ?";
                $params[] = $filters['event_type'];
                $types .= "i";
            }
    
            if (!empty($filters['registration_type'])) {
                $where_conditions[] = "e.registration_type = ?";
                $params[] = $filters['registration_type'];
                $types .= "i";
            }
    
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
    
            if (!empty($filters['search'])) {
                $where_conditions[] = "(e.name LIKE ? OR e.description LIKE ? OR e.event_location LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "sss";
            }
    
            // Add WHERE clause to query
            $query .= " WHERE " . implode(' AND ', $where_conditions);
    
            // Add GROUP BY
            $query .= " GROUP BY e.id";
    
            // Validate sort column
            $allowedSortColumns = ['event_date', 'created_at', 'name', 'status'];
            $sort = in_array($sort, $allowedSortColumns) ? $sort : 'event_date';
            
            // Validate order
            $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
    
            // Add ORDER BY and LIMIT
            $query .= " ORDER BY e.$sort $order LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
    
            // Prepare and execute
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
            $events = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
            mysqli_stmt_close($stmt);
    
            return $events;
    
        } catch (Exception $e) {
            Logger::error("EventService@filterEvents Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /*
    * Get total events
    * @param array $filters
    * @return int
    */
    public function getTotalEvents($filters = []) {
        try {
            $query = "SELECT COUNT(DISTINCT e.id) as total 
                     FROM events e 
                     LEFT JOIN event_registrations er ON e.id = er.event_id";
    
            $where_conditions = ["e.deleted_at IS NULL"];
            $params = [];
            $types = "";
    
            if (!empty($filters['status'])) {
                $where_conditions[] = "e.status = ?";
                $params[] = $filters['status'];
                $types .= "i";
            }
    
            if (!empty($filters['event_type'])) {
                $where_conditions[] = "e.event_type = ?";
                $params[] = $filters['event_type'];
                $types .= "i";
            }
    
            if (!empty($filters['registration_type'])) {
                $where_conditions[] = "e.registration_type = ?";
                $params[] = $filters['registration_type'];
                $types .= "i";
            }
    
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
    
            if (!empty($filters['search'])) {
                $where_conditions[] = "(e.name LIKE ? OR e.description LIKE ? OR e.event_location LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "sss";
            }
    
            $query .= " WHERE " . implode(' AND ', $where_conditions);
    
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
            $row = mysqli_fetch_assoc($result);
    
            mysqli_stmt_close($stmt);
    
            return (int)$row['total'];
    
        } catch (Exception $e) {
            Logger::error("EventService@getTotalEvents Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    // public function getTotalEvents() {
    //     $query = "SELECT COUNT(*) as total FROM events";
    //     $result = mysqli_query($this->conn, $query);
    //     return mysqli_fetch_assoc($result)['total'];
    // }

    /*
    * Validate event type
    * @param int $type
    * @return int
    */
    public function validateEventType($type) {
        $type = filter_var($type, FILTER_VALIDATE_INT);
        if (!isset(EventConstants::EVENT_TYPE_LABELS[$type])) {
            throw new Exception("Invalid event type");
        }
        return $type;
    }
    
    /*
    * Validate ticket price
    * @param float $price
    * @param int $eventType
    * @return float
    */
    public function validateTicketPrice($price, $eventType) {
        $price = filter_var($price, FILTER_VALIDATE_FLOAT);
        if ($eventType === EventConstants::EVENT_TYPE_PAID && $price <= 0) {
            throw new Exception("Paid events must have a ticket price greater than 0");
        }
        if ($eventType === EventConstants::EVENT_TYPE_FREE && $price > 0) {
            throw new Exception("Free events cannot have a ticket price");
        }
        return $price;
    }
    
    /**
    * Create a new event
    * @param array $data
    * @return int
    */
    public function createEvent($data) {
        try {
            $query = "INSERT INTO events (
                name, 
                slug, 
                description, 
                event_date, 
                registration_deadline,
                max_attendees,
                event_location,
                status,
                registration_type,
                event_type,
                ticket_price,
                created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("EventService@createEvent Prepare failed: " . mysqli_error($this->conn));
            }
            
            // Sanitize and validate inputs
            $name = $this->sanitizeInput($data['name']);
            $slug = $this->createSlug($name);
            $description = $this->sanitizeInput($data['description']);
            $eventDate = $this->validateDate($data['event_date']);
            $registrationDeadline = $this->validateDate($data['registration_deadline']);
            $maxAttendees = filter_var($data['max_attendees'], FILTER_VALIDATE_INT);
            $location = $this->sanitizeInput($data['event_location']);
            $status = $this->validateStatus($data['status'] ?? EventConstants::STATUS_ACTIVE);
            $registrationType = $this->validateRegistrationType($data['registration_type'] ?? EventConstants::REGISTRATION_USER_ONLY);
            $eventType = $this->validateEventType($data['event_type'] ?? EventConstants::EVENT_TYPE_FREE);
            $ticketPrice = $this->validateTicketPrice($data['ticket_price'] ?? 0.00, $eventType);
            $createdBy = filter_var($data['created_by'], FILTER_VALIDATE_INT);

            // Log the values for debugging
            Logger::debug("EventService@createEvent values: ", [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'eventDate' => $eventDate,
                'registrationDeadline' => $registrationDeadline,
                'maxAttendees' => $maxAttendees,
                'location' => $location,
                'status' => $status,
                'registrationType' => $registrationType,
                'eventType' => $eventType,
                'ticketPrice' => $ticketPrice,
                'createdBy' => $createdBy
            ]);

            mysqli_stmt_bind_param(
                $stmt,
                'sssssisiiidi',
                $name,
                $slug,
                $description,
                $eventDate,
                $registrationDeadline,
                $maxAttendees,
                $location,
                $status,
                $registrationType,
                $eventType,
                $ticketPrice,
                $createdBy
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("EventService@createEvent Execute failed: " . mysqli_stmt_error($stmt));
            }
            
            $eventId = mysqli_insert_id($this->conn);
            mysqli_stmt_close($stmt);
            
            return $eventId;
            
        } catch (Exception $e) {
            Logger::error("EventService@createEvent Error creating event: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get event by ID
     * @param int $eventId
     * @return array|null
     */
    public function getEvent($eventId) {
        try {
            $query = "SELECT * FROM events WHERE id = ? AND deleted_at IS NULL";
            $stmt = mysqli_prepare($this->conn, $query);
            
            if (!$stmt) {
                throw new Exception("EventService@getEvent Prepare failed: " . mysqli_error($this->conn));
            }
            
            $eventId = filter_var($eventId, FILTER_VALIDATE_INT);
            mysqli_stmt_bind_param($stmt, 'i', $eventId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("EventService@getEvent Execute failed: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $event = mysqli_fetch_assoc($result);
            
            mysqli_stmt_close($stmt);
            
            return $event;
        } catch (Exception $e) {
            Logger::error("EventService@getEvent Error fetching event: " . $e->getMessage());
            throw $e;
        }
    }
    
    /*
    * Update an event
    * @param int $eventId
    * @param array $data
    * @return bool
    */
    public function updateEvent($eventId, $data) {
        try {
            Logger::debug("EventService@updateEvent data: " . json_encode($data));

            // First check if event exists
            $event = $this->getEvent($eventId);
            if (!$event) {
                throw new Exception("EventService@updateEvent Event not found");
            }

            $query = "UPDATE events SET 
                name = ?, 
                description = ?, 
                event_date = ?,
                registration_deadline = ?,
                max_attendees = ?,
                event_location = ?,
                status = ?,
                registration_type = ?,
                event_type = ?,
                ticket_price = ?,
                updated_by = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";

            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("EventService@updateEvent Prepare failed: " . mysqli_error($this->conn));
            }
            
            // Sanitize and validate inputs
            $name = $this->sanitizeInput($data['name']);
            $description = $this->sanitizeInput($data['description']);
            $eventDate = $this->validateDate($data['event_date']);
            $registrationDeadline = $this->validateDate($data['registration_deadline']);
            $maxAttendees = filter_var($data['max_attendees'], FILTER_VALIDATE_INT);
            $location = $this->sanitizeInput($data['event_location']);
            $status = $this->validateStatus($data['status']);
            $registrationType = $this->validateRegistrationType($data['registration_type']);
            $eventType = $this->validateEventType($data['event_type']);
            $ticketPrice = $this->validateTicketPrice($data['ticket_price'], $eventType);
            $updatedBy = filter_var($data['updated_by'], FILTER_VALIDATE_INT);
            $eventId = filter_var($eventId, FILTER_VALIDATE_INT);


            // Log the values for debugging
            Logger::debug("EventService@updateEvent values: ", [
                'name' => $name,
                'description' => $description,
                'eventDate' => $eventDate,
                'registrationDeadline' => $registrationDeadline,
                'maxAttendees' => $maxAttendees,
                'location' => $location,
                'status' => $status,
                'registrationType' => $registrationType,
                'eventType' => $eventType,
                'ticketPrice' => $ticketPrice,
                'updatedBy' => $updatedBy,
                'eventId' => $eventId
            ]);
            
            mysqli_stmt_bind_param(
                $stmt,
                'ssssisiiiiii',
                $name,
                $description,
                $eventDate,
                $registrationDeadline,
                $maxAttendees,
                $location,
                $status,
                $registrationType,
                $eventType,
                $ticketPrice,
                $updatedBy,
                $eventId
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("EventService@updateEvent Execute failed: " . mysqli_stmt_error($stmt));
            }

            $affectedRows = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            
            Logger::debug("EventService@updateEvent affected rows: " . $affectedRows);
            
            return $affectedRows > 0;
        } catch (Exception $e) {
            Logger::error("EventService@updateEvent Error updating event: ", ["exception" => $e]);
            throw $e;
        }
    }
    
    /*
    * Sanitize input
    * @param mixed $input
    * @return mixed
    */
    private function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /*
    * Validate date
    * @param string $date
    * @return string
    */
    private function validateDate($date) {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            throw new Exception("EventService@validateDate Invalid date format");
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    /*
    * Validate status
    * @param int $status
    * @return int
    */
    private function validateStatus($status) {
        try {
            $status = filter_var($status, FILTER_VALIDATE_INT);
            if (!isset(EventConstants::STATUS_LABELS[$status])) {
                throw new Exception("EventService@validateStatus Invalid status");
            }
            return $status;
        } catch (Exception $exception) {
            Logger::error("EventService@validateStatus: ", ["exception" => $exception]);
        }
    }
    
    /*
    * Validate registration type
    * @param int $type
    * @return int
    */
    private function validateRegistrationType($type) {
        $type = filter_var($type, FILTER_VALIDATE_INT);
        if (!isset(EventConstants::REGISTRATION_TYPE_LABELS[$type])) {
            throw new Exception("EventService@validateRegistrationType Invalid registration type");
        }
        return $type;
    }
    
    /*
    * Create slug
    * @param string $title
    * @return string
    */
    private function createSlug($title) {
        try {
            // Convert to lowercase
            $slug = strtolower($title);
            // Replace non-alphanumeric characters with hyphens
            $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
            // Remove multiple consecutive hyphens
            $slug = preg_replace('/-+/', '-', $slug);
            // Remove leading and trailing hyphens
            $slug = trim($slug, '-');
            return $slug;
        } catch (Exception $exception) {
            Logger::error("EventService@createSlug: ", ["exception" => $exception]);
        }
    }
    
    /*
    * Search events
    * @param array $params
    * @return array
    */
    public function searchEvents($params) {
        try {
            $conditions = [];
            $values = [];
            $types = '';
            
            $query = "SELECT * FROM events WHERE deleted_at IS NULL";
            
            if (!empty($params['search'])) {
                $conditions[] = "(name LIKE ? OR description LIKE ?)";
                $searchTerm = "%" . $this->sanitizeInput($params['search']) . "%";
                $values[] = $searchTerm;
                $values[] = $searchTerm;
                $types .= 'ss';
            }
            
            if (isset($params['status'])) {
                $conditions[] = "status = ?";
                $values[] = $this->validateStatus($params['status']);
                $types .= 'i';
            }
            
            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }
            
            $query .= " ORDER BY event_date DESC";
            
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($this->conn));
            }
            
            if (!empty($values)) {
                mysqli_stmt_bind_param($stmt, $types, ...$values);
            }
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $events = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            mysqli_stmt_close($stmt);
            
            return $events;
            
        } catch (Exception $e) {
            Logger::error("Error searching events: " . $e->getMessage());
            throw $e;
        }
    }
    
    /*
    * Get event status
    * @param int $statusId
    * @return string
    */
    public function getEventStatus($statusId) {
        return EventConstants::STATUS_LABELS[$statusId] ?? 'Unknown';
    }
    
    /*
    * Get registration type
    * @param int $typeId
    * @return string
    */
    public function getRegistrationType($typeId) {
        return EventConstants::REGISTRATION_TYPE_LABELS[$typeId] ?? 'Unknown';
    }
    
    // public function validateRegistrationType($eventId, $registrationType) {
    //     $event = $this->getEvent($eventId);
        
    //     if (!$event) {
    //         throw new Exception('Event not found');
    //     }
        
    //     // Check if event is active
    //     if ($event['status'] !== EventConstants::STATUS_ACTIVE) {
    //         throw new Exception('Event is not active');
    //     }
        
    //     // Validate registration type
    //     switch ($event['registration_type']) {
    //         case EventConstants::REGISTRATION_USER_ONLY:
    //             if ($registrationType !== EventConstants::REGISTRATION_USER_ONLY) {
    //                 throw new Exception('This event requires user registration');
    //             }
    //             break;
                
    //         case EventConstants::REGISTRATION_GUEST_ONLY:
    //             if ($registrationType !== EventConstants::REGISTRATION_GUEST_ONLY) {
    //                 throw new Exception('This event is for guests only');
    //             }
    //             break;
                
    //         case EventConstants::REGISTRATION_ALL_ALLOWED:
    //             // Both types are allowed
    //             break;
                
    //         default:
    //             throw new Exception('Invalid registration type');
    //     }
        
    //     return true;
    // }

    /**
     * Soft delete an event
     * @param int $eventId
     * @param int $userId
     * @return bool
     */
    public function deleteEvent($eventId, $userId) {
        try {
            // First check if event exists and isn't already deleted
            $event = $this->getEvent($eventId);
            if (!$event) {
                throw new Exception("EventService@deleteEvent Event not found");
            }
            
            if ($event['deleted_at'] !== null) {
                throw new Exception("EventService@deleteEvent Event already deleted");
            }

            $query = "UPDATE events SET 
                deleted_at = CURRENT_TIMESTAMP,
                deleted_by = ?,
                updated_at = CURRENT_TIMESTAMP,
                updated_by = ?
                WHERE id = ?";
                
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("EventService@deleteEvent Prepare failed: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($stmt, 'iii', $userId, $userId, $eventId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("EventService@deleteEvent Execute failed: " . mysqli_stmt_error($stmt));
            }
            
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            
            return $affected > 0;
            
        } catch (Exception $e) {
            Logger::error("EventService@deleteEvent Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restore a soft-deleted event
     * @param int $eventId
     * @param int $userId
     * @return bool
     */
    public function restoreEvent($eventId, $userId) {
        try {
            // First check if event exists and is deleted
            $event = $this->getEvent($eventId);
            if (!$event) {
                throw new Exception("EventService@restoreEvent Event not found");
            }
            
            if ($event['deleted_at'] === null) {
                throw new Exception("EventService@restoreEvent Event is not deleted");
            }

            $query = "UPDATE events SET 
                deleted_at = NULL,
                deleted_by = NULL,
                updated_at = CURRENT_TIMESTAMP,
                updated_by = ?
                WHERE id = ?";
                
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("EventService@restoreEvent Prepare failed: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($stmt, 'ii', $userId, $eventId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("EventService@restoreEvent Execute failed: " . mysqli_stmt_error($stmt));
            }
            
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            
            return $affected > 0;
            
        } catch (Exception $e) {
            Logger::error("EventService@restoreEvent Error: " . $e->getMessage());
            throw $e;
        }
    }
}
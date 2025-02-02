<?php
namespace App\Services;

use App\Constants\EventConstants;
use App\Constants\UserConstants;
use App\Core\Logger;
use Exception;

class EventAttendeeService {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Get all registrations
     */
    public function getAllRegistrations($page, $limit, $sort, $order, $filters = []) {
        try {
            $params = [];
            $types = "";
            
            $query = $this->getBaseQuery();
            $query .= " FROM event_registrations er
                       LEFT JOIN events e ON er.event_id = e.id
                       LEFT JOIN users u ON er.user_id = u.id";
            $query .= $this->applyFilters($filters, $params, $types);
            
            // Add sorting and pagination
            $allowed_sort_columns = ['registration_date', 'created_at', 'event_name', 'attendee_name'];
            $sort = in_array($sort, $allowed_sort_columns) ? $sort : 'registration_date';
            $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
            
            $query .= " ORDER BY " . $sort . " " . $order;
            $query .= " LIMIT ? OFFSET ?";
            
            $offset = ($page - 1) * $limit;
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
            
            $result = $this->executeQuery($query, $params, $types);
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            Logger::error("EventAttendeeService@getAllRegistrations Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Build base query with common selects and joins
     */
    private function getBaseQuery() {
        try {
            return "SELECT 
                    er.*,
                    e.name as event_name,
                    e.event_date,
                    CASE 
                    WHEN er.user_id IS NOT NULL THEN u.name
                    ELSE er.guest_name
                END as attendee_name,
                CASE 
                    WHEN er.user_id IS NOT NULL THEN u.email
                    ELSE er.guest_email
                END as attendee_email,
                er.guest_phone as attendee_phone,
                er.registration_date,
                er.created_at,
                er.updated_at,
                CASE 
                    WHEN er.user_id IS NOT NULL THEN 'Registered User'
                    ELSE 'Guest'
                END as registration_type";
        } catch (Exception $e) {
            Logger::error("EventAttendeeService@getBaseQuery Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Apply common filters and return the WHERE clause with params
     * @param array $filters
     * @param array &$params
     * @param string &$types
     * @return string
     */
    private function applyFilters($filters, &$params, &$types) {
        $conditions = ["1=1"];
        
        if (!empty($filters['event_id'])) {
            $conditions[] = "er.event_id = ?";
            $params[] = $filters['event_id'];
            $types .= "i";
        }
        
        if (!empty($filters['registration_type'])) {
            if ($filters['registration_type'] == UserConstants::REGISTERED_USER) {
                $conditions[] = "er.user_id IS NOT NULL";
            } else if ($filters['registration_type'] == UserConstants::GUEST_USER) {
                $conditions[] = "er.user_id IS NULL";
            }
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(er.registration_date) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(er.registration_date) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }
        
        return " WHERE " . implode(" AND ", $conditions);
    }
    
    /**
     * Execute prepared statement and handle errors
     * @param string $query
     * @param array $params
     * @param string $types
     */
    private function executeQuery($query, $params = [], $types = "") {
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
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Get all active events
     * @return array
     */
    public function getActiveEvents() {
        try {
            $query = "SELECT id, name FROM events WHERE status = ? AND deleted_at IS NULL";
            $stmt = mysqli_prepare($this->conn, $query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($this->conn));
            }
            
            $status = EventConstants::STATUS_ACTIVE;
            mysqli_stmt_bind_param($stmt, "i", $status);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $events = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            mysqli_stmt_close($stmt);
            
            return $events;
            
        } catch (Exception $e) {
            Logger::error("EventAttendeeService@getActiveEvents Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get total registrations
     * @param array $filters
     * @return int
     */
    public function getTotalRegistrations($filters = []) {
        try {
            $params = [];
            $types = "";
            
            $query = "SELECT COUNT(DISTINCT er.id) as total 
                     FROM event_registrations er
                     LEFT JOIN events e ON er.event_id = e.id
                     LEFT JOIN users u ON er.user_id = u.id";
            $query .= $this->applyFilters($filters, $params, $types);
            
            $result = $this->executeQuery($query, $params, $types);
            $row = mysqli_fetch_assoc($result);
            
            return (int)$row['total'];
            
        } catch (Exception $e) {
            Logger::error("EventAttendeeService@getTotalRegistrations Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all registrations for export
     * @param array $filters
     * @return array
     */
    public function getAllRegistrationsForExport($filters = []) {
        try {
            $params = [];
            $types = "";
            
            $query = $this->getBaseQuery(true);
            $query .= " FROM event_registrations er
                       LEFT JOIN events e ON er.event_id = e.id
                       LEFT JOIN users u ON er.user_id = u.id";
            $query .= $this->applyFilters($filters, $params, $types);
            $query .= " ORDER BY er.registration_date DESC";
            
            $result = $this->executeQuery($query, $params, $types);
            $registrations = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            // Format the data for export
            foreach ($registrations as &$registration) {
                $registration['registration_date'] = date('Y-m-d H:i:s', strtotime($registration['registration_date']));
                $registration['created_at'] = date('Y-m-d H:i:s', strtotime($registration['created_at']));
                $registration['updated_at'] = date('Y-m-d H:i:s', strtotime($registration['updated_at']));
                
                $registration['attendee_name'] = $registration['attendee_name'] ?? 'N/A';
                $registration['attendee_email'] = $registration['attendee_email'] ?? 'N/A';
                $registration['attendee_phone'] = $registration['attendee_phone'] ?? 'N/A';
            }
            
            return $registrations;
            
        } catch (Exception $e) {
            Logger::error("EventAttendeeService@getAllRegistrationsForExport Error: " . $e->getMessage());
            throw $e;
        }
    }
}
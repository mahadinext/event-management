<?php

namespace App\Services;

use App\Core\Logger;
use Exception;
use App\Constants\EventConstants;

class AdminDashboardService {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Get dashboard statistics
     * @return array
     */
    public function getDashboardStats() {
        try {
            return [
                'totalEvents' => $this->getTotalEvents(),
                'activeEvents' => $this->getActiveEvents(),
                'expiredEvents' => $this->getExpiredEvents(),
                'fullEvents' => $this->getFullyBookedEvents(),
                'inactiveEvents' => $this->getInactiveEvents(),
                'totalRegistrations' => $this->getTotalRegistrations(),
                'eventsByType' => $this->getEventsByType(),
                'eventsByRegistrationType' => $this->getEventsByRegistrationType(),
                'registrationTrend' => $this->getRegistrationTrend(),
                'upcomingEvents' => $this->getUpcomingEvents(),
                'popularEvents' => $this->getPopularEvents(),
                'registrationsByStatus' => $this->getRegistrationsByStatus()
            ];
        } catch (Exception $e) {
            Logger::error("DashboardService@getDashboardStats Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getTotalEvents() {
        try {
            $query = "SELECT COUNT(*) as total FROM events WHERE deleted_at IS NULL";
            return $this->executeScalar($query);
        } catch (Exception $e) {
            Logger::error("DashboardService@getTotalEvents Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getActiveEvents() {
        try {
            $query = "SELECT COUNT(*) as total FROM events 
                     WHERE status = ? AND deleted_at IS NULL 
                     AND event_date >= CURDATE()";
            return $this->executeScalar($query, "i", [EventConstants::STATUS_ACTIVE]);
        } catch (Exception $e) {
            Logger::error("DashboardService@getActiveEvents Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getExpiredEvents() {
        try {
            $query = "SELECT COUNT(*) as total FROM events 
                     WHERE deleted_at IS NULL 
                     AND (registration_deadline < CURDATE() OR event_date < CURDATE())";
            return $this->executeScalar($query);
        } catch (Exception $e) {
            Logger::error("DashboardService@getExpiredEvents Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getFullyBookedEvents() {
        try {
            $query = "SELECT COUNT(*) as total 
                     FROM events e 
                     LEFT JOIN (
                         SELECT event_id, COUNT(*) as registration_count 
                         FROM event_registrations 
                         GROUP BY event_id
                     ) er ON e.id = er.event_id 
                     WHERE e.deleted_at IS NULL 
                     AND er.registration_count >= e.max_attendees";
            return $this->executeScalar($query);
        } catch (Exception $e) {
            Logger::error("DashboardService@getFullyBookedEvents Error: " . $e->getMessage(), [
                'query' => $query
            ]);
            throw $e;
        }
    }

    private function getInactiveEvents() {
        try {
            $query = "SELECT COUNT(*) as total FROM events 
                     WHERE status = ? AND deleted_at IS NULL";
            return $this->executeScalar($query, "i", [EventConstants::STATUS_INACTIVE]);
        } catch (Exception $e) {
            Logger::error("DashboardService@getInactiveEvents Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getTotalRegistrations() {
        try {
            $query = "SELECT COUNT(*) as total FROM event_registrations";
            return $this->executeScalar($query);
        } catch (Exception $e) {
            Logger::error("DashboardService@getTotalRegistrations Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getEventsByType() {
        try {
            $query = "SELECT 
                        event_type,
                        COUNT(*) as count
                     FROM events 
                 WHERE deleted_at IS NULL
                     GROUP BY event_type";
            return $this->executeQuery($query);
        } catch (Exception $e) {
            Logger::error("DashboardService@getEventsByType Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getEventsByRegistrationType() {
        try {
            $query = "SELECT 
                        registration_type,
                        COUNT(*) as count
                     FROM events 
                     WHERE deleted_at IS NULL
                     GROUP BY registration_type";
            return $this->executeQuery($query);
        } catch (Exception $e) {
            Logger::error("DashboardService@getEventsByRegistrationType Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getRegistrationTrend() {
        try {
            $query = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as count
                     FROM event_registrations
                     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                     GROUP BY DATE(created_at)
                     ORDER BY date";
            return $this->executeQuery($query);
        } catch (Exception $e) {
            Logger::error("DashboardService@getRegistrationTrend Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getUpcomingEvents() {
        try {
            $query = "SELECT 
                        e.name,
                        e.event_date,
                        e.max_attendees,
                        COUNT(er.id) as registrations
                 FROM events e
                 LEFT JOIN event_registrations er ON e.id = er.event_id
                 WHERE e.deleted_at IS NULL 
                 AND e.event_date >= CURDATE()
                 GROUP BY e.id
                 ORDER BY e.event_date
                     LIMIT 5";
            return $this->executeQuery($query);
        } catch (Exception $e) {
            Logger::error("DashboardService@getUpcomingEvents Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getPopularEvents() {
        try {
            $query = "SELECT 
                        e.name,
                        COUNT(er.id) as registration_count,
                        e.max_attendees
                 FROM events e
                 LEFT JOIN event_registrations er ON e.id = er.event_id
                 WHERE e.deleted_at IS NULL
                 GROUP BY e.id
                 ORDER BY registration_count DESC
                     LIMIT 5";
            return $this->executeQuery($query);
        } catch (Exception $e) {
            Logger::error("DashboardService@getPopularEvents Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getRegistrationsByStatus() {
        try {
            $query = "SELECT 
                        e.status,
                        COUNT(er.id) as count
                     FROM events e
                     LEFT JOIN event_registrations er ON e.id = er.event_id
                     WHERE e.deleted_at IS NULL
                     GROUP BY e.status";
            return $this->executeQuery($query);
        } catch (Exception $e) {
            Logger::error("DashboardService@getRegistrationsByStatus Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function executeScalar($query, $types = "", $params = []) {
        try {
            $result = $this->executeQuery($query, $types, $params, true);
            return (int)($result['total'] ?? 0);
        } catch (Exception $e) {
            Logger::error("DashboardService@executeScalar Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function executeQuery($query, $types = "", $params = [], $singleRow = false) {
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
            Logger::error("DashboardService@executeQuery Error: " . $e->getMessage());
            throw $e;
        }
    }
}
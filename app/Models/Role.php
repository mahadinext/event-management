<?php

namespace App\Models;
class Role {
    private $conn;
    private $id;
    private $data;

    public function __construct($conn, $id = null) {
        $this->conn = $conn;
        $this->id = $id;
        if ($id) {
            $this->loadRole();
        }
    }

    private function loadRole() {
        $query = "SELECT * FROM roles WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $this->data = mysqli_fetch_assoc($result);
    }

    public function givePermissionTo($permission) {
        $permissionId = $this->getPermissionId($permission);
        if ($permissionId) {
            $query = "INSERT IGNORE INTO role_has_permissions (permission_id, role_id) VALUES (?, ?)";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "ii", $permissionId, $this->id);
            return mysqli_stmt_execute($stmt);
        }
        return false;
    }

    public function revokePermissionTo($permission) {
        $permissionId = $this->getPermissionId($permission);
        if ($permissionId) {
            $query = "DELETE FROM role_has_permissions WHERE permission_id = ? AND role_id = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "ii", $permissionId, $this->id);
            return mysqli_stmt_execute($stmt);
        }
        return false;
    }

    private function getPermissionId($permissionName) {
        $query = "SELECT id FROM permissions WHERE name = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $permissionName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row ? $row['id'] : null;
    }
}
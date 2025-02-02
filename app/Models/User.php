<?php

namespace App\Models;
class User {
    private $conn;
    private $id;
    private $roles = null;
    private $permissions = null;
    private $data;

    public function __construct($conn, $id = null) {
        $this->conn = $conn;
        $this->id = $id;
        if ($id) {
            $this->loadUser();
        }
    }

    private function loadUser() {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $this->data = mysqli_fetch_assoc($result);
    }

    public function hasRole($role) {
        if ($this->roles === null) {
            $this->loadRoles();
        }
        return in_array($role, $this->roles);
    }

    public function hasAnyRole($roles) {
        if ($this->roles === null) {
            $this->loadRoles();
        }
        return !empty(array_intersect($roles, $this->roles));
    }

    public function hasPermission($permission) {
        if ($this->permissions === null) {
            $this->loadPermissions();
        }
        return in_array($permission, $this->permissions);
    }

    public function hasAnyPermission($permissions) {
        if ($this->permissions === null) {
            $this->loadPermissions();
        }
        return !empty(array_intersect($permissions, $this->permissions));
    }

    public function assignRole($role) {
        $roleId = $this->getRoleId($role);
        if ($roleId) {
            $query = "INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id) VALUES (?, 'User', ?)";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "ii", $roleId, $this->id);
            return mysqli_stmt_execute($stmt);
        }
        return false;
    }

    public function removeRole($role) {
        $roleId = $this->getRoleId($role);
        if ($roleId) {
            $query = "DELETE FROM model_has_roles WHERE role_id = ? AND model_id = ? AND model_type = 'User'";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "ii", $roleId, $this->id);
            return mysqli_stmt_execute($stmt);
        }
        return false;
    }

    private function getRoleId($roleName) {
        $query = "SELECT id FROM roles WHERE name = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $roleName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row ? $row['id'] : null;
    }

    private function loadRoles() {
        $query = "SELECT r.name 
                 FROM roles r 
                 JOIN model_has_roles mhr ON r.id = mhr.role_id 
                 WHERE mhr.model_id = ? AND mhr.model_type = 'User'";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->roles = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $this->roles[] = $row['name'];
        }
    }

    private function loadPermissions() {
        // Direct permissions
        $query1 = "SELECT p.name 
                  FROM permissions p 
                  JOIN model_has_permissions mhp ON p.id = mhp.permission_id 
                  WHERE mhp.model_id = ? AND mhp.model_type = 'User'";
        
        // Role permissions
        $query2 = "SELECT DISTINCT p.name 
                  FROM permissions p 
                  JOIN role_has_permissions rhp ON p.id = rhp.permission_id 
                  JOIN model_has_roles mhr ON rhp.role_id = mhr.role_id 
                  WHERE mhr.model_id = ? AND mhr.model_type = 'User'";
        
        $this->permissions = [];
        
        // Get direct permissions
        $stmt = mysqli_prepare($this->conn, $query1);
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $this->permissions[] = $row['name'];
        }
        
        // Get role permissions
        $stmt = mysqli_prepare($this->conn, $query2);
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            if (!in_array($row['name'], $this->permissions)) {
                $this->permissions[] = $row['name'];
            }
        }
    }

    // Getter methods
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->data['name'];
    }

    public function getEmail() {
        return $this->data['email'];
    }

    public function getRoles() {
        if ($this->roles === null) {
            $this->loadRoles();
        }
        return $this->roles;
    }

    public function getPermissions() {
        if ($this->permissions === null) {
            $this->loadPermissions();
        }
        return $this->permissions;
    }
}
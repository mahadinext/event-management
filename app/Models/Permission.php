<?php

namespace App\Models;

class Permission {
    private $conn;
    private $id;
    private $data;

    public function __construct($conn, $id = null) {
        $this->conn = $conn;
        $this->id = $id;
        if ($id) {
            $this->loadPermission();
        }
    }

    private function loadPermission() {
        $query = "SELECT * FROM permissions WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $this->data = mysqli_fetch_assoc($result);
    }
}
<?php
    function logActivity($conn, $role, $user_id, $action) {
        $stmt = $conn->prepare("INSERT INTO tblactivity_logs (user_id, role, action) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $role, $action);
        $stmt->execute();
    }
?>
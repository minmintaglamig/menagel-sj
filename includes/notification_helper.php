<?php
    function addNotification($conn, $role, $user_id, $message) {
        $table = "";
        if ($role == 'Client') {
          $table = "tblclient_notifications";
        } elseif ($role == 'Staff') {
            $table = "tblstaff_notifications";
        } elseif ($role == 'Admin') {
            $table = "tbladmin_notifications";
        }

        if ($table) {
            $redirect_url = '';
            $stmt = $conn->prepare("INSERT INTO $table (user_id, message, is_read, redirect_url) VALUES (?, ?, 0, ?)");
            $stmt->bind_param('iss', $user_id, $message, $redirect_url);
            $stmt->execute();            
        }
    }
?>
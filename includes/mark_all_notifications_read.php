<?php
    session_start();
    include('../includes/dbh.php');
    
    $user_id = $_SESSION['user_id'] ?? null;
    $role = $_SESSION['role'] ?? '';
    
    if (!$user_id || !$role) {
        http_response_code(400);
        exit();
    }
    
    $table = '';
    if ($role == 'Admin') {
        $table = 'tbladmin_notifications';
    } elseif ($role == 'Client') {
        $table = 'tblclient_notifications';
    } elseif ($role == 'Staff') {
        $table = 'tblstaff_notifications';
    }
    
    if ($table) {
        $stmt = $conn->prepare("UPDATE $table SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
    
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    } else {
        echo json_encode(['status' => 'error']);
    }
?>
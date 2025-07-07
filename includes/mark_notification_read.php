<?php
    session_start();
    include('../includes/dbh.php');
    
    $data = json_decode(file_get_contents('php://input'), true);
    $notificationId = $data['id'] ?? 0;
    
    $role = $_SESSION['role'] ?? '';
    
    if (!$notificationId || !$role) {
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
        $stmt = $conn->prepare("UPDATE $table SET is_read = 1 WHERE id = ?");
        $stmt->bind_param('i', $notificationId);
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
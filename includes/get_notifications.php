<?php
    session_start();
    include('../includes/dbh.php');
    
    $user_id = $_SESSION['user_id'] ?? null;
    $role = $_SESSION['role'] ?? '';
    
    if (!$user_id) {
        echo json_encode(['notifications' => [], 'unread_count' => 0]);
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
    
    $notifications = [];
    $unread_count = 0;
    
    if ($table) {
        $count_stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM $table WHERE user_id = ? AND is_read = 0");
        $count_stmt->bind_param('i', $user_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $count_row = $count_result->fetch_assoc();
        $unread_count = $count_row['unread_count'] ?? 0;
    
        $stmt = $conn->prepare("SELECT id, message, is_read, redirect_url FROM $table WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['id'],
                'message' => $row['message'],
                'is_read' => $row['is_read'],
                'redirect_url' => $row['redirect_url'] ?? '#'
            ];
        }
    }
    
    echo json_encode([
        'unread_count' => $unread_count,
        'notifications' => $notifications
    ]);
?>
<?php
    session_start();
    include_once 'dbh.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $staff_id = $_POST['staff_id'];
        $status = $_POST['status']; 

        if ($status !== '1' && $status !== '0') {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid status.'];
            header('Location: ../staff_management.php');
            exit();
        }
    
        $stmt = $conn->prepare("UPDATE tblusers SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $staff_id);
        $success = $stmt->execute();
        $stmt->close();
    
        if ($success) {
            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Staff status updated successfully.'];
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to update staff status.'];
        }
    
        header('Location: ../staff_management.php');
        exit();
    }
?>
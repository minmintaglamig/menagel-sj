<?php
    session_start();
    include_once 'includes/dbh.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $staff_id = $_POST['staff_id'];
        $status = $_POST['status'];
    
        $stmt = $conn->prepare("UPDATE tblstafflist SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $staff_id);
        if ($stmt->execute()) {
            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Staff status updated successfully.'];
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to update staff status.'];
        }
        $stmt->close();
        header("Location: staff_management.php");
        exit();
    }
?>
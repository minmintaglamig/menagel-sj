<?php
    session_start();
    include_once 'includes/dbh.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_schedule'])) {
        $staff_id = $_POST['staff_id'];
        $schedule = $_POST['schedule']; 
        
        $stmt = $conn->prepare("UPDATE tblstafflist SET schedule = ? WHERE id = ?");
        $stmt->bind_param("si", $schedule, $staff_id);
        if ($stmt->execute()) {
            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Schedule saved successfully.'];
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to save schedule.'];
        }
        $stmt->close();
        header("Location: staff_management.php");
        exit();
    }
?>
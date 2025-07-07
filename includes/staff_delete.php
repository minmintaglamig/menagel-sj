<?php
    session_start();
    include_once 'includes/dbh.php';
    include_once 'includes/encryption.php'; 
    
    function redirectWithPopup($type, $msg) {
        $_SESSION['popup'] = ['type' => $type, 'msg' => $msg];
        header("Location: staff_management.php");
        exit();
    }
    
    if (!isset($_SESSION['user_email'])) {
        redirectWithPopup("error", "Unauthorized access!");
    }
    
    $email = $_SESSION['user_email'];
    $query = "SELECT * FROM tblusers WHERE email = ? AND role = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        redirectWithPopup("error", "Unauthorized access!");
    }
    
    if (!isset($_GET['id'])) {
        redirectWithPopup("error", "Invalid request!");
    }
    
    $staff_id = $_GET['id'];
    
    $query = "SELECT email FROM tblstafflist WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();
    
    if (!$staff) {
        redirectWithPopup("error", "Staff not found!");
    }
    
    $staff_email = decryptData($staff['email']);
    
    $delete_user = "DELETE FROM tblusers WHERE email = ?";
    $stmt = $conn->prepare($delete_user);
    $stmt->bind_param("s", $staff_email);
    $stmt->execute();
    
    $delete_staff = "DELETE FROM tblstafflist WHERE id = ?";
    $stmt = $conn->prepare($delete_staff);
    $stmt->bind_param("i", $staff_id);
    
    if ($stmt->execute()) {
        redirectWithPopup("success", "Staff deleted successfully!");
    } else {
        redirectWithPopup("error", "Error deleting staff.");
    }
?>
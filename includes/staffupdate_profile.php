<?php
    session_start();
    include_once 'dbh.php';
    include_once 'encryption.php'; 
    
    if (!isset($_SESSION['user_email'])) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
        header("Location: staff_profile.php");
        exit();
    }
    
    $staff_email = decryptData($_SESSION['user_email']);
    
    $staff_query = "SELECT * FROM tblusers WHERE email = ?";
    $stmt = $conn->prepare($staff_query);
    $stmt->bind_param("s", $staff_email);
    $stmt->execute();
    $staff_result = $stmt->get_result();
    $staff_info = $staff_result->fetch_assoc();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
        $new_email = encryptData($_POST['email']);
        $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
        $update_query = "UPDATE tblusers SET email = ?, password = ? WHERE email = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sss", $new_email, $new_password, $staff_email);
    
        if ($stmt->execute()) {
            $_SESSION['email'] = $new_email;
            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Profile updated successfully!'];
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error updating profile'];
        }
    
        header("Location: staff_profile.php");
        exit();
    }
?>
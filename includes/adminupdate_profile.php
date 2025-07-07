<?php
    session_start();
    include('dbh.php');
    
    if (!isset($_SESSION['email'])) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
        header("Location: ../index.php");
        exit();
    }
    
    $admin_email = $_SESSION['email'];
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_email = $_POST['email'];
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
    
        if (empty($new_email)) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Email is required!'];
            header("Location: ../admin_profile.php");
            exit();
        }
    
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Passwords do not match!'];
                header("Location: ../admin_profile.php");
                exit();
            }
    
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE tblusers SET email = ?, password = ? WHERE email = ? AND role = 'admin'";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sss", $new_email, $hashed_password, $admin_email);
        } else {
            $update_query = "UPDATE tblusers SET email = ? WHERE email = ? AND role = 'admin'";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ss", $new_email, $admin_email);
        }
    
        if ($stmt->execute()) {
            $_SESSION['email'] = $new_email;
            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Profile updated successfully!'];
            header("Location: ../admin_profile.php");
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error updating profile.'];
            header("Location: ../admin_profile.php");
        }
    
        $stmt->close();
    }
    
    $conn->close();
?>
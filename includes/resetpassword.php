<?php
    session_start();
    include_once 'dbh.php';
    include_once 'encryption.php';
    
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $token = $_POST['token'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
    
        if (empty($token) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'All fields are required.'];
            header("Location: ../reset_password.php?token=" . urlencode($token));
            exit();
        }
    
        if ($new_password !== $confirm_password) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Passwords do not match.'];
            header("Location: ../reset_password.php?token=" . urlencode($token));
            exit();
        }
    
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    
        $query = $conn->prepare("SELECT email, reset_expires FROM password_reset_tokens WHERE reset_token = ?");
        $query->bind_param("s", encryptData($token));
        $query->execute();
        $result = $query->get_result();
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $reset_expires = strtotime($row['reset_expires']);
            $current_time = time();
    
            if ($reset_expires < $current_time) {
                $delete = $conn->prepare("DELETE FROM password_reset_tokens WHERE reset_token = ?");
                $delete->bind_param("s", encryptData($token));
                $delete->execute();
    
                $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Token has expired. Please request a new password reset.'];
                header("Location: ../request_reset.php");
                exit();
            }
    
            $decrypted_email = decryptData($row['email']);
            $update = $conn->prepare("UPDATE tblusers SET password=? WHERE email=?");
            $update->bind_param("ss", $hashed_password, $decrypted_email);
    
            if (!$update->execute()) {
                error_log("SQL Error: " . $update->error);
                $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to update password.'];
                header("Location: ../reset_password.php?token=" . urlencode($token));
                exit();
            }
    
            $delete = $conn->prepare("DELETE FROM password_reset_tokens WHERE reset_token = ?");
            $delete->bind_param("s", encryptData($token));
            $delete->execute();
    
            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Your password has been successfully reset.'];
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid or expired token.'];
        }
    
        header("Location: ../reset_password.php?token=" . urlencode($token));
        exit();
    }
?>
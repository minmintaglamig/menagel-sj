<?php
    session_start();
    include_once 'dbh.php';
    include_once 'encryption.php';
    
    if (!isset($_SESSION['user_email'])) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
        header("Location: index.php");
        exit();
    }
    
    $email = $_SESSION['email'];
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fname = encryptData($_POST['fname']);
        $mname = encryptData($_POST['mname']);
        $lname = encryptData($_POST['lname']);
        $mobile = encryptData($_POST['mobile']);
    
        $conn->begin_transaction();
    
        try {
            $updateClientQuery = "UPDATE tblclientlist SET fname = ?, mname = ?, lname = ?, mobile = ? WHERE email = ?";
            if ($stmt = $conn->prepare($updateClientQuery)) {
                $stmt->bind_param("sssss", $fname, $mname, $lname, $mobile, $email);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating client info.");
                }
                $stmt->close();
            }
    
            if (!empty($_POST['password'])) {
                $new_password = $_POST['password'];
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
                $updatePasswordQuery = "UPDATE tblusers SET password = ? WHERE email = ?";
                if ($stmt = $conn->prepare($updatePasswordQuery)) {
                    $stmt->bind_param("ss", $hashed_password, $email);
                    if (!$stmt->execute()) {
                        throw new Exception("Error updating password.");
                    }
                    $stmt->close();
                }
            }
    
            $conn->commit();
            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Profile updated successfully!'];
            header("Location: profile.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to update profile. Please try again.'];
            header("Location: profile.php");
            exit();
        }
    
        $conn->close();
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid request method.'];
        header("Location: profile.php");
        exit();
    }
?>
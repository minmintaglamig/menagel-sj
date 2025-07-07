<?php 
    session_start();
    include_once 'dbh.php';
    include_once 'encryption.php';
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $email_encrypted = encryptData($email);
    
        $stmt = $conn->prepare("SELECT id, email, password, role, is_verified, is_active, is_disabled_by_admin FROM tblusers WHERE email = ?");
        $stmt->bind_param("s", $email_encrypted);
        $stmt->execute();
        $stmt->store_result();
    
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $stored_email, $stored_password, $role, $is_verified, $is_active, $is_disabled_by_admin);
            $stmt->fetch();
    
            if (password_verify($password, $stored_password)) {
                if ($role === 'Staff') {
                    $staff_check_stmt = $conn->prepare("SELECT email FROM tblstafflist WHERE email = ?");
                    $staff_check_stmt->bind_param("s", $email_encrypted);
                    $staff_check_stmt->execute();
                    $staff_check_stmt->store_result();
    
                    if ($staff_check_stmt->num_rows === 0) {
                        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Account not found in staff records.'];
                        header("Location: ../login.php");
                        exit();
                    }
                    $staff_check_stmt->close();
    
                    $today = date("l");
    

                    $sched_stmt = $conn->prepare("SELECT COUNT(*) FROM tblstaff_schedule s INNER JOIN tblstafflist l ON s.staff_id = l.id WHERE l.email = ? AND s.day_of_week = ?");
                    $sched_stmt->bind_param("ss", $email_encrypted, $today);
                    $sched_stmt->execute();
                    $sched_stmt->bind_result($scheduled_today);
                    $sched_stmt->fetch();
                    $sched_stmt->close();
    
                    $staff_stmt = $conn->prepare("SELECT status FROM tblstafflist WHERE email = ?");
                    $staff_stmt->bind_param("s", $email_encrypted);
                    $staff_stmt->execute();
                    $staff_stmt->bind_result($status);
                    $staff_stmt->fetch();
                    $staff_stmt->close();
    
                    if ($status === "Active" && $is_active == 1) {
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['user_email'] = $email_encrypted;
                        $_SESSION['role'] = $role;
                        header("Location: ../staff.php");
                        exit();
                    } else {
                        $reason = $is_disabled_by_admin ? "Your account is disabled by the admin." : "You are not scheduled today.";
                        $_SESSION['popup'] = ['type' => 'error', 'msg' => $reason];
                        header("Location: ../login.php");
                        exit();
                    }
                } elseif ($role === 'Client') {
                    $client_check_stmt = $conn->prepare("SELECT email FROM tblclientlist WHERE email = ?");
                    $client_check_stmt->bind_param("s", $email_encrypted);
                    $client_check_stmt->execute();
                    $client_check_stmt->store_result();
    
                    if ($client_check_stmt->num_rows === 0) {
                        $app_check_stmt = $conn->prepare("SELECT id FROM tblapplication WHERE email = ?");
                        $app_check_stmt->bind_param("s", $email_encrypted);
                        $app_check_stmt->execute();
                        $app_check_stmt->store_result();
    
                        if ($app_check_stmt->num_rows === 0) {
                            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'You have an account but need to fill out the application form first.'];
    
                            $_SESSION['popup_redirect'] = 'clienttwo.php'; 
                            header("Location: ../login.php");
                            exit();
                        }
                        $app_check_stmt->close();
                    }
                    $client_check_stmt->close();
                    
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_email'] = $email_encrypted;
                    $_SESSION['role'] = $role;
                    header("Location: ../client.php");
                    exit();
                } else {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_email'] = $email_encrypted;
                    $_SESSION['role'] = $role;
                    header("Location: ../admin.php");
                    exit();
                }
            } else {
                $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Incorrect password.'];
                header("Location: ../login.php");
                exit();
            }
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Account not found.'];
            header("Location: ../login.php");
            exit();
        }
    }
?>
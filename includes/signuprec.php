<?php
    session_start();
    include_once 'dbh.php';
    include_once 'encryption.php';
    require '../vendor/autoload.php';
    require 'config.php'; 
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $role = 'Client';
        $verify_code = rand(100000, 999999);
    
        if ($password !== $confirm_password) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Passwords do not match!'];
            header("Location: ../signup.php");
            exit();
        }
    
        $email_encrypted = encryptData($email);  
    
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
        $check_stmt = $conn->prepare("SELECT email FROM tblusers WHERE email = ? UNION SELECT email FROM tblverifications WHERE email = ?");
        $check_stmt->bind_param("ss", $email_encrypted, $email_encrypted);
        $check_stmt->execute();
        $check_stmt->store_result();
    
        if ($check_stmt->num_rows > 0) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Email already exists or is pending verification.'];
            header("Location: ../signup.php");
            exit();
        }
        $check_stmt->close();
    
        $stmt = $conn->prepare("INSERT INTO tblverifications (email, password, role, verify_code, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $email_encrypted, $hashed_password, $role, $verify_code);
    
        if ($stmt->execute()) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
    
                $mail->setFrom('menagelsj@gmail.com', 'Menagel SJ');
                $mail->addAddress($email);
    
                $mail->isHTML(true);
                $mail->Subject = "Verify Your Email";
                $mail->Body = "
                    <h2>Email Verification</h2>
                    <p>Use the verification code below to activate your account:</p>
                    <h1 style='color:blue;'>$verify_code</h1>
                    <p>Enter this code on the verification page to complete your registration.</p>
                ";
    
                $mail->send();
                $_SESSION['popup'] = ['type' => 'alert', 'msg' => 'Verification email sent. Please check your email.'];
                header("Location: ../verify.php?email=" . urlencode($email_encrypted));
                exit();
    
            } catch (Exception $e) {
                $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error: Email could not be sent. Please try again later.'];
                header("Location: ../signup.php");
                exit();
            }
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Registration failed. Please try again.'];
            header("Location: ../signup.php");
            exit();
        }
    }
?>
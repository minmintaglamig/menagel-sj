<?php
    session_start();
    include_once 'dbh.php';
    include_once 'encryption.php';
    include_once 'config.php';
    require '../vendor/autoload.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['user_email']);
    
        $email_encrypted = encryptData($email);
    
        $stmt = $conn->prepare("SELECT verify_code FROM tblverifications WHERE email = ?");
        if (!$stmt) {
            error_log("Database error: " . $conn->error);
            $_SESSION['message'] = "Database error. Please try again.";
            header("Location: resend_verification.php");
            exit();
        }
    
        $stmt->bind_param("s", $email_encrypted);
        $stmt->execute();
        $stmt->store_result();
    
        if ($stmt->num_rows === 0) {
            $_SESSION['message'] = "No pending verification found for this email.";
            header("Location: resend_verification.php");
            exit();
        }
    
        $stmt->bind_result($old_verify_code);
        $stmt->fetch();
        $stmt->close();
    
        $new_verify_code = rand(100000, 999999);
    
        $update_stmt = $conn->prepare("UPDATE tblverifications SET verify_code = ? WHERE email = ?");
        if (!$update_stmt) {
            error_log("Database error: " . $conn->error);
            $_SESSION['message'] = "Error updating verification code. Please try again.";
            header("Location: resend_verification.php");
            exit();
        }
    
        $update_stmt->bind_param("ss", $new_verify_code, $email_encrypted);
        if ($update_stmt->execute()) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME; 
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = SMTP_SECURE;
                $mail->Port = SMTP_PORT;
    
                $mail->setFrom('menagelsj@gmail.com', 'Menagel SJ');
                $mail->addAddress($email);
    
                $mail->isHTML(true);
                $mail->Subject = "Resend Verification Code";
                $mail->Body = "
                    <h2>New Verification Code</h2>
                    <p>Your new verification code is:</p>
                    <h1 style='color:blue;'>$new_verify_code</h1>
                    <p>Enter this code on the verification page to activate your account.</p>
                ";
    
                $mail->send();
                $_SESSION['message'] = "New verification code sent successfully.";
                header("Location: verify.php?email=" . urlencode($email));
                exit();
    
            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                $_SESSION['message'] = "Error: Could not send email. Please try again.";
                header("Location: resend_verification.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Error updating verification code. Please try again.";
            header("Location: resend_verification.php");
            exit();
        }
    }
?>
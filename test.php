<?php
    require 'vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'menagelsj@gmail.com';
        $mail->Password = 'luhv bkem ydjj ksdj';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
    
        $mail->setFrom('menagelsj@gmail.com', 'Menagel SJ');
        $mail->addAddress('pjarmine000@gmail.com');
    
        $mail->isHTML(true);
        $mail->Subject = "Test Email";
        $mail->Body = "This is a test email from PHPMailer.";
    
        $mail->send();
        echo "✅ Test email sent successfully!";
    } catch (Exception $e) {
        echo "❌ Error: {$mail->ErrorInfo}";
    }
?>

<?php
    phpinfo();
?>
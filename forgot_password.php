<?php
session_start();
include('includes/dbh.php');
require 'vendor/autoload.php';
require 'includes/config.php';
require 'includes/encryption.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $encrypted_email = encryptData($email);

    $query = $conn->prepare("SELECT * FROM tblusers WHERE email = ?");
    $query->bind_param("s", $encrypted_email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50)); 
        $encrypted_token = encryptData($token);
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $insert = $conn->prepare("INSERT INTO password_reset_tokens (email, reset_token, reset_expires) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $encrypted_email, $encrypted_token, $expires);
        $insert->execute();

        $reset_link = "http://192.168.18.53/wifi/reset_password.php?token=" . urlencode($token);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;

            $mail->setFrom(SMTP_USERNAME, 'Support');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";
            $mail->Body = "
                <h2>Password Reset</h2>
                <p>Click the link below to reset your password. This link will expire in 1 hour.</p>
                <a href='$reset_link' style='padding:10px 20px; background-color:#6D2323; color:white; text-decoration:none; border-radius:5px; display:block; text-align:center; width: 100%; max-width: 300px; margin: 0 auto;'>Reset Password</a>
                <p>If you did not request this, ignore this email.</p>
            ";

            $mail->send();
            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'A password reset email has been sent.'];
            header("Location: forgot_password.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Email could not be sent. Please try again.'];
            header("Location: forgot_password.php");
            exit();
        }
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Email not found!'];
        header("Location: forgot_password.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="javascript/popup.js"></script>
    <script defer src="javascript/script.js"></script>
    <title>Forgot Password</title>
</head>
<body>
<?php if (isset($_SESSION['popup'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    showPopup('<?= htmlspecialchars($_SESSION['popup']['type'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($_SESSION['popup']['msg'], ENT_QUOTES, 'UTF-8') ?>    ');
  });
</script>
<?php unset($_SESSION['popup']); endif; ?>

<div id="popup-container" class="popup-container"></div>

<div class="auth-container">
  <form action="forgot_password.php" method="POST" class="form">
    <p class="title">Forgot Password</p>

    <p class="description">
      Enter your registered email address below and we'll send you a link to reset your password.
    </p>

    <div class="form__group">
      <input
        class="form__field"
        type="email"
        name="email"
        id="email"
        placeholder="Email Address"
        required
      />
      <label class="form__label" for="email">Email: </label>
    </div>

    <button type="submit" class="submit">Reset Password</button>
  </form>
</div>

<?php include('faq_widget.php'); ?>

</body>
</html>
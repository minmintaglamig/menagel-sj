<?php
session_start();
include('includes/dbh.php');
include('includes/config.php');
include('includes/encryption.php');
require 'vendor/autoload.php';

$email_encrypted = isset($_GET['email']) ? $_GET['email'] : (isset($_POST['email']) ? $_POST['email'] : '');

if (empty($email_encrypted)) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid request.'];
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['email'], $_POST['verify_code'])) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid request.'];
        header("Location: verify.php?email=" . urlencode($email_encrypted));
        exit;
    }

    $verify_code = $_POST['verify_code'];
    $email_encrypted = $_POST['email'];

    $stmt = $conn->prepare("SELECT email, password, role, verify_code FROM tblverifications WHERE email = ?");
    $stmt->bind_param("s", $email_encrypted);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Verification failed: No matching record.'];
        header("Location: verify.php?email=" . urlencode($email_encrypted));
        exit;
    }

    $stmt->bind_result($encrypted_email, $hashed_password, $role, $stored_code);
    $stmt->fetch();
    $stmt->close();

    if ($verify_code !== $stored_code) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Incorrect verification code.'];
        header("Location: verify.php?email=" . urlencode($email_encrypted));
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO tblusers (email, password, role, created_at, is_verified) 
                            VALUES (?, ?, ?, NOW(), 1)");
    $stmt->bind_param("sss", $encrypted_email, $hashed_password, $role);

    if ($stmt->execute()) {
        $delete_stmt = $conn->prepare("DELETE FROM tblverifications WHERE email = ?");
        $delete_stmt->bind_param("s", $email_encrypted);
        $delete_stmt->execute();
        $delete_stmt->close();

        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Account successfully verified. You can now log in.'];
        header("Location: login.php");
        exit;
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error verifying account. Please try again.'];
        header("Location: verify.php?email=" . urlencode($email_encrypted));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/verify.css">
    <link rel="stylesheet" href="css/popup.css">
    <script src="javascript/popup.js"></script>
    <title>Verify Your Email</title>
    <style>
        .resend-link {
            text-decoration: underline;
            color: blue;
            cursor: pointer;
        }
        .resend-link:hover {
            color: darkblue;
        }
    </style>
</head>
<body>
<?php if (isset($_SESSION['popup'])): ?>
<script>
    window.onload = function() {
        showPopup('<?= $_SESSION['popup']['type'] ?>', '<?= $_SESSION['popup']['msg'] ?>');
    };
</script>
<?php unset($_SESSION['popup']); endif; ?>

    <form action="verify.php" method="POST">
        <h2>Email Verification</h2>

        <?php
            if (isset($_SESSION['message'])) {
                echo "<p style='color: red;'>" . $_SESSION['message'] . "</p>";
                unset($_SESSION['message']);
            }
        ?>

        <p>We have sent a verification code to your email.</p>

        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_encrypted); ?>">
        <input type="text" name="verify_code" placeholder="Enter verification code" required>
        <button type="submit">Verify</button>

        <!-- <p>
            <a href="includes/resend_verification.php?email=<?php echo urlencode($email_encrypted); ?>" class="resend-link">
                Didn't receive a code? Resend Verification
            </a>
        </p> -->
    </form>
</body>
</html>
<?php $conn->close(); ?>
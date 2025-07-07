<?php
    session_start();
    include('includes/dbh.php');
    include('includes/encryption.php');
    
    if (!isset($_SESSION['user_email'])) {
        header("Location: login.php");
        exit();
    }
    
    $email = $_SESSION['user_email']; 
    
    $query = "SELECT * FROM tblusers WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        echo "User not found.";
        exit();
    }
    
    $decrypted_email = decryptData($user['email']);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($_POST['password'])) {
            $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
            $update_query = "UPDATE tblusers SET password = ? WHERE email = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ss", $new_password, $email);
    
            if ($stmt->execute()) {
                $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Password updated successfully!'];
                header("Location: admin_profile.php");
                exit();
            } else {
                $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error updating password.'];
            }            
        } else {
            $_SESSION['popup'] = ['type' => 'warning', 'msg' => 'Please enter a new password.'];
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="pictures/logo.png" type= "image">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/toggle.css">
    <script defer src="javascript/script.js"></script>
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="javascript/popup.js"></script>
    <title>Profile Management</title>
</head>
<body>
<?php
if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header('Location: login.php');
    exit();
}
if (isset($_SESSION['popup'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    showPopup('<?= $_SESSION['popup']['type'] ?>', '<?= $_SESSION['popup']['msg'] ?>');
  });
</script>
<?php unset($_SESSION['popup']); endif; ?>
<div id="popup-container" class="popup-container"></div>

    <?php include('header.php'); ?>
    <div class="main-container">
        <?php include('admin_sidebar.html'); ?>
    </div>
    
    <div class="container">
        <h1>Profile Management</h1>
        <form method="POST" onsubmit="return validatePassword()">
            <label>Email</label>
            <input type="email" value="<?= htmlspecialchars($decrypted_email) ?>" required readonly>

            <label>New Password</label>
            <div class="password-container">
                <input type="password" name="password" id="password" required>
                <i class='bx bx-show toggle-password' onclick="togglePassword('password')"></i>
            </div>
            
            <label>Confirm Password</label>
            <div class="password-container">
                <input type="password" id="confirm_password" required>
                <i class='bx bx-show toggle-password' onclick="togglePassword('confirm_password')"></i>
            </div>
            
            <p id="error-message" class="error" style="display: none;">Passwords do not match!</p>

            <button type="submit">Save Changes</button>
        </form>
    </div>

    <script src="javascript/sidebar-toggle.js"></script>
    <script>
        function togglePassword(fieldId) {
            let field = document.getElementById(fieldId);
            let icon = field.nextElementSibling;
            if (field.type === "password") {
                field.type = "text";
                icon.classList.replace("bx-show", "bx-hide");
            } else {
                field.type = "password";
                icon.classList.replace("bx-hide", "bx-show");
            }
        }

        function validatePassword() {
            let password = document.getElementById("password").value;
            let confirmPassword = document.getElementById("confirm_password").value;
            let errorMessage = document.getElementById("error-message");

            if (password !== confirmPassword) {
                errorMessage.style.display = "block";
                return false;
            } else {
                errorMessage.style.display = "none";
                return true;
            }
        }
    </script>
</body>
</html>
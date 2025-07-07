<?php
session_start();
include 'includes/dbh.php';
include 'includes/encryption.php';

if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: admin_access.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $email = encryptData($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO tblusers (email, password, role) VALUES (?, ?, 'Admin')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);

    if ($stmt->execute()) {
        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Admin account created successfully!'];
    } else {
        $_SESSION['popup'] = ['type' => 'alert', 'msg' => 'Error: ' . $conn->error];
    }

    $stmt->close();
    $conn->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <link rel="icon" href="pictures/logo.png" type= "image">
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/toggle.css" />
  <link rel="stylesheet" href="css/popup.css" />
  <script src="javascript/popup.js"></script>
  <title>Create Admin</title>
</head>
<body>

<?php
if (isset($_SESSION['popup'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    showPopup('<?= $_SESSION['popup']['type'] ?>', '<?= $_SESSION['popup']['msg'] ?>');
  });
</script>
<?php unset($_SESSION['popup']); endif; ?>
<div id="popup-container" class="popup-container"></div>

<div class="container">
  <form action="" method="POST" class="form" onsubmit="return validatePassword()">
    <h2>Create Admin Account</h2>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>New Password:</label>
    <div class="password-container">
      <input type="password" name="password" id="password" required oninput="checkPasswords()">
      <i class='bx bx-show toggle-password' onclick="togglePassword('password')"></i>
    </div>

    <label>Confirm Password:</label>
    <div class="password-container">
      <input type="password" name="confirm_password" id="confirm_password" required oninput="checkPasswords()">
      <i class='bx bx-show toggle-password' onclick="togglePassword('confirm_password')"></i>
    </div>

    <p id="error-message" class="error" style="display: none;">⚠ Passwords do not match!</p>

    <button type="submit" name="register">Register</button>
  </form>
</div>

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

function checkPasswords() {
  let password = document.getElementById("password").value;
  let confirmPassword = document.getElementById("confirm_password").value;
  let errorMessage = document.getElementById("error-message");

  if (password !== confirmPassword && confirmPassword.length > 0) {
    errorMessage.style.display = "block";
  } else {
    errorMessage.style.display = "none";
  }
}

function validatePassword() {
  let password = document.getElementById("password").value;
  let confirmPassword = document.getElementById("confirm_password").value;

  if (password !== confirmPassword) {
    alert("⚠ Passwords do not match!");
    return false;
  }
  return true;
}
</script>

</body>
</html>
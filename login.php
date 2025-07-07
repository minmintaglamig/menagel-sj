<?php 
session_start();
include_once 'includes/daily_status_update.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/auth.css" />
  <link rel="stylesheet" href="css/toggle.css" />
  <link rel="stylesheet" href="css/popup.css" />
  <script src="javascript/popup.js"></script>
  <title>Log-In</title>
</head>
<body>

<?php
if (isset($_SESSION['user_email'])) {
    header("Location: client.php");
    exit();
}

if (isset($_SESSION['popup'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const type = <?= json_encode($_SESSION['popup']['type']) ?>;
    const msg = <?= json_encode($_SESSION['popup']['msg']) ?>;
    showPopup(type, msg);

    // If a redirect is intended after popup
    <?php if (isset($_SESSION['popup_redirect'])): ?>
      setTimeout(function () {
        window.location.href = <?= json_encode($_SESSION['popup_redirect']) ?>;
      }, 3000); // 3 seconds delay
    <?php endif; ?>
  });
</script>

<?php unset($_SESSION['popup']); endif; ?>

<div id="popup-container" class="popup-container"></div>

<div class="auth-container">
  <form action="includes/loginrec.php" method="POST">
    <h1>Welcome!</h1>

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

    <div class="form__group password-container">
      <input
        class="form__field"
        type="password"
        name="password"
        id="password"
        placeholder="Password"
        required
      />
      <label class="form__label" for="password">Password:</label>
      <i class="bx bx-show toggle-password" onclick="togglePassword('password')"></i>
    </div>

    <button type="submit" class="submit">Login</button>
    <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
  </form>
</div>

<?php include('faq_widget.php'); ?>

<script>
function togglePassword(inputId) {
  const passwordInput = document.getElementById(inputId);
  const icon = document.querySelector(`.toggle-password`);

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    icon.classList.remove("bx-show");
    icon.classList.add("bx-hide");
  } else {
    passwordInput.type = "password";
    icon.classList.remove("bx-hide");
    icon.classList.add("bx-show");
  }
}
</script>
</body>
</html>
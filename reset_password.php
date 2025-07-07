<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/toggle.css" />
  <link rel="stylesheet" href="css/auth.css" />
  <link rel="stylesheet" href="css/popup.css" />
  <title>Reset Password</title>
  <style>
    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 20px;
      cursor: pointer;
    }
    .popup {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 20px;
      border-radius: 5px;
      font-weight: bold;
      color: #fff;
      z-index: 9999;
    }
    .popup.success { background-color: #4CAF50; }
    .popup.error { background-color: #f44336; }
    .password-validation .instruction {
      margin: 5px 0;
      font-size: 14px;
    }
    .valid { color: green; }
    .invalid { color: red; }
    #error-message {
      color: red;
      display: none;
      font-size: 14px;
      margin: 10px 0;
    }
  </style>
</head>
<body>

<div class="auth-container">
  <form action="includes/resetpassword.php" method="POST" id="reset-password-form">
    <h1>Reset Password</h1>

    <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">

    <div class="form__group">
      <input class="form__field" type="password" name="new_password" id="new_password" placeholder="New Password" required />
      <label class="form__label" for="new_password">New Password:</label>
      <i class="bx bx-show toggle-password" onclick="togglePassword('new_password')"></i>
    </div>

    <div class="form__group">
      <input class="form__field" type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required />
      <label class="form__label" for="confirm_password">Confirm Password:</label>
      <i class="bx bx-show toggle-password" onclick="togglePassword('confirm_password')"></i>
    </div>

    <div id="password-validation" class="password-validation">
      <p id="length" class="instruction">Password must be at least 8 characters.</p>
      <p id="uppercase" class="instruction">Password must contain at least one uppercase letter. (A-Z)</p>
      <p id="special" class="instruction">Password must contain at least one special character. (e.g., !@#$%^&*)</p>
    </div>

    <p id="error-message">Passwords do not match!</p>

    <button type="submit" name="btnreset" disabled>Reset Password</button>

    <p>Back to <a href="login.php">Login</a></p>
  </form>
</div>

<?php if (isset($_SESSION['popup'])): ?>
  <div class="popup <?= $_SESSION['popup']['type']; ?>">
    <?= htmlspecialchars($_SESSION['popup']['msg']); ?>
  </div>
  <script>
    setTimeout(() => document.querySelector('.popup')?.remove(), 4000);
  </script>
  <?php unset($_SESSION['popup']); ?>
<?php endif; ?>

<?php include('faq_widget.php'); ?>

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
    let newPassword = document.getElementById("new_password").value;
    let confirmPassword = document.getElementById("confirm_password").value;

    const minLength = 8;
    const uppercase = /[A-Z]/;
    const specialChar = /[!@#$%^&*(),.?":{}|<>[\]\\;:'",._+-=<>]/;

    const lengthValid = newPassword.length >= minLength;
    const uppercaseValid = uppercase.test(newPassword);
    const specialValid = specialChar.test(newPassword);

    const passwordMatch = newPassword === confirmPassword;

    document.getElementById("length").classList.toggle("valid", lengthValid);
    document.getElementById("length").classList.toggle("invalid", !lengthValid);
    document.getElementById("uppercase").classList.toggle("valid", uppercaseValid);
    document.getElementById("uppercase").classList.toggle("invalid", !uppercaseValid);
    document.getElementById("special").classList.toggle("valid", specialValid);
    document.getElementById("special").classList.toggle("invalid", !specialValid);
    document.getElementById("error-message").style.display = passwordMatch ? "none" : "block";

    const submitButton = document.querySelector('button[type="submit"]');
    submitButton.disabled = !(lengthValid && uppercaseValid && specialValid && passwordMatch);

    return lengthValid && uppercaseValid && specialValid && passwordMatch;
  }

  document.getElementById("new_password").addEventListener("input", validatePassword);
  document.getElementById("confirm_password").addEventListener("input", validatePassword);

  document.getElementById("reset-password-form").addEventListener("submit", function(event) {
    if (!validatePassword()) {
      event.preventDefault();
    }
  });
</script>

</body>
</html>
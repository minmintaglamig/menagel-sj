<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/toggle.css" />
  <link rel="stylesheet" href="css/auth.css" />
  <link rel="stylesheet" href="css/popup.css" />
  <script defer src="javascript/popup.js"></script>
  <title>Sign-Up</title>
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
    showPopup('<?= $_SESSION['popup']['type'] ?>', '<?= $_SESSION['popup']['msg'] ?>');
  });
</script>

<?php unset($_SESSION['popup']); endif; ?>

<div id="popup-container" class="popup-container"></div>

<div class="auth-container">
  <form action="includes/signuprec.php" method="POST" id="signup-form">
    <h1>Sign-Up</h1>

    <div class="form__group">
      <input
        class="form__field"
        type="email"
        name="email"
        id="email"
        placeholder="Email Address"
        required
      />
      <label class="form__label" for="email">Email:</label>
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

    <div class="form__group password-container">
      <input
        class="form__field"
        type="password"
        name="confirm_password"
        id="confirm_password"
        placeholder="Confirm Password"
        required
      />
      <label class="form__label" for="confirm_password">Confirm Password:</label>
      <i class="bx bx-show toggle-password" onclick="togglePassword('confirm_password')"></i>
    </div>

    <div id="password-validation" style="display: none;">
      <p id="length" class="instruction">Minimum 8 characters</p>
      <p id="uppercase" class="instruction">At least 1 uppercase letter (A-Z)</p>
      <p id="special" class="instruction">At least 1 special character or number (!@#$%^&*()_+[]{}|;:'",.<>?)</p>
    </div>

    <p id="error-message" class="error">Passwords do not match!</p>

    <button type="submit" name="btnsave" onclick="showSignupLoader()">Sign-Up</button>

    <p>Already have an account? <a href="login.php">Log in here</a></p>
  </form>
</div>

<?php include('faq_widget.php'); ?>

<script>
function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const icon = input.nextElementSibling;
  input.type = input.type === "password" ? "text" : "password";
  icon.classList.toggle("bx-show");
  icon.classList.toggle("bx-hide");
}

function validatePassword() {
  const password = document.getElementById('password').value;
  const confirmPassword = document.getElementById('confirm_password').value;

  const validationElement = document.getElementById('password-validation');
  validationElement.style.display = password.length > 0 ? 'block' : 'none';

  const minLengthValid = password.length >= 8;
  const uppercaseValid = /[A-Z]/.test(password);
  const specialValid = /[^a-zA-Z]/.test(password);

  document.getElementById('length').classList.toggle('valid', minLengthValid);
  document.getElementById('length').classList.toggle('invalid', !minLengthValid);
  document.getElementById('uppercase').classList.toggle('valid', uppercaseValid);
  document.getElementById('uppercase').classList.toggle('invalid', !uppercaseValid);
  document.getElementById('special').classList.toggle('valid', specialValid);
  document.getElementById('special').classList.toggle('invalid', !specialValid);

  const errorMessage = document.getElementById('error-message');
  errorMessage.style.display = password !== confirmPassword ? 'block' : 'none';

  const submitButton = document.querySelector('button[type="submit"]');
  submitButton.disabled = !(minLengthValid && uppercaseValid && specialValid && password === confirmPassword);
}

document.getElementById('password').addEventListener('input', validatePassword);
document.getElementById('confirm_password').addEventListener('input', validatePassword);
document.addEventListener('DOMContentLoaded', validatePassword);

// Load email from localStorage (prefilled from application form)
document.addEventListener('DOMContentLoaded', () => {
  const emailInput = document.getElementById("email");
  const saved = localStorage.getItem("prefill_signup_email");
  if (saved && emailInput.value === "") {
    emailInput.value = saved;
  }
});

function showSignupLoader() {
  document.body.insertAdjacentHTML("beforeend", `
    <div class="terminal-loader" id="loadingTerminal">
      <div class="terminal-header">
        <span class="terminal-title">Status</span>
        <span class="terminal-controls">
          <span class="control close"></span>
          <span class="control minimize"></span>
          <span class="control maximize"></span>
        </span>
      </div>
      <div class="text">Sending verification code...</div>
    </div>
  `);
}
</script>

</body>
</html>
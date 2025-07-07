<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');
require_once('includes/activity_helper.php');

if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header('Location: login.php');
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
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'User not found.'];
    header('Location: login.php');
    exit();
}

$query2 = "SELECT fname, mname, lname, mobile FROM tblclientlist WHERE email = ?";
$stmt2 = $conn->prepare($query2);
$stmt2->bind_param("s", $email);
$stmt2->execute();
$result2 = $stmt2->get_result();
$client = $result2->fetch_assoc();

if (!$client) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Client details not found.'];
    header('Location: dashboard.php');
    exit();
}

$client['fname'] = !empty($client['fname']) ? decryptData($client['fname']) : '';
$client['mname'] = !empty($client['mname']) ? decryptData($client['mname']) : '';
$client['lname'] = !empty($client['lname']) ? decryptData($client['lname']) : '';
$client['mobile'] = !empty($client['mobile']) ? decryptData($client['mobile']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = encryptData($_POST['fname']);
    $mname = encryptData($_POST['mname']);
    $lname = encryptData($_POST['lname']);
    $mobile = encryptData($_POST['mobile']);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $query3 = "UPDATE tblclientlist SET fname = ?, mname = ?, lname = ?, mobile = ? WHERE email = ?";
    $stmt3 = $conn->prepare($query3);
    $stmt3->bind_param("sssss", $fname, $mname, $lname, $mobile, $email);
    $stmt3->execute();

    $passwordChanged = false;

    if (!empty($password) || !empty($confirmPassword)) {
        if ($password !== $confirmPassword) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Passwords do not match.'];
            header("Location: client_profile.php");
            exit();
        }

        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[^a-zA-Z]/', $password)) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Password does not meet security requirements.'];
            header("Location: client_profile.php");
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updatePasswordQuery = "UPDATE tblusers SET password = ? WHERE email = ?";
        $stmt4 = $conn->prepare($updatePasswordQuery);
        $stmt4->bind_param("ss", $hashedPassword, $email);
        $stmt4->execute();
        $passwordChanged = true;
    }

    $fullName = $client['fname'] . ' ' . $client['lname'];
    $action = $passwordChanged
        ? "🔐 $fullName updated profile information and changed password."
        : "👤 $fullName updated profile information.";

    logActivity($conn, 'client', $user['id'], $action);

    $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Profile updated successfully!'];
    header("Location: client_profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Management</title>
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/popup.css">
    <link rel="stylesheet" href="css/profile.css">
    <script defer src="javascript/popup.js"></script>
    <script defer src="javascript/sidebar-toggle.js"></script>
    <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
    }
    .password-container {
      position: relative;
      width: 100%;
      margin-bottom: 10px;
    }
    .password-container input {
      width: 100%;
      padding: 10px 40px 10px 10px;
      font-size: 16px;
    }
    .eye-icon {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 20px;
      color: #555;
    }
    .instruction {
      color: gray;
      font-size: 0.9rem;
      margin: 3px 0;
    }
    .instruction.valid {
      color: green;
    }
    .instruction.invalid {
      color: red;
    }
    #error-message {
      color: red;
      font-size: 0.9rem;
      margin-top: 5px;
    }
    #password-validation {
      margin-top: 10px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<?php if (isset($_SESSION['popup'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    showPopup('<?= $_SESSION['popup']['type'] ?>', '<?= $_SESSION['popup']['msg'] ?>');
});
</script>
<?php unset($_SESSION['popup']); endif; ?>
<div id="popup-container" class="popup-container"></div>

<?php include('header.php'); ?>
<div class="main-container">
    <?php include('client_sidebar.html'); ?>
</div>

<div class="container">
    <h1>Profile Management</h1>
    <form action="client_profile.php" method="POST">

<label for="fname"><b>First Name:</b></label>
<input type="text" id="fname" name="fname" value="<?= htmlspecialchars($client['fname']) ?>">

<label for="mname"><b>Middle Name:</b></label>
<input type="text" id="mname" name="mname" value="<?= htmlspecialchars($client['mname']) ?>">

<label for="lname"><b>Last Name:</b></label>
<input type="text" id="lname" name="lname" value="<?= htmlspecialchars($client['lname']) ?>">

<label for="mobile"><b>Mobile Number:</b></label>
<input type="text" id="mobile" name="mobile" maxlength="11"
    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11);"
    value="<?= htmlspecialchars($client['mobile']) ?>">

<label for="password"><b>New Password (Optional):</b></label>
<div class="password-container">
    <input type="password" id="password" name="password">
    <i class="bx bx-show eye-icon" id="toggle-password" onclick="togglePassword('password')"></i>
</div>

<div id="password-validation" style="display: none;">
    <p id="length" class="instruction">Minimum 8 characters</p>
    <p id="uppercase" class="instruction">At least 1 uppercase letter (A-Z)</p>
    <p id="special" class="instruction">At least 1 special character or number (!@#$%^&*()_+[]{}|;:'",.<>?)</p>
</div>

<label for="confirm_password"><b>Confirm New Password:</b></label>
<div class="password-container">
    <input type="password" id="confirm_password" name="confirm_password">
    <i class="bx bx-show eye-icon" id="toggle-confirm-password" onclick="togglePassword('confirm_password')"></i>
</div>

<p id="error-message" style="display: none;">⚠ Passwords do not match!</p>

<br><br>
<button type="submit">Submit</button>

</form>
</div>

<?php include('faq_widget.php'); ?>

<script>
    function togglePassword(inputId) {
      const input = document.getElementById(inputId);
      input.type = input.type === "password" ? "text" : "password";
    }

    document.addEventListener('DOMContentLoaded', function () {
      const passwordInput = document.getElementById('password');
      const confirmPasswordInput = document.getElementById('confirm_password');
      const validationBox = document.getElementById('password-validation');
      const lengthCheck = document.getElementById('length');
      const uppercaseCheck = document.getElementById('uppercase');
      const specialCheck = document.getElementById('special');
      const errorMessage = document.getElementById('error-message');

      passwordInput.addEventListener('input', function () {
        const password = passwordInput.value;

        if (password.length > 0) {
          validationBox.style.display = 'block';
        } else {
          validationBox.style.display = 'none';
        }

        lengthCheck.className = 'instruction ' + (password.length >= 8 ? 'valid' : 'invalid');
        uppercaseCheck.className = 'instruction ' + (/[A-Z]/.test(password) ? 'valid' : 'invalid');
        specialCheck.className = 'instruction ' + (/[^a-zA-Z]/.test(password) ? 'valid' : 'invalid');


        if (confirmPasswordInput.value.length > 0) {
          errorMessage.style.display = password !== confirmPasswordInput.value ? 'block' : 'none';
        }
      });

      confirmPasswordInput.addEventListener('input', function () {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        errorMessage.style.display = (password !== confirmPassword) ? 'block' : 'none';
      });
    });
  </script>

</body>
</html>
<?php $conn->close(); ?>
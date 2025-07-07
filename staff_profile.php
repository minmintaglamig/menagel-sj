<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');

if (!isset($_SESSION['user_email'])) {
    header("Location: login.html");
    exit();
}

$email = $_SESSION['user_email'];

$query = "SELECT fname, mname, lname, mobile FROM tblstafflist WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Staff details not found.'];
    header("Location: staff_dashboard.php");
    exit();
}

$staff['fname'] = !empty($staff['fname']) ? decryptData($staff['fname']) : '';
$staff['mname'] = !empty($staff['mname']) ? decryptData($staff['mname']) : '';
$staff['lname'] = !empty($staff['lname']) ? decryptData($staff['lname']) : '';
$staff['mobile'] = !empty($staff['mobile']) ? decryptData($staff['mobile']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = encryptData($_POST['fname']);
    $mname = encryptData($_POST['mname']);
    $lname = encryptData($_POST['lname']);
    $mobile = encryptData($_POST['mobile']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($password)) {
        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query2 = "UPDATE tblstafflist SET fname = ?, mname = ?, lname = ?, mobile = ?, password = ? WHERE email = ?";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bind_param("ssssss", $fname, $mname, $lname, $mobile, $hashed_password, $email);
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Passwords do not match!'];
            header("Location: staff_profile.php");
            exit();
        }
    } else {
        $query2 = "UPDATE tblstafflist SET fname = ?, mname = ?, lname = ?, mobile = ? WHERE email = ?";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("sssss", $fname, $mname, $lname, $mobile, $email);
    }

    if ($stmt2->execute()) {
        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Profile updated successfully!'];
        header("Location: staff_profile.php");
        exit();
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error updating profile.'];
        header("Location: staff_profile.php");
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
    <link rel="stylesheet" href="css/toggle.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/popup.css">
    <script src="javascript/popup.js"></script>
    <script defer src="javascript/script.js"></script>
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
    <?php include('staff_sidebar.html'); ?>
</div>

<div class="container">
    <h1>Profile Management</h1>
    <form action="staff_profile.php" method="POST">
        <label><b>First Name: </b></label>
        <input type="text" name="fname" value="<?= htmlspecialchars($staff['fname']) ?>">

        <label><b>Middle Name: </b></label>
        <input type="text" name="mname" value="<?= htmlspecialchars($staff['mname']) ?>">

        <label><b>Last Name: </b></label>
        <input type="text" name="lname" value="<?= htmlspecialchars($staff['lname']) ?>">

        <label><b>Mobile Number: </b></label>
        <input type="text" name="mobile" value="<?= htmlspecialchars($staff['mobile']) ?>">

        <label for="password"><b>New Password (Optional):</b></label>
<div class="password-container">
  <input type="password" id="password" name="password" placeholder="Enter new password">
  <i class="bx bx-show toggle-password" onclick="togglePassword('password')"></i>
</div>

<label for="confirm_password"><b>Confirm New Password:</b></label>
<div class="password-container">
  <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
  <i class="bx bx-show toggle-password" onclick="togglePassword('confirm_password')"></i>
</div>

<div id="password-validation" style="display: none;">
  <p id="length" class="instruction">Minimum 8 characters</p>
  <p id="uppercase" class="instruction">At least 1 uppercase letter (A-Z)</p>
  <p id="special" class="instruction">At least 1 special character or number (!@#$%^&*()_+[]{}|;:'",.<>?)</p>
</div>

<p id="error-message" class="error" style="display: none;">⚠ Passwords do not match!</p>

<br>
<button type="submit" id="submit-btn">Submit</button>

    </form>
</div>

<script src="javascript/sidebar-toggle.js"></script>
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

  const submitButton = document.getElementById('submit-btn');
  submitButton.disabled = !(password.length === 0 || (minLengthValid && uppercaseValid && specialValid && password === confirmPassword));
}

// Event listeners
document.getElementById('password').addEventListener('input', validatePassword);
document.getElementById('confirm_password').addEventListener('input', validatePassword);
document.addEventListener('DOMContentLoaded', validatePassword);
</script>

</body>
</html>
<?php $conn->close(); ?>
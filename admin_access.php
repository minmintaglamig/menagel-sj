<?php
session_start();

$access_password = "secureAdmin123";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['access_password'])) {
    if ($_POST['access_password'] === $access_password) {
        $_SESSION['admin_auth'] = true;
        header("Location: add_admin.php");
        exit;
    } else {
        $_SESSION['popup'] = ['type' => 'alert', 'msg' => 'Incorrect password! Try again.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" href="pictures/logo.png" type= "image">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/popup.css">
  <script src="javascript/popup.js"></script>
  <title>Admin Access</title>
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
  <form method="POST" class="form">
    <h2>Enter Admin Access Password</h2>
    <label>Password:</label>
    <input type="password" name="access_password" required>
    <button type="submit">Access</button>
  </form>
</div>

</body>
</html>
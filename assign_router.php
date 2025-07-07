<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');
require_once('includes/notification_helper.php');

if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header('Location: login.php');
    exit();
}

$email = $_SESSION['user_email'];

$query = "SELECT * FROM tblusers WHERE email = ? AND role = 'Admin'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Client ID is missing or invalid!'];
    header('Location: client_management.php');
    exit();
}

$client_id = intval($_GET['id']);

$query = "SELECT * FROM tblclientlist WHERE client_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Client not found!'];
    header('Location: client_management.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_router'])) {
    $router_number = trim($_POST['router_number']);

    if (!preg_match('/^[A-Za-z0-9_-]+$/', $router_number)) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid router number format!'];
        header("Location: assign_router.php?id=$client_id");
        exit();
    }

    $encrypted_router = encryptData($router_number);

    $update_query = "UPDATE tblclientlist SET routernumber = ? WHERE client_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $encrypted_router, $client_id);

    addNotification($conn, 'client', $client_id, '📶 Your router has been assigned.');

    if ($stmt->execute()) {
        $_SESSION['popup'] = ['type' => 'success', 'msg' => '✅ Router assigned successfully!'];
        header('Location: client_management.php');
        exit();
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => '❌ Error assigning router.'];
        header("Location: assign_router.php?id=$client_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="pictures/logo.png" type= "image">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="javascript/popup.js"></script>
    <script defer src="javascript/sidebar-toggle.js"></script>
    <script defer src="javascript/script.js"></script>
    <title>Assign Router</title>
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
    <h1>Assign Router Number</h1>
    <form action="assign_router.php?id=<?= $client_id ?>" method="POST">
        <label for="router_number">Router Number</label>
        <input type="text" name="router_number" required value="<?= htmlspecialchars(decryptData($client['routernumber'] ?? '')) ?>">
        <button type="submit" name="assign_router">Assign Router</button>
    </form>
</div>
</body>
</html>

<?php $conn->close(); ?>
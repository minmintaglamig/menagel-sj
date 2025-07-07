<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');

if (!isset($_SESSION['user_email'])) {
    echo "Unauthorized access!";
    exit();
}

$email = decryptData($_SESSION['user_email']);

$query = "SELECT * FROM tblusers WHERE email = ? AND role = 'admin'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Unauthorized access!";
    exit();
}

if (!isset($_GET['id'])) {
    echo "Invalid request!";
    exit();
}

$client_id = $_GET['id'];

$query = "SELECT * FROM tblclientlist WHERE client_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    echo "Client not found!";
    exit();
}

$client['routernumber'] = decryptData($client['routernumber']);
$client['mobile'] = $client['mobile'];
$client['email'] = decryptData($client['email']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_client'])) {
    $routernumber = trim($_POST['routernumber']);
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $lname = trim($_POST['lname']);
    $address = trim($_POST['address']);
    $residenttype = trim($_POST['residenttype']);
    $mobile = trim($_POST['mobile']);
    $promo = trim($_POST['promo']);
    $status = trim($_POST['status']);
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid email format!'];
        header("Location: edit_client.php?id=$client_id");
        exit();
    }
    if (!preg_match('/^[A-Za-z0-9_-]+$/', $routernumber)) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid router number format!'];
        header("Location: edit_client.php?id=$client_id");
        exit();
    }
    if (!preg_match('/^\d{10,15}$/', $mobile)) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid mobile number!'];
        header("Location: edit_client.php?id=$client_id");
        exit();
    }

    $encrypted_router = encryptData($routernumber);
    $encrypted_mobile = $mobile;
    $encrypted_email = encryptData($email);

    $update_query = "UPDATE tblclientlist SET routernumber = ?, fname = ?, mname = ?, lname = ?, address = ?, residenttype = ?, mobile = ?, promo = ?, status = ?, email = ? WHERE client_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssssssssi", $encrypted_router, $fname, $mname, $lname, $address, $residenttype, $encrypted_mobile, $promo, $status, $encrypted_email, $client_id);

    if ($stmt->execute()) {
        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Client updated successfully!'];
        header("Location: client_management.php");
        exit();
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error updating client details.'];
        header("Location: edit_client.php?id=$client_id");
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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="javascript/popup.js"></script>
    <script defer src="javascript/script.js"></script>
    <title>Edit Client</title>
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
    <h1>Edit Client Details</h1>
    <form action="edit_client.php?id=<?= $client_id ?>" method="POST">
        <label>Router Number</label>
        <input type="text" name="routernumber" value="<?= htmlspecialchars($client['routernumber']) ?>">

        <label>First Name</label>
        <input type="text" name="fname" value="<?= htmlspecialchars($client['fname']) ?>">

        <label>Middle Name</label>
        <input type="text" name="mname" value="<?= htmlspecialchars($client['mname']) ?>">

        <label>Last Name</label>
        <input type="text" name="lname" value="<?= htmlspecialchars($client['lname']) ?>">

        <label>Address</label>
        <input type="text" name="address" value="<?= htmlspecialchars($client['address']) ?>">

        <label>Resident Type</label>
        <input type="text" name="residenttype" value="<?= htmlspecialchars($client['residenttype']) ?>">

        <label>Mobile Number</label>
        <input type="text" name="mobile" value="<?= htmlspecialchars($client['mobile']) ?>">

        <label>Promo</label>
        <input type="text" name="promo" value="<?= htmlspecialchars($client['promo']) ?>">

        <label>Status</label>
        <input type="text" name="status" value="<?= htmlspecialchars($client['status']) ?>">

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($client['email']) ?>">

        <button type="submit" name="update_client">Update Client</button>
    </form>
    <br>
    <a href="client_management.php">Back to Client Management</a>
</div>

<?php include('faq_widget.php'); ?>

<script src="javascript/sidebar-toggle.js"></script>
</body>
</html>

<?php $conn->close(); ?>
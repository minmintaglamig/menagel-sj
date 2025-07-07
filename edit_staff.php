<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');

if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];

$query = "SELECT * FROM tblusers WHERE email = ? AND role = 'admin'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid staff ID.'];
    header("Location: staff_management.php");
    exit();
}

$staff_id = $_GET['id'];

$query = "SELECT * FROM tblstafflist WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Staff member not found.'];
    header("Location: staff_management.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = encryptData($_POST['fname']);
    $mname = encryptData($_POST['mname']);
    $lname = encryptData($_POST['lname']);
    $mobile = $_POST['mobile'];
    $address = encryptData($_POST['address']);
    $specialization = $_POST['specialization'];

    $update_query = "UPDATE tblstafflist SET fname = ?, mname = ?, lname = ?, mobile = ?, address = ?, specialization = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssssi", $fname, $mname, $lname, $mobile, $address, $specialization, $staff_id);

    if ($stmt->execute()) {
        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Staff updated successfully.'];
        header("Location: staff_management.php");
        exit();
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error updating staff.'];
        header("Location: edit_staff.php?id=$staff_id");
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
    <title>Edit Staff</title>
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

<div class="layout">
    <?php include('header.php'); ?>
    <div class="main-container">
        <?php include('admin_sidebar.html'); ?>
    </div>
</div>

<div class="container">
    <h1>Edit Staff</h1>
    <form action="" method="POST">
        <label>First Name</label>
        <input type="text" name="fname" value="<?= htmlspecialchars(decryptData($staff['fname'])) ?>">

        <label>Middle Name</label>
        <input type="text" name="mname" value="<?= htmlspecialchars(decryptData($staff['mname'])) ?>">

        <label>Last Name</label>
        <input type="text" name="lname" value="<?= htmlspecialchars(decryptData($staff['lname'])) ?>">

        <label>Mobile</label>
        <input type="text" name="mobile" value="<?= htmlspecialchars($staff['mobile']) ?>">

        <label>Address</label>
        <input type="text" name="address" value="<?= htmlspecialchars(decryptData($staff['address'])) ?>">

        <label>Specialization</label>
        <input type="text" name="specialization" value="<?= htmlspecialchars($staff['specialization']) ?>">

        <button type="submit" id="saveBtn" disabled>Save Changes</button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    const inputs = form.querySelectorAll("input[type='text']");
    const saveBtn = document.getElementById("saveBtn");

    const originalValues = {};
    inputs.forEach(input => {
        originalValues[input.name] = input.value;
    });

    function checkForChanges() {
        let changed = false;
        inputs.forEach(input => {
            if (input.value !== originalValues[input.name]) {
                changed = true;
            }
        });
        saveBtn.disabled = !changed;
    }

    inputs.forEach(input => {
        input.addEventListener("input", checkForChanges);
    });
});
</script>

<script src="javascript/sidebar-toggle.js"></script>
</body>
</html>
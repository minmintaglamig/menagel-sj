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

$query = "SELECT routernumber FROM tblclientlist WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client || empty($client['routernumber'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'No router number found for your account.'];
    header("Location: client_tickets.php");
    exit();
}

if (!isset($_GET['ticket_id']) || empty($_GET['ticket_id'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid ticket ID.'];
    header("Location: client_tickets.php");
    exit();
}

$ticket_id = $_GET['ticket_id'];

$query = "SELECT ticket_id, concern_type, concern, status, image 
          FROM tbltickets 
          WHERE ticket_id = ? AND routernumber = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $ticket_id, $client['routernumber']);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();

if (!$ticket) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Ticket not found.'];
    header("Location: client_tickets.php");
    exit();
}

if ($ticket['status'] !== 'Pending') {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Only pending tickets can be edited.'];
    header("Location: client_tickets.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $concern_type = $_POST['concern_type'];
    $concern = encryptData($_POST['concern']);

    $update_query = "UPDATE tbltickets SET concern_type = ?, concern = ? WHERE ticket_id = ? AND routernumber = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssss", $concern_type, $concern, $ticket_id, $client['routernumber']);

    if ($stmt->execute()) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $oldImagePath = 'uploads/concerns/' . $ticket['image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }

            $image_name = time() . '_' . basename($_FILES['image']['name']);
            $uploadDir = 'uploads/concerns/';
            $uploadFile = $uploadDir . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $update_image_query = "UPDATE tbltickets SET image = ? WHERE ticket_id = ? AND routernumber = ?";
                $stmt_img = $conn->prepare($update_image_query);
                $stmt_img->bind_param("sss", $image_name, $ticket_id, $client['routernumber']);
                $stmt_img->execute();
            }
        }

        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Ticket updated successfully.'];
        header("Location: client_tickets.php");
        exit();
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to update the ticket. Please try again.'];
        header("Location: edit_ticket.php?ticket_id=" . urlencode($ticket_id));
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
    <title>Edit Ticket</title>
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
    <?php include('client_sidebar.html'); ?>
</div>
<div class="container">
    <h1>Edit Ticket</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <?php 
        $imagePath = "uploads/concerns/{$ticket['ticket_id']}.jpg";
        if (file_exists($imagePath)): ?>
            <p>Current Uploaded Image:</p>
            <img src="<?= $imagePath ?>" alt="Uploaded Image" style="max-width: 100%; height: auto; margin-bottom: 10px;">
        <?php endif; ?>

        <label for="concern_type">Concern Type:</label>
        <select name="concern_type" required>
            <option value="Technical" <?= $ticket['concern_type'] === 'Technical' ? 'selected' : '' ?>>Technical</option>
            <option value="Upgrade/Downgrade Internet" <?= $ticket['concern_type'] === 'Upgrade/Downgrade Internet' ? 'selected' : '' ?>>Upgrade/Downgrade Internet</option>
            <option value="Disconnection" <?= $ticket['concern_type'] === 'Disconnection' ? 'selected' : '' ?>>Disconnection</option>
            <option value="Other" <?= $ticket['concern_type'] === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>

        <label for="concern">Concern:</label>
        <textarea name="concern" id="concern" rows="5" required><?= htmlspecialchars(decryptData($ticket['concern'])) ?></textarea>

        <label for="image">Replace Uploaded Image (optional):</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">Update Ticket</button>
    </form>
</div>

<?php include('faq_widget.php'); ?>

<script src="javascript/sidebar-toggle.js"></script>
</body>
</html>

<?php $conn->close(); ?>
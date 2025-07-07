<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');

if (!isset($_SESSION['user_email'])) {
    echo "Unauthorized access!";
    exit();
}

$staff_email = $_SESSION['user_email'];

$staff_id_query = "SELECT id FROM tblstafflist WHERE email = ?";
$stmt = $conn->prepare($staff_id_query);
$stmt->bind_param("s", $staff_email);
$stmt->execute();
$staff_id_result = $stmt->get_result();

if ($staff_id_result->num_rows === 0) {
    echo "Staff not found!";
    exit();
}

$staff_row = $staff_id_result->fetch_assoc();
$staff_id = $staff_row['id'];

$tickets_query = "
    SELECT t.ticket_id, 
           t.fname, 
           t.mname, 
           t.lname, 
           t.mobile, 
           t.address, 
           t.routernumber, 
           t.concern_type, 
           t.concern, 
           IFNULL(t.status, 'Pending') AS status, 
           IFNULL(t.schedule_date, 'N/A') AS schedule_date, 
           IFNULL(t.schedule_time, 'N/A') AS schedule_time, 
           IFNULL(t.assigned_staff, 'Not Assigned') AS assigned_staff, 
           t.proof_photo,
           t.image,  -- Include image column from tbltickets
           s.email AS staff_email
    FROM tbltickets t
    LEFT JOIN tblstafflist s ON t.assigned_staff = s.id
    WHERE t.assigned_staff = ? AND t.status = 'Scheduled'
    ORDER BY t.ticket_id DESC
";

$tickets_result = $conn->prepare($tickets_query);
$tickets_result->bind_param("i", $staff_id);
$tickets_result->execute();
$result = $tickets_result->get_result();

$tickets = [];

while ($row = $result->fetch_assoc()) {
    $row['fname'] = decryptData($row['fname']);
    $row['mname'] = decryptData($row['mname']);
    $row['lname'] = decryptData($row['lname']);
    $row['mobile'] = decryptData($row['mobile']);
    $row['address'] = decryptData($row['address']);
    $row['concern'] = decryptData($row['concern']);

    $tickets[] = $row;
}

$completed_tickets_query = "
    SELECT t.ticket_id, 
           t.fname, 
           t.mname, 
           t.lname, 
           t.mobile, 
           t.address, 
           t.routernumber, 
           t.concern_type, 
           t.concern, 
           t.status, 
           t.completed_date, 
           t.completed_time, 
           t.proof_photo,
           t.image,
           s.email AS staff_email
    FROM tbltickets t
    LEFT JOIN tblstafflist s ON t.assigned_staff = s.id
    WHERE t.assigned_staff = ? AND t.status = 'Completed'
    ORDER BY t.ticket_id DESC
";

$completed_tickets_result = $conn->prepare($completed_tickets_query);
$completed_tickets_result->bind_param("i", $staff_id);
$completed_tickets_result->execute();
$completed_result = $completed_tickets_result->get_result();

$completed_tickets = [];

while ($row = $completed_result->fetch_assoc()) {
    $row['fname'] = decryptData($row['fname']);
    $row['mname'] = decryptData($row['mname']);
    $row['lname'] = decryptData($row['lname']);
    $row['mobile'] = decryptData($row['mobile']);
    $row['address'] = decryptData($row['address']);
    $row['concern'] = decryptData($row['concern']);

    $completed_tickets[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_done'])) {
    if (isset($_POST['ticket_id']) && !empty($_POST['ticket_id'])) {
        $ticket_id = intval($_POST['ticket_id']);

        if (isset($_FILES['proof_photo']) && $_FILES['proof_photo']['error'] === UPLOAD_ERR_OK) {
            $photo_name = $_FILES['proof_photo']['name'];
            $photo_tmp_name = $_FILES['proof_photo']['tmp_name'];
            $photo_ext = pathinfo($photo_name, PATHINFO_EXTENSION);
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array(strtolower($photo_ext), $allowed_ext)) {
                $upload_dir = 'uploads/ticketsproof/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $photo_path = $upload_dir . uniqid() . '.' . $photo_ext;

                if (move_uploaded_file($photo_tmp_name, $photo_path)) {
                    $current_date = date("Y-m-d");
                    $current_time = date("H:i:s");

                    $update_query = "
                        UPDATE tbltickets 
                        SET status = 'Completed', proof_photo = ?, completed_date = ?, completed_time = ? 
                        WHERE ticket_id = ?
                    ";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("sssi", $photo_path, $current_date, $current_time, $ticket_id);

                    if ($stmt->execute()) {
                        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Appointment marked as Completed and photo uploaded'];
                    } else {
                        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error marking appointment as Completed'];
                    }
                } else {
                    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error uploading photo'];
                }
            } else {
                $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid photo format. Allowed formats: jpg, jpeg, png, gif.'];
            }
        } else {
            $current_date = date("Y-m-d");
            $current_time = date("H:i:s");

            $update_query = "
                UPDATE tbltickets 
                SET status = 'Completed', completed_date = ?, completed_time = ? 
                WHERE ticket_id = ?
            ";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssi", $current_date, $current_time, $ticket_id);

            if ($stmt->execute()) {
                $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Appointment marked as Completed'];
            } else {
                $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error marking appointment as Completed'];
            }
        }
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid Ticket ID'];
    }
    header("Location: staff_tickets.php");
    exit();
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
    <script src="javascript/popup.js"></script>
    <script defer src="javascript/script.js"></script>
    <title>Scheduled Appointments</title>
    <style>
@media (max-width: 1024px) {
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .table-responsive table {
    min-width: 800px;
    border-collapse: collapse;
  }
}

</style>
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
    <h2>Scheduled Appointments</h2>
    <div class="table-responsive">
    <table border="1">
        <tr>
            <th>Ticket ID</th>
            <th>Client Name</th>
            <th>Mobile</th>
            <th>Address</th>
            <th>Concern Type</th>
            <th>Concern</th>
            <th>Status</th>
            <th>Schedule Date</th>
            <th>Schedule Time</th>
            <th>Client Image</th> 
            <th>Action</th>
        </tr>

        <?php foreach ($tickets as $ticket): ?>
            <tr>
                <td><?= htmlspecialchars($ticket['ticket_id']) ?></td>
                <td><?= htmlspecialchars($ticket['fname'] . ' ' . ($ticket['mname'] ? $ticket['mname'] . ' ' : '') . $ticket['lname']) ?></td>
                <td><?= htmlspecialchars($ticket['mobile']) ?></td>
                <td><?= htmlspecialchars($ticket['address']) ?></td>
                <td><?= htmlspecialchars($ticket['concern_type']) ?></td>
                <td><?= htmlspecialchars($ticket['concern']) ?></td>
                <td><?= htmlspecialchars($ticket['status']) ?></td>
                <td><?= htmlspecialchars($ticket['schedule_date']) ?></td>
                <td><?= htmlspecialchars($ticket['schedule_time']) ?></td>
                <td>
                    <?php if ($ticket['image']): ?>
                        <img src="uploads/concerns/<?= htmlspecialchars($ticket['image']) ?>" alt="Client Image" width="100">
                    <?php else: ?>
                        No image uploaded
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action">
                        <?php if ($ticket['status'] === 'Scheduled'): ?>
                            <form action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket['ticket_id']) ?>">
    <label for="proof_photo_<?= $ticket['ticket_id'] ?>">Upload Proof Photo</label>
    <input type="file" name="proof_photo" id="proof_photo_<?= $ticket['ticket_id'] ?>" onchange="enableButton(<?= $ticket['ticket_id'] ?>)" required>
    <button type="submit" name="mark_done" id="mark_done_btn_<?= $ticket['ticket_id'] ?>" class="done-btn" disabled>Mark as Completed</button>
</form>
                        <?php else: ?>
                            <span>Completed</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
                        </div>

    <h2>Completed Appointments</h2>
    <div class="table-responsive">
    <table border="1">
        <tr>
            <th>Ticket ID</th>
            <th>Client Name</th>
            <th>Mobile</th>
            <th>Address</th>
            <th>Concern Type</th>
            <th>Concern</th>
            <th>Status</th>
            <th>Completion Date</th>
            <th>Completion Time</th>
            <th>Client Image</th>
            <th>Proof Photo</th>
        </tr>

        <?php foreach ($completed_tickets as $ticket): ?>
            <tr>
                <td><?= htmlspecialchars($ticket['ticket_id']) ?></td>
                <td><?= htmlspecialchars($ticket['fname'] . ' ' . ($ticket['mname'] ? $ticket['mname'] . ' ' : '') . $ticket['lname']) ?></td>
                <td><?= htmlspecialchars($ticket['mobile']) ?></td>
                <td><?= htmlspecialchars($ticket['address']) ?></td>
                <td><?= htmlspecialchars($ticket['concern_type']) ?></td>
                <td><?= htmlspecialchars($ticket['concern']) ?></td>
                <td><?= htmlspecialchars($ticket['status']) ?></td>
                <td><?= htmlspecialchars($ticket['completed_date']) ?></td>
                <td><?= htmlspecialchars($ticket['completed_time']) ?></td>
                <td>
                    <?php if ($ticket['image']): ?>
                        <img src="uploads/concerns/<?= htmlspecialchars($ticket['image']) ?>" alt="Client Image" width="100">
                    <?php else: ?>
                        No image uploaded
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($ticket['proof_photo']): ?>
                        <img src="<?= htmlspecialchars($ticket['proof_photo']) ?>" alt="Proof" width="100">
                    <?php else: ?>
                        No photo uploaded
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
                    </div>
</div>

<script src="javascript/sidebar-toggle.js"></script>
<script>
function enableButton(ticketId) {
    const fileInput = document.getElementById('proof_photo_' + ticketId);
    const submitButton = document.getElementById('mark_done_btn_' + ticketId);

    if (fileInput.files.length > 0) {
        submitButton.disabled = false;
    } else {
        submitButton.disabled = true;
    }
}
</script>

</body>
</html>

<?php $conn->close(); ?>
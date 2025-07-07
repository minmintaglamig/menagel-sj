<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');
require_once('includes/activity_helper.php');
require_once('includes/notification_helper.php');

if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header('Location: login.php');
    exit();
}

$email = $_SESSION['user_email'];

$query = "SELECT client_id, fname, mname, lname, mobile, address, routernumber FROM tblclientlist WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'User not found.'];
    header('Location: login.php');
    exit();
}

$client_id = $client['client_id'];
$routernumber_encrypted = $client['routernumber'];
$show_modal = empty($routernumber_encrypted);
$routernumber = $routernumber_encrypted;

foreach (['fname', 'mname', 'lname', 'mobile', 'address'] as $field) {
    if (!empty($client[$field])) {
        $client[$field] = decryptData($client[$field]);
    }
}

$ticket_id = isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;

$can_reply = false;
if ($ticket_id > 0) {
    $check_ticket_query = "SELECT ticket_id FROM tbltickets WHERE ticket_id = ? AND routernumber = ?";
    $stmt = $conn->prepare($check_ticket_query);
    $stmt->bind_param("is", $ticket_id, $routernumber);
    $stmt->execute();
    $check_result = $stmt->get_result();
    if ($check_result->num_rows > 0) {
        $reply_query = "SELECT * FROM tblticket_replies WHERE ticket_id = ? AND sender = 'admin'";
        $stmt = $conn->prepare($reply_query);
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $admin_replies = $stmt->get_result();
        $can_reply = $admin_replies->num_rows > 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_reply'])) {
    $reply_ticket_id = (int)$_POST['ticket_id'];
    $stmt = $conn->prepare("SELECT ticket_id FROM tbltickets WHERE ticket_id = ? AND routernumber = ?");
    $stmt->bind_param("is", $reply_ticket_id, $routernumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $client_reply = $_POST['client_reply'];
        $stmt = $conn->prepare("INSERT INTO tblticket_replies (ticket_id, sender, message) VALUES (?, 'client', ?)");
        $stmt->bind_param("is", $reply_ticket_id, $client_reply);
        $stmt->execute();

        logActivity($conn, 'client', $client_id, "💬 Client replied to ticket #$reply_ticket_id.");

        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Your reply has been sent!'];
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'You cannot reply to this ticket.'];
    }
    header('Location: client_tickets.php');
    exit();
}

if (!$show_modal) {
    $statuses = [
        'pending' => 'Pending',
        'completed' => 'Completed',
    ];
    
    foreach ($statuses as $key => $status) {
        $sql = "SELECT * FROM tbltickets WHERE routernumber = ? AND status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $routernumber, $status);
        $stmt->execute();
        ${"tickets_$key"} = $stmt->get_result();
    }
    
    $sql = "SELECT * FROM tbltickets WHERE routernumber = ? AND schedule_date IS NOT NULL AND schedule_time IS NOT NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $routernumber);
    $stmt->execute();
    $tickets_scheduled = $stmt->get_result();
       
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['move_ticket_id'])) {
    $ticket_id = (int)$_POST['move_ticket_id'];

    $stmt = $conn->prepare("INSERT INTO tblcompleted (ticket_id, fname, mname, lname, mobile, address, concern_type, concern, routernumber, status, image)
                            SELECT ticket_id, fname, mname, lname, mobile, address, concern_type, concern, routernumber, status, image
                            FROM tbltickets WHERE ticket_id = ?");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM tbltickets WHERE ticket_id = ?");
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();

        addNotification($conn, 'Client', $client_id, "✅ Your ticket #$ticket_id has been completed!", "client_tickets.php");

        logActivity($conn, 'client', $client_id, "✅ Ticket #$ticket_id marked as completed.");

        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Ticket moved to completed!'];
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to move ticket.'];
    }
    header('Location: client_tickets.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    if ($show_modal) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'You cannot submit a ticket without a router number.'];
        header('Location: client_tickets.php');
        exit();
    }

    $concern_type = $_POST['concern_type'];
    $concern = encryptData($_POST['concern']);
    $image_name = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/concerns/' . $image_name);
    }

    $fname = encryptData($client['fname']);
    $mname = encryptData($client['mname']);
    $lname = encryptData($client['lname']);
    $address = encryptData($client['address']);
    $mobile = encryptData($client['mobile']);

    $stmt = $conn->prepare("INSERT INTO tbltickets (fname, mname, lname, address, mobile, concern_type, concern, routernumber, status, image)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");
    $stmt->bind_param("sssssssss", $fname, $mname, $lname, $address, $mobile, $concern_type, $concern, $routernumber, $image_name);

    if ($stmt->execute()) {
        $admins = $conn->query("SELECT id FROM tblusers WHERE role = 'Admin'");
        while ($admin = $admins->fetch_assoc()) {
            addNotification($conn, 'Admin', $admin['id'], '🎫 New ticket submitted!', 'admin_tickets.php');
        }

        $fullName = $client['fname'] . ' ' . $client['lname'];
        logActivity($conn, 'client', $client_id, "📩 $fullName submitted a new ticket.");

        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Ticket submitted successfully!'];
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error submitting ticket: ' . $stmt->error];
    }
    header('Location: client_tickets.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <title>Client Tickets</title>
    <link rel="stylesheet" href="css/client_tickets.css">
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="javascript/popup.js"></script>
    <script defer src="javascript/script.js"></script>
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
<div class="form-wrapper">
    <h2>Submit New Ticket</h2>

    <?php if ($show_modal): ?>
        <div class="error-message">
            You don't have a router number associated with your account. Please wait for the admin to assign a router number after wifi installation.
        </div>
    <?php endif; ?>

    <form action="client_tickets.php" method="POST" enctype="multipart/form-data">
        <label for="concern_type">Concern Type</label>
        <select name="concern_type" required <?= $show_modal ? 'disabled' : '' ?>>
            <option value="Technical">Technical</option>
            <option value="Upgrade/Downgrade Internet">Upgrade/Downgrade Internet</option>
            <option value="Disconnection">Disconnection</option>
            <option value="Other">Other</option>
        </select>

        <label for="concern">Concern</label>
        <textarea name="concern" required <?= $show_modal ? 'disabled' : '' ?> placeholder="Describe your concern..." rows="4"></textarea>

        <label for="image">Upload Image (optional)</label>
        <input type="file" name="image" accept="image/*" <?= $show_modal ? 'disabled' : '' ?>>

        <button type="submit" name="submit_ticket" <?= $show_modal ? 'disabled' : '' ?>>Submit Ticket</button>
    </form>
</div>

<?php if (!$show_modal): ?>
    <h2>Pending Tickets</h2>
    <div class="table-wrapper">
        <table border="1">
            <tr>
                <th>Ticket ID</th>
                <th>Router Number</th>
                <th>Full Name</th>
                <th>Address</th>
                <th>Concern Type</th>
                <th>Concern</th>
                <th>Status</th>
                <th>Image</th>
                <th>Actions</th>
                <th>Replies</th>
            </tr>
            <?php while ($ticket = $tickets_pending->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($ticket['ticket_id']) ?></td>
                    <td><?= htmlspecialchars(decryptData($ticket['routernumber'])) ?></td>
                    <td><?= htmlspecialchars(decryptData($ticket['fname'])) . ' ' . 
                            (!empty($ticket['mname']) ? decryptData($ticket['mname']) . ' ' : '') . 
                            htmlspecialchars(decryptData($ticket['lname'])) ?></td>
                    <td><?= htmlspecialchars(decryptData($ticket['address'])) ?></td>
                    <td><?= htmlspecialchars($ticket['concern_type']) ?></td>
                    <td><?= htmlspecialchars(decryptData($ticket['concern'])) ?></td>
                    <td><?= htmlspecialchars($ticket['status']) ?></td>
                    <td>
                        <?php if (!empty($ticket['image'])): ?>
                            <img src="uploads/concerns/<?= htmlspecialchars($ticket['image']) ?>" alt="Ticket Image" width="100">
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_ticket.php?ticket_id=<?= $ticket['ticket_id'] ?>" class="edit-ticket" title="Edit">
                            <i class="bx bx-edit"></i>
                        </a>
                        <a href="javascript:void(0);" 
                           class="delete-ticket" 
                           onclick="openDeleteModal(<?= $ticket['ticket_id'] ?>);" 
                           title="Delete">
                            <i class="bx bx-trash"></i>
                        </a>
                    </td>
                    <td>
                        <div class="messages-container" style="max-height: 150px; overflow-y: auto;">
                            <!-- Fetch and Display Admin Replies for the current ticket -->
                            <?php
                            // Fetch admin replies specific to the current ticket_id
                            $admin_reply_query = "SELECT * FROM tblticket_replies WHERE ticket_id = ? ORDER BY created_at ASC";
                            $stmt = $conn->prepare($admin_reply_query);
                            $stmt->bind_param("i", $ticket['ticket_id']);
                            $stmt->execute();
                            $admin_replies_result = $stmt->get_result();
                            $admin_replies = $admin_replies_result->fetch_all(MYSQLI_ASSOC);
                            ?>

                            <?php if (!empty($admin_replies)): ?>
                                <?php foreach ($admin_replies as $reply): ?>
                                    <div class="message-box <?= $reply['sender'] === 'client' ? 'right' : 'left' ?>">
                                        <p><?= htmlspecialchars($reply['message']) ?></p>
                                        <small><?= htmlspecialchars($reply['created_at']) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="message-box left">
                                    <p><em>No replies yet.</em></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!$show_modal): ?>
                                <form action="client_tickets.php" method="POST">
    <textarea name="client_reply" required placeholder="Write your reply..." rows="4"></textarea>
    <input type="hidden" name="ticket_id" value="<?= $ticket['ticket_id'] ?>" />
    <button type="submit" class="reply-btn">Reply</button>
</form>

<?php endif; ?>


                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </table>
        </div>

        <h2>Scheduled Tickets</h2>
        <div class="table-wrapper">
            <table border="1">
                <tr>
                    <th>Ticket ID</th>
                    <th>Router Number</th>
                    <th>Full Name</th>
                    <th>Address</th>
                    <th>Concern Type</th>
                    <th>Concern</th>
                    <th>Status</th>
                    <th>Image</th>
                </tr>
                <?php while ($ticket = $tickets_scheduled->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($ticket['ticket_id']) ?></td>
                        <td><?= htmlspecialchars(decryptData($ticket['routernumber'])) ?></td>
                        <td><?= htmlspecialchars(decryptData($ticket['fname'])) . ' ' .
                                (!empty($ticket['mname']) ? decryptData($ticket['mname']) . ' ' : '') .
                                htmlspecialchars(decryptData($ticket['lname'])) ?></td>
                        <td><?= htmlspecialchars(decryptData($ticket['address'])) ?></td>
                        <td><?= htmlspecialchars($ticket['concern_type']) ?></td>
                        <td><?= htmlspecialchars(decryptData($ticket['concern'])) ?></td>
                        <td><?= htmlspecialchars($ticket['status']) ?></td>
                        <td>
                            <?php if (!empty($ticket['image'])): ?>
                                <img src="uploads/concerns/<?= htmlspecialchars($ticket['image']) ?>" alt="Ticket Image" width="100">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <h2>Completed Tickets</h2>
        <div class="table-wrapper">
            <table border="1">
                <tr>
                    <th>Ticket ID</th>
                    <th>Router Number</th>
                    <th>Full Name</th>
                    <th>Address</th>
                    <th>Concern Type</th>
                    <th>Concern</th>
                    <th>Status</th>
                    <th>Image</th>
                </tr>
                <?php while ($ticket = $tickets_completed->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($ticket['ticket_id']) ?></td>
                        <td><?= htmlspecialchars(decryptData($ticket['routernumber'])) ?></td>
                        <td><?= htmlspecialchars(decryptData($ticket['fname'])) . ' ' .
                                (!empty($ticket['mname']) ? decryptData($ticket['mname']) . ' ' : '') .
                                htmlspecialchars(decryptData($ticket['lname'])) ?></td>
                        <td><?= htmlspecialchars(decryptData($ticket['address'])) ?></td>
                        <td><?= htmlspecialchars($ticket['concern_type']) ?></td>
                        <td><?= htmlspecialchars(decryptData($ticket['concern'])) ?></td>
                        <td>Completed</td>
                        <td>
                            <?php if (!empty($ticket['image'])): ?>
                                <img src="uploads/concerns/<?= htmlspecialchars($ticket['image']) ?>" alt="Ticket Image" width="100">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include('faq_widget.php'); ?>

<!-- Confirmation Modal -->
<div id="confirm-delete-modal" class="modal">
    <div class="modal-content">
        <h2>Are you sure you want to delete this ticket?</h2>
        <p>This action cannot be undone.</p>
        <div class="modal-buttons">
            <button id="confirm-delete-btn" class="btn-confirm">Yes, Delete</button>
            <button id="cancel-delete-btn" class="btn-cancel">Cancel</button>
        </div>
    </div>
</div>

<script src="javascript/sidebar-toggle.js"></script>
<script>
    // Get modal and buttons
const modal = document.getElementById('confirm-delete-modal');
const confirmBtn = document.getElementById('confirm-delete-btn');
const cancelBtn = document.getElementById('cancel-delete-btn');

let deleteUrl = ""; // To store the delete URL

// Function to open modal and set the delete URL
function openDeleteModal(ticketId) {
    deleteUrl = `includes/delete_ticket.php?ticket_id=${ticketId}`;
    modal.style.display = "block";
}

// Cancel button to close modal
cancelBtn.onclick = function() {
    modal.style.display = "none";
}

// Confirm button to proceed with deletion
confirmBtn.onclick = function() {
    window.location.href = deleteUrl;
}

// Close modal if clicked outside of it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>
</body>
</html>
<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');
require_once('includes/notification_helper.php');

if (!isset($_SESSION['user_email'])) {
    echo "Unauthorized access!";
    exit();
}

$staff_filter = $client_filter = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['filter_staff'])) {
        $staff_id = intval($_POST['filter_staff']);
        $staff_filter = " AND assigned_staff = '$staff_id'";
    }

    if (!empty($_POST['filter_name'])) {
        $filter_name = explode(" ", $_POST['filter_name']);
        $fname_enc = isset($filter_name[0]) ? encryptData($filter_name[0]) : '';
        $mname_enc = isset($filter_name[1]) ? encryptData($filter_name[1]) : '';
        $lname_enc = isset($filter_name[2]) ? encryptData($filter_name[2]) : '';

        $client_filter = " AND fname = '$fname_enc' AND mname = '$mname_enc' AND lname = '$lname_enc'";
    }

    if (isset($_POST['reply_ticket_id'], $_POST['admin_reply'])) {
$ticket_id = intval($_POST['reply_ticket_id']);
$admin_reply = $conn->real_escape_string($_POST['admin_reply']);
$sender = 'admin';
$created_at = date('Y-m-d H:i:s');

        $ticket_check = $conn->query("SELECT routernumber FROM tbltickets WHERE ticket_id = '$ticket_id'");
        if ($ticket_check && $ticket_check->num_rows > 0) {
            $ticket_data = $ticket_check->fetch_assoc();
            $routernumber = $ticket_data['routernumber'];

$conn->query("INSERT INTO tblticket_replies (ticket_id, sender, message, created_at) 
              VALUES ('$ticket_id', '$sender', '$admin_reply', '$created_at')");

            addNotification($conn, 'client', $routernumber, '📩 You received a message from admin.', 'client_tickets.php');

            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Reply sent successfully.'];
            header('Location: admin_tickets.php');
            exit();
        }
    }

    if (isset($_POST['ticket_id'], $_POST['staff_id'], $_POST['schedule_date'], $_POST['schedule_time'])) {
        $ticket_id = intval($_POST['ticket_id']);
        $staff_id = intval($_POST['staff_id']);
        $schedule_date = $_POST['schedule_date'];
        $schedule_time = $_POST['schedule_time'];

        $ticket_check = $conn->query("SELECT routernumber FROM tbltickets WHERE ticket_id = '$ticket_id'");
        if ($ticket_check && $ticket_check->num_rows > 0) {
            $ticket_data = $ticket_check->fetch_assoc();
            $routernumber = $ticket_data['routernumber'];

            addNotification($conn, 'Client', $routernumber, '🎫 Your ticket has been scheduled!', 'client_tickets.php');
            addNotification($conn, 'Admin', $_SESSION['admin_id'], '🎫 A ticket has been scheduled.', 'admin_tickets.php');
            addNotification($conn, 'Staff', $staff_id, '🎫 You have been assigned a ticket.', 'staff_tickets.php');

            $update_query = "UPDATE tbltickets SET assigned_staff = '$staff_id', schedule_date = '$schedule_date', schedule_time = '$schedule_time', status = 'Scheduled' WHERE ticket_id = '$ticket_id'";
            $conn->query($update_query);

            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Ticket assigned successfully!'];
            header('Location: admin_tickets.php');
            exit();
        }
    }

    if (isset($_POST['complete_ticket_id'])) {
        $ticket_id = intval($_POST['complete_ticket_id']);
        $completed_date = date('Y-m-d');
        $completed_time = date('H:i:s');

        $ticket_check = $conn->query("SELECT routernumber FROM tbltickets WHERE ticket_id = '$ticket_id'");
        if ($ticket_check && $ticket_check->num_rows > 0) {
            $ticket_data = $ticket_check->fetch_assoc();
            $routernumber = $ticket_data['routernumber'];

            $conn->query("UPDATE tbltickets SET status = 'Completed', completed_date = '$completed_date', completed_time = '$completed_time' WHERE ticket_id = '$ticket_id'");

            addNotification($conn, 'client', $routernumber, '✅ Your service ticket has been completed.');
            addNotification($conn, 'admin', $_SESSION['admin_id'], '🛠️ A ticket has been completed.');

            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Ticket marked as completed!'];
            header('Location: admin_tickets.php');
            exit();
        }
    }
}

$tickets_query = "SELECT * FROM tbltickets WHERE 1=1 $staff_filter $client_filter";
$tickets_result = $conn->query($tickets_query);

$client_names_query = "SELECT DISTINCT fname, mname, lname FROM tbltickets";
$client_names_result = $conn->query($client_names_query);

$pending = [];
$scheduled = [];
$completed = [];
$calendar_events = [];

if ($tickets_result->num_rows > 0) {
    while ($ticket = $tickets_result->fetch_assoc()) {
        $ticket['fname'] = decryptData($ticket['fname']);
        $ticket['mname'] = decryptData($ticket['mname']);
        $ticket['lname'] = decryptData($ticket['lname']);
        $ticket['address'] = decryptData($ticket['address']);
        $ticket['concern'] = decryptData($ticket['concern']);

        if (!empty($ticket['assigned_staff'])) {
            $staff_id = $ticket['assigned_staff'];
            $staff_result = $conn->query("SELECT fname, lname FROM tblstafflist WHERE id = '$staff_id'");
            if ($staff_result->num_rows > 0) {
                $staff = $staff_result->fetch_assoc();
                $ticket['assigned_staff'] = decryptData($staff['fname']) . ' ' . decryptData($staff['lname']);
            }
        } else {
            $ticket['assigned_staff'] = "Not Assigned";
        }

        $replies = [];
        $reply_result = $conn->query("SELECT message AS admin_reply, created_at, sender FROM tblticket_replies WHERE ticket_id = '{$ticket['ticket_id']}' ORDER BY created_at ASC");
        while ($reply = $reply_result->fetch_assoc()) {
            $replies[] = [
                'reply' => $reply['admin_reply'],
                'date' => $reply['created_at'],
                'sender' => $reply['sender']
            ];
        }
        $ticket['replies'] = $replies;

        if (!empty($ticket['schedule_date']) && strtolower($ticket['status']) !== 'completed') {
            $calendar_events[$ticket['schedule_date']][] = [
                'type' => 'ticket',
                'client_name' => $ticket['fname'] . ' ' . $ticket['mname'] . ' ' . $ticket['lname'],
                'staff_name' => $ticket['assigned_staff'],
                'time' => $ticket['schedule_time'],
            ];
        }

        switch (strtolower($ticket['status'])) {
            case 'pending':
                $pending[] = $ticket;
                break;
            case 'scheduled':
                $scheduled[] = $ticket;
                break;
            case 'completed':
                $completed[] = $ticket;
                break;
        }
    }
}

function isOldTicket($createdAt) {
    $created = new DateTime($createdAt);
    $now = new DateTime();
    return $created->diff($now)->days > 3;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Ticket Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin_tickets.css">
    <link rel="stylesheet" href="css/popup.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <script defer src="javascript/popup.js"></script>
</head>
<body>

<?php include('header.php'); ?>

<div class="main-container">
    <?php include('admin_sidebar.html'); ?>

    <div class="container">
        <div class="filters-container" style="margin-top: 50px;">
            <form action="" method="POST">
                <select name="filter_type" id="filter_type" onchange="filterOptions()">
                    <option value="">Select Filter</option>
                    <option value="staff">Staff</option>
                    <option value="client_name">Client Name</option>
                </select>

                <div id="staff_filter" style="display:none;">
                    <select name="filter_staff">
                        <option value="">Select Staff</option>
                        <?php 
                        $staff_result = $conn->query("SELECT id, fname, lname FROM tblstafflist");
                        while ($staff_member = $staff_result->fetch_assoc()):
                            $full_name = decryptData($staff_member['fname']) . ' ' . decryptData($staff_member['lname']);
                        ?>
                            <option value="<?= $staff_member['id'] ?>"><?= $full_name ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div id="client_name_filter" style="display:none;">
                    <select name="filter_name">
                        <option value="">Select Client Name</option>
                        <?php while ($row = $client_names_result->fetch_assoc()):
                            $full_name = decryptData($row['fname']) . ' ' . decryptData($row['mname']) . ' ' . decryptData($row['lname']);
                        ?>
                            <option value="<?= $full_name ?>"><?= $full_name ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit">Apply Filters</button>
            </form>
        </div>

        <div class="calendar">
        <div class="calendar-nav">
    <button onclick="prevMonth()">◀️</button>
    <span id="monthYear"></span>
    <button onclick="nextMonth()">▶️</button>
</div>
            <table id="calendarTable"></table>
        </div>

        <h2>Pending Tickets</h2>
<div class="form-container">
    <table border="1">
        <thead>
            <tr>
                <th>Ticket #</th>
                <th>Client Name</th>
                <th>Address</th>
                <th>Created At</th>
                <th>Concern Type</th>
                <th>Concern</th>
                <th>Assigned Staff</th>
                <th>Image</th>
                <th>Assign Action</th>
                <th>Reply (minor concerns)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending as $ticket): ?>
                <tr style="<?= isOldTicket($ticket['created_at']) ? 'background-color: #ffcccc;' : '' ?>">
                    <td><?= $ticket['ticket_id'] ?></td>
                    <td><?= $ticket['fname'] . ' ' . $ticket['mname'] . ' ' . $ticket['lname'] ?></td>
                    <td><?= $ticket['address'] ?></td>
                    <td><?= $ticket['created_at'] ?></td>
                    <td><?= $ticket['concern_type'] ?></td>
                    <td><?= $ticket['concern'] ?></td>
                    <td><?= $ticket['assigned_staff'] ?></td>
                    <td>
                        <?php if (!empty($ticket['image'])): ?>
                            <img src="uploads/concerns/<?= $ticket['image'] ?>" style="width: 100px; height: auto;">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>
                        <form action="admin_tickets.php" method="POST">
                            <input type="hidden" name="ticket_id" value="<?= $ticket['ticket_id'] ?>">
                            <select name="staff_id">
                                <?php 
                                $staff_result = $conn->query("SELECT id, fname, lname FROM tblstafflist");
                                while ($staff_member = $staff_result->fetch_assoc()):
                                    $staff_full_name = decryptData($staff_member['fname']) . ' ' . decryptData($staff_member['lname']);
                                ?>
                                    <option value="<?= $staff_member['id'] ?>"><?= $staff_full_name ?></option>
                                <?php endwhile; ?>
                            </select>
                            <input type="date" name="schedule_date" required>
                            <input type="time" name="schedule_time" required>
                            <button type="submit">Assign</button>
                        </form>
                    </td>
                    <td>
    <div class="card-container">
        <div class="card-header">
            <div class="img-avatar"></div>
            <div class="text-chat">Ticket Chat</div>
        </div>
        <div class="card-body">
            <div class="messages-container" style="max-height: 150px; overflow-y: auto;">
                <?php if (!empty($ticket['replies'])): ?>
                    <?php foreach ($ticket['replies'] as $reply): ?>
    <?php
        $isClient = strtolower($reply['sender']) === 'client';
        $boxClass = $isClient ? 'message-box left client-message' : 'message-box right';
    ?>
    <div class="<?= $boxClass ?>">
        <p><?= htmlspecialchars($reply['reply']) ?></p>
        <small><?= $reply['date'] ?></small>
    </div>
<?php endforeach; ?>
                <?php else: ?>
                    <div class="message-box left">
                        <p><em>No replies yet.</em></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="message-input">
                <form action="admin_tickets.php" method="POST">
                    <input type="hidden" name="reply_ticket_id" value="<?= $ticket['ticket_id'] ?>">
                    <textarea name="admin_reply" class="message-send" placeholder="Type your message here" required></textarea>
                    <button type="submit">Send</button>
                </form>
            </div>
        </div>
    </div>
</td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

        <h2>Scheduled Tickets</h2>
        <div class="form-container">
            <table border="1">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Client Name</th>
                        <th>Schedule</th>
                        <th>Assigned Staff</th>
                        <th>Image</th>
                        <th>Complete Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scheduled as $ticket): ?>
                        <tr>
                            <td><?= $ticket['ticket_id'] ?></td>
                            <td><?= $ticket['fname'] . ' ' . $ticket['mname'] . ' ' . $ticket['lname'] ?></td>
                            <td><?= $ticket['schedule_date'] ?> <?= $ticket['schedule_time'] ?></td>
                            <td><?= $ticket['assigned_staff'] ?></td>
                            <td>
                                <?php if (!empty($ticket['image'])): ?>
                                    <img src="uploads/concerns/<?= $ticket['image'] ?>" style="width: 100px; height: auto;">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="admin_tickets.php" method="POST">
                                    <input type="hidden" name="complete_ticket_id" value="<?= $ticket['ticket_id'] ?>">
                                    <button type="submit">Mark as Completed</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2>Completed Tickets</h2>
        <div class="form-container">
            <table border="1">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Client Name</th>
                        <th>Concern</th>
                        <th>Completed Date</th>
                        <th>Completed Time</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completed as $ticket): ?>
                        <tr>
                            <td><?= $ticket['ticket_id'] ?></td>
                            <td><?= $ticket['fname'] . ' ' . $ticket['mname'] . ' ' . $ticket['lname'] ?></td>
                            <td><?= $ticket['concern'] ?></td>
                            <td><?= $ticket['completed_date'] ?? '—' ?></td>
                            <td><?= $ticket['completed_time'] ?? '—' ?></td>
                            <td>
                                <?php if (!empty($ticket['image'])): ?>
                                    <img src="uploads/concerns/<?= $ticket['image'] ?>" style="width: 100px; height: auto;">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterOptions() {
    let filterType = document.getElementById('filter_type').value;
    document.getElementById('staff_filter').style.display = 'none';
    document.getElementById('client_name_filter').style.display = 'none';
    if (filterType === 'staff') {
        document.getElementById('staff_filter').style.display = 'block';
    } else if (filterType === 'client_name') {
        document.getElementById('client_name_filter').style.display = 'block';
    }
}

let events = <?= json_encode($calendar_events) ?>;
let today = new Date();
let currentMonth = today.getMonth();
let currentYear = today.getFullYear();

function generateCalendar(month, year) {
    const table = document.getElementById("calendarTable");
    table.innerHTML = "";

    const monthYear = document.getElementById("monthYear");
    monthYear.textContent = new Date(year, month).toLocaleString('default', { month: 'long', year: 'numeric' });

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    let date = 1;
    const headerRow = document.createElement("tr");
    ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
        const th = document.createElement("th");
        th.textContent = day;
        headerRow.appendChild(th);
    });
    table.appendChild(headerRow);

    for (let i = 0; i < 6; i++) {
        const row = document.createElement("tr");
        for (let j = 0; j < 7; j++) {
            const cell = document.createElement("td");
            if (i === 0 && j < firstDay) {
                cell.innerHTML = "";
            } else if (date > daysInMonth) {
                break;
            } else {
                const fullDate = `${year}-${String(month+1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                cell.innerHTML = `<div>${date}</div>`;
                if (events[fullDate]) {
                    cell.classList.add("marked");
                    let tooltipHTML = "";
events[fullDate].forEach(ev => {
    tooltipHTML += `
        <strong>${ev.type === 'appointment' ? 'Appointment' : 'Tickets'}</strong><br>
        Client name: ${ev.client_name}<br>
        Staff name: ${ev.staff_name}<br>
        Time: ${ev.time}<br><br>
    `;
});
                    const tooltip = document.createElement("div");
                    tooltip.className = "tooltip";
                    tooltip.innerHTML = tooltipHTML;
                    cell.appendChild(tooltip);
                }
                date++;
            }
            row.appendChild(cell);
        }
        table.appendChild(row);
    }
}

function prevMonth() {
    currentMonth--;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    generateCalendar(currentMonth, currentYear);
}

function nextMonth() {
    currentMonth++;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    generateCalendar(currentMonth, currentYear);
}

document.addEventListener("DOMContentLoaded", function() {
    generateCalendar(currentMonth, currentYear);
});
</script>

</body>
</html>
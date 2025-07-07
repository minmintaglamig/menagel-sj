<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
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
    echo "User not found.";
    exit();
}

// Total new applications (not approved)
$new_applications_query = "SELECT COUNT(*) AS count FROM tblapplication WHERE status != 'Approved'";
$new_applications = $conn->query($new_applications_query)->fetch_assoc()['count'];

// Tickets by status
$pending_tickets = $conn->query("SELECT COUNT(*) AS count FROM tbltickets WHERE status = 'Pending'")->fetch_assoc()['count'];
$scheduled_tickets = $conn->query("SELECT COUNT(*) AS count FROM tbltickets WHERE status = 'Scheduled'")->fetch_assoc()['count'];
$completed_tickets = $conn->query("SELECT COUNT(*) AS count FROM tbltickets WHERE status = 'Completed'")->fetch_assoc()['count'];

// Client count
$clients_count = $conn->query("SELECT COUNT(*) AS count FROM tblclientlist")->fetch_assoc()['count'];

// Staff count
$staff_count = $conn->query("SELECT COUNT(*) AS count FROM tblstafflist")->fetch_assoc()['count'];

// Installed WiFi
$installed_wifi = $conn->query("SELECT COUNT(*) AS count FROM tblinstallations WHERE status = 'Installed'")->fetch_assoc()['count'];

$app_query = "SELECT COUNT(*) as new_applications FROM tblapplication WHERE status = 'Pending'";
$app_result = $conn->query($app_query);
$app_count = $app_result->fetch_assoc()['new_applications'];

$ticket_query = "SELECT COUNT(*) as new_tickets FROM tbltickets WHERE status = 'Pending'";
$ticket_result = $conn->query($ticket_query);
$ticket_count = $ticket_result->fetch_assoc()['new_tickets'];

$appointment_query = "SELECT schedule_date, schedule_time, fname, lname 
                      FROM tbltickets WHERE status = 'Scheduled' ORDER BY schedule_date ASC";
$appt_result = $conn->query($appointment_query);

$appointments = [];
while ($row = $appt_result->fetch_assoc()) {
    $row['fname'] = decryptData($row['fname']);
    $row['lname'] = decryptData($row['lname']);
    $row['client_name'] = htmlspecialchars($row['fname'] . ' ' . $row['lname']);
    $appointments[] = $row;
}

$appointment_display = !empty($appointments) ? $appointments[0]['schedule_date'] . " at " . $appointments[0]['schedule_time'] : "No upcoming appointment";

$feedback_query = "
    SELECT 
        user_id,
        role,
        rating,
        comment,
        submitted_at
    FROM tblfeedbacks
    ORDER BY submitted_at DESC
";

$feedback_result = $conn->query($feedback_query);

$feedbacks = [];
if ($feedback_result && $feedback_result->num_rows > 0) {
    while ($row = $feedback_result->fetch_assoc()) {
        $feedbacks[] = [
            'user_id' => htmlspecialchars($row['user_id']),
            'role' => htmlspecialchars($row['role']),
            'rating' => intval($row['rating']),
            'comment' => htmlspecialchars($row['comment']),
            'submitted_at' => date("M d, Y", strtotime($row['submitted_at']))
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="pictures/logo.png" type= "image">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="css/cards.css">
    <script src="javascript/calendar_admin.js"></script>
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="javascript/popup.js"></script>
    <title>Admin Dashboard</title>
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

        <div class="content">
        <div class="dashboard-section">
    <h2>Overview Summary</h2>
    <div class="summary-cards">
        <div class="summary-card">
            <h3><?= $new_applications ?></h3>
            <p>New Applications</p>
        </div>
        <div class="summary-card">
            <h3><?= $pending_tickets ?></h3>
            <p>Pending Tickets</p>
        </div>
        <div class="summary-card">
            <h3><?= $scheduled_tickets ?></h3>
            <p>Scheduled Tickets</p>
        </div>
        <div class="summary-card">
            <h3><?= $completed_tickets ?></h3>
            <p>Completed Tickets</p>
        </div>
        <div class="summary-card">
            <h3><?= $clients_count ?></h3>
            <p>Total Clients</p>
        </div>
        <div class="summary-card">
            <h3><?= $staff_count ?></h3>
            <p>Total Staff</p>
        </div>
        <div class="summary-card">
            <h3><?= $installed_wifi ?></h3>
            <p>Installed WiFi</p>
        </div>
    </div>
</div>

            <div class="admin_dashboard-grid">
                <div class="dashboard-left">
                    <div class="dashboard-section">
                        <h2>Upcoming Scheduled Appointment</h2>
                        <div id="custom-calendar">
                            <div id="calendar-header">
                                <button id="prev-month">❮</button>
                                <h3 id="month-year"></h3>
                                <button id="next-month">❯</button>
                            </div>
                            <div id="calendar-body"></div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-right">
                    <div class="dashboard-section">
                        <h2>New Applications</h2>
                        <div class="application-container">
    <?php
        $app_query = "SELECT id, fname, lname, status FROM tblapplication WHERE status = 'Pending' ORDER BY id DESC";
        $app_result = $conn->query($app_query);

        if ($app_result->num_rows > 0) {
            while ($app = $app_result->fetch_assoc()) {
                $decrypted_fname = decryptData($app['fname']);
                $decrypted_lname = decryptData($app['lname']);

                echo '<a href="new_application.php">';
                echo '<div class="application-card">';
                echo '<div class="card-border-top"></div>';
                echo '<div class="ID number">' . htmlspecialchars($app['id']) . '</div>';
                echo '<span>' . htmlspecialchars($decrypted_fname) . ' ' . htmlspecialchars($decrypted_lname) . '</span>';
                echo '<p class="address">Status: ' . htmlspecialchars($app['status']) . '</p>';
                echo '</div>';
                echo '</a>';
            }
        } else {
            echo "<p>No pending applications.</p>";
        }
    ?>
                    </div>

                    <div class="dashboard-section">
                        <h2>Pending Tickets</h2>
                        <div class="ticket-container">
    <?php
        $pending_tickets_query = "SELECT ticket_id, schedule_date, schedule_time, fname, lname, concern_type 
        FROM tbltickets WHERE status = 'Pending' ORDER BY schedule_date ASC";
        $pending_tickets_result = $conn->query($pending_tickets_query);

        if ($pending_tickets_result->num_rows > 0) {
            while ($ticket = $pending_tickets_result->fetch_assoc()) {
                $ticket['fname'] = decryptData($ticket['fname']);
                $ticket['lname'] = decryptData($ticket['lname']);
                $client_name = htmlspecialchars($ticket['fname'] . ' ' . $ticket['lname']);
                $date_display = date("M d, Y", strtotime($ticket['schedule_date']));
                $time_display = date("h:i A", strtotime($ticket['schedule_time']));

                echo '<a href="admin_tickets.php">';
                echo '<div class="ticket-card">';
                echo '<h2>Ticket ' . htmlspecialchars($ticket['ticket_id']) . '</h2>';
                echo '<p><strong>' . $client_name . '</strong></p>';
                echo '<p>Date: ' . $date_display . '</p>';
                echo '<p>Time: ' . $time_display . '</p>';
                echo '<p>Status: Pending</p>';
                echo '</div>';
                echo '</a>';
            }
        } else {
            echo "<p>No pending tickets.</p>";
        }
    ?>
</div>
                </div>
                <div class="dashboard-section">
    <h2>Recent Feedback</h2>
    <div class="feedback-container">
        <?php if (!empty($feedbacks)): ?>
<?php foreach ($feedbacks as $fb): ?>
    <div class="feedback-card">
        <h3>User ID: <?= $fb['user_id'] ?></h3>
        <p><?= $fb['comment'] ?></p>
        <small><?= $fb['submitted_at'] ?></small>
    </div>
<?php endforeach; ?>

        <?php else: ?>
            <p>No feedback submitted yet.</p>
        <?php endif; ?>
    </div>
</div>

            </div>
        </div>
    </div>

    <script src="javascript/sidebar-toggle.js"></script>
    <script src="javascript/calendar.js"></script>
    <script>
        const appointments = <?php echo json_encode($appointments); ?>;
        console.log(appointments);

        document.addEventListener("DOMContentLoaded", function () {
            const calendarBody = document.getElementById("calendar-body");
            const monthYear = document.getElementById("month-year");
            const prevMonthBtn = document.getElementById("prev-month");
            const nextMonthBtn = document.getElementById("next-month");

            let currentDate = new Date();

            function formatTime(timeStr) {
                let [hours, minutes] = timeStr.split(":");
                hours = parseInt(hours);
                let period = hours >= 12 ? "P.M." : "A.M.";
                hours = hours % 12 || 12;
                return `${hours}:${minutes} ${period}`;
            }

            function renderCalendar() {
                calendarBody.innerHTML = "";
                let firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
                let lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
                monthYear.textContent = firstDay.toLocaleString("default", { month: "long", year: "numeric" });

                for (let i = 1; i <= lastDay.getDate(); i++) {
                    let dayDiv = document.createElement("div");
                    dayDiv.classList.add("calendar-day");
                    dayDiv.textContent = i;

                    let dateStr = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, "0")}-${String(i).padStart(2, "0")}`;

                    let event = appointments.find((app) => app.schedule_date === dateStr);
                    if (event) {
                        let eventDiv = document.createElement("div");
                        eventDiv.classList.add("event");
                        eventDiv.textContent = "📌 " + event.client_name;
                        dayDiv.appendChild(eventDiv);

                        eventDiv.addEventListener("mouseover", function () {
                            let stickyNote = document.createElement("div");
                            stickyNote.classList.add("sticky-note");
                            let formattedTime = formatTime(event.schedule_time);
                            stickyNote.textContent = `${event.client_name} ${formattedTime}`;
                            document.body.appendChild(stickyNote);
                            stickyNote.style.top = eventDiv.getBoundingClientRect().top + "px";
                            stickyNote.style.left = eventDiv.getBoundingClientRect().left + "px";
                        });

                        eventDiv.addEventListener("mouseleave", function () {
                            document.querySelectorAll(".sticky-note").forEach((note) => note.remove());
                        });
                    }

                    calendarBody.appendChild(dayDiv);
                }
            }

            prevMonthBtn.addEventListener("click", () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            });

            nextMonthBtn.addEventListener("click", () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            });

            renderCalendar();
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs</title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #D84040;
            margin-bottom: 20px;
        }

        form.filter-form {
            max-width: 600px;
            margin: 0 auto 25px auto;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .filter-select {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        #print-form {
        max-width: 1000px; /* Wider than default */
        width: 100%;
        margin: 0 auto;
        padding: 20px;
        box-sizing: border-box;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

        .log-entry {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        padding: 10px;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 6px;
    }

    .log-entry input[type="checkbox"] {
    margin-right: 12px;
    flex-shrink: 0;
    width: 18px;
    height: 18px;
}

.log-entry div {
    flex: 1;
}


        .log-entry::before {
            content: '';
            width: 10px;
            height: 10px;
            background: #D84040;
            border-radius: 50%;
            position: absolute;
            left: -6px;
            top: 18px;
        }

        .log-entry strong {
            color: #444;
        }

        .date-label {
        font-weight: bold;
        margin-top: 20px;
        margin-bottom: 10px;
        font-size: 16px;
    }

    .timestamp {
        float: right;
        font-size: 12px;
        color: #777;
    }

        .action-buttons {
            margin-bottom: 15px;
            gap: 10px;
        }

        .action-buttons button,
        .filter-form button,
        #print-form button {
            padding: 6px 12px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .select-btn, .unselect-btn, button[name="print"] {
        margin-right: 10px;
        padding: 8px 15px;
        border: none;
        background-color: #D2665A;
        color: white;
        border-radius: 4px;
        cursor: pointer;
    }

    .select-btn:hover, .unselect-btn:hover, button[name="print"]:hover {
        background-color:rgb(170, 71, 60);
    }

        @media print {
            .no-print {
                display: none;
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
    <?php include('admin_sidebar.html'); ?>
    <div class="container">
        <h2>📝 Activity Logs</h2>

        <form method="GET" class="filter-form no-print">
            <select name="role" class="filter-select">
                <option value="">All Roles</option>
                <option value="admin" <?= ($_GET['role'] ?? '') == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="staff" <?= ($_GET['role'] ?? '') == 'staff' ? 'selected' : '' ?>>Staff</option>
                <option value="client" <?= ($_GET['role'] ?? '') == 'client' ? 'selected' : '' ?>>Client</option>
            </select>

            <select name="date" class="filter-select">
                <option value="">All Time</option>
                <option value="today" <?= ($_GET['date'] ?? '') == 'today' ? 'selected' : '' ?>>Today</option>
                <option value="week" <?= ($_GET['date'] ?? '') == 'week' ? 'selected' : '' ?>>This Week</option>
                <option value="month" <?= ($_GET['date'] ?? '') == 'month' ? 'selected' : '' ?>>This Month</option>
                <option value="year" <?= ($_GET['date'] ?? '') == 'year' ? 'selected' : '' ?>>This Year</option>
            </select>

            <button type="submit">Filter</button>
        </form>

        <form method="POST" action="includes/print_logs.php" id="print-form">
            <div class="action-buttons no-print">
                <button type="button" onclick="selectAll(true)" class="select-btn">Select All</button>
                <button type="button" onclick="selectAll(false)" class="unselect-btn">Unselect All</button>
            </div>

            <?php
            $where = [];
            $params = [];
            $types = '';

            if (!empty($_GET['role'])) {
                $where[] = "role = ?";
                $params[] = $_GET['role'];
                $types .= 's';
            }

            if (!empty($_GET['date'])) {
                if ($_GET['date'] == 'today') {
                    $where[] = "DATE(created_at) = CURDATE()";
                } elseif ($_GET['date'] == 'week') {
                    $where[] = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
                } elseif ($_GET['date'] == 'month') {
                    $where[] = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
                } elseif ($_GET['date'] == 'year') {
                    $where[] = "YEAR(created_at) = YEAR(CURDATE())";
                }
            }

            $sql = "SELECT * FROM tblactivity_logs";
            if ($where) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            $sql .= " ORDER BY created_at DESC LIMIT 100";

            $stmt = $conn->prepare($sql);
            if ($types) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $currentLabel = '';
                while ($log = $result->fetch_assoc()) {
                    $createdAt = strtotime($log['created_at']);
                    $logDate = date('Y-m-d', $createdAt);
                    $today = date('Y-m-d');
                    $yesterday = date('Y-m-d', strtotime('-1 day'));

                    if ($logDate == $today) {
                        $label = "Today";
                    } elseif ($logDate == $yesterday) {
                        $label = "Yesterday";
                    } else {
                        $label = date('F j, Y', $createdAt);
                    }

                    if ($label !== $currentLabel) {
                        echo "<div class='date-label'>$label</div>";
                        $currentLabel = $label;
                    }

                    echo "<div class='log-entry'>
                            <input type='checkbox' name='logs[]' value='" . $log['id'] . "'>
                            <div>
                                <strong>" . ucfirst($log['role']) . "</strong>: " . htmlspecialchars($log['action']) . "
                                <span class='timestamp'>" . date('g:i A', $createdAt) . "</span>
                            </div>
                          </div>";
                }
            } else {
                echo "<p style='text-align:center; color:#999;'>No activity found for the selected filters.</p>";
            }

            $stmt->close();
            $conn->close();
            ?>

            <button type="submit" name="print">Print Selected Logs</button>
        </form>
    </div>
</div>

<script>
function selectAll(isChecked) {
    const checkboxes = document.querySelectorAll("input[name='logs[]']");
    checkboxes.forEach(cb => cb.checked = isChecked);
}
</script>

</body>
</html>
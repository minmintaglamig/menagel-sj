<?php
include('includes/dbh.php');
include('includes/encryption.php');

$where = [];
$params = [];
$types = '';

if (!empty($_GET['role'])) {
    $where[] = "role = ?";
    $params[] = $_GET['role'];
    $types .= 's';
}

if (!empty($_GET['staff'])) {
    $where[] = "full_name = ?";
    $params[] = $_GET['staff'];
    $types .= 's';
}

if (!empty($_GET['client'])) {
    $where[] = "full_name = ?";
    $params[] = $_GET['client'];
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

$output = '';
$currentLabel = '';

if ($result->num_rows > 0) {
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
            $output .= "<div class='date-label'>$label</div>";
            $currentLabel = $label;
        }

        $output .= "<div class='log-entry'>
            <strong>" . ucfirst($log['role']) . "</strong>: " . htmlspecialchars($log['action']) . "
            <span class='timestamp'>" . date('g:i A', $createdAt) . "</span>
            <form action='print_log_summary.php' method='POST' target='_blank' style='display:inline; float:right;'>
                <input type='hidden' name='name' value='" . htmlspecialchars($log['full_name']) . "'>
                <button type='submit' style='font-size:12px; margin-left:10px;'>🖨️ Print</button>
            </form>
        </div>";
    }
} else {
    $output .= "<p style='text-align:center; color:#999;'>No activity found for the selected filters.</p>";
}

echo $output;
?>
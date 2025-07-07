<?php
include_once '../includes/dbh.php';
include_once '../includes/encryption.php';

if (!isset($_POST['logs']) || !is_array($_POST['logs']) || count($_POST['logs']) === 0) {
    exit('Invalid request: No logs selected.');
}

// Sanitize and validate log IDs (ensure they are numeric)
$logIds = array_filter($_POST['logs'], fn($id) => is_numeric($id));
if (empty($logIds)) {
    exit('Invalid request: Invalid log IDs.');
}

// Prepare dynamic placeholders
$placeholders = implode(',', array_fill(0, count($logIds), '?'));
$types = str_repeat('i', count($logIds)); // all are integers

// Build the query
$query = "SELECT * FROM tblactivity_logs WHERE id IN ($placeholders) ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$logIds);
$stmt->execute();
$result = $stmt->get_result();

// Display logs
echo "<h2>🖨️ Printed Activity Summary</h2><hr>";

while ($log = $result->fetch_assoc()) {
    echo "<p><strong>" . ucfirst($log['role']) . "</strong>: " . htmlspecialchars($log['action']) .
         " <em>(" . $log['created_at'] . ")</em></p>";
}

echo "<script>window.print();</script>";
?>
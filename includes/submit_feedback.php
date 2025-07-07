<?php
session_start();
    include_once 'dbh.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';
$rating = $_POST['rating'] ?? 0;
$comment = trim($_POST['comment'] ?? '');

if (!$user_id || !$rating) {
    http_response_code(400);
    echo "Invalid input. User ID or rating missing.";
    exit;
}

$stmt = $conn->prepare("INSERT INTO tblfeedbacks (user_id, role, rating, comment) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo "Prepare failed: " . $conn->error;
    exit;
}

$stmt->bind_param("isis", $user_id, $role, $rating, $comment);

if ($stmt->execute()) {
    echo "Feedback saved.";
} else {
    http_response_code(500);
    echo "Execute failed: " . $stmt->error;
}
?>
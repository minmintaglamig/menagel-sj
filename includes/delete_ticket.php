<?php
    session_start();
    include_once('../includes/dbh.php');
    
    if (!isset($_SESSION['user_email'])) {
        header("Location: ../client_tickets.php");
        exit();
    }
    
    $email = $_SESSION['user_email'];
    
    $query = "SELECT routernumber FROM tblclientlist WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_assoc();
    
    if (!$client) {
        header("Location: ../client_tickets.php");
        exit();
    }
    
    $ticket_id = $_GET['ticket_id'] ?? null;
    if (!$ticket_id) {
        header("Location: ../client_tickets.php");
        exit();
    }
    
    $delete_query = "DELETE FROM tbltickets WHERE ticket_id = ? AND routernumber = ? AND status = 'Pending'";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("is", $ticket_id, $client['routernumber']);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Ticket deleted successfully.'];
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to delete the ticket.'];
    }
    
    $stmt->close();
    $conn->close();
    
    header("Location: ../client_tickets.php");
    exit();
?>
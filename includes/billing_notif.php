<?php
    include_once 'dbh.php';
    include_once 'notification_helper.php';
    
    $today = date('Y-m-d');
    $next_week = date('Y-m-d', strtotime('+7 days'));
    
    $result = $conn->query("SELECT billing_id, routernumber, due_date, status FROM tblbilling WHERE status = 'Unpaid'");
    
    while ($row = $result->fetch_assoc()) {
        if ($row['due_date'] <= $next_week) {
            $router = $row['routernumber'];
    
            $client_query = $conn->prepare("SELECT client_id FROM tblclientlist WHERE routernumber = ?");
            $client_query->bind_param('s', $router);
            $client_query->execute();
            $client_result = $client_query->get_result();
            $client = $client_result->fetch_assoc();
    
            if ($client) {
                $client_id = $client['client_id'];
                addNotification($conn, 'client', $client_id, '💸 Your billing is due soon. Please settle your payment.');
                addNotification($conn, 'staff', $staff_id, '💸 Billing due soon for your assigned client.');
                addNotification($conn, 'admin', $admin_id, '💸 Billing due soon for a client.');
            }
        }
    }
?>
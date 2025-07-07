<?php
    session_start();
    include('includes/dbh.php');
    include('includes/encryption.php');
    include('includes/notification_helper.php');
    
    if (!isset($_SESSION['user_email'])) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
        header('Location: login.php');
        exit();
    }
    
    $user_email = $_SESSION['user_email'];
    

    $query = "SELECT client_id, routernumber, promo_id FROM tblclientlist WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'No client record found for your account.'];
        header('Location: client_dashboard.php');
        exit();
    }
    
    $row = $result->fetch_assoc();
    $client_id = $row['client_id'];
    $routernumber = decryptData($row['routernumber']);
    $promo_id = $row['promo_id'];
    
    if (empty($routernumber)) {
        $show_modal = true;
        $billing_result = null;
    } else {
        $show_modal = false;
    
        $check_billing_query = "SELECT billing_id FROM tblbilling WHERE routernumber = ?";
        $stmt = $conn->prepare($check_billing_query);
        $encrypted_router = encryptData($routernumber);
        $stmt->bind_param("s", $encrypted_router);
        $stmt->execute();
        $existing_result = $stmt->get_result();
    
        if ($existing_result->num_rows == 0) {
        
            // Fetch the promo details based on promo_id
            $promo_query = "SELECT amount FROM tblpromo WHERE promo_id = ?";
            $stmt = $conn->prepare($promo_query);
            $stmt->bind_param("i", $promo_id);  // Use the promo_id associated with the client
            $stmt->execute();
            $promo_result = $stmt->get_result();
        
            if ($promo_result->num_rows > 0) {
                $promo = $promo_result->fetch_assoc();
                $amount_due = $promo['amount'];  // Use the amount from the promo
            } else {
                $amount_due = 0.00; // Fallback if promo not found
            }
        
            $due_date = date('Y-m-d', strtotime('+1 month'));
        
            // Insert the billing record
            $insert_query = "INSERT INTO tblbilling (routernumber, promo_id, amount_due, due_date, status)
                             VALUES (?, ?, ?, ?, 'Unpaid')";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("sids", $encrypted_router, $promo_id, $amount_due, $due_date);
            $stmt->execute();
        
            // ✅ Send billing notifications
            // Notify Admins
            $admins = $conn->query("SELECT id FROM tblusers WHERE role = 'Admin'");
            while ($admin = $admins->fetch_assoc()) {
                addNotification($conn, 'Admin', $admin['id'], '💳 Billing reminder: Client payment is due soon!', 'admin_billspayment.php');
            }
        
            // Notify Staff
            $staffs = $conn->query("SELECT id FROM tblusers WHERE role = 'Staff'");
            while ($staff = $staffs->fetch_assoc()) {
                addNotification($conn, 'Staff', $staff['id'], '💳 Billing reminder: Client payment is due soon!', 'staff_billspayment.php');
            }
        
            // Notify Client
            addNotification($conn, 'Client', $client_id, '💳 Billing reminder: Your payment is due soon!', 'client_billspayment.php');
        }        
    
        $latest_bill_query = "SELECT b.billing_id, b.routernumber, p.promo_name, b.amount_due, b.due_date, b.status,
                              pay.amount_paid, pay.payment_date, pay.payment_method
                              FROM tblbilling b
                              LEFT JOIN tblpromo p ON b.promo_id = p.promo_id
                              LEFT JOIN tblpayment pay ON b.billing_id = pay.billing_id
                              WHERE b.routernumber = ?
                              ORDER BY b.due_date DESC";
        $stmt = $conn->prepare($latest_bill_query);
        $stmt->bind_param("s", $encrypted_router);
        $stmt->execute();
        $billing_result = $stmt->get_result();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="pictures/logo.png" type= "image">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/popup.css">
    <link rel="stylesheet" href="css/bills.css">
    <script defer src="javascript/popup.js"></script>
    <title>Client - Bills & Payments</title>
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
        <h2>Bills & Payments</h2>

        <?php if ($show_modal): ?>
            <div class="error-message">
                You don't have a router number associated with your account. Please wait for the admin to assign a router number after wifi installation.
            </div>
        <?php endif; ?>

        <?php if ($billing_result && $billing_result->num_rows > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Billing ID</th>
                            <th>Router Number</th>
                            <th>Promo</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Amount Paid</th>
                            <th>Payment Date</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $billing_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['billing_id']); ?></td>
                                <td><?= htmlspecialchars(decryptData($row['routernumber'])); ?></td>
                                <td><?= htmlspecialchars($row['promo_name']); ?></td>
                                <td><?= htmlspecialchars($row['due_date']); ?></td>
                                <td><?= htmlspecialchars($row['status']); ?></td>
                                <td><?= htmlspecialchars($row['amount_paid'] ?? 'Not Paid'); ?></td>
                                <td><?= htmlspecialchars($row['payment_date'] ?? 'N/A'); ?></td>
                                <td><?= htmlspecialchars($row['payment_method'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>...</p>
        <?php endif; ?>
    </div>

    <?php include('faq_widget.php'); ?>

    <button class="print-button" onclick="window.print();">Print</button>

    <script src="javascript/sidebar-toggle.js"></script>
</body>
</html>

<?php $conn->close(); ?>
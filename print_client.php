<?php
include('includes/dbh.php');
include('includes/encryption.php');
session_start();

if (isset($_GET['client_id'])) {
    $client_id = $_GET['client_id'];
    
    $client_query = $conn->query("
        SELECT c.*, cl.promo_name
        FROM tblclient_archive c
        LEFT JOIN tblclientlist cl ON c.routernumber = cl.routernumber
        WHERE c.client_id = '$client_id'
    ");
    $client = $client_query->fetch_assoc();

    if (!$client) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Client not found.'];
        header('Location: archived_clients.php');
        exit();
    }

    $billing_query = $conn->query("SELECT * FROM tblbilling_archive WHERE routernumber = '{$client['routernumber']}'");
    $payment_query = $conn->query("SELECT * FROM tblpayment_archive WHERE routernumber = '{$client['routernumber']}'");
} else {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Client ID not provided.'];
    header('Location: archived_clients.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Client Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        h1 {
            text-align: center;
        }
        .btn {
            padding: 10px;
            margin: 20px 0;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        @media print {
            .btn {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Archived Client Details</h1>

    <h2>Client Information</h2>
    <table>
        <tr>
            <th>Client ID</th>
            <td><?= htmlspecialchars($client['client_id']) ?></td>
        </tr>
        <tr>
            <th>Full Name</th>
            <td><?= htmlspecialchars(decryptData($client['fname']) . ' ' . decryptData($client['mname']) . ' ' . decryptData($client['lname'])) ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?= htmlspecialchars(decryptData($client['address'])) ?></td>
        </tr>
        <tr>
            <th>Mobile</th>
            <td><?= htmlspecialchars(decryptData($client['mobile'])) ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= htmlspecialchars(decryptData($client['email'])) ?></td>
        </tr>
        <tr>
            <th>Promo Name</th>
            <td><?= htmlspecialchars($client['promo_name']) ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?= htmlspecialchars($client['status']) ?></td>
        </tr>
        <tr>
            <th>Drop Date</th>
            <td><?= $client['drop_date'] ?></td>
        </tr>
    </table>

    <h2>Billing Records</h2>
    <table>
        <thead>
            <tr>
                <th>Billing ID</th>
                <th>Promo ID</th>
                <th>Amount Due</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($bill = $billing_query->fetch_assoc()): ?>
                <tr>
                    <td><?= $bill['billing_id'] ?></td>
                    <td><?= $bill['promo_id'] ?></td>
                    <td><?= $bill['amount_due'] ?></td>
                    <td><?= $bill['due_date'] ?></td>
                    <td><?= $bill['status'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Payment Records</h2>
    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Payment Date</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Method</th>
                <th>Updated By</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($pay = $payment_query->fetch_assoc()): ?>
                <tr>
                    <td><?= $pay['payment_id'] ?></td>
                    <td><?= $pay['payment_date'] ?></td>
                    <td><?= $pay['amount'] ?></td>
                    <td><?= $pay['amount_paid'] ?></td>
                    <td><?= $pay['payment_method'] ?></td>
                    <td><?= $pay['updated_by_admin'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="javascript:window.print();" class="btn">Print this page</a>
</div>

</body>
</html>
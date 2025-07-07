<?php
    session_start();
    include('includes/dbh.php');
    include('includes/encryption.php');
    
    if (!isset($_SESSION['user_email'])) {
        echo "Unauthorized access!";
        exit();
    }

    $billing_query = "SELECT 
                        b.billing_id, 
                        c.routernumber, 
                        p.promo_name, 
                        p.amount, 
                        b.due_date, 
                        b.status,
                        pay.amount_paid, 
                        pay.payment_date, 
                        pay.payment_method
                    FROM tblbilling b
                    JOIN tblclientlist c ON b.routernumber = c.routernumber
                    LEFT JOIN tblpromo p ON b.promo_id = p.promo_id
                    LEFT JOIN tblpayment pay ON b.billing_id = pay.billing_id
                    ORDER BY c.routernumber, b.due_date DESC";

    $billing_result = $conn->query($billing_query);
    $billings_by_router = [];

    while ($row = $billing_result->fetch_assoc()) {
        $router = decryptData($row['routernumber']);
        $billings_by_router[$router][] = $row;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Bills & Payments</title>
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="javascript/popup.js"></script>
    <script defer src="javascript/script.js"></script>

    <style>
.router-card {
    margin: 20px auto;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 10px;
    background-color: #f9f9f9;
    max-width: 95%;
    box-sizing: border-box;
}

        .toggle-btn {
            background-color: #D84040;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 10px;
            margin-left: 20px;
        }
        .toggle-btn:hover {
            background-color:rgb(255, 107, 107);
        }
        .billing-details {
            display: none;
            margin-top: 10px;
        }

        .router-card table td form input,
.router-card table td form select,
.router-card table td form button {
    font-size: 14px;
    padding: 5px;
    margin-right: 4px;
}

@media (max-width: 1024px) {
    .layout {
        display: block;
    }

    .main-container {
        margin-left: 0;
        padding: 20px;
    }

    .main-container h2 {
        margin-top: 20px;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-responsive table {
        min-width: 800px;
        display: block;
    }

    .table-responsive th,
    .table-responsive td {
        white-space: nowrap;
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

<div class="layout">
    <?php include('header.php'); ?>
    <div class="main-container">
        <?php include('staff_sidebar.html'); ?>
    </div>
</div>
        <h2>Bills & Payments</h2>

        <?php foreach ($billings_by_router as $router => $bills): ?>
            <div class="router-card">
                <button class="toggle-btn" onclick="toggleCard('card-<?= md5($router) ?>')">
                    Router Number: <?= htmlspecialchars($router) ?>
                </button>
                <div id="card-<?= md5($router) ?>" class="billing-details">
    <div class="table-responsive">
        <table>
                        <thead>
                            <tr>
                                <th>Promo</th>
                                <th>Amount Due</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Amount Paid</th>
                                <th>Payment Date</th>
                                <th>Payment Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bills as $bill): ?>
                                <tr>
                                    <td><?= htmlspecialchars($bill['promo_name'] ?? 'N/A'); ?></td>
                                    <td><?= htmlspecialchars($bill['amount']); ?></td>
                                    <td><?= htmlspecialchars($bill['due_date']); ?></td>
                                    <td><?= htmlspecialchars($bill['status']); ?></td>
                                    <td><?= htmlspecialchars($bill['amount_paid'] ?? 'Not Paid'); ?></td>
                                    <td><?= htmlspecialchars($bill['payment_date'] ?? 'N/A'); ?></td>
                                    <td><?= htmlspecialchars($bill['payment_method'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
    </div>
</div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<script>
function toggleCard(id) {
    const card = document.getElementById(id);
    card.style.display = card.style.display === 'none' ? 'block' : 'none';
}
</script>

<script src="javascript/sidebar-toggle.js"></script>

</body>
</html>

<?php $conn->close(); ?>
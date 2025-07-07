<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');

if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'alert', 'msg' => 'Unauthorized access! Please log in.'];
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['billing_id'])) {
    $billing_id = $_POST['billing_id'];
    $amount_paid = ($_POST['amount_paid']);
    $payment_method = ($_POST['payment_method']);
    $payment_date = date("Y-m-d");
    $admin_updated = $_SESSION['email'];

    $update_billing_query = "UPDATE tblbilling SET status = 'Paid' WHERE billing_id = ?";
    $stmt = $conn->prepare($update_billing_query);
    $stmt->bind_param("i", $billing_id);
    $stmt->execute();

    $insert_payment_query = "INSERT INTO tblpayment (billing_id, routernumber, payment_date, amount_paid, payment_method, updated_by_admin)
                             SELECT billing_id, routernumber, ?, ?, ?, ? FROM tblbilling WHERE billing_id = ?";
    $stmt = $conn->prepare($insert_payment_query);
    $stmt->bind_param("ssssi", $payment_date, $amount_paid, $payment_method, $admin_updated, $billing_id);
    $stmt->execute();

    $new_due_date_query = "SELECT routernumber, promo_id, amount_due, DATE_ADD(due_date, INTERVAL 1 MONTH) AS new_due_date
                           FROM tblbilling WHERE billing_id = ?";
    $stmt = $conn->prepare($new_due_date_query);
    $stmt->bind_param("i", $billing_id);
    $stmt->execute();
    $stmt->bind_result($routernumber, $promo_id, $amount_due, $new_due_date);
    $stmt->fetch();
    $stmt->close();

    if ($routernumber) {
        $insert_new_billing = "INSERT INTO tblbilling (routernumber, promo_id, amount_due, due_date, status) 
                               VALUES (?, ?, ?, ?, 'Unpaid')";
        $stmt = $conn->prepare($insert_new_billing);
        $stmt->bind_param("sids", $routernumber, $promo_id, $amount_due, $new_due_date);
        $stmt->execute();
    }

    $_SESSION['popup'] = ['type' => 'success', 'msg' => '💰 Payment recorded successfully!'];
    header("Location: admin_billspayment.php");
    exit();
}

$billing_query = "SELECT b.billing_id, c.routernumber, p.promo_name, p.amount, b.due_date, b.status
                  FROM tblbilling b
                  JOIN tblclientlist c ON b.routernumber = c.routernumber
                  JOIN tblpromo_subscribers ps ON c.client_id = ps.client_id
                  JOIN tblpromo p ON ps.promo_id = p.promo_id
                  ORDER BY c.routernumber, b.due_date ASC";

$result = $conn->query($billing_query);
$billings_by_router = [];

while ($row = $result->fetch_assoc()) {
    $router = decryptData($row['routernumber']);
    $billings_by_router[$router][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Bills & Payments</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="pictures/logo.png" type= "image">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/popup.css">
    <script src="javascript/popup.js"></script>
    <style>
        .router-card {
            margin-bottom: 20px;
            margin-left: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .toggle-btn {
            background-color: #D84040;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 10px;
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
        <h1>Client Billing</h1>

        <?php foreach ($billings_by_router as $router => $bills): ?>
            <div class="router-card">
                <button class="toggle-btn" onclick="toggleCard('card-<?= md5($router) ?>')">
                    Router Number: <?= htmlspecialchars($router) ?>
                </button>
                <div id="card-<?= md5($router) ?>" class="billing-details">
                    <table>
                        <tr>
                            <th>Promo</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><?= htmlspecialchars($bill['promo_name']) ?></td>
                                <td><?= htmlspecialchars($bill['amount']) ?> PHP</td>
                                <td><?= htmlspecialchars($bill['due_date']) ?></td>
                                <td><?= htmlspecialchars($bill['status']) ?></td>
                                <td>
    <?php if ($bill['status'] === 'Unpaid'): ?>
        <form action="admin_billspayment.php" method="POST" style="display: flex; flex-wrap: wrap; gap: 5px; align-items: center;">
            <input type="hidden" name="billing_id" value="<?= $bill['billing_id'] ?>">
            <input type="number" name="amount_paid" placeholder="Amount" style="width: 80px;" required>
            <select name="payment_method" required>
                <option value="Cash">Cash</option>
                <option value="Online Transfer">Online Transfer</option>
            </select>
            <button type="submit">Pay</button>
        </form>
    <?php else: ?>
        Paid
    <?php endif; ?>
</td>

                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <button onclick="printCard('card-<?= md5($router) ?>')">🖨️ Print</button>
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

function printCard(id) {
    const content = document.getElementById(id).innerHTML;
    const win = window.open('', '', 'width=800,height=600');
    win.document.write('<html><head><title>Print Billing</title></head><body>');
    win.document.write(content);
    win.document.write('</body></html>');
    win.document.close();
    win.print();
}
</script>

<script src="javascript/sidebar-toggle.js"></script>
</body>
</html>

<?php $conn->close(); ?>
<?php
include('includes/dbh.php');
include('includes/encryption.php');
session_start();

$query = "SELECT * FROM tblclient_archive";
$result = $conn->query($query);
$archived_clients = ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Archived Clients</title>
    <link rel="stylesheet" href="css/style.css">
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
</div>

<h2>Archived Clients</h2>
<table border="1" cellpadding="10">
    <thead>
        <tr>
            <th>Client ID</th>
            <th>Full Name</th>
            <th>Address</th>
            <th>Mobile</th>
            <th>Email</th>
            <th>Drop Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
<?php foreach ($archived_clients as $client): ?>
    <tr>
        <td><?= htmlspecialchars($client['client_id']) ?></td>
        <td><?= htmlspecialchars(decryptData($client['fname']) . ' ' . decryptData($client['mname']) . ' ' . decryptData($client['lname'])) ?></td>
        <td><?= htmlspecialchars(decryptData($client['address'])) ?></td>
        <td><?= htmlspecialchars(decryptData($client['mobile'])) ?></td>
        <td><?= htmlspecialchars(decryptData($client['email'])) ?></td>
        <td><?= $client['drop_date'] ?></td>
        <td>
            <form method="POST" action="includes/unarchive_client.php" style="display:inline-block;">
                <input type="hidden" name="client_id" value="<?= $client['client_id'] ?>">
                <button type="submit" onclick="return confirm('Are you sure you want to unarchive? It will save to the client list...')">Unarchive</button>
            </form>
            <form method="GET" action="print_client.php" target="_blank" style="display:inline-block;">
                <input type="hidden" name="client_id" value="<?= $client['client_id'] ?>">
                <button type="submit">Print</button>
            </form>
        </td>
    </tr>

    <tr>
        <td colspan="7">
            <strong>Billing Records</strong>
            <table border="1" cellpadding="5">
                <tr><th>Billing ID</th><th>Promo ID</th><th>Amount Due</th><th>Due Date</th><th>Status</th></tr>
                <?php
                $routernumber = $conn->real_escape_string($client['routernumber']);
                $billingResult = $conn->query("SELECT * FROM tblbilling_archive WHERE routernumber = '$routernumber'");
                if ($billingResult && $billingResult->num_rows > 0):
                    while ($bill = $billingResult->fetch_assoc()):
                ?>
                        <tr>
                            <td><?= $bill['billing_id'] ?></td>
                            <td><?= $bill['promo_id'] ?></td>
                            <td><?= $bill['amount_due'] ?></td>
                            <td><?= $bill['due_date'] ?></td>
                            <td><?= $bill['status'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No billing history found.</td></tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="7">
            <strong>Payment Records</strong>
            <table border="1" cellpadding="5">
                <tr><th>Payment ID</th><th>Payment Date</th><th>Amount</th><th>Paid</th><th>Method</th><th>Updated By</th></tr>
                <?php
                $paymentResult = $conn->query("SELECT * FROM tblpayment_archive WHERE routernumber = '$routernumber'");
                if ($paymentResult && $paymentResult->num_rows > 0):
                    while ($pay = $paymentResult->fetch_assoc()):
                ?>
                        <tr>
                            <td><?= $pay['payment_id'] ?></td>
                            <td><?= $pay['payment_date'] ?></td>
                            <td><?= $pay['amount'] ?></td>
                            <td><?= $pay['amount_paid'] ?></td>
                            <td><?= $pay['payment_method'] ?></td>
                            <td><?= $pay['updated_by_admin'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No payment history found.</td></tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>

</body>
</html>
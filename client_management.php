<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header('Location: login.php');
    exit();
}

$email = $_SESSION['user_email'];

$query = "SELECT * FROM tblusers WHERE email = ? AND role = 'admin'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Admin privileges required.'];
    header('Location: login.php');
    exit();
}

$query = "SELECT * FROM tblclientlist";
$result = $conn->query($query);
$client_list = ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];

$promo_result = $conn->query("SELECT promo_id, promo_name FROM tblpromo");
$promos = $promo_result ? $promo_result->fetch_all(MYSQLI_ASSOC) : [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = htmlspecialchars(trim($_POST['fname']));
    $mname = htmlspecialchars(trim($_POST['mname']));
    $lname = htmlspecialchars(trim($_POST['lname']));
    $city = htmlspecialchars(trim($_POST['city']));
    $barangay = htmlspecialchars(trim($_POST['barangay']));
    $street = htmlspecialchars(trim($_POST['street']));
    $full_address = "$street, $barangay, $city";
    $mobile = htmlspecialchars(trim($_POST['mobile']));
    $email = htmlspecialchars(trim($_POST['email']));
    $promo_id = intval($_POST['promo_id']);

    if (empty($fname) || empty($lname) || empty($full_address) || empty($mobile) || empty($email) || $promo_id <= 0) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'All required fields must be filled.'];
        header("Location: client_management.php");
        exit();
    }

    $stmt_check_promo = $conn->prepare("SELECT promo_name FROM tblpromo WHERE promo_id = ?");
    $stmt_check_promo->bind_param("i", $promo_id);
    $stmt_check_promo->execute();
    $stmt_check_promo->bind_result($promo_name);
    if (!$stmt_check_promo->fetch()) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid promo selected.'];
        header("Location: client_management.php");
        exit();
    }
    $stmt_check_promo->close();

    $encrypted_email = encryptData($email);
    $enc_fname = encryptData($fname);
    $enc_mname = encryptData($mname);
    $enc_lname = encryptData($lname);
    $enc_address = encryptData($full_address);
    $enc_mobile = encryptData($mobile);
    $enc_promo_name = encryptData($promo_name);

    $conn->begin_transaction();
    try {
        $stmt_app = $conn->prepare("INSERT INTO tblapplication (fname, mname, lname, address, mobile, email, promo_id, promo_name, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt_app->bind_param("ssssssis", $enc_fname, $enc_mname, $enc_lname, $enc_address, $enc_mobile, $encrypted_email, $promo_id, $promo_name);        
        if (!$stmt_app->execute()) throw new Exception("Insert to tblapplication failed");

        $stmt_client = $conn->prepare("INSERT INTO tblclientlist (fname, mname, lname, address, mobile, email, promo_id, promo_name, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt_client->bind_param("ssssssis", $enc_fname, $enc_mname, $enc_lname, $enc_address, $enc_mobile, $encrypted_email, $promo_id, $promo_name);        
        if (!$stmt_client->execute()) throw new Exception("Insert to tblclientlist failed");

        $client_id = $stmt_client->insert_id;

        $stmt_sub = $conn->prepare("INSERT INTO tblpromo_subscribers (client_id, promo_id, fname, mname, lname, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_sub->bind_param("iissss", $client_id, $promo_id, $enc_fname, $enc_mname, $enc_lname, $encrypted_email);
        if (!$stmt_sub->execute()) throw new Exception("Insert to tblpromo_subscribers failed");

        $conn->commit();
        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Client successfully added.'];
        header("Location: client_management.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error: " . $e->getMessage());
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Database error: ' . $e->getMessage()];
        header("Location: client_management.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Management</title>
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/popup.css">
    <link rel="stylesheet" href="css/client_management.css">
    <script src="javascript/popup.js"></script>
    <script src="javascript/location.js"></script>
</head>
<body>

<?php if (isset($_SESSION['popup'])): ?>
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

<div class="client-container">
    <table>
        <thead>
            <tr>
                <th>Client ID</th>
                <th>Router Number</th>
                <th>Full Name</th>
                <th>Address</th>
                <th>Mobile Number</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($client_list as $client): ?>
                <tr>
                    <td><?= htmlspecialchars($client['client_id']) ?></td>
                    <td><?= htmlspecialchars(decryptData($client['routernumber'] ?? 'Not Assigned')) ?></td>
                    <td><?= htmlspecialchars(decryptData($client['fname']) . ' ' . decryptData($client['mname']) . ' ' . decryptData($client['lname'])) ?></td>
                    <td><?= htmlspecialchars(decryptData($client['address'])) ?></td>
                    <td><?= htmlspecialchars(decryptData($client['mobile'])) ?></td>
                    <td><?= htmlspecialchars($client['status']) ?></td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($client['status'] === 'Installed'): ?>
                                <?php if (empty($client['routernumber'])): ?>
                                    <form action="assign_router.php" method="GET">
                                        <input type="hidden" name="id" value="<?= $client['client_id'] ?>">
                                        <button type="submit" class="assign-btn">Assign Router</button>
                                    </form>
                                <?php endif; ?>
                                <form action="includes/drop_wifi.php" method="POST">
                                    <input type="hidden" name="client_id" value="<?= $client['client_id'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to drop this client?');">Drop WIFI</button>
                                </form>
                            <?php else: ?>
                                <span>Pending Installation</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="add-client-container" style="display: flex; gap: 10px; align-items: center;">

    <a href="archived_clients.php" class="add-client-btn" title="Archived Clients" style="background-color: #444;">
        <i class='bx bx-archive-in'></i>
        <span class="tooltip">View Archived Clients</span>
    </a>

    <button class="add-client-btn" onclick="document.getElementById('addClientModal').style.display='flex'">
        <i class='bx bx-plus'></i>
        <span class="tooltip">Add Walk-in Client</span>
    </button>
</div>

<div id="addClientModal" class="popup-modal">
    <div class="popup-content">
        <span class="popup-close" onclick="document.getElementById('addClientModal').style.display='none'">&times;</span>
        <h2>Application Form</h2>
        <form method="POST" action="client_management.php">
            <label>First Name *:</label>
            <input type="text" name="fname" placeholder="First Name" required>

            <label>Middle Name :</label>
            <input type="text" name="mname" placeholder="Middle Name">

            <label>Last Name *:</label>
            <input type="text" name="lname" placeholder="Last Name" required>

            <label>Complete Address *:</label>
            <select name="city" required>
                <option value="">Select City</option>
                <option value="Biñan City">Biñan City</option>
                <option value="Cabuyao City">Cabuyao City</option>
                <option value="Calamba City">Calamba City</option>
                <option value="San Pablo City">San Pablo City</option>
                <option value="San Pedro City">San Pedro City</option>
                <option value="Santa Rosa City">Santa Rosa City</option>
            </select>

            <select name="barangay" id="barangay" required>
                <option value="">Select Barangay</option>
            </select>

            <input type="text" name="street" placeholder="Street Address" required>

            
            <label>Promo *:</label>
<select name="promo_id" required>
    <option value="">Select Promo</option>
    <?php foreach ($promos as $promo): ?>
        <option value="<?= $promo['promo_id'] ?>"><?= htmlspecialchars($promo['promo_name']) ?></option>
    <?php endforeach; ?>
</select>

            <label>Email *:</label>
            <input type="email" name="email" placeholder="Email Address" required>

            <label>Mobile Number *:</label>
            <input type="tel" name="mobile" placeholder="Mobile Number" required maxlength="11" pattern="\d{11}" title="Please enter a valid 11-digit mobile number">

            <button type="submit">Submit</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('applicationFormModal');
    const summaryModal = document.getElementById('summaryModal');
    const summaryContent = document.getElementById('summaryContent');
    const loading = document.getElementById('loadingTerminal');

    document.getElementById('submitButtonModal').addEventListener('click', () => {
        const formData = new FormData(form);
        const required = ['fname', 'lname', 'street', 'barangay', 'city', 'residenttype', 'email', 'mobile', 'promo_id'];
        if (!required.every(f => formData.get(f)?.trim())) {
            alert("Please complete all required fields.");
            return;
        }

        const billing = form.querySelector('input[name="billing_proof"]').files[0];
        const validID = form.querySelector('input[name="valid_id"]').files[0];
        if (!billing || !validID) {
            alert("Please upload required documents.");
            return;
        }

        const address = `${formData.get('street')}, ${formData.get('barangay')}, ${formData.get('city')}`;
        const promoText = form.querySelector("select[name='promo_id']").selectedOptions[0].text;

        summaryContent.innerHTML = `
            <p><strong>Name:</strong> ${formData.get('fname')} ${formData.get('mname')} ${formData.get('lname')}</p>
            <p><strong>Address:</strong> ${address}</p>
            <p><strong>Resident Type:</strong> ${formData.get('residenttype')}</p>
            <p><strong>Email:</strong> ${formData.get('email')}</p>
            <p><strong>Mobile:</strong> ${formData.get('mobile')}</p>
            <p><strong>Promo:</strong> ${promoText}</p>
            <div class="image-summary">
                <div><strong>Billing Proof:</strong><br><img src="${URL.createObjectURL(billing)}" /></div>
                <div><strong>Valid ID:</strong><br><img src="${URL.createObjectURL(validID)}" /></div>
            </div>
        `;

        summaryModal.style.display = 'block';
    });

    document.querySelector('.back-button').addEventListener('click', () => {
        summaryModal.style.display = 'none';
    });

    document.querySelector('.confirm-button').addEventListener('click', () => {
        summaryModal.style.display = 'none';
        loading.style.display = 'block';
        setTimeout(() => {
            form.submit();
        }, 1000);
    });
});
</script>

</body>
</html>
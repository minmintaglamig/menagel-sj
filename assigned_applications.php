<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');

if (!isset($_SESSION['user_email'])) {
    echo "Unauthorized access!";
    exit();
}

$email = $_SESSION['user_email'];

$staff_query = "SELECT id, fname, lname FROM tblstafflist WHERE email = ?";
$stmt = $conn->prepare($staff_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$staff_result = $stmt->get_result();
$staff = $staff_result->fetch_assoc();

if (!$staff) {
    echo "Staff not found!";
    exit();
}

$applications_query = "
    SELECT 
        a.id AS application_id,
        a.fname,
        a.mname,
        a.lname,
        a.address,
        a.residenttype,
        a.mobile,
        a.billing_proof,
        a.valid_id,
        a.created_at,
        a.email,
        a.promo_id,
        a.promo_name,
        a.status AS application_status,
        IFNULL(ap.approval_status, 'Pending') AS approval_status,
        IFNULL(ap.approved_at, 'N/A') AS approved_at,
        ap.client_id,
        cl.fname AS client_fname,
        cl.mname AS client_mname,
        cl.lname AS client_lname,
        cl.mobile AS client_mobile,
        cl.address AS client_address
    FROM tblapplication a
    INNER JOIN tblapprove ap ON a.id = ap.application_id
    LEFT JOIN tblclientlist cl ON ap.client_id = cl.client_id
    WHERE ap.staff_id = ? 
    ORDER BY a.id DESC";

$stmt = $conn->prepare($applications_query);
$stmt->bind_param("s", $staff['id']);
$stmt->execute();
$applications_result = $stmt->get_result();
$applications = [];

while ($row = $applications_result->fetch_assoc()) {
    $row['fname'] = decryptData($row['fname']);
    $row['mname'] = decryptData($row['mname']);
    $row['lname'] = decryptData($row['lname']);
    $row['address'] = decryptData($row['address']);
    $row['mobile'] = decryptData($row['mobile']);
    
    $row['client_fname'] = decryptData($row['client_fname']);
    $row['client_mname'] = decryptData($row['client_mname']);
    $row['client_lname'] = decryptData($row['client_lname']);
    $row['client_mobile'] = decryptData($row['client_mobile']);
    $row['client_address'] = decryptData($row['client_address']);
    
    $applications[] = $row;
}

$installed_clients = [];
$installed_result = $conn->query("SELECT client_id FROM tblclientlist WHERE status = 'Installed'");
if ($installed_result) {
    while ($row = $installed_result->fetch_assoc()) {
        $installed_clients[] = $row['client_id'];
    }
} else {
    error_log("Failed to fetch installed clients: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_installed'])) {
    if (!empty($_POST['application_id'])) {
        $application_id = intval($_POST['application_id']);

        $client_query = "SELECT client_id FROM tblapprove WHERE application_id = ?";
        $stmt_client = $conn->prepare($client_query);
        if ($stmt_client === false) {
            error_log("Prepare failed: " . $conn->error);
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Internal error preparing statement.'];
            header("Location: assigned_applications.php");
            exit();
        }
        $stmt_client->bind_param("i", $application_id);
        $stmt_client->execute();
        $client_result = $stmt_client->get_result();
        
        if ($client_result && $client_result->num_rows > 0) {
            $client_data = $client_result->fetch_assoc();
            $client_id = $client_data['client_id'];
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Client not found for this application.'];
            header("Location: assigned_applications.php");
            exit();
        }

        if (in_array($client_id, $installed_clients)) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Client is already marked as Installed.'];
            header("Location: assigned_applications.php");
            exit();
        }

        if (isset($_FILES['proof_photo']) && $_FILES['proof_photo']['error'] === UPLOAD_ERR_OK) {
            $photo_name = $_FILES['proof_photo']['name'];
            $photo_tmp = $_FILES['proof_photo']['tmp_name'];
            $photo_ext = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($photo_ext, $allowed)) {
                $unique_name = uniqid('install_') . '.' . $photo_ext;
                $upload_dir = __DIR__ . '/uploads/installationproof/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $upload_path = $upload_dir . $unique_name;

                if (move_uploaded_file($photo_tmp, $upload_path)) {
                    $insert_query = "
                        INSERT INTO tblinstallations (client_id, status, install_date, proof_photo)
                        VALUES (?, 'Installed', NOW(), ?)";
                    $stmt_insert = $conn->prepare($insert_query);
                    if ($stmt_insert === false) {
                        error_log("Prepare failed: " . $conn->error);
                        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Internal error preparing insert statement.'];
                        header("Location: assigned_applications.php");
                        exit();
                    }
                    $stmt_insert->bind_param("is", $client_id, $unique_name);
                    if ($stmt_insert->execute()) {
                        $stmt_cli = $conn->prepare("UPDATE tblclientlist SET status = 'Installed' WHERE client_id = ?");
                        if ($stmt_cli === false) {
                            error_log("Prepare failed: " . $conn->error);
                            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Internal error preparing update statement.'];
                            header("Location: assigned_applications.php");
                            exit();
                        }
                        $stmt_cli->bind_param("i", $client_id);
                        if ($stmt_cli->execute()) {
                            $delete_approve_stmt = $conn->prepare("DELETE FROM tblapprove WHERE application_id = ?");
                            if ($delete_approve_stmt === false) {
                                error_log("Prepare failed: " . $conn->error);
                                $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Internal error preparing delete statement.'];
                                header("Location: assigned_applications.php");
                                exit();
                            }
                            $delete_approve_stmt->bind_param("i", $application_id);
                            $delete_approve_stmt->execute();

                            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Marked as Installed successfully!'];
                        } else {
                            error_log("Update failed: " . $stmt_cli->error);
                            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to update client status: ' . $stmt_cli->error];
                        }
                    } else {
                        error_log("Insert failed: " . $stmt_insert->error);
                        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Insert failed: ' . $stmt_insert->error];
                    }
                } else {
                    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to move proof photo.'];
                }
            } else {
                $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid file type.'];
            }
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Proof photo missing or upload error.'];
        }

        header("Location: assigned_applications.php");
        exit();
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Application ID is required.'];
        header("Location: assigned_applications.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assigned Applications</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="javascript/popup.js"></script>
    <style>
@media (max-width: 1024px) {
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .table-responsive table {
    min-width: 800px;
    border-collapse: collapse;
  }
}

</style>
</head>
<body>

<?php if (isset($_SESSION['popup'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    showPopup('<?= htmlspecialchars($_SESSION['popup']['type'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($_SESSION['popup']['msg'], ENT_QUOTES, 'UTF-8') ?>');
  });
</script>
<?php unset($_SESSION['popup']); endif; ?>

<div id="popup-container" class="popup-container"></div>

<?php include('header.php'); ?>
<div class="main-container">
    <?php include('staff_sidebar.html'); ?>
</div>

<div class="container">
    <h2>Assigned Applications</h2>
    <div class="table-responsive">
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Client Name</th>
            <th>Mobile</th>
            <th>Address</th>
            <th>Approval</th>
            <th>Date</th>
            <th>Proof Photo</th>
            <th>Action</th>
        </tr>
        <?php foreach ($applications as $app): ?>
            <tr>
                <td><?= htmlspecialchars($app['application_id']) ?></td>
                <td><?= htmlspecialchars($app['fname'] . ' ' . $app['mname'] . ' ' . $app['lname']) ?></td>
                <td><?= htmlspecialchars($app['mobile']) ?></td>
                <td><?= htmlspecialchars($app['address']) ?></td>
                <td><?= htmlspecialchars($app['approval_status']) ?></td>
                <td><?= htmlspecialchars($app['approved_at']) ?></td>
                <td>
                    <?php
                    $proof_q = $conn->prepare("SELECT proof_photo FROM tblinstallations WHERE client_id = ? AND status = 'Installed' ORDER BY install_date DESC LIMIT 1");
                    $proof_q->bind_param("i", $app['client_id']);
                    $proof_q->execute();
                    $proof_result = $proof_q->get_result();
                    if ($proof_result && $proof_result->num_rows > 0) {
                        $proof = $proof_result->fetch_assoc();
                        echo "<img src='uploads/installationproof/" . htmlspecialchars($proof['proof_photo'], ENT_QUOTES, 'UTF-8') . "' style='max-width:100px;' alt='Proof Photo'>";
                    } else {
                        echo "No Proof Photo";
                    }
                    ?>
                </td>
                <td>
                    <?php if (!in_array($app['client_id'], $installed_clients) && $app['approval_status'] === 'Approved'): ?>
                        <form method="POST" enctype="multipart/form-data" onsubmit="return confirmInstall();">
                            <input type="hidden" name="application_id" value="<?= htmlspecialchars($app['application_id']) ?>">
                            <input type="file" name="proof_photo" onchange="previewImage(this, 'preview-<?= $app['application_id'] ?>')" required>
                            <img id="preview-<?= $app['application_id'] ?>" style="max-height:60px; display:none; margin:5px;" alt="Proof Preview">
                            <button type="submit" name="mark_installed">Mark as Installed</button>
                        </form>
                    <?php elseif ($app['approval_status'] !== 'Approved'): ?>
                        <span style="color: gray;">Waiting for Approval</span>
                    <?php else: ?>
                        <span>Installed</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>

    <h2>Installed Applications</h2>
    <div class="table-responsive">
    <table border="1">
        <tr>
            <th>Client ID</th>
            <th>Name</th>
            <th>Mobile</th>
            <th>Address</th>
            <th>Date</th>
            <th>Proof</th>
        </tr>
        <?php
        $installed_result = $conn->query("
            SELECT c.client_id, c.fname, c.mname, c.lname, c.address, c.mobile, i.install_date, i.proof_photo
            FROM tblclientlist c
            JOIN tblinstallations i ON c.client_id = i.client_id
            WHERE c.status = 'Installed'
            ORDER BY i.install_date DESC");
        while ($row = $installed_result->fetch_assoc()): 
        ?>
            <tr>
                <td><?= htmlspecialchars($row['client_id']) ?></td>
                <td><?= htmlspecialchars(decryptData($row['fname']) . ' ' . decryptData($row['mname']) . ' ' . decryptData($row['lname'])) ?></td>
                <td><?= htmlspecialchars(decryptData($row['mobile'])) ?></td>
                <td><?= htmlspecialchars(decryptData($row['address'])) ?></td>
                <td><?= htmlspecialchars($row['install_date']) ?></td>
                <td>
                    <?php
                    $path = __DIR__ . '/uploads/installationproof/' . $row['proof_photo'];
                    if (file_exists($path)) {
                        echo "<img src='uploads/installationproof/" . htmlspecialchars($row['proof_photo'], ENT_QUOTES, 'UTF-8') . "' style='max-width:100px;' alt='Proof Photo'>";
                    } else {
                        echo "Missing Photo";
                    }
                    ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
                </div>
</div>
<script src="javascript/sidebar-toggle.js"></script>
</body>
</html>

<?php $conn->close(); ?>
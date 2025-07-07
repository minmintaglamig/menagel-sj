<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');
include('includes/daily_status_update.php');

    if (!isset($_SESSION['user_email']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_add'])) {
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $specialization = $_POST['specialization'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Passwords do not match.'];
        header("Location: staff_management.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $encrypted_fname = encryptData($fname);
    $encrypted_mname = encryptData($mname);
    $encrypted_lname = encryptData($lname);
    $encrypted_address = encryptData($address);
    $encrypted_email = encryptData($email);

    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("INSERT INTO tblstafflist (fname, mname, lname, mobile, address, specialization, email, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')");
        $stmt1->bind_param("sssssss", $encrypted_fname, $encrypted_mname, $encrypted_lname, $mobile, $encrypted_address, $specialization, $encrypted_email);
        $stmt1->execute();
        $staff_id = $conn->insert_id;
        $stmt1->close();

        if (!empty($_POST['day_of_week'])) {
            foreach ($_POST['day_of_week'] as $i => $day) {
                $day = $_POST['day_of_week'][$i];
                $time_from = $_POST['time_from'][$i];
                $time_to = $_POST['time_to'][$i];
                if ($day && $time_from && $time_to) {
                    $stmt2 = $conn->prepare("INSERT INTO tblstaff_schedule (staff_id, day_of_week, time_from, time_to) VALUES (?, ?, ?, ?)");
                    $stmt2->bind_param("isss", $staff_id, $day, $time_from, $time_to);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }
        }

        $stmt3 = $conn->prepare("INSERT INTO tblusers (email, password, role, is_verified, is_active, is_disabled_by_admin, created_at) VALUES (?, ?, 'Staff', 1, 1, 0, NOW())");
        $stmt3->bind_param("ss", $encrypted_email, $hashed_password);
        $stmt3->execute();
        $stmt3->close();

        $conn->commit();
        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Staff added successfully.'];
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to add staff. ' . $e->getMessage()];
    }

    header("Location: staff_management.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("SELECT email FROM tblstafflist WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("DELETE FROM tblstaff_schedule WHERE staff_id = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $conn->prepare("DELETE FROM tblstafflist WHERE id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();

        $stmt3 = $conn->prepare("DELETE FROM tblusers WHERE email = ?");
        $stmt3->bind_param("s", $email);
        $stmt3->execute();
        $stmt3->close();

        $conn->commit();
        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Staff deleted successfully.'];
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to delete staff. ' . $e->getMessage()];
    }

    header("Location: staff_management.php");
    exit();
}

$staff_list = [];
$result = $conn->query("SELECT s.*, u.is_active, u.is_disabled_by_admin FROM tblstafflist s LEFT JOIN tblusers u ON s.email = u.email ORDER BY s.id DESC");
if ($result) {
    $staff_list = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/toggle.css">
    <link rel="stylesheet" href="css/popup.css">
    <link rel="stylesheet" href="css/management.css">
    <title>Staff Management</title>
</head>
<body>

<?php
    if (!isset($_SESSION['user_email']) || $_SESSION['role'] !== 'Admin') {
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
        <?php include('admin_sidebar.html'); ?>
    </div>
</div>

<div class="container">
    <div class="content">
        <h2>Manage Staff</h2>
        <?php if (!empty($staff_list)): ?>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Address</th>
                    <th>Email</th>
                    <th>Schedule</th>
                    <th>Actions</th>
                    <th>Disable Account</th>
                </tr>
            </thead>
            <tbody>

<?php foreach ($staff_list as $staff): ?>
<tr>
<td>
    <span class="status-dot <?= $staff['is_active'] == 1 ? 'green' : 'red' ?>"></span>
    <span class="status-dot <?= $staff['is_disabled_by_admin'] ? 'red' : 'green' ?>"></span>
</td>
    <td><?= htmlspecialchars(decryptData($staff['fname'])) ?></td>
    <td><?= htmlspecialchars(decryptData($staff['mname'])) ?></td>
    <td><?= htmlspecialchars(decryptData($staff['lname'])) ?></td>
    <td><?= htmlspecialchars(decryptData($staff['address'])) ?></td>
    <td><?= htmlspecialchars(decryptData($staff['email'])) ?></td>
    <td>
    <?php
    $stmt = $conn->prepare("SELECT day_of_week, time_from, time_to FROM tblstaff_schedule WHERE staff_id = ?");
    $stmt->bind_param("i", $staff['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $from = date("g:i A", strtotime($row['time_from']));
        $to = date("g:i A", strtotime($row['time_to']));
        echo "{$row['day_of_week']}: {$from} - {$to}<br>";
    }
    $stmt->close();
    ?>
</td>
    <td>
        <a href="edit_staff.php?id=<?= $staff['id'] ?>">Edit</a> | 
        <a href="?delete=<?= $staff['id'] ?>" class="delete-btn">Delete</a>
    </td>
<td>
    <form method="POST" action="includes/toggle_admin_disable.php">
    <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">
    <label class="switch">
    <input type="checkbox" name="disabled" onchange="this.form.submit()" <?= $staff['is_disabled_by_admin'] ? 'checked' : '' ?>>
    <span class="slider"></span>
</label>
</form>
</td>

</tr>
<?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-staff">
            <i class="bx bx-user-x"></i> 
            <p>No staff members found.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<button class="add-btn" id="openModal">
    <i class='bx bx-plus'></i> 
    <span class="tooltip">Add Staff</span>
</button>

<div id="addStaffModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <div class="modal-header">
            <h2>Add New Staff</h2>
        </div>
        <form action="staff_management.php" method="POST">
            <label>First Name:</label>
            <input type="text" name="fname" required>

            <label>Middle Name:</label>
            <input type="text" name="mname">

            <label>Last Name:</label>
            <input type="text" name="lname" required>

            <label>Mobile:</label>
            <input type="tel" name="mobile" required pattern="\d{11}" maxlength="11" title="Enter exactly 11 digits" oninput="validateMobile()">

            <label>Address:</label>
            <input type="text" name="address" required>

            <label>Specialization:</label>
            <select name="specialization" required>
                <option value="Technical">Technical</option>
                <option value="Upgrade/Downgrade Internet">Upgrade/Downgrade Internet</option>
                <option value="Disconnection">Disconnection</option>
                <option value="Other">Other</option>
            </select>

            <fieldset>
    <legend>Work Schedule</legend>
    <div id="schedule-container">
        <div class="schedule-row">
            <select name="day_of_week[]" required>
                <option value="">Select Day</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
            </select>

            <label>From: <input type="time" name="time_from[]" required></label>
            <label>To: <input type="time" name="time_to[]" required></label>
            <button type="button" onclick="removeScheduleRow(this)">Remove</button>
        </div>
    </div>
    <button type="button" onclick="addScheduleRow()">+ Add Another Day</button>
</fieldset>

            <label>Email:</label>
            <input type="email" name="email" required>

            <div class="form__group">
                <label>Password:</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" required style="padding-right: 30px;">
                    <i class="bx bx-show toggle-password" onclick="togglePassword('password')" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;"></i>
                </div>

                <div class="instruction" id="password-instruction">Password must be at least 8 characters long and contain a number and a special character.</div>

                <label>Confirm Password:</label>
                <div style="position: relative;">
                    <input type="password" name="confirm_password" id="confirm_password" required style="padding-right: 30px;">
                    <i class="bx bx-show toggle-password" onclick="togglePassword('confirm_password')" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;"></i>
                </div>
            </div>

            <button type="submit" name="staff_add">Save Staff</button>
        </form>
    </div>
</div>

<script>
    function togglePassword(id) {
        const passwordField = document.getElementById(id);
        const type = passwordField.type === "password" ? "text" : "password";
        passwordField.type = type;
    }

    document.getElementById("openModal").addEventListener("click", function() {
        document.getElementById("addStaffModal").style.display = "block";
    });

    document.getElementById("closeModal").addEventListener("click", function() {
        document.getElementById("addStaffModal").style.display = "none";
    });

    function addScheduleRow() {
        const scheduleContainer = document.getElementById("schedule-container");
        const newRow = document.createElement("div");
        newRow.classList.add("schedule-row");
        newRow.innerHTML = `
            <select name="day_of_week[]" required>
                <option value="">Select Day</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
            </select>
            <label>From: <input type="time" name="time_from[]" required></label>
            <label>To: <input type="time" name="time_to[]" required></label>
            <button type="button" onclick="removeScheduleRow(this)">Remove</button>
        `;
        scheduleContainer.appendChild(newRow);
    }

    function removeScheduleRow(button) {
        button.closest(".schedule-row").remove();
    }
    
    document.getElementById("password").addEventListener("input", function() {
        const password = this.value;
        const instruction = document.getElementById("password-instruction");
        if (password.length >= 8 && /[0-9]/.test(password) && /[!@#$%^&*(),.?":{}|<>]/.test(password)) {
            instruction.textContent = "Password is strong.";
            instruction.classList.add("valid");
        } else {
            instruction.textContent = "Password must be at least 8 characters long and contain a number and a special character.";
            instruction.classList.remove("valid");
        }
    });

    function validateMobile() {
        const mobileInput = document.querySelector('input[name="mobile"]');
        const regex = /^\d{11}$/;
        if (!regex.test(mobileInput.value)) {
            mobileInput.setCustomValidity("Please enter exactly 11 digits.");
        } else {
            mobileInput.setCustomValidity("");
        }
    }
</script>

</body>
</html>
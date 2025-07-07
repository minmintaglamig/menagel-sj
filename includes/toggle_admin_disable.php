<?php
    include_once('../includes/dbh.php');

    $staff_id = intval($_POST['staff_id'] ?? 0);

    $stmt = $conn->prepare("SELECT email FROM tblstafflist WHERE id = ?");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    $checked = isset($_POST['disabled']) ? 1 : 0;
    $is_active = $checked ? 0 : 1;

    $stmt = $conn->prepare("UPDATE tblusers SET is_disabled_by_admin = ?, is_active = ? WHERE email = ?");
    $stmt->bind_param("iis", $checked, $is_active, $email);
    $stmt->execute();
    $stmt->close();

    if ($checked) {
        $stmt = $conn->prepare("UPDATE tblstafflist SET status = 'Inactive' WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        $_SESSION['popup'] = [
            'type' => 'success',
            'msg' => $disabled ? 'Staff account disabled.' : 'Staff account enabled.'
        ];

        header("Location: ../staff_management.php");
        exit();
    }
?>
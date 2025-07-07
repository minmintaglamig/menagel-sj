<?php
    include_once('../includes/dbh.php');

    if (isset($_POST['toggle_visibility'])) {
        $announcement_id = $_POST['announcement_id'];

        $sql = "SELECT is_visible FROM tblannouncements WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        if ($row) {
            $new_status = $row['is_visible'] ? 0 : 1;

            $update_sql = "UPDATE tblannouncements SET is_visible = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ii", $new_status, $announcement_id);
            $stmt->execute();
        }
    }

    header("Location: ../admin_announcement.php");
    exit();
?>
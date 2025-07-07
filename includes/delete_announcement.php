<?php
    session_start();
    include_once('../includes/dbh.php');
    
    if (!isset($_GET['id'])) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid request.'];
        header("Location: ../admin_announcement.php");
        exit();
    }
    
    $id = $_GET['id'];
    
    $sql = "SELECT announcement_image FROM tblannouncements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['announcement_image'])) {
            $file_path = "../uploads/" . $row['announcement_image'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    
        $stmt->close();
    
        $delete_sql = "DELETE FROM tblannouncements WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id);
    
        if ($delete_stmt->execute()) {
            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Announcement deleted successfully.'];
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to delete announcement.'];
        }
    
        $delete_stmt->close();
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Announcement not found.'];
    }
    
    $conn->close();
    header("Location: ../admin_announcement.php");
    exit();
?>
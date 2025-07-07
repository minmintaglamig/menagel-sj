<?php
    session_start();
    include_once 'dbh.php';
    
    if (!isset($_SESSION['user_email'])) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
        header("Location: ../admin_advertisement.php");
        exit();
    }
    
    function redirectWithPopup($type, $msg) {
        $_SESSION['popup'] = ['type' => $type, 'msg' => $msg];
        header("Location: ../admin_advertisement.php");
        exit();
    }
    
    $upload_dir = "../uploads/advertisement/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (isset($_POST['add_ad'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $image = $_FILES['image'];
    
        if (!is_uploaded_file($image['tmp_name'])) {
            redirectWithPopup('error', 'No image uploaded.');
        }
    
        if (!is_writable($upload_dir)) {
            redirectWithPopup('error', 'Upload directory is not writable.');
        }
    
        $image_name = time() . "_" . basename($image['name']);
        $target_path = $upload_dir . $image_name;
    
        if (!move_uploaded_file($image['tmp_name'], $target_path)) {
            redirectWithPopup('error', 'Failed to upload image.');
        }
    
        $stmt = $conn->prepare("INSERT INTO tbladvertisement (title, content, image) VALUES (?, ?, ?)");
        if (!$stmt) {
            redirectWithPopup('error', 'DB prepare error: ' . $conn->error);
        }
    
        $stmt->bind_param("sss", $title, $content, $image_name);
        if ($stmt->execute()) {
            redirectWithPopup('success', 'Advertisement added successfully!');
        } else {
            redirectWithPopup('error', 'DB error: ' . $stmt->error);
        }
    }
    
    if (isset($_POST['toggle_visibility'])) {
        $ad_id = intval($_POST['ad_id']);
    
        $stmt = $conn->prepare("UPDATE tbladvertisement SET is_visible = CASE WHEN is_visible = 1 THEN 0 ELSE 1 END WHERE id = ?");
        if (!$stmt) {
            redirectWithPopup('error', 'Error preparing statement: ' . $conn->error);
        }
    
        $stmt->bind_param("i", $ad_id);
        if ($stmt->execute()) {
            redirectWithPopup('success', 'Advertisement visibility toggled.');
        } else {
            redirectWithPopup('error', 'Failed to update visibility: ' . $stmt->error);
        }
    }
    
    if (isset($_POST['delete_ad'])) {
        if (!isset($_POST['ad_id']) || empty($_POST['ad_id'])) {
            redirectWithPopup('error', 'No advertisement ID provided.');
        }
    
        $ad_id = intval($_POST['ad_id']);
    
        $stmt = $conn->prepare("SELECT image FROM tbladvertisement WHERE id = ?");
        if (!$stmt) {
            redirectWithPopup('error', 'Error preparing statement: ' . $conn->error);
        }
    
        $stmt->bind_param("i", $ad_id);
        $stmt->execute();
        $stmt->bind_result($image_name);
        $stmt->fetch();
        $stmt->close();
    
        $image_path = realpath("../uploads/advertisement/" . $image_name);
        if ($image_path && file_exists($image_path)) {
            if (!unlink($image_path)) {
                redirectWithPopup('error', 'Failed to delete image file.');
            }
        }
    
        $stmt = $conn->prepare("DELETE FROM tbladvertisement WHERE id = ?");
        if (!$stmt) {
            redirectWithPopup('error', 'Error preparing delete statement: ' . $conn->error);
        }
    
        $stmt->bind_param("i", $ad_id);
        if ($stmt->execute()) {
            redirectWithPopup('success', 'Advertisement deleted successfully!');
        } else {
            redirectWithPopup('error', 'Failed to delete advertisement: ' . $stmt->error);
        }
    }
?>
<?php
    session_start();
    include_once('../includes/dbh.php');
    
    function redirectWithPopup($type, $msg) {
        $_SESSION['popup'] = ['type' => $type, 'msg' => $msg];
        header("Location: ../admin_announcement.php");
        exit();
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $announcement_text = $conn->real_escape_string($_POST['announcement_text']);
        $announcement_image = "";
    
        if (!empty($_FILES['announcement_image']['name'])) {
            $target_dir = "../uploads/";
            $image_name = basename($_FILES["announcement_image"]["name"]);
            $timestamped_name = time() . "_" . $image_name;
            $target_file = $target_dir . $timestamped_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
            $allowed_types = ["jpg", "jpeg", "png", "gif"];
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["announcement_image"]["tmp_name"], $target_file)) {
                    $announcement_image = $timestamped_name;
                } else {
                    redirectWithPopup("error", "Failed to upload image.");
                }
            } else {
                redirectWithPopup("error", "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.");
            }
        }
    
        $sql = "INSERT INTO tblannouncements (announcement_text, announcement_image, created_at) 
                VALUES (?, ?, NOW())";
    
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            redirectWithPopup("error", "Database error: " . $conn->error);
        }
    
        $stmt->bind_param("ss", $announcement_text, $announcement_image);
    
        if ($stmt->execute()) {
            redirectWithPopup("success", "Announcement added successfully!");
        } else {
            redirectWithPopup("error", "Error adding announcement: " . $stmt->error);
        }
    
        $stmt->close();
        $conn->close();
    }
?>
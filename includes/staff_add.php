<?php
    session_start();
    include('../includes/dbh.php');
    include('../includes/encryption.php');
    
    function redirectWithPopup($type, $msg) {
        $_SESSION['popup'] = ['type' => $type, 'msg' => $msg];
        header("Location: ../staff_management.php");
        exit();
    }
    
    if (!isset($_SESSION['user_email'])) {
        redirectWithPopup("error", "Unauthorized access!");
    }
    
    $email = $_SESSION['user_email'];
    
    $query = "SELECT * FROM tblusers WHERE email = ? AND role = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        redirectWithPopup("error", "Unauthorized access!");
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_add'])) {
        $fname = encryptData(trim($_POST['fname']));
        $mname = encryptData(trim($_POST['mname']));
        $lname = encryptData(trim($_POST['lname']));
        $mobile = encryptData(trim($_POST['mobile']));
        $address = encryptData(trim($_POST['address']));
        $specialization = encryptData(trim($_POST['specialization']));
        $new_email = trim($_POST['email']);
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    
        $check_email = "SELECT * FROM tblusers WHERE email = ?";
        $stmt = $conn->prepare($check_email);
        $stmt->bind_param("s", $new_email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            redirectWithPopup("error", "Email already exists!");
        }
    
        $query = "INSERT INTO tblusers (email, password, role) VALUES (?, ?, 'staff')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $new_email, $password);
    
        if (!$stmt->execute()) {
            redirectWithPopup("error", "Error adding user!");
        }
    
        $query = "INSERT INTO tblstafflist (fname, mname, lname, mobile, address, specialization, email) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", $fname, $mname, $lname, $mobile, $address, $specialization, $new_email);
    
        if ($stmt->execute()) {
            redirectWithPopup("success", "Staff added successfully!");
        } else {
            redirectWithPopup("error", "Error adding staff details!");
        }
    }
?>
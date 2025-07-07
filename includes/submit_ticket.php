<?php
    session_start();
    include_once 'dbh.php';
    include_once 'encryption.php';
    
    if (!isset($_SESSION['user_email'])) {
        $_SESSION['message'] = "Unauthorized access!";
        header("Location: ../login.php");
        exit();
    }
    
    $email = $_SESSION['user_email'];
    
    $query = "SELECT fname, mname, lname, mobile, address, routernumber FROM tblclientlist WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_assoc();
    
    if (!$client) {
        $_SESSION['message'] = "Client not found!";
        header("Location: ../client_tickets.php");
        exit();
    }
    
    if (empty($client['routernumber'])) {
        $_SESSION['message'] = "You don’t have a router number yet. Please wait for the admin to assign one.";
        header("Location: ../client_tickets.php");
        exit();
    }
    
    $client['fname'] = decryptData($client['fname']);
    $client['mname'] = !empty($client['mname']) ? decryptData($client['mname']) : "";
    $client['lname'] = decryptData($client['lname']);
    $client['mobile'] = decryptData($client['mobile']);
    $client['address'] = decryptData($client['address']);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST['concern_type']) || empty($_POST['concern_type'])) {
            $_SESSION['message'] = "Error: Concern type is missing!";
            header("Location: ../client_tickets.php");
            exit();
        }
    
        $concern_type = trim($_POST['concern_type']);
        $concern = trim($_POST['concern']);
    
        $fname = encryptData($client['fname']);
        $mname = encryptData($client['mname']);
        $lname = encryptData($client['lname']);
        $mobile = encryptData($client['mobile']);
        $address = encryptData($client['address']);
        $concern = encryptData($concern);
    
        $sql = "INSERT INTO tbltickets (routernumber, fname, mname, lname, mobile, address, concern_type, concern, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $client['routernumber'], $fname, $mname, $lname, $mobile, $address, $concern_type, $concern);
    

        if ($stmt->execute()) {
            $admins = $conn->query("SELECT id FROM tblusers WHERE role = 'Admin'");
            while ($staff = $staffs->fetch_assoc()) {
                addNotification($conn, 'Admin', $admin['id'], '🎫 New ticket submitted!', 'admin_tickets.php');
            }            
        
            redirectWithPopup("success", "Ticket submitted successfully!");
        } else {
            redirectWithPopup("error", "Error submitting ticket: " . $stmt->error);
        }
    
        header("Location: ../client_tickets.php");
        exit();
    }
    
    $conn->close();
?>
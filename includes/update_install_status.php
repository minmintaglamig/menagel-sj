<?php
    session_start();
    include_once 'dbh.php';
    include_once 'encryption.php';
    require_once 'notification_helper.php';
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['client_id'])) {
        $client_id = $_POST['client_id'];
        $current_date = date("Y-m-d H:i:s");
    
        $get_client_name = "SELECT CONCAT(fname, ' ', COALESCE(mname, ''), ' ', lname) AS full_name FROM tblclientlist WHERE client_id = ?";
        $stmt3 = $conn->prepare($get_client_name);
        if (!$stmt3) {
            echo json_encode(["success" => false, "error" => "Query Error: " . $conn->error]);
            exit();
        }
        $stmt3->bind_param("i", $client_id);
        $stmt3->execute();
        $result = $stmt3->get_result();
        $client = $result->fetch_assoc();
        $client_name = $client['full_name'] ?? '';
    
        $encrypted_client_name = encryptData($client_name);
    
        $check_install = "SELECT installation_id FROM tblinstallations WHERE client_id = ?";
        $stmt_check = $conn->prepare($check_install);
        $stmt_check->bind_param("i", $client_id);
        $stmt_check->execute();
        $stmt_check->store_result();
    
        if ($stmt_check->num_rows > 0) {
            $update_install = "UPDATE tblinstallations SET install_date = ?, status = 'Installed', client_name = ? WHERE client_id = ?";
            $stmt1 = $conn->prepare($update_install);
            if (!$stmt1) {
                echo json_encode(["success" => false, "error" => "Query Error: " . $conn->error]);
                exit();
            }
            $stmt1->bind_param("ssi", $current_date, $encrypted_client_name, $client_id);
            $stmt1->execute();
        } else {
            $insert_install = "INSERT INTO tblinstallations (client_id, status, install_date, client_name) VALUES (?, 'Installed', ?, ?)";
            $stmt1 = $conn->prepare($insert_install);
            if (!$stmt1) {
                echo json_encode(["success" => false, "error" => "Query Error: " . $conn->error]);
                exit();
            }
            $stmt1->bind_param("iss", $client_id, $current_date, $encrypted_client_name);
            $stmt1->execute();
        }
    
        $get_current_status = "SELECT status FROM tblclientlist WHERE client_id = ?";
        $stmt_status = $conn->prepare($get_current_status);
        $stmt_status->bind_param("i", $client_id);
        $stmt_status->execute();
        $result_status = $stmt_status->get_result();
        $current_status = $result_status->fetch_assoc();
    
        if ($current_status && $current_status['status'] === 'Installed') {
            echo json_encode(["success" => false, "error" => "Already installed."]);
            exit();
        }
    
        $update_client = "UPDATE tblclientlist SET status = 'Installed' WHERE client_id = ?";
        $stmt2 = $conn->prepare($update_client);
        if (!$stmt2) {
            echo json_encode(["success" => false, "error" => "Query Error: " . $conn->error]);
            exit();
        }
        $stmt2->bind_param("i", $client_id);
        $stmt2->execute();
    
        $update_application = "UPDATE tblapplication SET status = 'Approved' WHERE client_id = ?";
        $stmt4 = $conn->prepare($update_application);
        if (!$stmt4) {
            echo json_encode(["success" => false, "error" => "Query Error: " . $conn->error]);
            exit();
        }
        addNotification($conn, 'client', $client_id, '✅ Your application has been approved!');
        $stmt4->bind_param("i", $client_id);
        $stmt4->execute();
    
        if ($stmt1->affected_rows > 0 || $stmt2->affected_rows > 0 || $stmt4->affected_rows > 0) {
            echo json_encode(["success" => true, "install_date" => $current_date]);
        } else {
            echo json_encode(["success" => false, "error" => "No rows updated. Data may already be correct."]);
        }
    
        $stmt1->close();
        $stmt2->close();
        $stmt3->close();
        $stmt4->close();
        $stmt_check->close();
        $stmt_status->close();
        $conn->close();
    } else {
        echo json_encode(["success" => false, "error" => "Invalid request."]);
    }
?>
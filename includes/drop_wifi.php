<?php
    include('dbh.php');
    include('encryption.php');
    session_start();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $client_id = $_POST['client_id'];
    
        $checkStmt = $conn->prepare("SELECT 1 FROM tblclient_archive WHERE client_id = ?");
        $checkStmt->bind_param("s", $client_id);
        $checkStmt->execute();
        $checkStmt->store_result();
    
        if ($checkStmt->num_rows > 0) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Client is already archived.'];
            header('Location: ../client_management.php');
            exit();
        }
    
        $query = "
            SELECT 
                c.client_id, c.fname, c.mname, c.lname, c.routernumber, c.address, c.residenttype, 
                c.mobile, c.status, c.email, c.promo_id,
                a.promo_name,
                i.install_date
            FROM tblclientlist c
            LEFT JOIN tblapplication a ON c.client_id = a.id
            LEFT JOIN tblinstallations i ON c.client_id = i.client_id
            WHERE c.client_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result && $result->num_rows > 0) {
            $client = $result->fetch_assoc();
    
            $insertStmt = $conn->prepare("
                INSERT INTO tblclient_archive (
                    client_id, fname, mname, lname, routernumber, address, residenttype, mobile, 
                    promo_name, status, email, promo_id, install_date, drop_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
    
            $promo_name = $client['promo_name'] ?? '';
            $install_date = $client['install_date'] ?? NULL;
    
            $insertStmt->bind_param(
                "sssssssssssis",
                $client['client_id'],
                $client['fname'],
                $client['mname'],
                $client['lname'],
                $client['routernumber'],
                $client['address'],
                $client['residenttype'],
                $client['mobile'],
                $promo_name,
                $client['status'],
                $client['email'],
                $client['promo_id'],
                $install_date
            );
            $insertStmt->execute();
    
            $routernumber = $client['routernumber'];
    
            $conn->query("INSERT INTO tblbilling_archive SELECT * FROM tblbilling WHERE routernumber = '$routernumber'");

            $conn->query("INSERT INTO tblpayment_archive SELECT * FROM tblpayment WHERE routernumber = '$routernumber'");
    
            $conn->query("DELETE FROM tblbilling WHERE routernumber = '$routernumber'");
            $conn->query("DELETE FROM tblpayment WHERE routernumber = '$routernumber'");
            $conn->query("DELETE FROM tblinstallations WHERE client_id = '$client_id'");
            $conn->query("DELETE FROM tblpromo_subscribers WHERE client_id = '$client_id'");
            $conn->query("DELETE FROM tblclientlist WHERE client_id = '$client_id'");
    
            $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Client dropped and archived successfully.'];
            header('Location: ../client_management.php');
            exit();
        } else {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Client not found or already archived.'];
            header('Location: ../client_management.php');
            exit();
        }
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid request method.'];
        header('Location: ../client_management.php');
        exit();
    }
?>
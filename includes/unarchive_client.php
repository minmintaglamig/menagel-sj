<?php
    include('dbh.php');
    include('encryption.php');
    session_start();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $client_id = $_POST['client_id'];
    
        $client = $conn->query("SELECT * FROM tblclient_archive WHERE client_id = '$client_id'")->fetch_assoc();
        $routernumber = $client['routernumber'];
    
        if (!$client) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Client not found in archive.'];
            header('Location: ../archived_clients.php');
            exit();
        }
    
        $checkClient = $conn->query("SELECT client_id FROM tblclientlist WHERE client_id = '$client_id'");
    
        if ($checkClient->num_rows > 0) {
            $conn->query("UPDATE tblclientlist SET
                routernumber = '{$client['routernumber']}',
                fname = '{$client['fname']}',
                mname = '{$client['mname']}',
                lname = '{$client['lname']}',
                address = '{$client['address']}',
                residenttype = '{$client['residenttype']}',
                mobile = '{$client['mobile']}',
                promo_name = '{$client['promo_name']}',
                status = '{$client['status']}',
                email = '{$client['email']}',
                promo_id = '{$client['promo_id']}'
                WHERE client_id = '$client_id'");
        } else {
            $conn->query("INSERT INTO tblclientlist (client_id, routernumber, fname, mname, lname, address, residenttype, mobile, promo_name, status, email, promo_id)
                          VALUES ('{$client['client_id']}', '{$client['routernumber']}', '{$client['fname']}', '{$client['mname']}', '{$client['lname']}', '{$client['address']}', '{$client['residenttype']}', '{$client['mobile']}', '{$client['promo_name']}', '{$client['status']}', '{$client['email']}', '{$client['promo_id']}')");
        }
    
        $conn->query("INSERT INTO tblbilling SELECT * FROM tblbilling_archive WHERE routernumber = '$routernumber'");
        $conn->query("INSERT INTO tblpayment SELECT * FROM tblpayment_archive WHERE routernumber = '$routernumber'");    
    
        $conn->query("INSERT INTO tblpromo_subscribers (promo_id, client_id, fname, mname, lname, email)
                      VALUES ('{$client['promo_id']}', '{$client['client_id']}', '{$client['fname']}', '{$client['mname']}', '{$client['lname']}', '{$client['email']}')");
    
        $conn->query("DELETE FROM tblclient_archive WHERE client_id = '$client_id'");
        $conn->query("DELETE FROM tblbilling_archive WHERE routernumber = '$routernumber'");
        $conn->query("DELETE FROM tblpayment_archive WHERE routernumber = '$routernumber'");
    
        $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Client successfully unarchived.'];
        header('Location: ../archived_clients.php');
        exit();
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Invalid request.'];
        header('Location: ../archived_clients.php');
        exit();
    }
?>
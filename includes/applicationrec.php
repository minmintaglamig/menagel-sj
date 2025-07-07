<?php
    session_start();
    include_once 'dbh.php';
    include_once 'encryption.php';
    include_once 'config.php';
    require '../vendor/autoload.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fname = htmlspecialchars(trim($_POST['fname']));
        $mname = htmlspecialchars(trim($_POST['mname']));
        $lname = htmlspecialchars(trim($_POST['lname']));
        $city = htmlspecialchars(trim($_POST['city']));
        $barangay = htmlspecialchars(trim($_POST['barangay']));
        $street = htmlspecialchars(trim($_POST['street']));
        $full_address = htmlspecialchars(trim("$street, $barangay, $city"));
        $residenttype = htmlspecialchars(trim($_POST['residenttype']));
        $mobile = htmlspecialchars(trim($_POST['mobile']));
        $email = htmlspecialchars(trim($_POST['email']));
        $promo_id = intval($_POST['promo_id']);
    
        if (empty($fname) || empty($lname) || empty($full_address) || empty($mobile) || empty($email) || empty($promo_id)) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Please ensure all fields are filled in.'];
            header("Location: ../applicationform.php");
            exit();
        }
    
        $encrypted_email = encryptData($email);
    
        $sql_check_email = "SELECT COUNT(*) FROM tblapplication WHERE email = ?";
        $stmt_check_email = $conn->prepare($sql_check_email);
        $stmt_check_email->bind_param("s", $encrypted_email);
        $stmt_check_email->execute();
        $result = $stmt_check_email->get_result();
        $row = $result->fetch_assoc();
    
        if ($row['COUNT(*)'] > 0) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Email already exists in the system.'];
            header("Location: ../applicationform.php");
            exit();
        }
    
        $billing_proof = $_FILES['billing_proof'];
        $valid_id = $_FILES['valid_id'];
    
        if ($billing_proof['size'] > 5000000 || !in_array($billing_proof['type'], ['image/jpeg', 'image/png', 'application/pdf'])) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Billing proof file is too large or has an invalid format.'];
            header("Location: ../applicationform.php");
            exit();
        }
        if ($valid_id['size'] > 5000000 || !in_array($valid_id['type'], ['image/jpeg', 'image/png', 'application/pdf'])) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Valid ID file is too large or has an invalid format.'];
            header("Location: ../applicationform.php");
            exit();
        }
    
        $billing_proof_path = '../uploads/billing_proof/' . basename($billing_proof['name']);
        $valid_id_path = '../uploads/valid_id/' . basename($valid_id['name']);
    
        if (!move_uploaded_file($billing_proof['tmp_name'], $billing_proof_path) || !move_uploaded_file($valid_id['tmp_name'], $valid_id_path)) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to upload files.'];
            header("Location: ../applicationform.php");
            exit();
        }
    
        $fname = encryptData($fname);
        $mname = encryptData($mname);
        $lname = encryptData($lname);
        $full_address = encryptData($full_address);
        $mobile = encryptData($mobile);
        $residenttype = ($residenttype);
    
        $conn->begin_transaction();
        try {
            $sql_promo = "SELECT promo_name FROM tblpromo WHERE promo_id = ?";
            $stmt_promo = $conn->prepare($sql_promo);
            $stmt_promo->bind_param("i", $promo_id);
            $stmt_promo->execute();
            $promo_result = $stmt_promo->get_result();
    
            if ($promo_result->num_rows > 0) {
                $promo_name = $promo_result->fetch_assoc()['promo_name'];
            } else {
                error_log("No promo found for promo_id: $promo_id");
                $promo_name = null;
            }
    
            $sql = "INSERT INTO tblapplication (fname, mname, lname, address, mobile, email, promo_id, residenttype, billing_proof, valid_id, promo_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssss", $fname, $mname, $lname, $full_address, $mobile, $encrypted_email, $promo_id, $residenttype, $billing_proof_path, $valid_id_path, $promo_name);
            if (!$stmt->execute()) throw new Exception("Error inserting into tblapplication");
    
            $stmt_clientlist = $conn->prepare("INSERT INTO tblclientlist (fname, mname, lname, address, mobile, email, promo_id, residenttype, promo_name) 
                                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_clientlist->bind_param("sssssssss", $fname, $mname, $lname, $full_address, $mobile, $encrypted_email, $promo_id, $residenttype, $promo_name);
            if (!$stmt_clientlist->execute()) throw new Exception("Error inserting into tblclientlist");
    
            $client_id = $stmt_clientlist->insert_id;
    
            $stmt_subscriber = $conn->prepare("INSERT INTO tblpromo_subscribers (client_id, fname, mname, lname, email, promo_id)
                                             VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_subscriber->bind_param("issssi", $client_id, $fname, $mname, $lname, $encrypted_email, $promo_id); // Using encrypted email here
            if (!$stmt_subscriber->execute()) throw new Exception("Error inserting into tblpromo_subscribers");
    
            $conn->commit();
    
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
    
                $mail->setFrom('menagelsj@gmail.com', 'Menagel SJ');
                $mail->addAddress($email);
                $mail->Subject = 'Confirmation of Application Submission';
    
                $decrypted_fname = decryptData($fname);
                $decrypted_mname = decryptData($mname);
                $decrypted_lname = decryptData($lname);
                $decrypted_full_address = decryptData($full_address);
                $decrypted_mobile = decryptData($mobile);
                $decrypted_residenttype = ($residenttype);
    
                $mail->isHTML(true);
                $mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
                    </style>
                </head>
                <body>
                    <h2>Application Confirmation</h2>
                    <p>Dear $decrypted_fname $decrypted_lname,</p>
                    <p>Thank you for your application! We have successfully received your details for the $promo_name promo.</p>
                    <h3>Your Information:</h3>
                    <table>
                        <tr><th>First Name</th><td>$decrypted_fname</td></tr>
                        <tr><th>Middle Name</th><td>$decrypted_mname</td></tr>
                        <tr><th>Last Name</th><td>$decrypted_lname</td></tr>
                        <tr><th>Address</th><td>$decrypted_full_address</td></tr>
                        <tr><th>Mobile</th><td>$decrypted_mobile</td></tr>
                        <tr><th>Resident Type</th><td>$decrypted_residenttype</td></tr>
                    </table>
                    <p>We will contact you soon for further details.</p>
                    <p>Best regards, <br> Application Team</p>
                </body>
                </html>";
    
                $mail->send();
    
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Failed to send confirmation email.'];
                header("Location: ../applicationform.php");
                exit();
            }
    
            header("Location: ../success.php");
            exit();
    
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'An error occurred during your application submission.'];
            header("Location: ../applicationform.php");
            exit();
        }
    }
?>
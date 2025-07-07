<?php
    session_start();
    include('includes/dbh.php');
    include('includes/encryption.php');
    
    if (!isset($_SESSION['user_email'])) {
        header("Location: login.php");
        exit();
    }
    
    $email = $_SESSION['user_email'];
    
    $client_check_query = "SELECT client_id FROM tblclientlist WHERE email = ?";
    $stmt = $conn->prepare($client_check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $client_result = $stmt->get_result();
    $client = $client_result->fetch_assoc();
    
    if (!$client) {
        $show_apply_button = true;
    } else {
        $show_apply_button = false;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/popup.css">
    <script src="javascript/sidebar-toggle.js"></script>
    <title>Client Application</title>
    <style>
        .main-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;

}

.apply-now-section {
    text-align: center;
    margin-top: 30px;
}

.apply-now-section h3 {
    font-size: 1.8em;
    color: #333;
}

.apply-now-section p {
    font-size: 1.2em;
    color: #555;
}

.apply-now-section .btn-primary {
    background-color: #6d2323;
    color: white;
    padding: 12px 20px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 1.1em;
}

.apply-now-section .btn-primary:hover {
    background-color:rgb(150, 53, 53);
}

@media (max-width: 768px) {
    .main-container {
        padding: 15px;
    }

    .apply-now-section h3 {
        font-size: 1.6em;
    }

    .apply-now-section p {
        font-size: 1.1em;
    }

    .apply-now-section .btn-primary {
        font-size: 1em;
        padding: 10px 18px;
    }
}

@media (max-width: 480px) {
    body {
        padding: 10px;
    }

    .apply-now-section h3 {
        font-size: 1.4em;
    }

    .apply-now-section p {
        font-size: 1em;
    }

    .apply-now-section .btn-primary {
        font-size: 1em;
        padding: 10px 15px;
    }
}
</style>
</head>
<body>
    <?php include('header.php'); ?>

    <div class="main-container">
        <div class="apply-now-section">
            <?php if ($show_apply_button): ?>
                <h3>You need to fill out the application form to complete your registration.</h3>
                <a href="applicationform.php" class="btn btn-primary">Apply Now</a>
            <?php else: ?>
                <h3>You are already registered!</h3>
                <p>Thank you for completing the registration process. You can now access the full features.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="javascript/sidebar-toggle.js"></script>
</body>
</html>
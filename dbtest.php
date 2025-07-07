<?php
    $host = "localhost";
    $username = "root";
    $password = ""; 
    $dbname = "test";
    
    $conn = mysqli_connect($host, $username, $password, $dbname);
    
    if ($conn) {
        echo "✅ Database connection successful!";
    } else {
        echo "❌ Connection failed: " . mysqli_connect_error();
    }
?>
<?php
    include_once 'encryption.php';

    $email = "pjarmine000@gmail.com";
    $encrypted1 = encryptData($email);
    $encrypted2 = encryptData($email);
    
    echo "Encrypted 1: " . $encrypted1 . "<br>";
    echo "Encrypted 2: " . $encrypted2 . "<br>";
    
    if ($encrypted1 === $encrypted2) {
        echo "✅ Encryption is now stable and consistent!";
    } else {
        echo "❌ Encryption is still inconsistent!";
    }
?>
<?php
    include('includes/encryption.php');

    $test_data = 'Jarmine Nicole Perez';
    $encrypted_test = encryptData($test_data);
    echo "Encrypted: $encrypted_test\n";

    $decrypted_test = decryptData($encrypted_test);
    echo "Decrypted: $decrypted_test\n";
?>
<?php
    include('includes/encryption.php');

    $sampleEncrypted = 'SnJrTWwzdnFGSEF=';
    
    $decrypted = decryptData($sampleEncrypted);
    echo "Decrypted Value: " . htmlspecialchars($decrypted);
?>

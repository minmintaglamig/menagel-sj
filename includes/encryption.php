<?php
    define("ENCRYPTION_KEY", "86H+_)lV^jRa^BqMMMVaA#GSE7iAkb%w");
    define("ENCRYPTION_IV", "1234567890abcdef");  

    /*require_once 'includes/load_env.php';
    loadEnv(__DIR__ . '/.env'); 

    define("ENCRYPTION_KEY", getenv("ENCRYPTION_KEY"));
    define("ENCRYPTION_IV", getenv("ENCRYPTION_IV"));*/
    
    function encryptData($data) {
        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', ENCRYPTION_KEY, 0, ENCRYPTION_IV));
    }
    
    function decryptData($encryptedData) {
        if (empty($encryptedData)) return '';
    
        $decoded = base64_decode($encryptedData, true);
        if ($decoded === false) {
            error_log("Base64 decoding failed for value: $encryptedData");
            return null;
        }
    
        $decrypted = openssl_decrypt($decoded, 'AES-256-CBC', ENCRYPTION_KEY, 0, ENCRYPTION_IV);
        if ($decrypted === false) {
            error_log("Decryption failed for value: $encryptedData");
        }
        return $decrypted !== false ? $decrypted : null;
    }
    
    function safeDecrypt($value) {
        $decrypted = decryptData($value);
        return $decrypted !== null ? $decrypted : "[DECRYPTION ERROR]";
    }
?>
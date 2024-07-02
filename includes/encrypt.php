<?php
function pl_encrypt_password($plaintext_password) {
    $encryption_key = PL_ENCRYPTION_KEY;
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted_password = openssl_encrypt($plaintext_password, 'aes-256-cbc', $encryption_key, 0, $iv);
    return base64_encode($iv . $encrypted_password);
}

// Store the encrypted password in the database
$encrypted_password = pl_encrypt_password($plaintext_password);

// insert encrypted password into db

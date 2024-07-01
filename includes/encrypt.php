<?php
function my_plugin_encrypt_password($plaintext_password) {
    $encryption_key = MY_PLUGIN_ENCRYPTION_KEY;
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted_password = openssl_encrypt($plaintext_password, 'aes-256-cbc', $encryption_key, 0, $iv);
    return base64_encode($iv . $encrypted_password);
}

// Store the encrypted password in the database
$encrypted_password = my_plugin_encrypt_password($plaintext_password);
update_option('my_plugin_encrypted_password', $encrypted_password);


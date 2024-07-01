<?php
function my_plugin_decrypt_password($encrypted_password) {
    $encryption_key = MY_PLUGIN_ENCRYPTION_KEY;
    $data = base64_decode($encrypted_password);
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $iv_length);
    $encrypted_password = substr($data, $iv_length);
    $decrypted_password = openssl_decrypt($encrypted_password, 'aes-256-cbc', $encryption_key, 0, $iv);
    return $decrypted_password;
}

// Retrieve the encrypted password from the database
$encrypted_password = get_option('my_plugin_encrypted_password');
$plaintext_password = my_plugin_decrypt_password($encrypted_password);


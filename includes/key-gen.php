<?php
// Function to generate a secure encryption key
function pl_generate_encryption_key() {
    return bin2hex(openssl_random_pseudo_bytes(32));
}

// Function to write the encryption key to wp-config.php
function pl_write_encryption_key_to_config() {
    $key = pl_generate_encryption_key();
    $config_path = ABSPATH . 'wp-config.php';
    
    if (is_writable($config_path)) {
        $config_file = file_get_contents($config_path);
        
        // Check if the key already exists
        if (strpos($config_file, 'define(\'PL_ENCRYPTION_KEY\'') === false) {
            $key_define = "\ndefine('PL_ENCRYPTION_KEY', '$key');\n";
            
            // Insert the key before the "That's all, stop editing!" comment
            $config_file = preg_replace('/(\/\* That\'s all, stop editing! Happy blogging. \*\/)/', $key_define . '$1', $config_file);
            
            // Write the updated config file
            file_put_contents($config_path, $config_file);
        }
    } else {
        // Handle the error if the file is not writable
        wp_die('The wp-config.php file is not writable.');
    }
}

// Hook into plugin activation
register_activation_hook(__FILE__, 'pl_write_encryption_key_to_config');


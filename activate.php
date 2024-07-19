<?php


function sl_activate_plugin() {

require_once(plugin_dir_path(__FILE__) . '/classes/sl-email-template.php');

require_once(plugin_dir_path(__FILE__) . '/classes/slinit.class.php');

$init = new SLinit();

$init->sl_create_tokens_table();

// Add encryption key to wp-config.php
    $key = bin2hex(openssl_random_pseudo_bytes(32));
    $config_path = ABSPATH . 'wp-config.php';

    if (is_writable($config_path)) {
        $config_file = file_get_contents($config_path);

        // Check if the key already exists
        if (strpos($config_file, 'define(\'PL_ENCRYPTION_KEY\'') === false) {

            $key_define = "define('PL_ENCRYPTION_KEY', '$key');\n";

            // Insert the key after the opening PHP tag
            $new_config_file = preg_replace(
                '/(<\?php\s+)/',
                '$1' . $key_define,
                $config_file
            );

            // Attempt to write the updated config file
            if (file_put_contents($config_path, $new_config_file) !== false) {
                error_log('Successfully wrote to wp-config.php.');
            } else {
                error_log('Failed to write to wp-config.php.');
                wp_die('Failed to write the encryption key to wp-config.php.');
            }
        } else {
            error_log('Encryption key already exists in wp-config.php.');
        }
    } else {
        error_log('wp-config.php is not writable.');
        wp_die('The wp-config.php file is not writable.');
    }



    

}

// Ensure the function is called during plugin activation
sl_activate_plugin();



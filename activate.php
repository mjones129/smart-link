
<?php

function pl_activate() {
    global $wpdb;

    // Define table names
    $pl_tokens = $wpdb->prefix . 'pl_tokens';
    $pl_smtp_creds = $wpdb->prefix . 'pl_smtp_creds';

    $charset_collate = $wpdb->get_charset_collate();

    // SQL to create the tokens table
    $sql = "CREATE TABLE $pl_tokens (
        id INT NOT NULL AUTO_INCREMENT,
        token VARCHAR(32) NOT NULL,
        expiration DATETIME NOT NULL,
        used TINYINT DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE (token)
    ) $charset_collate;";

    // SQL to create the SMTP credentials table
    $sql2 = "CREATE TABLE $pl_smtp_creds (
        id TINYINT NOT NULL DEFAULT 1,
        host VARCHAR(100) NOT NULL,
        port SMALLINT NOT NULL,
        username VARCHAR(100) NOT NULL,
        password VARCHAR(100) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // SQL to insert default values into the SMTP credentials table
    $sql3 = "INSERT INTO $pl_smtp_creds (host, port, username, password)
             VALUES ('hostname-here', 9000, 'fake-user', 'fake-pass');";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Execute the SQL queries
    dbDelta($sql);
    dbDelta($sql2);
    $wpdb->query($sql3);

    // Add encryption key to wp-config.php
    $key = bin2hex(openssl_random_pseudo_bytes(32));
    $config_path = ABSPATH . 'wp-config.php';

    if (is_writable($config_path)) {
        $config_file = file_get_contents($config_path);

        // Check if the key already exists
        if (strpos($config_file, 'define(\'PL_ENCRYPTION_KEY\'') === false) {
            $key_define = "\ndefine('PL_ENCRYPTION_KEY', '$key');\n";

            // Insert the key before the "That's all, stop editing!" comment
            $config_file = preg_replace(
                '/(\/\* That\'s all, stop editing! Happy blogging. \*\/)/',
                $key_define . '$1',
                $config_file
            );

            // Write the updated config file
            if (file_put_contents($config_path, $config_file) === false) {
                // Handle the error if the file could not be written
                wp_die('Failed to write the encryption key to wp-config.php.');
            }
        }
    } else {
        // Handle the error if the file is not writable
        wp_die('The wp-config.php file is not writable.');
    }
}



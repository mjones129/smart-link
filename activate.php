<?php

function pl_activate_plugin() {

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


    global $wpdb;

    // Define table names
    $pl_tokens = $wpdb->prefix . 'pl_tokens';
    $pl_smtp_creds = $wpdb->prefix . 'pl_smtp_creds';

    $charset_collate = $wpdb->get_charset_collate();

    if($wpdb->get_var("SHOW TABLES LIKE '$pl_tokens'") != $pl_tokens) {
      // SQL to create the tokens table
      $sql = "CREATE TABLE $pl_tokens (
        id INT NOT NULL AUTO_INCREMENT,
        slug VARCHAR(255) NOT NULL,
        token VARCHAR(32) NOT NULL,
        expiration DATETIME NOT NULL,
        used TINYINT DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE (token)
      ) $charset_collate;";

      dbDelta($sql);  
  
    }
    
    if($wpdb->get_var("SHOW TABLES LIKE '$pl_smtp_creds'") != $pl_smtp_creds) {
      // SQL to create the SMTP credentials table
        $sql2 = "CREATE TABLE $pl_smtp_creds (
          id TINYINT NOT NULL DEFAULT 1,
          host VARCHAR(100) NOT NULL,
          port SMALLINT NOT NULL,
          username VARCHAR(100) NOT NULL,
          name VARCHAR(100) NOT NULL,
          password VARCHAR(255) NOT NULL,
          first_time TINYINT NOT NULL,
          PRIMARY KEY (id)
      ) $charset_collate;";

      // SQL to insert default values into the SMTP credentials table
      $sql3 = "INSERT INTO $pl_smtp_creds (host, port, username, name, password, first_time)
               VALUES ('hostname-here', 9000, 'fake-user', 'John Doe', 'fake-pass', 1);";

    //create the smtp creds table and insert placeholder data
     dbDelta($sql2);
     $wpdb->query($sql3);

    }
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $first_time = $wpdb->get_col("SELECT first_time FROM TABLE " . $wpdb->prefix . "pl_smtp_creds");

    if($first_time === 1) {
      wp_safe_redirect(admin_url('admin.php?page=smtp-settings'));
      exit;
    }
}

// Ensure the function is called during plugin activation
pl_activate_plugin();


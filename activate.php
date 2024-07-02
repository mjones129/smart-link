<?php
  global $wpdb;

  //define table names
  $pl_tokens = $wpdb->prefix . 'pl_tokens';
  $pl_smtp_creds = $wpdb->prefix . 'pl_smtp_creds';

  $charset_collate = $wpdb->get_charset_collate();

  //create the tokens table
  $sql = "CREATE TABLE $pl_tokens (
    id INT NOT NULL AUTO_INCREMENT,
    token VARCHAR(32) NOT NULL,
    expiration DATETIME NOT NULL,
    used TINYINT DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE (token)
) $charset_collate;";

  //create the smtp credentials table
  $sql2 = "CREATE TABLE $pl_smtp_creds (
  id TINYINT NOT NULL DEFAULT 1,
  host VARCHAR(100) NOT NULL,
  port SMALLINT NOT NULL,
  username VARCHAR(100) NOT NULL,
  password VARCHAR(100) NOT NULL,
  PRIMARY KEY (id) 
) $charset_collate;";

  //insert default values into the first row
  $sql3 = "INSERT INTO $pl_smtp_creds (host, port, username, password)
VALUES('hostname-here', 9000, 'fake-user', 'fake-pass');";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

  //execute all sql queries
  dbDelta($sql);
  dbDelta($sql2);
  dbDelta($sql3);


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
//execute
pl_write_encryption_key_to_config();

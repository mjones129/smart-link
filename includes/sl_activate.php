<?php

function sl_activate_plugin()
{
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    global $wpdb;
    //get table name
    $sl_tokens = $wpdb->prefix . 'sl_tokens';
    $charset_collate = $wpdb->get_charset_collate();
    //check if tokens table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$sl_tokens'") != $sl_tokens) {
      // SQL to create the tokens table
      $sql = "CREATE TABLE $sl_tokens (
        id INT NOT NULL AUTO_INCREMENT,
        page_ID INT NOT NULL,
        slug VARCHAR(255) NOT NULL,
        token VARCHAR(32) NOT NULL,
        expiration DATETIME NOT NULL,
        used TINYINT DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE (token)
      ) $charset_collate;";

      dbDelta($sql);
    }

}



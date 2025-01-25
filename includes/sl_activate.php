<?php

function sl_activate_plugin()
{
    //create database table
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

    //create access denied page
    $access_denied_page = array(
        'post_title' => 'Access Denied',
        'post_content' => 'This is a protected page. You do not have access to view this content.',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => 'access-denied'
    );
    wp_insert_post($access_denied_page);

}



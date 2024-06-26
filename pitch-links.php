<?php
/*
 * Plugin Name: Pitch Links
 * Description: Generate one-time-use links for easily sharing pitch pages
 * Version: 0.0.1
 * Author: Matt Jones
 */

if (!defined('ABSPATH')) {
  exit; //Exit if accessed directly
}

// create db table on activiation
register_activation_hook(__FILE__, 'pl_create_token_table');
function pl_create_token_table() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'user_tokens';

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(32) NOT NULL,
    expiration DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE (token)
) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

// Generate and store token
function pl_generate_user_token($user_id) {
  global $wpdb;
  $token = bin2hex(random_bytes(16));
  $expiration = date('Y-m-d H:i:s', strtotime('+1 day')); // Token valid for 1 day

  $wpdb->insert(
    $wpdb->prefix . 'user_tokens',
    array(
      'user_id' => $user_id,
      'token' => $token,
      'expiration' => $expiration,
      'used' => 0
    ),
    array(
      '%d',
      '%s',
      '%s',
      '%d'
    )
  );

  return $token;
}

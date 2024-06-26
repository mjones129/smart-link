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


}

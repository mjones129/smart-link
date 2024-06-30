<?php
function pl_create_tables() {
  global $wpdb;

  //define table names
  $pl_tokens = $wpdb->prefix . 'pl_tokens';
  $pl_smtp_creds = $wpdb->prefix . 'pl_smtp_creds';

  $charset_collate = $wpdb->get_charset_collate();

  //create the tokens table
  $sql = "CREATE TABLE $pl_tokens (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(32) NOT NULL,
    expiration DATETIME NOT NULL,
    used TINYINT DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE (token)
) $charset_collate;";

  //create the smtp credentials table
  $sql2 = "CREATE TABLE $pl_smtp_creds (
  id MEDIUMINT NOT NULL AUTO_INCREMENT,
  host VARCHAR(100) NOT NULL,
  port SMALLINT NOT NULL,
  username VARCHAR(100) NOT NULL,
  password VARCHAR(100) NOT NULL,
  PRIMARY KEY (id) 
) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

  //execute both sql queries
  dbDelta($sql);
  dbDelta($sql2);
}

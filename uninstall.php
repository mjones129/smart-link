<?php

if(!defined('WP_UNINSTALL_PLUGIN')) {
  die;
}

global $wpdb;

//grab table to be dropped
$table_name = $wpdb->prefix . 'user_tokens';

//query to drop table
$sql = "DROP TABLE IF EXISTS $table_name";

//execute
$wpdb->query($sql);

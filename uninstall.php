<?php

if(!defined('WP_UNINSTALL_PLUGIN')) {
  die;
}

global $wpdb;

//grab tables to be dropped
$tokens = $wpdb->prefix . 'pl_tokens';
$creds = $wpdb->prefix . 'pl_smtp_creds';

//query to drop table
$sql1 = "DROP TABLE IF EXISTS $tokens";
$sql2 = "DROP TABLE IF EXISTS $creds";

//execute
$wpdb->query($sql1);
$wpdb->query($sql2);

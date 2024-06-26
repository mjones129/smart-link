<?php
/*
 * Plugin Name: Private Links
 * Description: Generate one-time-use links that expire after 24 hours. 
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

//Send email with private link
function pl_send_private_link_email($user_email, $user_id) {
  $token = pl_generate_user_token($user_id);
  $private_link = home_url('/special-page/?access_token=' . $token);

  $subject = 'Your Private Link';
  $message = 'Here is your private link: ' . $private_link;

  wp_mail($user_email, $subject, $message);
}

//check user token for page access
function pl_check_access_token() {
  global $wpdb;

  $protected_page_id = 123; //Replace with real page ID.
  //if there is no access token, redirect to /access-denied/ page.
  if (is_page($protected_page_id)) {
    if(!isset($_GET['access_token'])) {
      wp_redirect(home_url('/access-denied/'));
      exit();
    }

    $token = $_GET['access_token'];
    $current_time = current_time('mysql');

    $token_entry = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT * FROM " . $wpdb->prefix . "user_tokens WHERE token = %s AND expiration > %s AND used = 0",
        $token,
        $current_time
      )
    );

    if(!$token_entry) {
      wp_redirect(home_url('/access-denied/'));
      exit();
    } else {
      //Mark token as used
      $wpdb->update(
        $wpdb->prefix . 'user_tokens',
        array('used' => 1),
        array('id' => $token_entry->id),
        array('%d'),
        array('%d')
      );
    }
  }
}

<?php
/*
 * Plugin Name: Private Links
 * Description: Generate one-time-use links that expire after 24 hours. 
 * Version: 0.0.2
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
function pl_send_private_link_email($user_email, $user_id, $email_subject, $page_slug) {
  $token = pl_generate_user_token($user_id);
  $private_link = home_url($page_slug . '?access_token=' . $token);

  $message = 'Here is your private link: ' . $private_link;

  wp_mail($user_email, $email_subject, $message);
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
add_action('template_redirect', 'pl_check_access_token');

//add admin menu item
function pl_admin_menu() {
  add_menu_page('Private Links',
    'Private Links',
    'manage_options',
    'private-links',
    'pl_admin_page',
    'dashicons-admin-network'
  );
}
add_action('admin_menu', 'pl_admin_menu');

function pl_admin_page() {
?>
<div class="wrap">
<h1>Send Access Email</h1>
<form method="post" action="">
<table class="form-table">
<tr valign="top">
<th scope="row">User ID</th>
<td><input type="number" name="user_id" required /></td>
</tr>
<tr>
<th>User Email</th>
<td><input type="email" name="user_email" required /></td>
</tr>
<tr>
<th scope="row">Email Subject</th>
<td><input type="text" name="email_subject" required /></td>
</tr>
<tr>
<th>Private Page Slug (including leading and trailing slashes)</th>
<td><input type="text" name="page_slug" required /></td>
</tr>
</table>
<input type="submit" name="submit" class="button-primary" value="Send Email" />
</form>
</div>
<?php

  if (isset($_POST['submit'])) {
    $user_email = sanitize_email($_POST['user_email']);
    $user_id = intval($_POST['user_id']);
    $email_subject = sanitize_text_field($_POST['email_subject']);
    $page_slug = sanitize_text_field($_POST['page_slug']);

    if ($user_email && $user_id) {
      pl_send_private_link_email($user_email, $user_id, $email_subject, $page_slug);
      echo '<div class="notice notice-success is-dismissible"><p>Email sent successfully!</p></div>';
    } else {
      echo '<div class="notice notice-error is-dismissible"><p>Invalid input. Please try again.</p></div>';
    }
  }
}
?>

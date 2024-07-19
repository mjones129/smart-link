<?php
/*
 * Plugin Name: Private Links
 * Description: Generate one-time-use links that expire after 24 hours. 
 * Version: 0.1.32
 * Author: Matt Jones
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


if (!defined('ABSPATH')) {
  exit; //Exit if accessed directly
}

//include the smtp settings page
include_once plugin_dir_path(__FILE__) . '/pages/smtp-settings.php';

//include the send private link page
include_once plugin_dir_path(__FILE__) . '/pages/send-private-link.php';

//register custom post type for email templates
include_once plugin_dir_path(__FILE__) . '/classes/sl-email-template.php';

// Register custom post types on the 'init' hook.
add_action( 'init', 'sl_register_email_template' );


//plugin setup
register_activation_hook(__FILE__,  'pl_plugin_activate');

function pl_plugin_activate() {
  require_once plugin_dir_path(__FILE__) . 'activate.php';
}

// drop db table on deletion
register_uninstall_hook(__FILE__, 'pl_plugin_uninstall');

function pl_plugin_uninstall() {
    // Path to the uninstall script
    require_once plugin_dir_path(__FILE__) . 'uninstall.php';
}

//redirect if first time
function pl_first_time_redirect() {
  global $wpdb;
  $pl_smtp_creds = $wpdb->prefix . 'pl_smtp_creds';
  $first_time = $wpdb->get_var("SELECT first_time FROM $pl_smtp_creds WHERE id = 1");

    if($first_time == '1') {
      // Update the first_time value to prevent subsequent redirects
      // $wpdb->update($pl_smtp_creds, ['first_time' => 0], ['id' => 1]);

      // wp_safe_redirect(admin_url('admin.php?page=smtp-settings'));
      // exit;
    }
}
add_action('admin_init', 'pl_first_time_redirect');   

// Generate and store token
function pl_generate_user_token($page_slug) {
  global $wpdb;
  $token = bin2hex(random_bytes(16));
  $expiration = date('Y-m-d H:i:s', strtotime('+1 day')); // Token valid for 1 day

  $wpdb->insert(
    $wpdb->prefix . 'pl_tokens',
    array(
      'slug' => $page_slug,
      'token' => $token,
      'expiration' => $expiration,
      'used' => 0
    ),
    //specify data types
    array(
      '%s', //string
      '%s', //string
      '%s', //string
      '%d' //integer
    )
  );

  return $token;
}

//decrypt password
function pl_decrypt_password($encrypted_password) {
    $encryption_key = PL_ENCRYPTION_KEY;
    $data = base64_decode($encrypted_password);
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $iv_length);
    $encrypted_password = substr($data, $iv_length);
    $decrypted_password = openssl_decrypt($encrypted_password, 'aes-256-cbc', $encryption_key, 0, $iv);
    return $decrypted_password;
}

//Send email with private link
function pl_send_private_link_email($email_to, $email_subject, $email_body, $page_slug, $email_to_name) {

  global $wpdb;
  $table = $wpdb->prefix . 'pl_smtp_creds';
  $query = $wpdb->prepare("SELECT * FROM $table;");
  $creds = $wpdb->get_results($query, ARRAY_A);

  $token = pl_generate_user_token($page_slug);
  $private_link = home_url($page_slug . '?access_token=' . $token);
  $message = 'Here is your private link: ' . $private_link;

  // Prepare variables for the email template
  ob_start();
  $email_subject = $email_subject;
  $email_body = $email_body;
  $private_link = $private_link;
  $email_to_name = $email_to_name;

  $email_content = require_once plugin_dir_path(__FILE__) . '/includes/email-template.php';
  ob_end_clean();

//begin PHPmailer setup

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require ((__DIR__) . '/vendor/autoload.php');

//Create a new PHPMailer instance
$mail = new PHPMailer();
//Tell PHPMailer to use SMTP
$mail->isSMTP();
//Enable SMTP debugging
// SMTP::DEBUG_OFF = off (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = SMTP::DEBUG_SERVER;
//Set the hostname of the mail server
$mail->Host = $creds[0]['host'];
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = $creds[0]['port'];
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//define encryption
$mail->SMTPSecure = 'ENCRYPTION_STARTTLS';
//enable HTML
$mail->isHTML(true);
//Username to use for SMTP authentication
$mail->Username = $creds[0]['username'];
//Password to use for SMTP authentication
$mail->Password = pl_decrypt_password($creds[0]['password']);
//Set who the message is to be sent from
$mail->setFrom($creds[0]['username'], $creds[0]['name']);
//Set an alternative reply-to address
$mail->addReplyTo($creds[0]['username'], $creds[0]['name']);
//Set who the message is to be sent to
$mail->addAddress($email_to, $email_to_name);
//Set the subject line
$mail->Subject = $email_subject;
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
// $mail->msgHTML(file_get_contents('contents.html'), __DIR__);
//Replace the plain text body with one created manually
// $mail->AltBody = 'This is a plain-text message body';
// Add email body
$mail->Body = stripslashes($email_content); 
//Attach an image file
// $mail->addAttachment('images/phpmailer_mini.png');

//SMTP XCLIENT attributes can be passed with setSMTPXclientAttribute method
//$mail->setSMTPXclientAttribute('LOGIN', 'yourname@example.com');
//$mail->setSMTPXclientAttribute('ADDR', '10.10.10.10');
//$mail->setSMTPXclientAttribute('HELO', 'test.example.com');

//send the message, check for errors
if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message sent!';
}



}


// Check user token for page access
function pl_check_access_token() {
    global $wpdb;

    error_log("pl_check_access_token function called");

    // Select all the page slugs from the tokens table
    $protected_slugs = $wpdb->get_col("SELECT slug FROM " . $wpdb->prefix . "pl_tokens");
    error_log("Protected slugs: " . print_r($protected_slugs, true));

    // Only run on pages, not blog posts
    if (is_page()) {
        global $post;
        error_log("Post object: " . print_r($post, true));
        
        $current_slug = $post->post_name;
        error_log("Current slug: " . $current_slug);

        // If current page exists in protected_slugs, check for token
        if (in_array($current_slug, $protected_slugs)) {
            error_log("current_slug is in array protected_slugs.");
            if (!isset($_GET['access_token'])) {
                wp_redirect(home_url('/access-denied/'));
                exit();
            }
            $token = $_GET['access_token'];
            $current_time = current_time('mysql');
            $token_entry = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM " . $wpdb->prefix . "pl_tokens WHERE token = %s AND expiration > %s AND used = 0",
                    $token,
                    $current_time
                )
            );

            if (!$token_entry) {
                wp_redirect(home_url('/access-denied/'));
                exit();
            } else {
                // Mark token as used
                $wpdb->update(
                    $wpdb->prefix . 'pl_tokens',
                    array('used' => 1),
                    array('id' => $token_entry->id),
                    array('%d'),
                    array('%d')
                );
            }
        } else {
            error_log("current_slug is NOT in array protected_slugs.");
        }
    } else {
        error_log("Not a page.");
    }
}
add_action('template_redirect', 'pl_check_access_token');

//add admin menu item
function pl_admin_menu() {
  add_menu_page(
    'Private Links', //page title
    'Private Links', //menu title
    'manage_options', //capability
    'send-email', //menu slug
    'pl_admin_page', //function to render the page
    'dashicons-admin-network' //icon (optional)
  );
  add_submenu_page(
    'send-email', //parent slug
    'Email Link', //page title
    'Email Link', //menu title
    'manage_options', //capability
    'send-email', //menu slug
    'pl_admin_page', //function to render the page
    1 //menu position
  );
  add_submenu_page(
    'send-email', //parent slug
    'SMTP Settings', //page title
    'SMTP Settings', //menu title
    'manage_options', // capability
    'smtp-settings', //menu slug
    'pl_render_smtp_settings_page', //function to render the page
    2 //menu position
  );
}
add_action('admin_menu', 'pl_admin_menu');

// Handle AJAX request to retrieve first_time value
add_action('wp_ajax_get_first_time', 'pl_get_first_time');

function pl_get_first_time() {
  global $wpdb;
  $pl_smtp_creds = $wpdb->prefix . 'pl_smtp_creds';
  $first_time = $wpdb->get_var("SELECT first_time FROM $pl_smtp_creds WHERE id = 1");

  wp_send_json_success(array('first_time' => $first_time));
}

// Handle AJAX request to update first_time value
add_action('wp_ajax_update_first_time', 'pl_update_first_time');

function pl_update_first_time() {
  check_ajax_referer('pl_ajax_nonce', 'nonce');

  global $wpdb;
  $pl_smtp_creds = $wpdb->prefix . 'pl_smtp_creds';
  $result = $wpdb->update($pl_smtp_creds, ['first_time' => 0], ['id' => 1]);

  if ($result !== false) {
    wp_send_json_success();
  } else {
    wp_send_json_error('Failed to update first_time');
  }
}


//enqueue stylesheet on smtp settings page
function pl_smtp_styles() {
  wp_register_style('pl_style', plugin_dir_url(__FILE__) . '/css/pl-style.css', array(), '1.0', 'all');
  wp_register_style('bootstrap5', plugin_dir_url(__FILE__) . '/vendor/twbs/bootstrap/dist/css/bootstrap.min.css');
  wp_enqueue_style('pl_style');
  wp_enqueue_style('bootstrap5');
  wp_enqueue_script('bootstrapjs', plugin_dir_url(__FILE__) . '/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js', array(), '5.3.3', array() );
  wp_enqueue_script('pl_first_time_check', plugin_dir_url(__FILE__) . '/js/first-time-check.js', array('jquery'), null, true);
  wp_localize_script('pl_first_time_check', 'pl_ajax_object', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('pl_ajax_nonce'),
    'redirect_url' => admin_url('admin.php?page=smtp-settings')
  ));
}
add_action('admin_enqueue_scripts', 'pl_smtp_styles');

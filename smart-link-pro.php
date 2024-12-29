<?php
/*
 * Plugin Name: Smart Link Pro
 * Description: Generate one-time-use links that expire after 24 hours.
 * Version: 0.3
 * Author: Matt Jones
 * Update URI: https://mattjones.tech/hello/info.json
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

if (!defined('ABSPATH')) {
    exit; //Exit if accessed directly
}

// Register sidebar button

// add a link to the WP Toolbar
function custom_toolbar_link($wp_admin_bar) {
    $args = array(
        'id' => 'smartLinkPro',
        'title' => 'Smart Link Pro', 
        'href' => 'https://smartlinkpro.io', 
        'meta' => array(
            'class' => 'wpbeginner', 
            'title' => 'Go to Smart Link Pro'
            )
    );
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'custom_toolbar_link', 999);


//include the smtp settings page
require_once(plugin_dir_path(__FILE__) . '/pages/smtp-settings.php');

//include the send private link page
require_once(plugin_dir_path(__FILE__) . '/pages/send-private-link.php');

require_once(plugin_dir_path(__FILE__) . '/classes/sl-email-template.php');

//plugin setup
register_activation_hook(__FILE__, 'sl_plugin_activate');

function sl_plugin_activate()
{
    error_log('This is the sl_plugin_activate function');
    sl_register_email_template();
    error_log('This is after the email template should have been registered');
    require_once plugin_dir_path(__FILE__) . 'activate.php';
    sl_activate_plugin();
    flush_rewrite_rules();
}

// drop db table on deletion
register_uninstall_hook(__FILE__, 'sl_plugin_uninstall');

function sl_plugin_uninstall()
{
    // Path to the uninstall script
    require_once plugin_dir_path(__FILE__) . 'uninstall.php';
}

//redirect if first time
function sl_first_time_redirect()
{
    global $wpdb;
    //check if creds table exists
    if ($wpdb->prefix . 'sl_smtp_creds') {
        $sl_smtp_creds = $wpdb->prefix . 'sl_smtp_creds';

        $first_time = $wpdb->get_var("SELECT first_time FROM $sl_smtp_creds WHERE id = 1");
    }
}
add_action('admin_init', 'sl_first_time_redirect');

// Generate and store token
function sl_generate_user_token($page_slug)
{
    global $wpdb;
    $token = bin2hex(random_bytes(16));
    $expiration = date('Y-m-d H:i:s', strtotime('+1 day')); // Token valid for 1 day

    $wpdb->insert(
        $wpdb->prefix . 'sl_tokens',
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



//full private link
//$private_link = home_url($page_slug . '?access_token=' . $token);



// Check user token for page access
function sl_check_access_token()
{
    global $wpdb;

    error_log("sl_check_access_token function called");

    // Select all the page slugs from the tokens table
    $protected_slugs = $wpdb->get_col("SELECT slug FROM " . $wpdb->prefix . "sl_tokens");
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
                    "SELECT * FROM " . $wpdb->prefix . "sl_tokens WHERE token = %s AND expiration > %s AND used = 0",
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
                    $wpdb->prefix . 'sl_tokens',
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
add_action('template_redirect', 'sl_check_access_token');

//add admin menu item
function sl_admin_menu()
{
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
add_action('admin_menu', 'sl_admin_menu');

// Handle AJAX request to retrieve first_time value
add_action('wp_ajax_get_first_time', 'sl_get_first_time');

function sl_get_first_time()
{
    global $wpdb;
    $sl_smtp_creds = $wpdb->prefix . 'sl_smtp_creds';
    $first_time = $wpdb->get_var("SELECT first_time FROM $sl_smtp_creds WHERE id = 1");

    wp_send_json_success(array('first_time' => $first_time));
}

// Handle AJAX request to update first_time value
add_action('wp_ajax_update_first_time', 'sl_update_first_time');

function sl_update_first_time()
{
    check_ajax_referer('pl_ajax_nonce', 'nonce');

    global $wpdb;
    $sl_smtp_creds = $wpdb->prefix . 'sl_smtp_creds';
    $result = $wpdb->update($sl_smtp_creds, ['first_time' => 0], ['id' => 1]);

    if ($result !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to update first_time');
    }
}


//enqueue stylesheet on smtp settings page
function sl_smtp_styles()
{
    wp_register_style('pl_style', plugin_dir_url(__FILE__) . '/css/pl-style.css', array(), '1.0', 'all');
    wp_register_style('bootstrap5', plugin_dir_url(__FILE__) . '/vendor/twbs/bootstrap/dist/css/bootstrap.min.css');
    wp_enqueue_style('pl_style');
    wp_enqueue_style('bootstrap5');
    wp_enqueue_script(
        'my-custom-component',
        plugins_url('/js/sidebar.js', __FILE__),
        ['wp-blocks', 'wp-i18n', 'wp-element'],
        false,
        1
    );
    wp_enqueue_script('pl_first_time_check', plugin_dir_url(__FILE__) . '/js/first-time-check.js', array('jquery'), null, true);
    wp_enqueue_script('pl_edit_page_button', plugin_dir_url(__FILE__) . '/js/editPageButton.js', array(), null, true);
    wp_localize_script('pl_first_time_check', 'pl_ajax_object', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('pl_ajax_nonce'),
      'redirect_url' => admin_url('admin.php?page=smtp-settings')
    ));
}
add_action('admin_enqueue_scripts', 'sl_smtp_styles');


//add custom button to the gutenberg editor
function add_button_to_gutenberg_toolbar($settings)
{
    $settings['items'][] = [
      'id' => 'custom-button', // This should be a unique identifier for your button
      'title' => 'Click Me',
      'description' => '',
      'icon' => 'wordpress', // Replace with the icon of your choice
      'onclick' => "window.open('https://your-link-here.com', '_blank')", // Add your desired action here (e.g., open a new window)
    ];
    return $settings;
}
add_filter('block_editor_settings_all', 'add_button_to_gutenberg_toolbar');

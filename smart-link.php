<?php
/*
 * Plugin Name: Smart Link
 * Description: Generate one-time-use links that expire after 24 hours.
 * Version: 0.5.2
 * Author: Smart Link Pro
 * Author URI: https://smartlinkpro.io
 * Text Domain: smart-link
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */


if (!defined('ABSPATH')) {
    exit;
}

// Register copy private link button
function sl_load_column() {
    require_once plugin_dir_path(__FILE__) . '/classes/sl_columns.php';
    $columns = new SLColumns();
    $columns->sl_add();
}
add_action('wp_loaded', 'sl_load_column');

// add a link to the WP Toolbar
function custom_toolbar_link($wp_admin_bar) {
    $args = array(
        'id' => 'smartLinkPro',
        'title' => 'Smart Link Pro', 
        'href' => 'https://smartlinkpro.io', 
        'meta' => array(
            'class' => 'slp', 
            'title' => 'Go to Smart Link Pro'
            )
    );
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'custom_toolbar_link', 999);


//include the main dashboard page
require_once(plugin_dir_path(__FILE__) . '/pages/slp_dashboard.php');

//include the ajax handler file
require_once(plugin_dir_path(__FILE__) . '/includes/sl_ajax_handler.php');

//plugin setup
register_activation_hook(__FILE__, 'sl_plugin_activate');

function sl_plugin_activate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/sl_activate.php';
    sl_activate_plugin();
    flush_rewrite_rules();
}

// drop db table on deletion
register_uninstall_hook(__FILE__, 'sl_plugin_uninstall');

function sl_plugin_uninstall()
{
    
    global $wpdb;
    
    // Grab tables to be dropped
    $tokens = $wpdb->prefix . 'sl_tokens';
    
    // Execute queries
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $result = $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i", $tokens));
    
    if( is_wp_error($result) ) {
        wp_die(
            'Failed to drop table: ' . esc_html($tokens) . '. Error: ' . esc_html($result->get_error_message()),
            'Database Error',
            array('back_link' => true)
        );
    }

    // Delete access denied page
    $access_denied_page = get_page_by_path('access-denied');
    if ($access_denied_page) {
        wp_delete_post($access_denied_page->ID, true);
    }
}

function sl_enable_token_check() {
    if (!is_admin()) {
        require_once(plugin_dir_path(__FILE__) . '/includes/sl_check_access_rewrite.php');
    }
}
add_action('init', 'sl_enable_token_check');

//add admin menu item
function sl_admin_menu()
{
    $icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gQ3JlYXRlZCB3aXRoIElua3NjYXBlIChodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy8pIC0tPgoKPHN2ZwogICB3aWR0aD0iNTM0LjY0NDM1IgogICBoZWlnaHQ9IjY4OS4yMjg4MiIKICAgdmlld0JveD0iMCAwIDE0MS40NTc5OCAxODIuMzU4NDYiCiAgIHZlcnNpb249IjEuMSIKICAgaWQ9InN2ZzEiCiAgIHhtbDpzcGFjZT0icHJlc2VydmUiCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnMKICAgICBpZD0iZGVmczEiIC8+PGcKICAgICBpZD0ibGF5ZXIxIgogICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKC03OS4xNDE0MjYsLTI0LjYwODk0OCkiPjxwYXRoCiAgICAgICBpZD0icGF0aDItMi01LTctNCIKICAgICAgIHN0eWxlPSJmaWxsOiM3ZDdkN2Q7ZmlsbC1vcGFjaXR5OjE7c3Ryb2tlOm5vbmU7c3Ryb2tlLXdpZHRoOjAuOTM2MzU3O3N0cm9rZS1saW5lY2FwOnJvdW5kO3N0cm9rZS1kYXNoYXJyYXk6bm9uZTtzdHJva2Utb3BhY2l0eToxIgogICAgICAgZD0ibSA3OS4xNDE0MTksMTg2LjUxNzE2IGMgNDMuNTYzMDAxLC00My41NjMgNzkuNDgxMTMxLC03OS40ODExNSA5OC43NDI1MTEsLTk4Ljc0MjUwMSAyNy4yNjY5OSwyNy4yNjY5OTEgMjcuMjY2OTksNzEuNDc1NTExIDAsOTguNzQyNTAxIC0yNy4yNjcwMSwyNy4yNjY5OSAtNzEuNDc1NTIsMjcuMjY2OTkgLTk4Ljc0MjUxMSwwIHoiIC8+PHBhdGgKICAgICAgIGlkPSJwYXRoMi03LTgtMCIKICAgICAgIHN0eWxlPSJmaWxsOiM1MjUyNTI7ZmlsbC1vcGFjaXR5OjE7c3Ryb2tlOm5vbmU7c3Ryb2tlLXdpZHRoOjEwLjU0MTU7c3Ryb2tlLWxpbmVjYXA6cm91bmQ7c3Ryb2tlLWRhc2hhcnJheTpub25lO3N0cm9rZS1vcGFjaXR5OjEiCiAgICAgICBkPSJtIDIyMC41OTk0MSw0NS4wNTkxODkgYyAtNDMuNTYzLDQzLjU2MyAtNzkuNDgxMTIsNzkuNDgxMTMxIC05OC43NDI1MSw5OC43NDI0OTEgLTI3LjI2Njk5MSwtMjcuMjY2OTkgLTI3LjI2Njk5MSwtNzEuNDc1NDkxIDAsLTk4Ljc0MjQ5MSAyNy4yNjcsLTI3LjI2Njk5IDcxLjQ3NTUyLC0yNy4yNjY5OSA5OC43NDI1MSwwIHoiIC8+PC9nPjwvc3ZnPgo=';
    add_menu_page(
        'Smart Link', //page title
        'Smart Link', //menu title
        'manage_options', //capability
        'slp-dashboard', //menu slug
        'slp_admin_page', //function to render the page
        $icon //icon (optional)
    );
    add_submenu_page(
        'slp-dashboard', //parent slug
        'Link Dashboard', //page title
        'Link Dashboard', //menu title
        'manage_options', //capability
        'slp-dashboard', //menu slug
        'slp_admin_page', //function to render the page
        1 //menu position
    );
}
add_action('admin_menu', 'sl_admin_menu');



//enqueue stylesheet on admin settings page
function sl_admin_styles()
{
    // wp_enqueue_style('pl_style', plugin_dir_url(__FILE__) . 'css/pl-style.css', array(), '1.1', 'all');
    // wp_enqueue_style('bootstrap5', plugin_dir_url(__FILE__) . 'node_modules/bootstrap/dist/css/bootstrap.min.css', array(), '5.3.3', 'all');
    // wp_enqueue_style('sl_toastify_css', plugin_dir_url(__FILE__) . 'node_modules/toastify-js/src/toastify.css', array(), '1.12.0');
    // wp_enqueue_script('sl_toastify_js', plugin_dir_url(__FILE__) . 'node_modules/toastify-js/src/toastify.js', array(), '1.12.0', true);
    // wp_enqueue_script('copy-private-link', plugin_dir_url(__FILE__) . 'js/copyPrivateLink.js', array('jquery'), '1.0.7', null, true);

    // wp_enqueue_style('sl_style', plugin_dir_url(__FILE__) . 'dist/main.css', array(), '1.0.1', 'all');
    // wp_enqueue_script('copy-private-link', plugin_dir_url(__FILE__) . 'dist/main.js', array('vendor-js'), '1.0.1', true);
    // wp_enqueue_script('vendor-js', plugin_dir_url(__FILE__) . 'dist/vendor.js', array(), '1.0.1', true);

    wp_enqueue_style('sl_style', plugin_dir_url(__FILE__) . 'css/main.css', array(), '1.0.1', 'all');
    wp_enqueue_script('copy-private-link', plugin_dir_url(__FILE__) . 'js/main.js', array('jquery'), '1.0.1', true);
    wp_enqueue_script('toastify-js', plugin_dir_url(__FILE__) . 'js/toastify.js', array(), '1.0.1', true);

    wp_localize_script('copy-private-link', 'sl_ajax_object', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('sl_nonce')
    ));

}
add_action('admin_enqueue_scripts', 'sl_admin_styles');

function sl_send_nonce()
{
    wp_enqueue_script('sl_nonce', plugin_dir_url(__FILE__) . 'js/sendNonce.js', array('jquery'), '1.0.4', true);
    wp_localize_script('sl_nonce', 'sl_ajax_object', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('sl_check_token')
    ));
}
add_action('wp_enqueue_scripts', 'sl_send_nonce');

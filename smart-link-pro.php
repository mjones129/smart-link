<?php
/*
 * Plugin Name: Smart Link Pro
 * Description: Generate one-time-use links that expire after 24 hours.
 * Version: 0.4.41
 * Author: Smart Link Pro
 * Author URI: https://smartlinkpro.io
 */

if (!defined('ABSPATH')) {
    exit; //Exit if accessed directly
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


//include the smtp settings page
// require_once(plugin_dir_path(__FILE__) . '/pages/smtp-settings.php');

//include the main dashboard page
require_once(plugin_dir_path(__FILE__) . '/pages/slp_dashboard.php');

//include the ajax handler file
require_once(plugin_dir_path(__FILE__) . '/includes/sl_store_data.php');

//plugin setup
register_activation_hook(__FILE__, 'sl_plugin_activate');

function sl_plugin_activate()
{
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

//redirect if first time?


// Check user token for page access
function sl_check_access_token()
{
    global $wpdb;

    // error_log("sl_check_access_token function called");

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
add_action('template_redirect', 'sl_check_access_token'); //TODO: check if this is the right hook

//add admin menu item
function sl_admin_menu()
{
    $icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gQ3JlYXRlZCB3aXRoIElua3NjYXBlIChodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy8pIC0tPgoKPHN2ZwogICB3aWR0aD0iNTM0LjY0NDM1IgogICBoZWlnaHQ9IjY4OS4yMjg4MiIKICAgdmlld0JveD0iMCAwIDE0MS40NTc5OCAxODIuMzU4NDYiCiAgIHZlcnNpb249IjEuMSIKICAgaWQ9InN2ZzEiCiAgIHhtbDpzcGFjZT0icHJlc2VydmUiCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnMKICAgICBpZD0iZGVmczEiIC8+PGcKICAgICBpZD0ibGF5ZXIxIgogICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKC03OS4xNDE0MjYsLTI0LjYwODk0OCkiPjxwYXRoCiAgICAgICBpZD0icGF0aDItMi01LTctNCIKICAgICAgIHN0eWxlPSJmaWxsOiM3ZDdkN2Q7ZmlsbC1vcGFjaXR5OjE7c3Ryb2tlOm5vbmU7c3Ryb2tlLXdpZHRoOjAuOTM2MzU3O3N0cm9rZS1saW5lY2FwOnJvdW5kO3N0cm9rZS1kYXNoYXJyYXk6bm9uZTtzdHJva2Utb3BhY2l0eToxIgogICAgICAgZD0ibSA3OS4xNDE0MTksMTg2LjUxNzE2IGMgNDMuNTYzMDAxLC00My41NjMgNzkuNDgxMTMxLC03OS40ODExNSA5OC43NDI1MTEsLTk4Ljc0MjUwMSAyNy4yNjY5OSwyNy4yNjY5OTEgMjcuMjY2OTksNzEuNDc1NTExIDAsOTguNzQyNTAxIC0yNy4yNjcwMSwyNy4yNjY5OSAtNzEuNDc1NTIsMjcuMjY2OTkgLTk4Ljc0MjUxMSwwIHoiIC8+PHBhdGgKICAgICAgIGlkPSJwYXRoMi03LTgtMCIKICAgICAgIHN0eWxlPSJmaWxsOiM1MjUyNTI7ZmlsbC1vcGFjaXR5OjE7c3Ryb2tlOm5vbmU7c3Ryb2tlLXdpZHRoOjEwLjU0MTU7c3Ryb2tlLWxpbmVjYXA6cm91bmQ7c3Ryb2tlLWRhc2hhcnJheTpub25lO3N0cm9rZS1vcGFjaXR5OjEiCiAgICAgICBkPSJtIDIyMC41OTk0MSw0NS4wNTkxODkgYyAtNDMuNTYzLDQzLjU2MyAtNzkuNDgxMTIsNzkuNDgxMTMxIC05OC43NDI1MSw5OC43NDI0OTEgLTI3LjI2Njk5MSwtMjcuMjY2OTkgLTI3LjI2Njk5MSwtNzEuNDc1NDkxIDAsLTk4Ljc0MjQ5MSAyNy4yNjcsLTI3LjI2Njk5IDcxLjQ3NTUyLC0yNy4yNjY5OSA5OC43NDI1MSwwIHoiIC8+PC9nPjwvc3ZnPgo=';
    add_menu_page(
        'Smart Link Pro', //page title
        'Smart Link Pro', //menu title
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

// Handle AJAX request to update first_time value?



//enqueue stylesheet on smtp settings page
function sl_smtp_styles()
{
    wp_register_style('pl_style', plugin_dir_url(__FILE__) . 'css/pl-style.css', array(), '1.1', 'all');
    wp_register_style('bootstrap5', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
    wp_enqueue_style('pl_style');
    wp_enqueue_style('bootstrap5');
    // wp_enqueue_script(
    //     'my-custom-component',
    //     plugins_url('/js/sidebar.js', __FILE__),
    //     ['wp-blocks', 'wp-i18n', 'wp-element'],
    //     false,
    //     1
    // );
    wp_enqueue_style('sl_toastify_css', 'https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.css', array(), '1.0.0');
    wp_enqueue_script('sl_toastify_js', 'https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.js', array(), '1.0.0', true);
    // wp_enqueue_script('pl_first_time_check', plugin_dir_url(__FILE__) . '/js/first-time-check.js', array('jquery'), null, true);
    wp_enqueue_script('pl_edit_page_button', plugin_dir_url(__FILE__) . '/js/editPageButton.js', array(), null, true);
    // wp_localize_script('pl_first_time_check', 'pl_ajax_object', array(
    //   'ajax_url' => admin_url('admin-ajax.php'),
    //   'nonce' => wp_create_nonce('pl_ajax_nonce'),
    //   'redirect_url' => admin_url('admin.php?page=smtp-settings')
    // ));
    wp_enqueue_script(
        'copy-private-link',
        plugin_dir_url(__FILE__) . 'js/copyPrivateLink.js',
        array('jquery'),
        '1.0.7',
        null,
        true
    );
    wp_localize_script('copy-private-link', 'sl_ajax_object', array(
      'ajax_url' => admin_url('admin-ajax.php'),
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

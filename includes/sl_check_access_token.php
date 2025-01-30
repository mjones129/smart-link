<?php
// Check user token for page access
function sl_check_token()
{

    $nonce = isset($_SERVER['HTTP_X_WP_NONCE']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_X_WP_NONCE'])) : '';

    if (!wp_verify_nonce($nonce, 'sl_check_token')) {
        wp_send_json_error('Invalid nonce from sl_check_token');
        return;
    } else {
        wp_send_json_success('Nonce accepted from sl_check_token');
    }

    global $wpdb;

    // Select all the page slugs from the tokens table
    $tokens_table = $wpdb->prefix . 'sl_tokens';

    $cache_key = 'sl_protected_slugs_cache';
    $tokens_in_database = array();


    if (!isset($tokens_in_database)) {
        $escaped_table = esc_sql($tokens_table);
        $tokens_in_database = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare("SELECT token FROM %i", $escaped_table)
        );

        // wp_cache_set($cache_key, $tokens_in_database, '', 12 * HOUR_IN_SECONDS);
    }


    // Only run on pages, not blog posts
    if (is_page()) {
        global $post;

        $current_slug = $post->post_name;

        // If current page exists in protected_slugs, check for token
        if (in_array($current_slug, $tokens_in_database)) {
            $sanitized_token = isset($_GET['access_token']) ? sanitize_text_field(wp_unslash($_GET['access_token'])) : '';
            if (!isset($sanitized_token)) {
                wp_redirect(home_url('/access-denied/'));
                exit();
            }

            $current_time = current_time('mysql');

            $token_entry = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $wpdb->prepare(
                    "SELECT * FROM %i WHERE token = %s AND expiration > %s AND used = 0",
                    $escaped_table,
                    $sanitized_token,
                    $current_time
                )
            );

            if (!$token_entry) {
                wp_redirect(home_url('/access-denied/'));
                exit();
            } else {
                // Mark token as used
                $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                    $escaped_table,
                    array('used' => 1),
                    array('id' => $token_entry->id),
                    array('%d'),
                    array('%d')
                );
            }
        }
    }
}
add_action('wp_ajax_nopriv_sl_check_token', 'sl_check_token');
?>
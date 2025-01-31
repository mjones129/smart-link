<?php
// Check user token for page access
function sl_check_token()
{
    // nonce validation
    $nonce = isset($_SERVER['HTTP_X_WP_NONCE']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_X_WP_NONCE'])) : '';

    if (!wp_verify_nonce($nonce, 'sl_check_token')) {
        wp_send_json_error('Invalid nonce from sl_check_token_rewrite');
        return;
    }

        global $wpdb;
        global $post;

        // Select all the page slugs from the tokens table
        $tokens_table = $wpdb->prefix . 'sl_tokens';
        $escaped_table = esc_sql($tokens_table);

        $links_in_database = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare("SELECT * FROM %i", $escaped_table)
        );

        $current_slug = $post->post_name;

        
        // If a link exists in the database that matches the current page, check for a token
        if (in_array($current_slug, $links_in_database)) {

            $tokens_in_database = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $wpdb->prepare("SELECT token FROM %i, $escaped_table")
            );

            $sanitized_token = isset($_GET['access_token']) ? sanitize_text_field(wp_unslash($_GET['access_token'])) : '';

            $current_time = current_time('mysql');

            if ($sanitized_token === '' || !in_array($sanitized_token, $tokens_in_database)) {
                wp_redirect(home_url('/access-denied/'));
                exit();
            } 

            //elseif check if token is expired

            

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
add_action('wp_ajax_nopriv_sl_check_token', 'sl_check_token');

<?php
// Check user token for page access
function sl_check_token()
{

    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

    if (! isset($_POST['nonce']) || ! wp_verify_nonce($nonce, 'sl-check-token')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    global $wpdb;

    // Select all the page slugs from the tokens table
    $tokens_table = $wpdb->prefix . 'sl_tokens';

    $cache_key = 'sl_protected_slugs_cache';
    $protected_slugs = wp_cache_get($cache_key);

    if ($protected_slugs === false) {
        $escaped_table = esc_sql($tokens_table);
        $protected_slugs = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare("SELECT slug FROM %i", $escaped_table)
        );

        wp_cache_set($cache_key, $protected_slugs);
    }


    // Only run on pages, not blog posts
    if (is_page()) {
        global $post;

        $current_slug = $post->post_name;

        // If current page exists in protected_slugs, check for token
        if (in_array($current_slug, $protected_slugs)) {
            $sanitized_token = isset($_GET['access_token']) ? sanitize_text_field(wp_unslash($_GET['access_token'])) : '';
            if (!isset($sanitized_token)) {
                wp_redirect(home_url('/access-denied/'));
                exit();
            }

            $token = sanitize_text_field(wp_unslash($_GET['access_token']));
            // $sanitized_token = esc_html(wp_unslash($token));
            $current_time = current_time('mysql');

            $token_entry = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $wpdb->prepare(
                    "SELECT * FROM %i WHERE token = %s AND expiration > %s AND used = 0",
                    $escaped_table,
                    $token,
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
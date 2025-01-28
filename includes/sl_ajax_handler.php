<?php

add_action('wp_ajax_sl_save_token', 'sl_save_token');

function sl_save_token()
{
    global $wpdb;

    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    $page_id = isset($_POST['page_id']) ? sanitize_text_field(wp_unslash($_POST['page_id'])) : '';

    if (! isset($_POST['nonce']) || ! wp_verify_nonce(wp_unslash($nonce), 'sl-copy-link_' . $page_id)) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';

    $slug = isset($_POST['slug']) ? sanitize_text_field(wp_unslash($_POST['slug'])) : '';
    $current_time = isset($_POST['current_time']) ? sanitize_text_field(wp_unslash($_POST['current_time'])) : '';


    //Calculate expiration time (24 hours from current time)
    $expiration_time = gmdate('Y-m-d H:i:s', strtotime($current_time . ' + 24 hours'));

    $tokens_table = $wpdb->prefix . 'sl_tokens';

    $escaped_table = esc_sql($tokens_table);

    $inserted = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery 
        $wpdb->prepare(
            "INSERT INTO %i (page_ID, token, slug, expiration) VALUES (%d, %s, %s, %s)",
            $escaped_table,
            $page_id,
            $token,
            $slug,
            $expiration_time
        )
    );
    
    if ($inserted) {
        wp_send_json_success('Data inserted successfully.');
    } else {
        wp_send_json_error('Error inserting data.');
    }
}

add_action('wp_ajax_sl_delete_token', 'sl_delete_token');

function sl_delete_token()
{
    global $wpdb;

    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    $token_id = isset($_POST['token_id']) ? sanitize_text_field(wp_unslash($_POST['token_id'])) : '';

    if (! isset($_POST['nonce']) || ! wp_verify_nonce($nonce, 'sl-delete-link_' . $token_id)) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    $id = isset($_POST['token_id']) ? sanitize_text_field(wp_unslash($_POST['token_id'])) : '';

    $tokens_table = $wpdb->prefix . 'sl_tokens';
    $escaped_table = esc_sql($tokens_table);

    $deleted = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->prepare(
            "DELETE FROM %i WHERE ID = %d",
            $escaped_table,
            $id
        )
    ); 

    if (! $deleted) {
        wp_send_json_error('Error deleting data: ' . $wpdb->last_error);
        return;
    }
    wp_send_json_success('Data Deleted!');
}

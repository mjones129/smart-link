<?php

add_action( 'wp_ajax_sl_save_token', 'sl_save_token' );

function sl_save_token() {
    global $wpdb;

    if( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'sl-copy-link_' . $_POST['page_id'] ) ) {
        wp_send_json_error( 'Invalid nonce' );
        return;
    }

    $token = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';
    $page_id = isset( $_POST['page_id'] ) ? sanitize_text_field( $_POST['page_id'] ) : '';
    $current_time = isset( $_POST['current_time'] ) ? sanitize_text_field( $_POST['current_time'] ) : '';

    //Calculate expiration time (24 hours from current time)
    $expiration_time = date( 'Y-m-d H:i:s', strtotime( $current_time . ' + 24 hours' ) );

    $tokens_table = $wpdb->prefix . 'sl_tokens';

    $inserted = $wpdb->insert(
        $tokens_table,
        array(
            'token' => $token,
            'page_ID' => $page_id,
            'expiration' => $expiration_time,
        )
    );

    if( ! $inserted ) {
        wp_send_json_error( 'Error storing data: ' $wpdb->last_error );
        return;
    }
    wp_send_json_success( 'Data Stored!' );
}


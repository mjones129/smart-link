<?php

add_action( 'wp_ajax_store_sl_save_token', 'sl_save_token' );
add_action( 'wp_ajax_nopriv_store_sl_save_token', 'sl_save_token' );

function sl_save_token() {

    if( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'sl_save_token' ) ) {
        wp_send_json_error( 'Invalid nonce' );
        return;
    }

    global $wpdb;

    $token = sanitize_text_field( $_POST['token'] );
    $page_id = sanitize_text_field( $_POST['page_id'] );
    $current_time = sanitize_text_field( $_POST['current_time'] );

    //Calculate expiration time (24 hours from current time)
    $expiration_time = date( 'Y-m-d H:i:s', strtotime( $current_time . ' + 24 hours' ) );

    $wpdb->insert(
        $wpdb->prefix . 'sl_tokens',
        array(
            'token' => $token,
            'page_ID' => $page_id,
            'expiration' => $expiration_time,
        )
    );

    wp_send_json_success( 'Data Stored!' );
}


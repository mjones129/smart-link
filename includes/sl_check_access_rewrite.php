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

    sl_get_links();
}
add_action('wp_ajax_nopriv_sl_check_token', 'sl_check_token');


function sl_get_links() {
    global $wpdb;
    $escaped_table = esc_sql($wpdb->prefix . 'sl_tokens');
    $link_query = $wpdb->prepare("SELECT * FROM %i;", $escaped_table);
    $sl_links = $wpdb->get_results($link_query, ARRAY_A);
    if (!empty($sl_links)) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Page ID</th><th>Slug</th><th>Token</th><th>Expiration</th><th>Used</th></tr>";

        foreach($sl_links as $link) {
            echo "<tr>";
            echo "<td>" . esc_html($link['id']) . "</td>";
            echo "<td>" . esc_html($link['page_ID']) . "</td>";
            echo "<td>" . esc_html($link['slug']) . "</td>";
            echo "<td>" . esc_html($link['token']) . "</td>";
            echo "<td>" . esc_html($link['expiration']) . "</td>";
            echo "<td>" . esc_html($link['used']) . "</td>";
        }
        echo "</table>";
    }
}
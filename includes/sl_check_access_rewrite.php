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

    // sl_token_checker();

}
add_action('wp_ajax_nopriv_sl_check_token', 'sl_check_token');


function sl_get_links() {
    global $wpdb;
    $escaped_table = esc_sql($wpdb->prefix . 'sl_tokens');
    $link_query = $wpdb->prepare("SELECT * FROM %i;", $escaped_table);
    $sl_links = $wpdb->get_results($link_query, ARRAY_A);
    if (!empty($sl_links)) {

        // echo "<table>";
        // echo "<tr><th>ID</th><th>Page ID</th><th>Slug</th><th>Token</th><th>Expiration</th><th>Used</th></tr>";

        // foreach($sl_links as $link) {
        //     echo "<tr>";
        //     echo "<td>" . esc_html($link['id']) . "</td>";
        //     echo "<td>" . esc_html($link['page_ID']) . "</td>";
        //     echo "<td>" . esc_html($link['slug']) . "</td>";
        //     echo "<td>" . esc_html($link['token']) . "</td>";
        //     echo "<td>" . esc_html($link['expiration']) . "</td>";
        //     echo "<td>" . esc_html($link['used']) . "</td>";
        // }
        // echo "</table>";
        return $sl_links;
    }
}

$link_check = sl_get_links();
if (empty($link_check)) {
    wp_send_json_success('No smart links in database');
} else {
    $sl_links = sl_get_links();
}

$sl_token_validation = array();

$match_found = false;

function sl_is_slug_protected() {
    global $sl_links, $sl_token_validation, $match_found;
    $current_slug = isset($_POST['slug']) ? sanitize_text_field(wp_unslash($_POST['slug'])) : '';
    $total_links = count($sl_links);
    for ($i = 0; $i < $total_links; $i++) {
        if($current_slug === $sl_links[$i]['slug']) {
            $match_found = true;
            $sl_token_validation['slug'][$current_slug];
            break;
        }
    }
    wp_send_json_success('Unprotected slug');
}


$global_index = 0;
$index_match = null;

function sl_is_token_valid() {
    global $sl_links, $index_match, $sl_token_validation, $match_found;
    $user_token = isset($_POST['access_token']) ? sanitize_text_field(wp_unslash($_POST['access_token'])) : '';
    if ($match_found && $user_token == '') {
        wp_send_json_error('No access token provided.');
    }
    if($match_found) {
        for ($global_index = 0; $global_index <= count($sl_links); $global_index++) {
            if($user_token === $sl_links[$global_index]['token']) {
                $index_match = $global_index;
                $sl_token_validation['token'][$sl_links[$global_index]['token']];
                break;
            }
        }
    }
}

function sl_expiration_still_valid() {
    global $sl_links, $index_match, $sl_token_validation, $match_found;
    $current_time = current_time('mysql');
    if($match_found) {
        if($current_time <= $sl_links[$index_match]['expiration']) {
            $sl_token_validation['expiration'][0];
            return $index_match;
        }        
    }
}

function sl_token_checker() {
    global $sl_token_validation, $sl_links, $match_found;
    //check the slug
    sl_is_slug_protected();
    //check for a token
    sl_is_token_valid();
    //check expiration time
    $index_match = sl_expiration_still_valid();
    if ($index_match) {
        if($match_found) {
            if($sl_links[$index_match]['slug'] === $sl_token_validation['slug'] && $sl_links[$index_match]['token'] === $sl_token_validation['token'] && $sl_token_validation['expiration'] === 0) {
                wp_send_json_success('Token accepted.');
            }
        }
    } else {
        wp_send_json_error('No index was matched.');
    }
}
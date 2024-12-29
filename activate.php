<?php

function sl_activate_plugin()
{

    require_once(plugin_dir_path(__FILE__) . '/classes/slinit.class.php');

    $init = new SLinit();

    $init->sl_create_tokens_table();

    // $init->sl_create_smtp_table();

    // $init->sl_generate_encryption_key();

    

}


function sl_add(){
    add_filter('post_row_actions', array($this, 'sl_add_link_button'), 10, 2);
    add_filter('page_row_actions', array($this, 'sl_add_link_button'), 10, 2);
}

function sl_add_link_button($actions, $post){
    $actions['clear_cache_link'] = '<a data-id="'.$post->ID.'" data-nonce="'.wp_create_nonce('clear-cache_'.$post->ID).'" id="wpfc-clear-cache-link-'.$post->ID.'" style="cursor:pointer;">' . __('Clear Cache') . '</a>';
    return $actions;
}
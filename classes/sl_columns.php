<?php

class SLColumns
{
    public function sl_add()
    {
        // add_filter('post_row_actions', array($this, 'sl_add_link_button'), 10, 2);
        add_filter('page_row_actions', array($this, 'sl_add_link_button'), 10, 2);
    }

    public function sl_add_link_button($actions, $post)
    {
        $actions['sl_copy_link'] = '<a data-id="' . $post->ID . '" data-nonce="' . wp_create_nonce('sl-copy-link_' . $post->ID) . '" id="sl-copy-link-' . $post->ID . '" style="cursor:pointer; color: #0d6efd;">' . __('Copy Smart Link', 'smart-link') . '</a>';
        return $actions;
    }
}

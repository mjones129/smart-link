<?php

function sl_activate_plugin()
{

    require_once(plugin_dir_path(__FILE__) . '/classes/slinit.class.php');

    $init = new SLinit();

    $init->sl_create_tokens_table();

}



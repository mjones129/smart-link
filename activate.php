<?php

function sl_activate_plugin() {

require_once(plugin_dir_path(__FILE__) . '/classes/sl-email-template.php');

require_once(plugin_dir_path(__FILE__) . '/classes/slinit.class.php');

$init = new SLinit();

$init->sl_create_tokens_table();

$init->sl_create_smtp_table();

$init->sl_generate_encryption_key();    

sl_register_email_template();

}

// Ensure the function is called during plugin activation
sl_activate_plugin();



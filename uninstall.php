<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;

// Grab tables to be dropped
$tokens = $wpdb->prefix . 'pl_tokens';
$creds = $wpdb->prefix . 'pl_smtp_creds';

// Query to drop tables
$sql1 = "DROP TABLE IF EXISTS $tokens";
$sql2 = "DROP TABLE IF EXISTS $creds";

// Execute queries
$wpdb->query($sql1);
$wpdb->query($sql2);

// Remove encryption key
$keypath = ABSPATH . 'wp-config.php';
$pattern = "/define\('PL_ENCRYPTION_KEY',\s*'[a-f0-9]{64}'\s*\);/";

if (is_writable($keypath)) {
    $configfile = file_get_contents($keypath);
    
    // Check if encryption key exists
    if (strpos($configfile, "define('PL_ENCRYPTION_KEY'") !== false) {
        // Replace the entire line with an empty string
        $newfile = preg_replace($pattern, '', $configfile);
        
        if (file_put_contents($keypath, $newfile) !== false) {
            error_log('Cleanup successful.');
        } else {
            error_log('Cleanup failed.');
            wp_die('Failed to clean up config.');
        }
    }
}
?>


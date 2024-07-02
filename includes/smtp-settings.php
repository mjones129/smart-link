<?php

function pl_encrypt_password($smtp_password) {
    $encryption_key = PL_ENCRYPTION_KEY;
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted_password = openssl_encrypt($smtp_password, 'aes-256-cbc', $encryption_key, 0, $iv);
    return base64_encode($iv . $encrypted_password);
}

function pl_insert_smtp_data ($smtp_host, $smtp_email, $hashed_pw, $smtp_password, $smtp_port) {
  //global wordpress db object
  global $wpdb;
  
  //define data to insert
  $creds_info = array(
    'host' => $smtp_host,
    'port' => $smtp_port,
    'username' => $smtp_email,
    'password' => $hashed_pw
  );
  //specify data types
  $data_types = array(
    '%s', //string
    '%d', //integer
    '%s', //string
    '%s' //string
  );


  //update row 1
  $wpdb->update(
    $wpdb->prefix . 'pl_smtp_creds',
    // specify data to insert
    $creds_info,
    //define exactly which row to update
    array(
      'id' => 1
    ),
    //specify data types
    $data_types
    );

}

function pl_render_smtp_settings_page() {
?>
<div class="wrap">
<h1>Enter Your SMTP Settings.</h1>
<form method="post" action="">
<?php wp_nonce_field('smtp_settings_nonce', 'smtp_settings_nonce_field'); ?>
<table class="form-table">
<tr>
<th>SMTP Host</th>
<td><input type="text" name="smtp_host" required /></td>
</tr>
<tr>
<th>SMTP Port Number</th>
<td><input type="number" name="smtp_port" required /></td>
</tr>
<tr>
<th>SMTP username/email</th>
<td><input type="email" name="smtp_email" required /></td>
</tr>
<tr>
<th>SMTP password</th>
<td><input type="password" name="smtp_password" required /></td>
</tr>
<tr>
<td><input type="submit" name="submit" class="button-primary" value="Save Settings"</td>
</tr>
</table>
</form>
</div>
<?php 

  if(isset($_POST['submit']) && check_admin_referer('smtp_settings_nonce', 'smtp_settings_nonce_field')) {
    $smtp_host = sanitize_text_field($_POST['smtp_host']);
    $smtp_port = intval($_POST['smtp_port']);
    $smtp_email = sanitize_email($_POST['smtp_email']);
    $smtp_password = sanitize_text_field($_POST['smtp_password']);
  } else {
    $smtp_host = '';
    $smtp_port = '';
    $smtp_email = '';
    $smtp_password = '';
  }

  //encrypt password
  $hashed_pw = pl_encrypt_password($smtp_password);
  pl_insert_smtp_data($smtp_host, $smtp_email, $hashed_pw, $smtp_password, $smtp_port);

}

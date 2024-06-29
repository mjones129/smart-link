<?php
function pl_render_smtp_settings_page() {
?>
<div class="wrap">
<h1>Enter Your SMTP Settings.</h1>
<form method="post" action="">
<table class="form-table">
<tr>
<th>SMTP Host</th>
<td><input type="text" name="smtp_host" required /></td>
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

  if(isset($_POST['submit'])) {
  $smtp_host = sanitize_text_field($_POST['smtp_host']);
  $smtp_email = sanitize_text_field($_POST['smtp_email']);
  $smtp_password = sanitize_text_field($_POST['smtp_password']);
  }

  //encrypt password
  $hashed_pw = password_hash($smtp_password, PASSWORD_DEFAULT);
  encryption_test($hashed_pw, $smtp_password);

}

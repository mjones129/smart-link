<?php
function pl_render_smtp_settings_page() {
?>
<div class="wrap">
<h1>Hello world from the SMTP Settings Page!</h1>
<form>
<table class="form-table">
<tr>
<th>SMTP Host</th>
<td><input type="text" name="smtp_host" required /></td>
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
  }
}

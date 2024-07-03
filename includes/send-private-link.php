<?php
function pl_admin_page() {
?>
<style>
.table-width {
width: 500px;
}
.table-height {
height: 300px;
}
</style>
<div class="wrap">
<h1>Send Private Link Email</h1>
<form method="post" action="">
<?php wp_nonce_field('send_private_link_email_nonce', 'send_private_link_email_nonce_field'); ?>
<table class="form-table">
<tr valign="top">
<th>Send Email To:</th>
<td><input class="table-width" type="email" name="email_to" required /></td>
</tr>
<tr>
<th>Recipiant Full Name:</th>
<td><input class="table-width" type="text" name="email_to_name" required /></td>
</tr>
<tr>
<th scope="row">Email Subject:</th>
<td><input class="table-width" type="text" name="email_subject" required /></td>
</tr>
<tr>
<th>Email Body:</th>
<td><textarea class="table-height table-width" name="email_body" required ></textarea></td>
</tr>
<tr>
<th>Private Page Slug (including leading and trailing slashes):</th>
<td><input class="table-width" type="text" name="page_slug" required /></td>
</tr>
</table>
<input type="submit" name="submit" class="button-primary" value="Send Email" />
</form>
</div>
<?php

  if (isset($_POST['submit']) && check_admin_referer('send_private_link_email_nonce', 'send_private_link_email_nonce_field')) {
    $email_to = sanitize_email($_POST['email_to']);
    $email_to_name = sanitize_text_field($_POST['email_to_name']);
    $email_subject = sanitize_text_field($_POST['email_subject']);
    $email_body = sanitize_text_field($_POST['email_body']);
    $page_slug = sanitize_text_field($_POST['page_slug']);

    if ($email_to && $page_slug && $email_body) {
      pl_send_private_link_email($email_to, $email_subject, $email_body, $page_slug, $email_to_name);
      echo '<div class="notice notice-success is-dismissible"><p>Email sent successfully!</p></div>';
    } else {
      echo '<div class="notice notice-error is-dismissible"><p>Invalid input. Please try again.</p></div>';
    }
  }
}
?>

<?php
function pl_admin_page() {
?>
<div class="wrap">
<h1>Send Private Link Email</h1>
<form method="post" action="">
<table class="form-table">
<tr valign="top">
<th scope="row">User ID</th>
<td><input type="number" name="user_id" required /></td>
</tr>
<tr>
<th>User Email</th>
<td><input type="email" name="user_email" required /></td>
</tr>
<tr>
<th scope="row">Email Subject</th>
<td><input type="text" name="email_subject" required /></td>
</tr>
<tr>
<th>Private Page Slug (including leading and trailing slashes)</th>
<td><input type="text" name="page_slug" required /></td>
</tr>
</table>
<input type="submit" name="submit" class="button-primary" value="Send Email" />
</form>
</div>
<?php

  if (isset($_POST['submit'])) {
    $user_email = sanitize_email($_POST['user_email']);
    $user_id = intval($_POST['user_id']);
    $email_subject = sanitize_text_field($_POST['email_subject']);
    $page_slug = sanitize_text_field($_POST['page_slug']);

    if ($user_email && $user_id) {
      pl_send_private_link_email($user_email, $user_id, $email_subject, $page_slug);
      echo '<div class="notice notice-success is-dismissible"><p>Email sent successfully!</p></div>';
    } else {
      echo '<div class="notice notice-error is-dismissible"><p>Invalid input. Please try again.</p></div>';
    }
  }
}
?>

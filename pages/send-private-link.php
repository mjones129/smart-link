<?php
function pl_admin_page() {
?>

<!-- Modal -->
<div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Smart Link Pro Setup</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
       Thanks for installing Smart Link Pro! Enter your SMTP credentials to get started. 
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Awesome</button>
      </div>
    </div>
  </div>
</div>


<div class="wrap pl-form">
<h1>Send Private Link Email</h1>
<form method="post" action="">
<?php wp_nonce_field('send_private_link_email_nonce', 'send_private_link_email_nonce_field'); ?>
<table class="form-table">
<tr valign="top">
<th>Send Email To:</th>
<td><input class="form-control form-control-lg" type="email" name="email_to" required /></td>
</tr>
<tr>
<th>Recipiant Full Name:</th>
<td><input class="form-control form-control-lg" type="text" name="email_to_name" required /></td>
</tr>
<tr>
<th scope="row">Email Subject:</th>
<td><input class="form-control form-control-lg" type="text" name="email_subject" required /></td>
</tr>
<tr>
<th>Email Body:</th>
<td><textarea class="form-control form-control-lg" name="email_body" required ></textarea></td>
</tr>
<tr>
<th>Private Page Slug (no slashes):</th>
<td><input class="form-control form-control-lg" type="text" name="page_slug" required /></td>
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

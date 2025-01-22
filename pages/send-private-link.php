<?php
function pl_admin_page()
{
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
    <h1>Smart Link Dashboard</h1>
    <div class="row">
      <div class="col-md-6">
        <h2>Protected Links</h2>
        <table class="table table-striped">
          <thead>
            <tr>
              <th scope="col">Page ID</th>
              <th scope="col">Slug</th>
              <th scope="col">Token</th>
              <th scope="col">Expiration</th>
              <th scope="col">Smart Link</th>
              <th scope="col">Used</th>
            </tr>
          </thead>
          <tbody>
            <?php
            global $wpdb;
            //make a request to the database and get all rows from the sl_tokens table
            $tokens = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "sl_tokens");
            
            //only loop through if there are tokens
            if ($tokens) {
              foreach ($tokens as $token) {
                $smart_link = site_url() . '/?access_token=' . $token->token;
                echo "<tr>";
                echo "<td>" . $token->page_ID . "</td>";
                echo "<td>" . $token->slug . "</td>";
                echo "<td>" . $token->token . "</td>";
                echo "<td>" . $token->expiration . "</td>";
                echo "<td>" . $smart_link . "</td>";
                echo "<td>" . ($token->used ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
              }
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php }

<?php

function time_ago_or_expired($datetime, $full = false) {
  $now = new DateTime;
  $then = new DateTime($datetime);
  if ($now > $then) {
      return 'expired';
  }

  $diff = $now->diff($then);
  $days = $diff->days;
  $weeks = floor($days / 7);
  $days -= $weeks * 7;

  $string = array(
      'y' => 'year',
      'm' => 'month',
      'w' => 'week',
      'd' => 'day',
      'h' => 'hour',
      'i' => 'minute',
      's' => 'second',
  );
  $diffValues = array(
      'y' => $diff->y,
      'm' => $diff->m,
      'w' => $weeks,
      'd' => $days,
      'h' => $diff->h,
      'i' => $diff->i,
      's' => $diff->s,
  );

  foreach ($string as $k => &$v) {
      if ($diffValues[$k]) {
          $v = $diffValues[$k] . ' ' . $v . ($diffValues[$k] > 1 ? 's' : '');
      } else {
          unset($string[$k]);
      }
  }

  if (!$full) $string = array_slice($string, 0, 1);
  return $string ? implode(', ', $string) . ' from now' : 'just now';
}



function slp_admin_page()
{
?>

  <div class="wrap pl-form">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-4">
          <img src="<?php echo plugins_url('../images/smartlinklogo-512-alpha.png', __FILE__); ?>" alt="Logo" class="pl-logo">
        </div>
        <div class="col-8">
          <h1>Smart Link Dashboard</h1>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <h2>Protected Links</h2>
        <table class="table table-striped">
          <thead>
            <tr>
              <th scope="col">Page ID</th>
              <th scope="col">Slug</th>
              <th scope="col">Expiration</th>
              <th scope="col">Smart Link</th>
              <th scope="col">Used</th>
              <th scope="col">Delete</th>
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
                $url = trailingslashit(get_site_url());
                $smart_link = $url . $token->slug . '/?access_token=' . $token->token;
                $nonce = wp_create_nonce('sl-delete-link_' . $token->id);
                $id = 'sl-delete-link-' . $nonce;
                echo "<tr>";
                echo "<td>" . $token->page_ID . "</td>";
                echo "<td>" . $token->slug . "</td>";
                // echo "<td>" . $token->token . "</td>";
                echo "<td>" . time_ago_or_expired($token->expiration) . "</td>";
                echo "<td>" . $smart_link . "</td>";
                echo "<td>" . ($token->used ? 'Yes' : 'No') . "</td>";
                echo "<td><button data-nonce=\"" . $nonce . "\" class='btn btn-danger' data-token-id=" . $token->id . " id=" . $id . ">Delete</button></td>";
                echo "</tr>";

              }
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php }

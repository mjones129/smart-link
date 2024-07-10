
jQuery(document).ready(function($) {
  // Check if first_time is 1 and show the modal
  $.ajax({
    url: pl_ajax_object.ajax_url,
    method: 'POST',
    data: {
      action: 'get_first_time',
      nonce: pl_ajax_object.nonce
    },
    success: function(response) {
      if (response.success && response.data.first_time == '1') {
        // Display the Bootstrap modal
        $('#welcomeModal').modal('show');
      }
    }
  });

  // Update first_time to 0 when the modal is dismissed
  $('#welcomeModal').on('hidden.bs.modal', function() {
    $.ajax({
      url: pl_ajax_object.ajax_url,
      method: 'POST',
      data: {
        action: 'update_first_time',
        nonce: pl_ajax_object.nonce
      },
      success: function(response) {
        if (response.success) {
          console.log('first_time updated to 0');
          //Redirect to SMTP settings page
          window.location.href = pl_ajax_object.redirect_url;
        } else {
          console.error('Failed to update first_time');
        }
      }
    });

  });
});


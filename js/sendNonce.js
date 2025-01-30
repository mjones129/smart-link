console.log('sendNonce.js loaded');
jQuery(document).ready(function($) {
    

    // Example function to send nonce via custom HTTP header
    function sendNonceViaHeader() {
        const token = window.location.search.split('access_token=')[1];
        $.ajax({
            url: sl_ajax_object.ajax_url,
            type: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', sl_ajax_object.nonce);
            },
            data: {
                action: 'sl_check_token',
                token: token,
                nonce: sl_ajax_object.nonce,
                page_id: 123,
                current_time: new Date().toISOString()
            },
            success: function(response) {
                console.log(response);
            }
        });
    }

    sendNonceViaHeader();
});
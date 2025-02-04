console.log('sendNonce.js loaded');
jQuery(document).ready(function($) {
    

    // Example function to send nonce via custom HTTP header
    function sendNonceViaHeader() {
        const tokenCheck = window.location.search.split('access_token=')[1];
        const token = tokenCheck === undefined ? '' : tokenCheck;
        const slug = window.location.pathname;
        const cleanSlug = slug.replace(/^\/|\/$/g, '');
        console.log(`nonce: ${sl_ajax_object.nonce}`); 
        $.ajax({
            url: sl_ajax_object.ajax_url,
            type: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', sl_ajax_object.nonce);
            },
            data: {
                action: 'sl_check_token',
                token: token,
                slug: cleanSlug,
                nonce: sl_ajax_object.nonce,
                page_id: 123,
                current_time: new Date().toISOString()
            },
            success: function(response) {
                if(response.success) {
                    output = JSON.stringify(response)
                    console.log(`ajax response success: ${output}`);
                } else {
                    let currentURL = window.location.href;
                    if(!currentURL.includes('redirected=true')) {
                        window.location.href = '/access-denied/?redirected=true';
                        output = JSON.stringify(response);
                        console.log(`Response object: ${output}`);
                    }
                }
            },
            error: function(response) {
                output = JSON.stringify(response);
                $(".page").append(`error: ${output}`);
                console.log(`Error: ${output}`);
            }
        });
    }

    sendNonceViaHeader();
});
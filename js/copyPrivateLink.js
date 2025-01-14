if(window.attachEvent) {
    window.attachEvent('onload', sl_column_button_action);
} else {
    if(window.onload) {
        var curronload_1 = window.onload;
        var newonload_1 = function(evt) {
            curronload_1(evt);
            sl_column_button_action(evt);
        };
        window.onload = newonload_1;
    } else {
        window.onload = sl_column_button_action;
    }
}

function generateToken() {
    const array = new Uint8Array(8);
    window.crypto.getRandomValues(array);
    let token = Array.from(array, byte => ('0' + byte.toString(16)).slice(-2)).join('');
    return token;
}

function saveToken() {
    //since you can't directly write to the database from JS, we'll need to use AJAX to send the token to the server
}

async function copyPrivateLink(link) {
    try {
        await navigator.clipboard.writeText(link);
        Toastify({
            text: "Link copied successfully",
            duration: 5000,
            newWindow: true,
            close: true,
            gravity: "bottom", // `top` or `bottom`
            position: "right", // `left`, `center` or `right`
            stopOnFocus: true, // Prevents dismissing of toast on hover
            style: {
              background: "linear-gradient(to right, #00b09b, #96c93d)",
            },
            onClick: function(){} // Callback after click
          }).showToast();
    } catch (error) {
        Toastify({
            text: "Failed to copy link: " + error,
            duration: 5000,
            destination: "https://github.com/apvarun/toastify-js",
            newWindow: true,
            close: true,
            gravity: "bottom", // `top` or `bottom`
            position: "right", // `left`, `center` or `right`
            stopOnFocus: true, // Prevents dismissing of toast on hover
            style: {
              background: "linear-gradient(to right,rgb(176, 0, 0),rgb(221, 125, 15))",
            },
            onClick: function(){} // Callback after click
          }).showToast();
    }
}

function sl_column_button_action(){
    // this regex will match any element with an id that starts with "sl-copy-link-" and ends with a number
        jQuery('a[id^="sl-copy-link-"]').filter(function() {
            return this.id.match(/^sl-copy-link-\d+$/);
        }).on("click", function(e){
            let page_id = jQuery(e.target).attr("data-id");
            let nonce = jQuery(e.target).attr("data-nonce");
            let currentTime = new Date().toISOString();
            // this ajax call will convert the post id from the data attribute and return the matching private link for that post id
            jQuery.ajax({
                url: `/wp-json/wp/v2/pages/${page_id}/`,
                type: 'GET',
                error: () => {alert("That didn't work. Please ensure this page is public and published before attempting to copy the secured link."); }, //TODO: make this a Toastify error
                success: (response) => {
                    let storedToken = generateToken();
                    let privateLink = `${window.location.origin}/${response.slug}?access_token=${storedToken}`;
                    copyPrivateLink(privateLink);
                    // this ajax call will save the token to the database for later use
                    jQuery.ajax({
                        url: sl_ajax_object.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'sl_save_token',
                            nonce: nonce,
                            token: storedToken,
                            page_id: page_id,
                            current_time: currentTime
                        },
                        success: (response) => {
                            Toastify({
                                text: "Link stored successfully",
                                duration: 5000,
                                newWindow: true,
                                close: true,
                                gravity: "bottom", // `top` or `bottom`
                                position: "right", // `left`, `center` or `right`
                                stopOnFocus: true, // Prevents dismissing of toast on hover
                                style: {
                                  background: "linear-gradient(to right, #00b09b, #96c93d)",
                                },
                                onClick: function(){} // Callback after click
                              }).showToast();
                        },
                        error: function(error) {
                            Toastify({
                                text: "Failed to store link: " + error,
                                duration: 5000,
                                destination: "",
                            }).showToast();
                        }
                    })
                },
            });
        
        });
}



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
        console.log('Link copied to clipboard!');
    } catch (error) {
        console.error('Failed to copy link: ', error);
    }
}

function sl_column_button_action(){
        jQuery('a[id^="sl-copy-link-"]').filter(function() {
            return this.id.match(/^sl-copy-link-\d+$/);
        }).on("click", function(e){
            let post_id = jQuery(e.target).attr("data-id");
            let nonce = jQuery(e.target).attr("data-nonce");

            jQuery.ajax({
                url: `https://mattjones.tech/wp-json/wp/v2/pages/${post_id}/`,
                type: 'GET',
                error: () => {alert("That didn't work. Please ensure this page is public and published before attempting to copy the secured link."); },
                success: (response) => {
                    let storedToken = generateToken();
                    // console.log(`random token: ${storedToken}`)
                    // console.log(`response slug: ${response.slug}`)
                    // console.log(`full private link: ${window.location.origin}/${response.slug}?access_token=${storedToken}`)
                    let privateLink = `${window.location.origin}/${response.slug}?access_token=${storedToken}`;
                    copyPrivateLink(privateLink);
                }
            });
        
        });
}

// button.addEventListener("click", () => writeClipboardText("<empty clipboard>"));

// async function writeClipboardText(text) {
//   try {
//     await navigator.clipboard.writeText(text);
//   } catch (error) {
//     console.error(error.message);
//   }
// }

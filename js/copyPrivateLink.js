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
function sl_column_button_action(){
        jQuery('a[id^="sl-copy-link-"]').filter(function() {
            return this.id.match(/^sl-copy-link-\d+$/);
        }).on("click", function(e){
            let post_id = jQuery(e.target).attr("data-id");
            let nonce = jQuery(e.target).attr("data-nonce");
        
            alert(`post ID: ${post_id}`);
        });
}

button.addEventListener("click", () => writeClipboardText("<empty clipboard>"));

async function writeClipboardText(text) {
  try {
    await navigator.clipboard.writeText(text);
  } catch (error) {
    console.error(error.message);
  }
}

<?php
function get_include_contents($filename, $variablesToMakeLocal) {
    extract($variablesToMakeLocal);
    if (is_file($filename)) {
        ob_start();
        include $filename;
        return ob_get_clean();
    }
    return false;
}

$mail->IsHTML(true);    // set email format to HTML
$mail->Subject = "You have an event today";
$mail->Body = get_include_contents('../emails/event.php', $data); // HTML -> PHP!
$mail->Send(); // send message

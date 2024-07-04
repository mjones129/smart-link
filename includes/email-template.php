<?php
ob_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $email_subject; ?></title>
</head>
<body>
    <p>Dear <?php echo $email_to_name; ?>,</p>
    <p><?php echo $email_body; ?></p>
    <p>Here is your private link: <a href="<?php echo $private_link; ?>"><?php echo $private_link; ?></a></p>
</body>
</html>

<?php
$content = ob_get_clean();
return $content;
?>


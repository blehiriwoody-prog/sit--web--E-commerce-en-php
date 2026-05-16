<?php
session_start();
session_destroy();
header('Location: /ecommerce-php/index.php');
exit;
?>

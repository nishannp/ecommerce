<?php
$password = 'suraj';
$hashed = password_hash($password, PASSWORD_DEFAULT);
echo $hashed;
?>
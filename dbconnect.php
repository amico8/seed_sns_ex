<?php
// DB接続準備
$db = mysqli_connect('localhost', 'root', 'mysql', 'seed_sns') or die(mysqli_connect_error());
mysqli_set_charset($db, 'utf8');

?>
<?php
session_start();

// 定型文なので、あまり中の意味まで説明、理解する必要ない
// http://php.net/manual/ja/function.session-destroy.php

$_SESSION = array();

if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(),
  			'',
  			time() - 42000,
  			$params['pasth'],
  			$params['domain'],
  			$params['secure'],
  			$params['httponly']);
}
session_destroy();

// Cookie情報も削除
setcookie('email', '', time() - 3600);
setcookie('password', '', time() - 3600);

header('Location: login.php');
exit();
?>
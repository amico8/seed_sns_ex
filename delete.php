<?php
session_start();
require('dbconnect.php');

// ログインしているかチェックする
if (isset($_SESSION['member_id']) && $_SESSION['time'] + 3600 > time()) {
  $tweet_id = $_REQUEST['tweet_id'];

  // 指定されたつぶやきが、ログインしているユーザのものかチェック
  $sql = sprintf('SELECT * FROM `tweets` WHERE `tweet_id` = %d',
  	mysqli_real_escape_string($db, $tweet_id)
  );
  $record = mysqli_query($db, $sql) or die(mysqli_error($db));
  $table = mysqli_fetch_assoc($record);

  // 一致したら削除する
  if ($table['member_id'] == $_SESSION['member_id']) {
    $sql = sprintf('DELETE FROM `tweets` WHERE `tweet_id` = %d',
 	  mysqli_real_escape_string($db, $tweet_id)
    );
    mysqli_query($db, $sql) or die(mysqli_error($db));
  }
}

header('Location: index.php');
exit();
?>
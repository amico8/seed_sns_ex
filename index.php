<?php
session_start();

require('dbconnect.php');

// htmlspecialcharsのショートカット
function h($value){
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// つぶやき内にURLがあったらリンクを設定する
function makeLink($value){
  return mb_ereg_replace('(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)','<a href="\1\2" target="_blank">\1\2</a>', $value);
}

// ログイン中の条件
// セッションにmember_idが入っている・ログインした時間が1時間以内
if (isset($_SESSION['member_id']) && $_SESSION['time'] + 3600 > time()) {
  // セッションの時間を更新
  $_SESSION['time'] = time();
  // 名前を表示させるため実行
  $sql = sprintf('SELECT * FROM `members` WHERE `member_id` = %d',
   mysqli_real_escape_string($db, $_SESSION['member_id']));

  $record = mysqli_query($db, $sql) or die(mysqli_error());
  $member = mysqli_fetch_assoc($record);

} else {
  // ログインしてないのでログインページヘ
  header('Location: login.php');
  exit();
}

// 投稿するボタンクリック時
if (!empty($_POST)) {
  // つぶやきが空じゃなかったらDBに登録
  if ($_POST['tweet'] != '') {
    $sql = sprintf('INSERT INTO `tweets` SET `tweet`="%s",`member_id`=%d, `reply_tweet_id`=%d, `created`=now()',
    mysqli_real_escape_string($db, $_POST['tweet']),
    mysqli_real_escape_string($db, $member['member_id']),
    mysqli_real_escape_string($db, $_POST['reply_tweet_id'])
    );

    mysqli_query($db, $sql) or die(mysqli_error());
    // リロードでの重複登録を防ぐ
    header('Location: index.php');
    exit();
  }
}

// ページング処理
$page = '';
// URLに?page=2などのパラメータがある場合、それを取得して$pageに格納
if (isset($_REQUEST['page'])) {
  $page = $_REQUEST['page'];
}
if ($page == '') {
  $page = 1;
}
// max関数：()内に指定した複数のデータから、一番大きい値を取得する
// もしユーザーがURLに?page=0.8のような値を入れてリクエストした場合に、強制的に1ページ目にとぶように処理している
$page = max($page, 1);


// 投稿を取得する
$sql = sprintf('SELECT m.nick_name, m.picture_path, t.* FROM members m, tweets t WHERE m.member_id = t.member_id ORDER BY t.created DESC');
$tweets = mysqli_query($db,$sql) or die(mysqli_error($db));

// while ($tweet = mysqli_fetch_assoc($tweets)) {
//   echo htmlspecialchars($tweet['picture_path'], ENT_QUOTES, 'UTF-8');
//   echo htmlspecialchars($tweet['tweet'], ENT_QUOTES, 'UTF-8');
//   echo htmlspecialchars($tweet['nick_name'], ENT_QUOTES, 'UTF-8');
//   echo htmlspecialchars($tweet['created'], ENT_QUOTES, 'UTF-8');
// }

// 返信する
if (isset($_REQUEST['res'])) {
  // 取ってきたいのはニックネームとつぶやき内容
  $sql = sprintf('SELECT m.nick_name, m.picture_path, t.* FROM members m, tweets t WHERE m.member_id = t.member_id AND t.tweet_id=%d ORDER BY t.created DESC',
    mysqli_real_escape_string($db, $_REQUEST['res']));
  $record = mysqli_query($db, $sql);
  $table = mysqli_fetch_assoc($record);
  $tweet = '>> @' . $table['nick_name']. ' ' . $table['tweet'];
}

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/form.css" rel="stylesheet">
    <link href="assets/css/timeline.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.html">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ <?php echo h($member['nick_name']); ?>さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
                  <?php if(isset($_REQUEST['res'])): ?>
                  <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"><?php echo h($tweet); ?></textarea>
                  <input type="hidden" name="reply_tweet_id" value="<?php echo h($_REQUEST['res']); ?>">
                  <?php else: ?>
                  <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
                  <?php endif; ?>
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <li><a href="index.php?page=<?php print($page-1); ?>" class="btn btn-default">前</a></li>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <li><a href="index.php?page=<?php print($page+1); ?>" class="btn btn-default">次</a></li>
          </ul>
        </form>
      </div>

      <div class="col-md-8 content-margin-top">
      <?php while($tweet = mysqli_fetch_assoc($tweets)): ?>
        <div class="msg">
          <img src="member_picture/<?php echo h($tweet['picture_path']); ?>" width="48" height="48">
          <p>
            <?php echo makeLink(h($tweet['tweet'])); ?>
            <span class="name"> (<?php echo h($tweet['nick_name']); ?>) </span>
            [<a href="index.php?res=<?php echo h($tweet['tweet_id']); ?>">Re</a>]
          </p>
          <p class="day">
            <a href="view.php?tweet_id=<?php echo h($tweet['tweet_id']); ?>">
              <?php echo h($tweet['created']); ?>
            </a>
            <?php if($tweet['reply_tweet_id'] > 0): ?>
            <a href="view.php?tweet_id=<?php echo h($tweet['reply_tweet_id']); ?>"> | 返信元のつぶやき</a>
          <?php endif; ?>
            [<a href="#" style="color: #00994C;">編集</a>]
          <?php if($_SESSION['member_id'] == $tweet['member_id']): ?>
            [<a href="delete.php?tweet_id=<?php echo h($tweet['tweet_id']); ?>" style="color: #F33;">削除</a>]
          <?php endif; ?>
          </p>
        </div>
      <?php endwhile; ?>
      </div>

    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>

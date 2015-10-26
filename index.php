<?php
    session_start();
    require('dbconnect.php');

    if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
        // ログインしている
        $_SESSION['time'] = time();

        $sql = sprintf('SELECT * FROM members WHERE id=%d',
          mysqli_real_escape_string($db, $_SESSION['id'])
        );
        $record = mysqli_query($db, $sql) or die(mysqli_error($db));
        $member = mysqli_fetch_assoc($record);
    } else {
        // ログインしていない
        header('Location: login.php');
        exit();
    }

    // 投稿を記録する
    if (!empty($_POST)) {
        if ($_POST['message'] != '') {
            $sql = sprintf('INSERT INTO posts SET member_id=%d, message="%s", reply_post_id=%d, created=NOW()',
              mysqli_real_escape_string($db, $member['id']),
              mysqli_real_escape_string($db, $_POST['message']),
              mysqli_real_escape_string($db, $_POST['reply_post_id'])
            );
            mysqli_query($db, $sql) or die(mysqli_error($db));
            header('Location: index.php');
            exit();
        }
    }

    // 投稿を取得する

    $page = $_REQUEST['page'];
    if ($page == '') {
      $page = 1;
    }
    $page = max($page, 1);
    // 最終ページを取得する
    $sql = 'SELECT COUNT(*) AS cnt FROM posts';
    $recordSet = mysqli_query($db, $sql);
    $table = mysqli_fetch_assoc($recordSet);
    $maxPage = ceil($table['cnt'] / 5);
    $page = min($page, $maxPage);

    $start = ($page - 1) * 5;
    $start = max(0, $start);


    $sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT %d, 5',
      $start
    );
    $posts = mysqli_query($db, $sql) or die(mysqli_error($db));

    // 返信の場合
    if (isset($_REQUEST['res'])) {
        $sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=%d ORDER BY p.created DESC',
          mysqli_real_escape_string($db, $_REQUEST['res'])
        );
        $record = mysqli_query($db, $sql) or die(mysqli_error($db));
        $table = mysqli_fetch_assoc($record);
        $message = ' -> @' . $table['name'] . ' ' . $table['message'];
    }

    // htmlspecialcharsのショートカット
    function h($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ひとこと掲示版</title>
  <link rel="stylesheet" type="text/css" href="assets/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
  <link rel="stylesheet" type="text/css" href="assets/css/form.css">
  <link rel="stylesheet" type="text/css" href="assets/css/timeline.css">
</head>
<body style="margin-top: 60px;">
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-slide-dropdown">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">ひとこと掲示版</a>
      </div>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="bs-slide-dropdown">
        <ul class="nav navbar-nav navbar-right">
          <li><a href="#">Link</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <span class="caret"></span></a>
            <ul class="dropdown-menu" role="menu">
              <li><a href="#">Action</a></li>
              <li><a href="#">Another action</a></li>
              <li><a href="#">Something else here</a></li>
              <li class="divider"></li>
              <li><a href="logout.php">ログアウト</a></li>
            </ul>
          </li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <form action="" method="post">
          <div class="form-group">
            <label for="validate-length"><?php echo htmlspecialchars($member['name'], ENT_QUOTES,'UTF-8'); ?>さん、メッセージをどうぞ</label>

            <div class="input-group" data-validate="length" data-length="1">
              <textarea type="text" class="form-control" id="validate-length" placeholder="つぶやき.." required name="message" cols="50" rows="5"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></textarea>
              <input type="hidden" name="reply_post_id" value="<?php echo htmlspecialchars($_REQUEST['res'], ENT_QUOTES, 'UTF-8'); ?>" />                
              <span class="input-group-addon danger"><span class="glyphicon glyphicon-remove"></span></span>
            </div>
          </div>
          
          <div class="row">
            <button type="submit" class="btn btn-primary col-xs-3 col-xs-offset-8" disabled>つぶやく</button>  
          </div>
          
        </form>
      </div>
      <div class="col-md-8">  
        <div class="qa-message-list" id="wallmessages">
          <?php while($post = mysqli_fetch_assoc($posts)): ?>
          <div class="message-item" id="m16">
            <div class="message-inner">
              <div class="message-head clearfix">
                <div class="avatar pull-left"><img src="member_picture/<?php echo htmlspecialchars($post['picture'], ENT_QUOTES,'UTF-8'); ?>" width="48" height="48" alt="<?php echo htmlspecialchars($post['name'], ENT_QUOTES,'UTF-8'); ?>" /></div>
                  <div class="user-detail">
                    <h5 class="handle"><?php echo htmlspecialchars($post['name'], ENT_QUOTES,'UTF-8'); ?>[<a href="index.php?res=<?php echo htmlspecialchars($post['id'], ENT_QUOTES,'UTF-8'); ?>">Re</a>]</h5>
                    <div class="post-meta">
                      <div class="asker-meta">
                        <span class="qa-message-what"></span>
                        <span class="qa-message-who">
                          <span class="qa-message-who-data"><a href="view.php?id=<?php echo htmlspecialchars($post['id'], ENT_QUOTES,'UTF-8'); ?>"><?php echo htmlspecialchars($post['created'], ENT_QUOTES,'UTF-8'); ?></a></span>
                          <?php if ($post['reply_post_id'] > 0):?>
                            <a href="view.php?id=<?php echo htmlspecialchars($post['reply_post_id'], ENT_QUOTES,'UTF-8');?>">返信元のメッセージ</a>
                          <?php endif; ?>
                          <?php if ($_SESSION['id'] == $post['member_id']): ?>
                            [<a href="delete.php?id=<?php echo h($post['id']); ?>" style="color: #F33;">削除</a>]
                          <?php endif; ?>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="qa-message-content">
                  <?php echo htmlspecialchars($post['message'], ENT_QUOTES,'UTF-8'); ?>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>

        <ul class="paging">
        <?php
        if ($page > 1) {
        ?>
        <li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ
        </a></li>
        <?php
        } else {
        ?>
        <li>前のページへ</li>
        <?php
        }
        ?>
        <?php
        if ($page < $maxPage) {
        ?>
          <li><a href="index.php?page=<?php print($page + 1); ?>">次のページへ
        </a></li>
        <?php
        } else {
        ?>
          <li>次のページへ</li>
        <?php
        }
        ?>
        </ul>
      </div>
    </div>
  </div>
  
  <!-- jQuery (JavaScript plugin) -->
  <script type="text/javascript" src="assets/js/jquery-1.11.3.js"></script>
  <!-- Included other js files -->
  <script type="text/javascript" src="assets/bootstrap/js/bootstrap.js"></script>
  <script type="text/javascript" src="assets/js/form.js"></script>
  <script type="text/javascript">
    $(document).ready(function(){
        $(".dropdown").hover(            
            function() {
                $('.dropdown-menu', this).not('.in .dropdown-menu').stop( true, true ).slideDown("fast");
                $(this).toggleClass('open');        
            },
            function() {
                $('.dropdown-menu', this).not('.in .dropdown-menu').stop( true, true ).slideUp("fast");
                $(this).toggleClass('open');       
            }
        );
    });
  </script>
</body>
</html>

<?php

    require('../dbconnect.php');

    session_start();

    // フォームのバリデーション (エラー分岐処理)
    if (!empty($_POST)) { // isset($_POST)
        // nameのinputタグが空だった場合
        if ($_POST['name'] == '') {
            $errors['name'] = 'blank';
        }

        // emailのinputタグが空だった場合
        if ($_POST['email'] == '') {
            $errors['email'] = 'blank';
        }

        // passwordのinputタグが空だった場合
        if ($_POST['password'] == '') {
            $errors['password'] = 'blank';
        }

        // passwordの文字数制限4文字以上
        if (strlen($_POST['password']) < 4) {
            $errors['password'] = 'length';
        }

        
        $fileName = $_FILES['image']['name'];
        if (!empty($fileName)) {
            // substr(string, start)関数で、
            // 指定した文字列の指定したスタート地点からの文字列のみ取得
            $ext = substr($fileName, -3);
            if ($ext != 'jpg' && $ext != 'gif') {
              $errors['image'] = 'type';
            }
        }

        // 重複アカウントのチェック
        if (empty($errors)) {
            $sql = sprintf('SELECT COUNT(*) AS cnt FROM members WHERE email="%s"',
              mysqli_real_escape_string($db, $_POST['email'])
            );
            $record = mysqli_query($db, $sql) or die(mysqli_error($db));
            $table = mysqli_fetch_assoc($record);
            if ($table['cnt'] > 0) {
              $errors['email'] = 'duplicate';
            }
        }

        // エラーがなければ、チェック画面へ遷移
        if (empty($errors)) {
            
            $image = date('YmdHis') . $_FILES['image']['name'];

            // アップロード処理本体 
            move_uploaded_file($_FILES['image']['tmp_name'], '../member_picture/' . $image);

            
            $_SESSION['join'] = $_POST;
            $_SESSION['join']['image'] = $image;
            header('Location: check.php');
            exit();
        }
    }

    if ($_REQUEST['action'] == 'rewrite') {
      $_POST = $_SESSION['join'];
      $errors['rewrite'] = true;
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>会員登録</title>
  <link rel="stylesheet" type="text/css" href="../assets/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="../assets/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" type="text/css" href="../assets/css/form.css">
  <link rel="stylesheet" type="text/css" href="../assets/css/login.css">
  
</head>
<body>

  


<div class="container">
      <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-login">
          <div class="panel-heading">
            <div class="row">
              <div class="col-xs-6">
                <a href="#" class="active" id="login-form-link">ログイン</a>
              </div>
              <div class="col-xs-6">
                <a href="#" id="register-form-link">ユーザー登録</a>
              </div>
            </div>
            <hr>
          </div>
          <div class="panel-body">
            <div class="row">
              <div class="col-lg-12">
                <!-- ログインフォーム -->
                <form id="login-form" action="../login.php" method="post" role="form" style="display: block;">
                  <div class="form-group">
                    <input type="text" name="email" id="email" tabindex="1" class="form-control" placeholder="email" maxlength="255" value="<?php echo htmlspecialchars($_POST['email']); ?>" />
                    <?php if ($error['login'] == 'blank'): ?>
                    <p class="error">* メールアドレスとパスワードをご記入ください</p>
                    <?php endif; ?>
                    <?php if ($error['login'] == 'failed'): ?>
                    <p class="error">* ログインに失敗しました。正しくご記入ください。
                    </p>
                    <?php endif; ?>
                  </div>

                  <div class="form-group">
                    <input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder="Password" maxlength="255" value="<?php echo htmlspecialchars($_POST['password']); ?>" />
                  </div>

                  <div class="form-group text-center">
                    <input type="checkbox" tabindex="3" class="" name="remember" id="remember">
                    <label for="remember"> Remember Me</label>
                  </div>

                  <div class="form-group">
                    <div class="row">
                      <div class="col-sm-6 col-sm-offset-3">
                        <input type="submit" name="login-submit" id="login-submit" tabindex="4" class="form-control btn btn-login" value="Log In">
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="row">
                      <div class="col-lg-12">
                        <div class="text-center">
                          <a href="http://phpoll.com/recover" tabindex="5" class="forgot-password">Forgot Password?</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
                <!-- ユーザー登録フォーム -->
                <form id="register-form" action="index.php" method="post" role="form" style="display: none;" enctype="multipart/form-data">
                  <div class="form-group">
                    <input type="text" name="name" id="username" tabindex="1" class="form-control" placeholder="nickname" maxlength="255" value="<?php echo htmlspecialchars($_POST['name'], ENT_QUOTES,'UTF-8'); ?>">
                    <?php if ($errors['name'] == 'blank'): ?>
                      <p class="error">* ニックネームを入力してください</p>
                    <?php endif; ?>
                  </div>

                  <div class="form-group">
                    <input type="email" name="email" id="email" tabindex="1" class="form-control" placeholder="Email Address" maxlength="255" value="<?php echo htmlspecialchars($_POST['email'], ENT_QUOTES,'UTF-8'); ?>">
                    <?php if ($errors['email'] == 'blank'): ?>
                      <p class="error">* メールアドレスを入力してください</p>
                    <?php endif; ?>
                    <?php if ($errors['email'] == 'duplicate'): ?>
                      <p class="error">* 指定されたメールアドレスはすでに登録されています</p>
                    <?php endif; ?>
                  </div>

                  <div class="form-group">
                    <input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder="Password" maxlength="20" value="<?php echo htmlspecialchars($_POST['password'], ENT_QUOTES,'UTF-8'); ?>">
                    <?php if ($errors['password'] == 'blank'): ?>
                      <p class="error">* パスワードを入力してください</p>
                    <?php endif; ?>
                    <?php if ($errors['password'] == 'length'): ?>
                      <p class="error">* パスワードは4文字以上で入力してください</p>
                    <?php endif; ?>
                  </div>

                  <div class="form-group">
                    <input type="file" name="image" size="35" value="test">
                    <?php if ($errors['image'] == 'type'): ?>
                      <p class="error">写真などは「jpg」または「gif」の画像を指定してください</p>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                      <p class="error">恐れ入りますが、画像を改めて指定してください</p>
                    <?php endif; ?>
                  </div>

                  <div class="form-group">
                    <div class="row">
                      <div class="col-sm-6 col-sm-offset-3">
                        <input type="submit" name="register-submit" id="register-submit" tabindex="4" class="form-control btn btn-register" value="確認画面へ">
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- jQuery (javascript plugin) -->
   <script type="text/javascript" src="../assets/js/jquery-1.11.3.js"></script>
   <!-- Included other js files -->
   <script type="text/javascript" src="../assets/bootstrap/js/bootstrap.js"></script>
   <script type="text/javascript" src="../assets/js/form.js"></script>
   <script type="text/javascript" src="../assets/js/login.js"></script>
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

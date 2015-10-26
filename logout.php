<?php
    session_start();

    //セッション情報を削除
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
      $paramas = session_get_cookie_params();
      setcookie(session_name(), '', time() -42000,
        $paramas['path'], $paramas['domain'],
        $paramas["secure"], $paramas["httponly"]
      );
    }
    session_destroy();

    //Cookie情報も削除
    setcookie('email', '', time() -3600);
    setcookie('password', '', time() -3600);

    header('Location: join/index.php');
    exit();
?>

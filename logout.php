<?php

session_start();

// Xoá toàn bộ dữ liệu session (id_doc_gia, ten_tai_khoan, vai_tro, ...)
$_SESSION = [];

// Xoá luôn cookie session trên trình duyệt (nếu có)
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );

}

session_destroy();

header("Location: login.php");
exit;

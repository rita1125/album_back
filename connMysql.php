<?php 
  // Heroku環境
if (getenv("JAWSDB_URL")) {
    //JawsDB
    $jawsdb_url = parse_url(getenv("JAWSDB_URL"));
    $host = $jawsdb_url["host"];
    $username = $jawsdb_url["user"];
    $password = $jawsdb_url["pass"];
    $db = ltrim($jawsdb_url["path"], '/'); // 去掉路徑前的斜杠

    //資料庫連線
    $link = new mysqli($host, $username, $password, $db); // new mysqli() 物件導向方式
    if ($link->connect_error) {
        die("資料連結失敗: " . $link->connect_error);
    }
} else {
    //本機XAMPP
    $db_host = "localhost";
    $db_name = "album";
    $db_username = "root";
    $db_password = "";

    //資料庫連線
    $link = new mysqli($db_host, $db_username, $db_password, $db_name);
    $link->set_charset("utf8");
    if ($link->connect_error) {
        die("資料連結失敗: " . $link->connect_error);
    }
}
?>
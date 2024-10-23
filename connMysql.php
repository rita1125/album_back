<?php
    //Heroku環境
  if(getenv("CLEARDB_DATABASE_URL")){
      //ClearDB
      $cleardb_url = parse_url(getenv("CLEARDB_DATABASE_URL"));
      $host = $cleardb_url["host"];
      $username = $cleardb_url["user"];
      $password = $cleardb_url["pass"];
      $db = substr($cleardb_url["path"], 1);
      
      //資料庫連線
      $link = new mysqli($host, $username, $password, $db);  //new mysqli()->物件導向方式
      if ($link->connect_error) {
        die("資料連結失敗: " . $link->connect_error);
      }
  } else {
      //本機(XAMPP)
      $db_host = "localhost";
      $db_name = "album";
      $db_username = "root";
      $db_password = "";

      //資料庫連線
      $link = new mysqli($db_host, $db_username, $db_password, $db_name);
      $link->set_charset("utf8");
      if($link->connect_error){
        die("資料連結失敗".mysqli_connect_error());
      }
      // $link = mysqli_connect($db_host, $db_username, $db_password);  //mysqli_connect()->程序式方式
      // if(!$link){
      //   die("資料連結失敗".mysqli_connect_error());
      // }
      // mysqli_select_db($link,$db_name);
      // mysqli_query($link,"SET NAMES 'utf8'");
  }
?>


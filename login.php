<?php
  // header("Access-Control-Allow-Origin: *");
  // header("Access-Control-Allow-Methods: POST");
  // header("Access-Control-Allow-Headers: Content-Type");
  
  require_once("connMysql.php");
  require 'vendor/autoload.php';  //JSON Web Token套件
  use Firebase\JWT\JWT;
  use Firebase\JWT\Key;

  $key = "thisissecretkey0000";  // 定義你的密鑰

  // if(isset($_POST["username"]) && isset($_POST["password"])) {
  //   $sql = "SELECT * FROM admin";
  //   $result = mysqli_query($link, $sql);
  //   $row = mysqli_fetch_assoc($result);
    
  //   if($_POST["username"] == $row["username"] && $_POST["password"] == $row["password"]) {
  //     setcookie("username", $_POST["username"]);
  //     echo json_encode(["success" => true]);
  //   } else {
  //     echo json_encode(["success" => false]);
  //   }
  // }
  if(isset($_POST["username"]) && isset($_POST["password"])) {
    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 's', $_POST["username"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    //if ($row && $_POST["password"] == $row["password"]) {
    if ($row && password_verify($_POST["password"], $row["password"])) { 
      //編碼到token的資料
      $payload = [
          "username" => $row["username"],
          "exp" => time() + 3600  // token有效時間 : time()返回當前的時間，1小時(3600s)
      ];
      // 生成 JSON Web Token
      $jwt = JWT::encode($payload, $key, 'HS256');  // $payload:想要編碼到 token 裡的資料，$key:密鑰，加密演算法的名稱，HS256:用這個演算法來對token進行加密簽名
      echo json_encode(["success" => true, "token" => $jwt]);
    } else {
      echo json_encode(["success" => false]);
    }
  }
?>
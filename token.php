<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');  // 允許的請求標頭
//header("Content-Type: application/json");

require 'vendor/autoload.php';  // JSON Web Token 套件
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "thisissecretkey0000";   // 從環境變數中獲取密鑰

// 獲取POST請求裡的token
$data = json_decode(file_get_contents("php://input"));

if (isset($data->token)) {
    try {
        $decoded = JWT::decode($data->token, new Key($key, 'HS256'));  //對傳入的JWTtoken解碼
        echo json_encode(["success" => true, "decoded" => $decoded]);
    } catch (Exception) {
        // token 無效或過期
        echo json_encode(["success" => false, "message" => "Token is invalid or expired"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No token"]);
}
?>
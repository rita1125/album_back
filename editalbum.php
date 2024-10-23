<?php
//允許來自任何來源的跨域請求(網站在 localhost:3000 上運行，而 API 在 localhost:80 上運行)，解決跨來源資源共享問題
header('Access-Control-Allow-Origin: *');  
//允許的 HTTP方法
header('Access-Control-Allow-Methods: GET, POST'); 
//允許客戶端發送 Content-Type 標頭，讓客戶端在請求時可以自定義 Content-Type
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With'); 
//告訴客戶端，伺服器返回的內容類型是 JSON 格式
header('Content-Type: application/json');

require_once("connMysql.php");

//編輯相簿資料
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  if(isset($_POST["album_name"])) {
    $album_name = $_POST["album_name"];
    $album_date = $_POST["album_date"];
    $album_place = $_POST["album_place"];
    $album_desc = $_POST["album_desc"];
    $album_id = $_GET["albumId"]; // 取得 albumId
  
    //更新相簿
    $sql = "UPDATE album SET album_name = ?, album_desc = ?, album_date = ?, album_place = ? WHERE album_id = ?";
    $sqlresult = $link->prepare($sql);   // 使用 prepare 防止 SQL 注入
    $sqlresult->bind_param("ssssi", $album_name, $album_desc, $album_date, $album_place, $album_id); // 綁定參數，最後一個參數是 album_id (int)
    $sqlresult->execute(); 
    error_log("收到修改相簿要求 photo_id: " . $_GET["albumId"]);
    echo json_encode(['success' => '相簿已更新']);
}
}

//  if(isset($_POST["album_name"])) {
//     $sql = "UPDATE album SET 
//             album_name='".$_POST["album_name"]."',
//             album_desc='".$_POST["album_desc"]."',
//             album_date='".$_POST["album_date"]."',
//             album_place='".$_POST["album_place"]."'
//             WHERE album_id=".$_GET["albumId"];
//     mysqli_query($link, $sql);
//     error_log("收到修改相簿要求 photo_id: " . $_GET["albumId"]);
//     echo json_encode(['success' => '相簿已更新']);
//     exit();
// }



// 取得相簿資料
if ($_SERVER['REQUEST_METHOD'] === 'GET'){
  if(isset($_GET["albumId"])) {
    // old code
    // $sql = "SELECT * FROM album WHERE album_id=".$_GET["albumId"];
    // $record = mysqli_query($link, $sql);
    // $row = mysqli_fetch_assoc($record);
    // echo json_encode($row); 

    $sql_album = $link->prepare("SELECT * FROM album WHERE album_id = ?");
    $albumId = intval($_GET["albumId"]); 
    $sql_album->bind_param("i", $albumId); //綁定
    $sql_album->execute(); //執行
    $result = $sql_album->get_result(); //結果
    $row = $result->fetch_assoc(); //取關聯
    $sql_album->close();
    echo json_encode($row);
  }
}


?>
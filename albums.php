<?php
//允許來自任何來源的跨域請求(網站在 localhost:3000 上運行，而 API 在 localhost:80 上運行)，解決跨來源資源共享問題
header('Access-Control-Allow-Origin: *');  
//允許的 HTTP方法
header('Access-Control-Allow-Methods: GET'); 
//允許客戶端發送 Content-Type 標頭，讓客戶端在請求時可以自定義 Content-Type
header('Access-Control-Allow-Headers: Content-Type'); 
//告訴客戶端，伺服器返回的內容類型是 JSON 格式
header('Content-Type: application/json');


require_once("connMysql.php");

$sql = "SELECT album.album_id, album.album_name, photo.photo_file, photo.imgur_link, photo.imgur_resize_link, count(photo.album_id) as photo_count 
        FROM album 
        LEFT JOIN photo ON album.album_id = photo.album_id 
        GROUP BY album.album_id 
      ";
$result = mysqli_query($link, $sql);
$albums = [];
while ($row = mysqli_fetch_assoc($result)) {
    $albums[] = $row;
}
echo json_encode([
    "albums" => $albums,
    "totalPages" => COUNT($albums)
]);
exit; //確保在這行之後不會執行其他代碼
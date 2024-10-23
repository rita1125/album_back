<?php
//允許來自任何來源的跨域請求(網站在 localhost:3000 上運行，而 API 在 localhost:80 上運行)，解決跨來源資源共享問題
header('Access-Control-Allow-Origin: *'); 
//告訴客戶端，伺服器返回的內容類型是 JSON 格式
header('Content-Type: application/json');

require_once("connMysql.php");

//取得圖片
//$photo_Id = $_GET['photoId'] ?? null;
//mysqli_real_escape_string :  MySQLi 擴展函數，主要目的是防止SQL注入攻擊，字串用
$photo_Id = isset($_GET['photoId']) ? intval($_GET['photoId']) : null;
if ($photo_Id) {
    //查圖片 old
    // $sql_photo = "SELECT * FROM photo WHERE photo_Id = '$photo_Id'";
    // $result_photo = mysqli_query($link, $sql_photo);
    
    // $photos = [];
    // while ($row = mysqli_fetch_assoc($result_photo)) {
    //     $photos[] = $row;
    // }
    $sql_photo = $link->prepare("SELECT * FROM photo WHERE photo_Id = ?");
    $sql_photo->bind_param("i", $photo_Id); 
    $sql_photo->execute();
    $result_photo = $sql_photo->get_result();

    $photos = [];
    while ($row = $result_photo->fetch_assoc()) {
        $photos[] = $row;
    }
    $sql_photo->close(); 

    $data = [
        'photos' => $photos,
    ];
    echo json_encode($data); 
} else {
    echo json_encode(['error' => '無 AlbumID']);
}

?>

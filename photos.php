<?php
//允許來自任何來源的跨域請求(網站在 localhost:3000 上運行，而 API 在 localhost:80 上運行)，解決跨來源資源共享問題
header('Access-Control-Allow-Origin: *');  
//允許的 HTTP方法
header('Access-Control-Allow-Methods: GET, DELETE'); 
//允許客戶端發送 Content-Type 標頭，讓客戶端在請求時可以自定義 Content-Type
header('Access-Control-Allow-Headers: Content-Type'); 
//告訴客戶端，伺服器返回的內容類型是 JSON 格式
header('Content-Type: application/json');

require_once("connMysql.php");

//取得圖片
//$album_id = $_GET['albumId'] ?? null;
//mysqli_real_escape_string :  MySQLi 擴展函數，主要目的是防止SQL注入攻擊，字串用
$album_id = isset($_GET['albumId']) ? intval($_GET['albumId']) : null;
if ($album_id) {
    //查相簿 old
    // $sql_album = "SELECT * FROM album WHERE album_id = '$album_id'";
    // $result_album = mysqli_query($link, $sql_album);
    // $album = mysqli_fetch_assoc($result_album);
    // if (!$album) {
    //     $album = null;
    // }
    $sql_album = $link->prepare("SELECT * FROM album WHERE album_id = ?");
    $sql_album->bind_param("i", $album_id);
    $sql_album->execute();
    $result_album = $sql_album->get_result();
    $album = $result_album->fetch_assoc();  //從結果集中獲取關聯數組
    if (!$album) {
        $album = null;
    }
    $sql_album->close();

    //查圖片 old
    // $sql_photo = "SELECT * FROM photo WHERE album_id = '$album_id'";
    // $result_photo = mysqli_query($link, $sql_photo);
    // $photos = [];
    // while ($row = mysqli_fetch_assoc($result_photo)) {
    //     $photos[] = $row;
    // }
    $sql_photo = $link->prepare("SELECT * FROM photo WHERE album_id = ?");
    $sql_photo->bind_param("i", $album_id);
    $sql_photo->execute();
    $result_photo = $sql_photo->get_result();

    $photos = [];
    while ($row = $result_photo->fetch_assoc()) {
        $photos[] = $row;
    }
    $sql_photo->close();

    $data = [
        'thisAlbum' => $album,
        'photos' => $photos,
    ];
    echo json_encode($data); 
} else {
    echo json_encode(['error' => '無 AlbumID']);
}


//刪除圖片
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    //讀取請求的原始輸入（因為前端 photo_manage.js 把資料格式設為 JSON格式）
    $input = file_get_contents("php://input");
    //將 JSON解碼為 PHP陣列
    $delete_arr = json_decode($input, true);
    //取 photo_id
    $photo_id = intval($delete_arr['photo_id']);
    error_log("收到刪除圖片要求 photo_id: " . $photo_id);


    // // 刪除圖片 old
    // $get_photos = "SELECT * FROM photo WHERE photo_id = $photo_id";  
    // $result = mysqli_query($link, $get_photos);  //對於 SELECT ，返回結果集對象，使用 mysqli_fetch_assoc、mysqli_fetch_array獲取數據
    // while($row = mysqli_fetch_assoc($result)) {       //mysqli_fetch_assoc來逐行遍歷 SQL查詢結果，將每行資料以關聯數組返回
    //     unlink("./photos/" . $row["photo_file"]);     //PHP刪除檔案的 unlink 函數 ，從伺服器 photos 資料夾中刪除圖片
    // }
    // $del_photos = "DELETE FROM photo WHERE photo_id = $photo_id";  
    // $photoDeleted = mysqli_query($link, $del_photos); //對 INSERT、UPDATE 或 DELETE 查詢，返回 true，表示操作成功

    // if ($photoDeleted) {
    //     echo json_encode(['message' => 'Photo deleted successfully']);
    // } else {
    //     echo json_encode(['error' => 'Failed to delete Photo']);
    // }
    
    //刪除圖片
    $get_photos = "SELECT * FROM photo WHERE photo_id = ?";  
    // 使用預處理語句
    if ($sql_select = mysqli_prepare($link, $get_photos)) {
        //綁定參數
        mysqli_stmt_bind_param($sql_select, "i", $photo_id); // 參數 int
        //執行SQL
        mysqli_stmt_execute($sql_select);
        //獲取結果
        $result = mysqli_stmt_get_result($sql_select);  
        //從伺服器 photos 資料夾中刪除圖片
        while ($row = mysqli_fetch_assoc($result)) { //對於 SELECT ，返回結果集對象，使用 mysqli_fetch_assoc、mysqli_fetch_array獲取數據
            unlink("./photos/" . $row["photo_file"]); // PHP刪除檔案的 unlink 函數
        }
        
        // 刪除資料庫中的記錄
        $del_photos = "DELETE FROM photo WHERE photo_id = ?";
        if ($sql_delete = mysqli_prepare($link, $del_photos)) {
            // 綁定參數
            mysqli_stmt_bind_param($sql_delete, "i", $photo_id);  // 參數 int
            // 執行SQL
            $photoDeleted = mysqli_stmt_execute($sql_delete);     //返回值 true false

            if ($photoDeleted) {
                echo json_encode(['message' => 'Photo deleted successfully']);
            } else {
                echo json_encode(['error' => 'Failed to delete Photo']);
            }

            // 關閉語句，釋放資源
            mysqli_stmt_close($sql_delete);
        } else {
            echo json_encode(['error' => '準備刪除語句失敗']);
        }

        // 關閉查詢語句
        mysqli_stmt_close($sql_select);
    } else {
        echo json_encode(['error' => '準備選擇語句失敗']);
    }

}

?>

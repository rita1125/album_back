<?php
// 允許所有來源
header('Access-Control-Allow-Origin: *');  
// 允許的 HTTP 方法
header('Access-Control-Allow-Methods: GET, DELETE'); 
// 允許客戶端發送 Content-Type 標頭，讓客戶端在請求時可以自定義 Content-Type
header('Access-Control-Allow-Headers: Content-Type'); 
// 當伺服器回傳 JSON 數據時，需要設置 Content-Type: application/json，這樣客戶端可以正確地解析回應數據為 JSON
// 如果伺服器回傳的數據格式不同，比如 HTML 或 XML，則需要設置相應的 Content-Type（如 text/html 或 application/xml）
// 設置回應的內容類型為 application/json，告訴客戶端（如瀏覽器）: 伺服器回傳的數據是 JSON 格式
header('Content-Type: application/json');

require_once("connMysql.php");

//取得圖片
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $sql = "SELECT album.album_id, album.album_name, photo.photo_file, photo.imgur_link, photo.imgur_resize_link
          FROM album
          LEFT JOIN photo ON album.album_id = photo.album_id
          GROUP BY album.album_id
         ";
  $result = mysqli_query($link, $sql);
  
  $albums = [];
  while($row = mysqli_fetch_assoc($result)) {
    $albums[] = $row;
  }

  //準備語句
  // $sql_select = $link->prepare("SELECT album.album_id, album.album_name, photo.photo_file
  //                               FROM album
  //                               LEFT JOIN photo ON album.album_id = photo.album_id
  //                               GROUP BY album.album_id
  //                             ");
  // $sql_select->execute();
  // $result = $sql_select->get_result();

  // $albums = [];
  // while ($row = $result->fetch_assoc()) {
  //   $albums[] = $row;
  // }
  // $sql_select->close(); 

  echo json_encode(['albums' => $albums]);
}


//刪除圖片
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  //讀取請求的原始輸入（因為前端 photo_manage.js 把資料格式設為 JSON格式）
  $input = file_get_contents("php://input");
  //將 JSON 數據解碼為 PHP 陣列
  $delete_arr = json_decode($input, true);
  //取 album_id
  $album_id = intval($delete_arr['album_id']);
  error_log("收到刪除相簿要求 album_id: " . $album_id);
  
  // // 刪除相簿和相片 old
  // $get_photos = "SELECT * FROM photo WHERE album_id = $album_id";
  // $result = mysqli_query($link, $get_photos);
  // while($row = mysqli_fetch_assoc($result)) {
  //   unlink("./photos/" . $row["photo_file"]);
  // }
  
  // $del_album = "DELETE FROM album WHERE album_id = $album_id";
  // $albumDeleted = mysqli_query($link, $del_album);
  
  // $del_photo = "DELETE FROM photo WHERE album_id = $album_id";
  // $photoDeleted = mysqli_query($link, $del_photo);

  // // 檢查是否成功刪除
  // if ($albumDeleted && $photoDeleted) {
  //   echo json_encode(['message' => 'Album deleted successfully']);
  // } else {
  //   echo json_encode(['error' => 'Failed to delete album']);
  // }


  $get_photos = "SELECT photo_file FROM photo WHERE album_id = ?";
  //使用預處理語句
  $sql_select = mysqli_prepare($link, $get_photos);
  //綁定參數
  mysqli_stmt_bind_param($sql_select, "i", $album_id);
  mysqli_stmt_execute($sql_select);
  $result = mysqli_stmt_get_result($sql_select);
  //從伺服器 photos 資料夾中刪除圖片
  while($row = mysqli_fetch_assoc($result)) {
      unlink("./photos/" . $row["photo_file"]);  // PHP刪除檔案的 unlink 函數
  }
  //關閉查詢語句
  mysqli_stmt_close($sql_select);
  
  //刪除相簿
  $del_sql_album = "DELETE FROM album WHERE album_id = ?";
  $album_delete = mysqli_prepare($link, $del_sql_album);
  mysqli_stmt_bind_param($album_delete, "i", $album_id);
  $albumDeleted = mysqli_stmt_execute($album_delete);
  //關閉語句
  mysqli_stmt_close($album_delete);
  
  //刪除相片
  $del_sql_photo = "DELETE FROM photo WHERE album_id = ?";
  $photo_delete = mysqli_prepare($link, $del_sql_photo);
  mysqli_stmt_bind_param($photo_delete, "i", $album_id);
  $photoDeleted = mysqli_stmt_execute($photo_delete);
  //關閉語句
  mysqli_stmt_close($photo_delete);

  if ($albumDeleted && $photoDeleted) {
      echo json_encode(['message' => 'Album deleted successfully']);
  } else {
      echo json_encode(['error' => 'Failed to delete album']);
  }
}

?>
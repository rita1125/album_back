<?php
header('Access-Control-Allow-Origin: *');  
header('Access-Control-Allow-Methods: POST'); 
//告訴客戶端，伺服器返回的內容類型是 JSON 格式
header('Content-Type: application/json');

require_once("connMysql.php");

if(isset($_POST["album_name"])) {
  $album_name = $_POST["album_name"];
  $album_date = $_POST["album_date"];
  $album_place = $_POST["album_place"];
  $album_desc = $_POST["album_desc"];

  //新增相簿
  $sql = "INSERT INTO album(album_name, album_desc, album_date, album_place) VALUES (?, ?, ?, ?)"; // ?:佔位符
  $sqlresult = $link->prepare($sql);   //prepare 防止 SQL注入，允許安全地執行帶有參數的 SQL查詢
  $sqlresult->bind_param("ssss", $album_name, $album_desc, $album_date, $album_place); //綁定參數，四個參數:ssss(str意思)。i(int)，d(double)
  $sqlresult->execute();
  $album_id = $sqlresult->insert_id;  //執行INSERT後，DB自動生成唯一ID，insert_id取回此值

  //處理圖片，用FormData上傳檔案，PHP會自動填充 $_FILES 陣列，name: 檔案原始名稱，tmp_name: 檔案在伺服器上的暫存路徑
  if (!empty($_FILES["up_photo"]["name"][0])) { //檢查是否有上傳的檔案
    foreach ($_FILES["up_photo"]["tmp_name"] as $key => $tmp_name) {  //歷所有上傳的檔案的暫存路徑， $key對應到每個上傳檔案的索引
      // 測試用
      // error_log("tmp_name: ".$tmp_name);
      // error_log("name: ".$_FILES["up_photo"]["name"][$key]);
        if (!empty($tmp_name)) { 
            $desc = uniqid();
            $src_ext = strtolower(pathinfo($_FILES["up_photo"]["name"][$key], PATHINFO_EXTENSION));
            $desc_file_name = $desc . "." . $src_ext;
            //$thumbnail_desc_file_name = "./public/images/thumbnail/$desc_file_name";
            $frontend_url = getenv('FRONTEND_URL');
            if($frontend_url){
              $thumbnail_desc_file_name = "$frontend_url/images/thumbnail/$desc_file_name";
            }else{
              $thumbnail_desc_file_name = "C:/xampp/htdocs/album_nextjs/client/public/images/thumbnail/$desc_file_name";
            }

            //調整圖片大小
            resize_photo($tmp_name, $src_ext, $thumbnail_desc_file_name, 250);

            // 把檔案移動到指定目錄，並插入DB
            //if (move_uploaded_file($tmp_name, "前端網址/public/images/bigphoto/" . $desc_file_name)) {
            if($frontend_url){
              if (move_uploaded_file($tmp_name, "$frontend_url/images/bigphoto/" . $desc_file_name)) {
                $sql = "INSERT INTO photo(album_id, photo_file) VALUES (?, ?)"; // ?:佔位符
                $sqlresult = $link->prepare($sql); //prepare 防止 SQL注入，允許安全地執行帶有參數的 SQL查詢
                $sqlresult->bind_param("is", $album_id, $desc_file_name); //綁定參數，兩個參數 : is (int跟str意思)
                $sqlresult->execute();
              } else {
                  error_log("無法移動檔案: " .$_FILES["up_photo"]["name"][$key]);
              }
            }else{
              if (move_uploaded_file($tmp_name, "C:/xampp/htdocs/album_nextjs/client/public/images/bigphoto/" . $desc_file_name)) {
                $sql = "INSERT INTO photo(album_id, photo_file) VALUES (?, ?)"; // ?:佔位符
                $sqlresult = $link->prepare($sql); //prepare 防止 SQL注入，允許安全地執行帶有參數的 SQL查詢
                $sqlresult->bind_param("is", $album_id, $desc_file_name); //綁定參數，兩個參數 : is (int跟str意思)
                $sqlresult->execute();
              } else {
                  error_log("無法移動檔案: " .$_FILES["up_photo"]["name"][$key]);
              }
            }
        }
    }
} else {
    error_log("未上傳任何檔案");
}
  echo json_encode(["success" => true]);
} else {
  echo json_encode(["success" => false]);
}


//調尺寸
function resize_photo($src_file, $src_ext, $dest_name, $max_size) {
  switch ($src_ext) {
    case "jpg":
    case "jpeg":
      $src = imagecreatefromjpeg($src_file);   //imagecreatefromjpeg()函數 來自 php.ini的 extension=gd
      break;
    case "png":
      $src = imagecreatefrompng($src_file);
      break;
    case "gif":
      $src = imagecreatefromgif($src_file);
      break;
    default:
      return false;
  }

  $src_w = imagesx($src);  //寬度
  $src_h = imagesy($src);  //高度
  if($src_w > $src_h) {    //保持圖片比例
    $thumb_w = $max_size;
    $thumb_h = intval($src_h / $src_w * $thumb_w);
  } else {
    $thumb_h = $max_size;
    $thumb_w = intval($src_w / $src_h * $thumb_h);
  }

  $thumb = imagecreatetruecolor($thumb_w, $thumb_h);   //創建新的真彩色圖像，作為縮放後的圖像
  imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumb_w, $thumb_h, $src_w, $src_h);  //將原始圖像複製到新圖像上，並進行縮放
  imagejpeg($thumb, $dest_name, 100);  //保存為 JPEG 格式，100是最高保存品質
  imagedestroy($src);  //imagedestroy釋放內存，這樣可以避免內存泄漏
  imagedestroy($thumb);
}
?>
<?php
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST'); 
//告訴客戶端，伺服器返回的內容類型是 JSON 格式
header('Content-Type: application/json');

require_once("connMysql.php");

if (isset($_POST["album_id"])) {
    $album_id = $_POST["album_id"];  //從表單中獲取 album_id
    //處理照片上傳的邏輯
    if (!empty($_FILES["up_photo"]["name"][0])) {
      foreach ($_FILES["up_photo"]["tmp_name"] as $key => $tmp_name) {
        if (!empty($tmp_name)) {
          $desc = uniqid();
          $src_ext = strtolower(pathinfo($_FILES["up_photo"]["name"][$key], PATHINFO_EXTENSION));
          $desc_file_name = $desc . "." . $src_ext;
          //$thumbnail_desc_file_name = "C:/xampp/htdocs/album_nextjs/client/public/images/thumbnail/$desc_file_name";
          $thumbnail_desc_file_name = "./public/images/thumbnail/$desc_file_name";
          
          //調整圖片大小
          resize_photo($tmp_name, $src_ext, $thumbnail_desc_file_name, 250);
  
          //把檔案移動到指定目錄，並插入DB
          //if (move_uploaded_file($tmp_name, "C:/xampp/htdocs/album_nextjs/client/public/images/bigphoto/" . $desc_file_name)) {
          if (move_uploaded_file($tmp_name, "./public/images/bigphoto/" . $desc_file_name)) {   
            $sql = "INSERT INTO photo(album_id, photo_file) VALUES (?, ?)";
              $sqlresult = $link->prepare($sql);
              $sqlresult->bind_param("is", $album_id, $desc_file_name);
              $sqlresult->execute();
          } else {
              error_log("無法移動檔案: " . $_FILES["up_photo"]["name"][$key]);
          }
        }
      }
    }
    echo json_encode(["success" => true]);
  } else {
    echo json_encode(["success" => false, "message" => "No album_id"]);
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
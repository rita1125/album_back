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
          
          // //old 手動調整圖片大小
          // resize_photo($tmp_name, $src_ext, $thumbnail_desc_file_name, 250);
  
          // old 把檔案移動到指定目錄，並插入DB
          // //if (move_uploaded_file($tmp_name, "C:/xampp/htdocs/album_nextjs/client/public/images/bigphoto/" . $desc_file_name)) {
          // if (move_uploaded_file($tmp_name, "./public/images/bigphoto/" . $desc_file_name)) {   
          //   $sql = "INSERT INTO photo(album_id, photo_file) VALUES (?, ?)";
          //     $sqlresult = $link->prepare($sql);
          //     $sqlresult->bind_param("is", $album_id, $desc_file_name);
          //     $sqlresult->execute();
          // } else {
          //     error_log("無法移動檔案: " . $_FILES["up_photo"]["name"][$key]);
          // }

          // 把檔案移動到指定目錄，並插入DB
          if (move_uploaded_file($tmp_name, "./public/images/bigphoto/" . $desc_file_name)) {
            // 上傳到 Imgur
            $imgurLink = uploadToImgur("./public/images/bigphoto/" . $desc_file_name);

            if ($imgurLink) {
                // imgur自動幫忙生成縮圖之 URL，有不同尺寸，在檔名最後面加上s、m、l做區分 (小、中、大)，例如 eDQib1M.png 變成 eDQib1Ms.png
                // 用 pathinfo 解析 URL: 路徑 dirname、檔名filename、副檔名 extension
                $linkInfo = pathinfo($imgurLink);
                // 重組縮圖 URL，在副檔名前加上 "m"(中尺寸)
                $resizeImgurLink = $linkInfo['dirname'] . '/' . $linkInfo['filename'] . 'm.' . $linkInfo['extension'];
                // 把圖片 URL 插入數據庫
                $sql = "INSERT INTO photo(album_id, photo_file, imgur_link, imgur_resize_link) VALUES (?, ?, ?, ?)";  // imgur_link 欄位
                $sqlresult = $link->prepare($sql);
                $sqlresult->bind_param("isss", $album_id, $desc_file_name, $imgurLink, $resizeImgurLink);
                $sqlresult->execute();

            } else {
                // 處理 imgur 上傳錯誤
                error_log("Imgur 上傳失敗: " . $_FILES["up_photo"]["name"][$key]);
            }
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
// function resize_photo($src_file, $src_ext, $dest_name, $max_size) {
//   switch ($src_ext) {
//     case "jpg":
//     case "jpeg":
//       $src = imagecreatefromjpeg($src_file);   //imagecreatefromjpeg()函數 來自 php.ini的 extension=gd
//       break;
//     case "png":
//       $src = imagecreatefrompng($src_file);
//       break;
//     case "gif":
//       $src = imagecreatefromgif($src_file);
//       break;
//     default:
//       return false;
//   }

//   $src_w = imagesx($src);  //寬度
//   $src_h = imagesy($src);  //高度
//   if($src_w > $src_h) {    //保持圖片比例
//     $thumb_w = $max_size;
//     $thumb_h = intval($src_h / $src_w * $thumb_w);
//   } else {
//     $thumb_h = $max_size;
//     $thumb_w = intval($src_w / $src_h * $thumb_h);
//   }

//   $thumb = imagecreatetruecolor($thumb_w, $thumb_h);   //創建新的真彩色圖像，作為縮放後的圖像
//   imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumb_w, $thumb_h, $src_w, $src_h);  //將原始圖像複製到新圖像上，並進行縮放
//   imagejpeg($thumb, $dest_name, 100);  //保存為 JPEG 格式，100是最高保存品質
//   imagedestroy($src);  //imagedestroy釋放內存，這樣可以避免內存泄漏
//   imagedestroy($thumb);
// }

//上傳圖片到免費圖片空間 Imgur
function uploadToImgur($imagePath) {
  // Imgur API 的 Client ID
  $client_id = '9a75475a0645536';
  
  // 讀取要上傳的圖片
  $imageData = base64_encode(file_get_contents($imagePath));

  // 設定 HTTP 標頭
  $options = [
      'http' => [
          'method' => 'POST',
          'header' => [
            "Authorization: Client-ID $client_id",
            "Content-Type: application/x-www-form-urlencoded\r\n",
          ],
          'content' => http_build_query(['image' => $imageData]),
      ]
  ];

  // 發送請求
  $context = stream_context_create($options);
  $response = file_get_contents('https://api.imgur.com/3/image', false, $context);
  $responseData = json_decode($response, true);

  // 檢查回應結果
  if ($responseData['success']) {
      // 回傳圖片 URL
      return $responseData['data']['link'];
  } else {
      // 處理錯誤
      return "上傳失敗: " . $responseData['data']['error'];
  }
}

?>
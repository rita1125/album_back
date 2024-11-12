<?php
    $password = 'thisispass1234'; 
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    echo $hashedPassword; //哈希值
?>
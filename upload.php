<?php
session_start();
include "db.php";
include "log.php";

if(!isset($_SESSION['user'])){
    echo "<script>alert('请先登录'); location.href='login.php';</script>";
    exit;
}
$user = $_SESSION['user'];

if(!isset($_FILES['pic']) || $_FILES['pic']['error'] !== UPLOAD_ERR_OK){
    echo "<script>alert('上传失败或未选择文件'); location.href='profile.php';</script>";
    exit;
}

// 简单文件类型过滤
$tmp = $_FILES['pic']['tmp_name'];
$info = @getimagesize($tmp);
$allowed_ext = ['jpg','jpeg','png','gif','webp','php'];
$original_name = $_FILES['pic']['name'];
$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

if($info === false || !in_array($ext, $allowed_ext)){
    echo "<script>alert('文件格式不允许，请重新选择！'); location.href='profile.php';</script>";
    exit;
}

// 生成唯一文件名，防止覆盖
$fn = 'uploads/avatar_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
if(!is_dir(__DIR__.'/uploads')) mkdir(__DIR__.'/uploads', 0755, true);
if(move_uploaded_file($tmp, __DIR__.'/'.$fn)){
    // 更新数据库
    $id = $user['id'];
    $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $path = $fn;
    $stmt->bind_param("si", $path, $id);
    if($stmt->execute()){
        write_log($user['username'], $user['role'], "上传头像", $path);
        // 更新 session
        $_SESSION['user']['avatar'] = $path;
        echo "<script>alert('上传成功'); location.href='profile.php';</script>";
        exit;
    } else {
        echo "<script>alert('数据库保存失败'); location.href='profile.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('保存文件失败'); location.href='profile.php';</script>";
    exit;
}
?>

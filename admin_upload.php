<?php
session_start();
include "db.php";
include "log.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){
    die("无权限");
}
$me = $_SESSION['user'];

if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['uid'])){
    header("Location: admin.php");
    exit;
}

$targetId = (int)$_POST['uid'];

if(!isset($_FILES['pic']) || $_FILES['pic']['error'] !== UPLOAD_ERR_OK){
    $err = "上传失败或未选择文件";
    header("Location: admin.php?err=" . rawurlencode($err));
    exit;
}

$tmp = $_FILES['pic']['tmp_name'];
$info = @getimagesize($tmp);
$allowed_ext = ['jpg','jpeg','png','gif','webp'];
$original_name = $_FILES['pic']['name'];
$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

if($info === false || !in_array($ext, $allowed_ext)){
    $err = "文件格式不允许，请重新选择！";
    header("Location: admin.php?err=" . rawurlencode($err));
    exit;
}

// 生成文件名并移动
if(!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
$fn = 'uploads/admin_avatar_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
if(!move_uploaded_file($tmp, __DIR__ . '/' . $fn)){
    $err = "保存文件失败";
    header("Location: admin.php?err=" . rawurlencode($err));
    exit;
}

// 更新该用户 avatar 字段
$stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
$stmt->bind_param("si", $fn, $targetId);
if($stmt->execute()){
    // 记录日志：管理员为用户上传头像
    write_log($me['username'], $me['role'], "管理员上传头像", "为用户ID=$targetId 上传文件=$fn");
    $msg = "头像上传成功";
    header("Location: admin.php?msg=" . rawurlencode($msg));
    exit;
} else {
    $err = "数据库更新失败";
    header("Location: admin.php?err=" . rawurlencode($err));
    exit;
}

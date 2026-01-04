<?php
session_start();
include "db.php";
include "log.php";

if(!isset($_SESSION['user'])){
    echo "<script>alert('请先登录'); location.href='index.php';</script>";
    exit;
}
$uid = (int)$_SESSION['user']['id'];
$role = $_SESSION['user']['role'];
$username = $_SESSION['user']['username'];

$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($pid <= 0){
    header("Location: index.php");
    exit;
}

// 为避免重复点赞，可先检查是否已赞（此处允许重复或可改为唯一）
// 这里我们允许用户对同一篇文章只记录一次点赞：
$stmt = $db->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
$stmt->bind_param("ii", $uid, $pid);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows == 0){
    $ins = $db->prepare("INSERT INTO likes(user_id, post_id) VALUES(?, ?)");
    $ins->bind_param("ii", $uid, $pid);
    $ins->execute();
    write_log($username, $role, "点赞", "点赞了文章ID=$pid");
}
$stmt->close();

header("Location: index.php");

<?php
session_start();
include "db.php";
include "log.php";

if(!isset($_SESSION['user'])){
    echo "<script>alert('请先登录'); location.href='login.php';</script>";
    exit;
}
$user = $_SESSION['user'];
$role = $user['role'] ?? 'user';
$uid = (int)$user['id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if($id <= 0){
    echo "<script>alert('参数错误'); location.href='index.php';</script>";
    exit;
}

// 读取评论
$q = $db->prepare("SELECT id, user_id, content FROM comments WHERE id = ?");
$q->bind_param("i",$id);
$q->execute();
$comment = $q->get_result()->fetch_assoc();
$q->close();

if(!$comment){
    echo "<script>alert('评论不存在'); location.href='index.php';</script>";
    exit;
}

// 权限判断
if(!($role === 'admin' || $comment['user_id'] === $uid)){
    write_log($user['username'], $role, "非法评论删除尝试", "尝试删除评论ID=$id");
    echo "<script>alert('无权删除此评论'); location.href='index.php';</script>";
    exit;
}

// 执行删除
$stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
$stmt->bind_param("i", $id);
if($stmt->execute()){
    write_log($user['username'], $role, "删除评论", "评论ID=$id 内容预览=" . mb_substr($comment['content'],0,200));
    // 删除后回到原文章的显示位置或首页（这里回首页）
    header("Location: index.php");
    exit;
} else {
    echo "<script>alert('删除失败'); location.href='index.php';</script>";
    exit;
}

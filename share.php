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
if($pid <= 0){ header("Location:index.php"); exit; }

// 插入转发记录
$ins = $db->prepare("INSERT INTO shares(user_id, post_id) VALUES(?, ?)");
$ins->bind_param("ii", $uid, $pid);
$ins->execute();
write_log($username, $role, "转发", "转发文章ID=$pid");

header("Location: index.php");

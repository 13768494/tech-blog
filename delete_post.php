<?php
session_start();
include "db.php";
include "log.php";

if(!isset($_SESSION['user'])){
    echo "<script>alert('请先登录'); location.href='login.php';</script>";
    exit;
}
$user = $_SESSION['user'];
$uid = (int)$user['id'];
$role = $user['role'] ?? 'user';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0){
    echo "<script>alert('参数错误'); location.href='index.php';</script>";
    exit;
}

// 读取文章并检查
$q = $db->prepare("SELECT id, title, author_id FROM posts WHERE id = ?");
$q->bind_param("i",$id);
$q->execute();
$post = $q->get_result()->fetch_assoc();
$q->close();

if(!$post){
    echo "<script>alert('文章不存在'); location.href='index.php';</script>";
    exit;
}

// 权限判断：管理员可以删除任意文章；普通用户只能删除自己发布的文章
$canDelete = false;
if($role === 'admin') $canDelete = true;
elseif($post['author_id'] === $uid) $canDelete = true;

if(!$canDelete){
    write_log($user['username'], $role, "非法删除尝试", "尝试删除文章ID={$id}");
    echo "<script>alert('无权删除此文章'); location.href='index.php';</script>";
    exit;
}

// 进行删除（先删关联表，再删文章）
$db->begin_transaction();

try {
    // 删除 likes
    $stmt = $db->prepare("DELETE FROM likes WHERE post_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // 删除 comments
    $stmt = $db->prepare("DELETE FROM comments WHERE post_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // 删除 shares
    $stmt = $db->prepare("DELETE FROM shares WHERE post_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // 删除文章
    $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $db->commit();

    write_log($user['username'], $role, "删除博文", "文章ID={$id} 标题=" . ($post['title'] ?? ''));

    // 删除后跳回合适页面
    if($role === 'admin'){
        header("Location: admin_posts.php");
    } else {
        header("Location: user_posts.php");
    }
    exit;
} catch (Exception $e) {
    $db->rollback();
    write_log($user['username'], $role, "删除博文失败", "文章ID={$id} 错误=" . $e->getMessage());
    echo "<script>alert('删除失败'); location.href='index.php';</script>";
    exit;
}

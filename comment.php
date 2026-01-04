<?php
session_start();
include "db.php";
include "log.php";

// 获取文章 id
$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($pid <= 0){
    echo "<script>alert('参数错误'); location.href='index.php';</script>";
    exit;
}

// 读取文章标题（用于页面显示）
$pstmt = $db->prepare("SELECT title FROM posts WHERE id = ?");
$pstmt->bind_param("i", $pid);
$pstmt->execute();
$presult = $pstmt->get_result();
$post = $presult->fetch_assoc();
$pstmt->close();

if(!$post){
    echo "<script>alert('文章不存在或已被删除'); location.href='index.php';</script>";
    exit;
}

$post_title = $post['title'];

// 处理提交
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!isset($_SESSION['user'])){
        echo "<script>alert('请先登录'); location.href='login.php';</script>";
        exit;
    }
    $c = trim($_POST['c'] ?? '');
    if($c !== ''){
        $uid = (int)$_SESSION['user']['id'];
        $stmt = $db->prepare("INSERT INTO comments(user_id, post_id, content) VALUES(?, ?, ?)");
        $stmt->bind_param("iis", $uid, $pid, $c);
        if($stmt->execute()){
            // 记录日志，包含文章标题的前200字符方便审计
            $title_preview = mb_substr($post_title, 0, 200);
            write_log($_SESSION['user']['username'], $_SESSION['user']['role'], "评论", "文章ID=$pid 标题={$title_preview} 内容=" . mb_substr($c,0,200));
        }
        $stmt->close();
    }
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>评论</title></head><body>
<h3>对文章《<?php echo htmlspecialchars($post_title); ?>》发表评论</h3>
<form method="post">
    <textarea name="c" rows="6" cols="60" required></textarea><br>
    <button type="submit">提交</button>
</form>
<p><a href="index.php">返回主页</a></p>
</body></html>

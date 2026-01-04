<?php
session_start();
include "db.php";
include "log.php";

if(!isset($_SESSION['user'])){
    echo "<script>alert('请先登录后再撰写博文'); location.href='login.php';</script>";
    exit;
}

$msg = "";
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if($title === '' || $content === ''){
        $msg = "标题与内容不能为空";
    } else {
        $stmt = $db->prepare("INSERT INTO posts(title, content, author_id) VALUES(?,?,?)");
        $uid = (int)$_SESSION['user']['id'];
        $stmt->bind_param("ssi", $title, $content, $uid);
        if($stmt->execute()){
            write_log($_SESSION['user']['username'], $_SESSION['user']['role'], "新建博文", "标题=" . mb_substr($title,0,200));
            header("Location: index.php");
            exit;
        } else {
            $msg = "发布失败，请稍后重试";
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>写博文</title></head><body>
<p><a href="index.php">返回主页</a> | <a href="dashboard.php">返回后台</a></p>
<h2>撰写新博文</h2>
<?php if($msg) echo '<p style="color:red">'.htmlspecialchars($msg).'</p>'; ?>
<form method="post">
    标题：<br><input type="text" name="title" style="width:80%" required><br><br>
    内容：<br><textarea name="content" rows="12" cols="80" required></textarea><br><br>
    <button type="submit">发布</button>
</form>
</body></html>

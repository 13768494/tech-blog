<?php
session_start();
include "db.php";
include "log.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){
    die("无权限");
}
$me = $_SESSION['user'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0){
    echo "参数错误";
    exit;
}

// 处理提交
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if($title === '' || $content === ''){
        $err = "标题与内容不能为空";
    } else {
        $up = $db->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $up->bind_param("ssi", $title, $content, $id);
        if($up->execute()){
            write_log($me['username'], $me['role'], "编辑博文", "文章ID=$id 标题=" . mb_substr($title,0,200));
            header("Location: admin_posts.php");
            exit;
        } else {
            $err = "更新失败";
        }
        $up->close();
    }
}

// 读取文章
$q = $db->prepare("SELECT id, title, content FROM posts WHERE id = ?");
$q->bind_param("i", $id);
$q->execute();
$post = $q->get_result()->fetch_assoc();
$q->close();
if(!$post){
    echo "文章不存在";
    exit;
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>编辑博文</title></head><body>
<p><a href="admin_posts.php">返回博文管理</a> | <a href="index.php">返回主页</a></p>
<h2>编辑文章 #<?php echo $post['id']; ?></h2>
<?php if(!empty($err)) echo '<p style="color:red">'.htmlspecialchars($err).'</p>'; ?>
<form method="post">
    标题：<br><input type="text" name="title" style="width:80%" value="<?php echo htmlspecialchars($post['title']); ?>" required><br><br>
    内容：<br><textarea name="content" rows="12" cols="80" required><?php echo htmlspecialchars($post['content']); ?></textarea><br><br>
    <button type="submit">保存修改</button>
</form>
</body></html>

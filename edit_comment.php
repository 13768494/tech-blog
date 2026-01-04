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
if($id <= 0){
    echo "<script>alert('参数错误'); location.href='index.php';</script>";
    exit;
}

// 读取评论
$q = $db->prepare("SELECT id, user_id, post_id, content FROM comments WHERE id = ?");
$q->bind_param("i",$id);
$q->execute();
$comment = $q->get_result()->fetch_assoc();
$q->close();

if(!$comment){
    echo "<script>alert('评论不存在'); location.href='index.php';</script>";
    exit;
}

// 权限：作者或管理员
if(!($role === 'admin' || $comment['user_id'] === $uid)){
    write_log($user['username'], $role, "非法评论编辑尝试", "尝试编辑评论ID=$id");
    echo "<script>alert('无权编辑此评论'); location.href='index.php';</script>";
    exit;
}

$err = "";
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $content = trim($_POST['content'] ?? '');
    if($content === ''){
        $err = "评论内容不能为空";
    } else {
        $up = $db->prepare("UPDATE comments SET content = ? WHERE id = ?");
        $up->bind_param("si", $content, $id);
        if($up->execute()){
            write_log($user['username'], $role, "编辑评论", "评论ID=$id 内容预览=" . mb_substr($content,0,200));
            header("Location: index.php");
            exit;
        } else {
            $err = "更新失败";
        }
        $up->close();
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>编辑评论</title></head><body>
<p><a href="index.php">返回主页</a></p>
<h2>编辑评论 #<?php echo $comment['id']; ?></h2>
<?php if($err) echo '<p style="color:red">'.htmlspecialchars($err).'</p>'; ?>
<form method="post">
    <textarea name="content" rows="6" cols="80" required><?php echo htmlspecialchars($comment['content']); ?></textarea><br><br>
    <button type="submit">保存修改</button>
    &nbsp; <a href="delete_comment.php?id=<?php echo $comment['id']; ?>&post_id=<?php echo $comment['post_id']; ?>" onclick="return confirm('确定删除该评论吗？')">删除评论</a>
</form>
</body></html>

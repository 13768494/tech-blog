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
    echo "参数错误";
    exit;
}

// 读取文章（并检查是否存在）
$q = $db->prepare("SELECT id, title, content, author_id FROM posts WHERE id = ?");
$q->bind_param("i", $id);
$q->execute();
$post = $q->get_result()->fetch_assoc();
$q->close();

if(!$post){
    echo "<script>alert('文章不存在'); location.href='index.php';</script>";
    exit;
}

// 权限检查：管理员可编辑任何文章；普通用户仅能编辑自己发布的文章
$canEdit = false;
if($role === 'admin'){
    $canEdit = true;
} elseif($post['author_id'] === $uid){
    $canEdit = true;
}

if(!$canEdit){
    // 记录日志：非法访问或修改尝试
    write_log($user['username'], $role, "非法修改尝试", "尝试修改文章ID=$id");
    echo "<script>alert('无权编辑此文章'); location.href='index.php';</script>";
    exit;
}

// 处理提交（保存修改）
$err = "";
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if($title === '' || $content === ''){
        $err = "标题与内容不能为空";
    } else {
        $up = $db->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $up->bind_param("ssi", $title, $content, $id);
        if($up->execute()){
            write_log($user['username'], $role, "编辑博文", "文章ID=$id 标题=" . mb_substr($title,0,200));
            // 编辑成功后返回“我的博文”页（若是管理员也可返回 admin_posts）
            if($role === 'admin'){
                header("Location: admin_posts.php");
            } else {
                header("Location: user_posts.php");
            }
            exit;
        } else {
            $err = "更新失败";
        }
        $up->close();
    }
}

// 页面表单（显示原始文章内容）
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>编辑博文</title></head><body>
<p>
    <a href="<?php echo ($role==='admin') ? 'admin_posts.php' : 'user_posts.php'; ?>">返回</a> |
    <a href="index.php">返回主页</a>
</p>
<h2>编辑文章 #<?php echo $post['id']; ?></h2>
<?php if($err) echo '<p style="color:red">'.htmlspecialchars($err).'</p>'; ?>
<form method="post">
    标题：<br><input type="text" name="title" style="width:80%" value="<?php echo htmlspecialchars($post['title']); ?>" required><br><br>
    内容：<br><textarea name="content" rows="12" cols="80" required><?php echo htmlspecialchars($post['content']); ?></textarea><br><br>
    <button type="submit">保存修改</button>
    &nbsp;&nbsp;
    <!-- 删除快捷入口（也会走 delete_post.php 的权限校验） -->
    <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('确定删除此文章吗？此操作不可恢复。')">删除文章</a>
</form>
</body></html>

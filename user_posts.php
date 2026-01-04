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

// 读取当前用户的文章
$stmt = $db->prepare("SELECT id, title, created_at FROM posts WHERE author_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>我的博文</title></head><body>
<p><a href="dashboard.php">返回后台</a> | <a href="index.php">返回主页</a></p>
<h2>我的博文（<?php echo htmlspecialchars($user['username']); ?>）</h2>
<p><a href="create_post.php">撰写新博文</a></p>

<table border="1" cellpadding="6">
<tr><th>ID</th><th>标题</th><th>创建时间</th><th>操作</th></tr>
<?php while($row = $res->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo htmlspecialchars($row['title']); ?></td>
    <td><?php echo $row['created_at']; ?></td>
    <td>
        <a href="edit_post.php?id=<?php echo $row['id']; ?>">编辑</a>
        |
        <a href="delete_post.php?id=<?php echo $row['id']; ?>" onclick="return confirm('确定删除此文章吗？此操作不可恢复。')">删除</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
</body></html>

<?php
session_start();
include "db.php";
include "log.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){
    die("无权限");
}
$me = $_SESSION['user'];
$errorMsg = "";
$successMsg = "";

// 读取所有文章
$res = $db->query("SELECT p.id, p.title, p.created_at, u.username FROM posts p LEFT JOIN users u ON p.author_id=u.id ORDER BY p.id DESC");
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>管理员 - 博文管理</title></head><body>
<p><a href="index.php">返回主页</a> | <a href="dashboard.php">返回后台</a> | <a href="admin.php">用户管理</a></p>
<h2>博文管理（管理员）</h2>
<?php if($errorMsg) echo '<p style="color:red">'.htmlspecialchars($errorMsg).'</p>'; ?>
<?php if($successMsg) echo '<p style="color:green">'.htmlspecialchars($successMsg).'</p>'; ?>

<table border="1" cellpadding="6">
<tr><th>ID</th><th>标题</th><th>作者</th><th>创建时间</th><th>操作</th></tr>
<?php while($row = $res->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo htmlspecialchars($row['title']); ?></td>
    <td><?php echo htmlspecialchars($row['username'] ?? '匿名'); ?></td>
    <td><?php echo $row['created_at']; ?></td>
    <td>
        <a href="edit_post.php?id=<?php echo $row['id']; ?>">编辑</a> |
        <a href="delete_post.php?id=<?php echo $row['id']; ?>" onclick="return confirm('确定删除该博文吗？此操作不可恢复。')">删除</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
</body></html>

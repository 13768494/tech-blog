<?php
session_start();
if(!isset($_SESSION['user'])){
    echo "<script>alert('用户账户未登录，请先登录账户！'); window.location.href='index.php';</script>";
    exit;
}
include "db.php";
include "log.php";

$user = $_SESSION['user'];
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>后台</title></head><body>
<p><a href="index.php">返回主页</a></p>
<h2>后台（欢迎你, <?php echo htmlspecialchars($user['username']); ?>）</h2>
<?php if(!empty($user['avatar'])): ?>
    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" style="width:64px;height:64px;border-radius:6px">
<?php endif; ?>
<ul>
    <li><a href="profile.php">个人资料</a></li>
    <li><a href="user_posts.php">我的博文</a></li> 
    <?php if($user['role']==='admin'): ?>
        <li><a href="admin.php">用户管理</a></li>
        <li><a href="admin_posts.php">博文管理</a></li>
        <li><a href="admin_view_logs.php">查看系统日志</a></li>        
    <?php endif; ?>
    <li><a href="logout.php">退出</a></li>
</ul>
</body></html>

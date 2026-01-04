<?php
session_start();
include "db.php";
include "log.php";

if(!isset($_SESSION['user'])){
    echo "<script>alert('请先登录'); location.href='login.php';</script>";
    exit;
}
$user = $_SESSION['user'];
$errMsg = "";
$succMsg = "";

// 处理修改用户名（需求7 + 需求2）
// 如果新用户名与当前一致 -> 提示 "修改失败，用户名与当前一致！"
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newname'])){
    $new = trim($_POST['newname']);
    if($new === ""){
        $errMsg = "新用户名不能为空";
    } else {
        if($new === $user['username']){
            $errMsg = "修改失败，用户名与当前一致！"; // **需求2：同名提示**
        } else {
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id <> ?");
            $stmt->bind_param("si", $new, $user['id']);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0){
                $errMsg = "新用户名冲突，请重新选择！";
            } else {
                $up = $db->prepare("UPDATE users SET username = ? WHERE id = ?");
                $up->bind_param("si", $new, $user['id']);
                if($up->execute()){
                    write_log($user['username'], $user['role'], "修改资料", "修改用户名为 $new (由个人资料页)");
                    $_SESSION['user']['username'] = $new;
                    $user['username'] = $new;
                    $succMsg = "用户名修改成功";
                } else {
                    $errMsg = "修改失败";
                }
            }
            $stmt->close();
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>个人资料</title></head><body>
<p><a href="index.php">返回主页</a> | <a href="dashboard.php">返回后台</a></p>

<h2>个人资料（欢迎你, <?php echo htmlspecialchars($user['username']); ?>）</h2>
<?php if(!empty($user['avatar'])): ?>
    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" style="width:96px;height:96px;border-radius:6px"><br>
<?php else: ?>
    <img src="uploads/default.png" style="width:96px;height:96px;border-radius:6px"><br>
<?php endif; ?>

<?php
if($errMsg) {
    echo '<p style="color:red">'.htmlspecialchars($errMsg).'</p>';
} elseif($succMsg) {
    echo '<p style="color:green">'.htmlspecialchars($succMsg).'</p>';
}
?>

<h3>修改用户名</h3>
<form method="post">
    新用户名:<input type="text" name="newname" required>
    <button type="submit">保存</button>
</form>

<h3>修改头像</h3>
<form action="upload.php" method="post" enctype="multipart/form-data">
    上传头像: <input type="file" name="pic" accept="image/*" required>
    <button type="submit">上传</button>
</form>
</body></html>

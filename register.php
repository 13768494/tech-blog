<?php
session_start();
include "db.php";
include "log.php";

$msg = "";
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $u = trim($_POST['user'] ?? '');
    $p = $_POST['pass'] ?? '';

    if($u === "" || $p === ""){
        $msg = "用户名和密码不能为空";
    } else {
        // 查重
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s",$u);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0){
            $msg = "该用户已存在，请重新注册！"; // 需求4
        } else {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO users(username,password) VALUES(?,?)");
            $ins->bind_param("ss", $u, $hash);
            if($ins->execute()){
                write_log($u,"user","注册","新用户注册成功");
                // 注册成功跳转登录页
                header("Location: login.php");
                exit;
            } else {
                $msg = "注册失败，请稍后重试";
            }
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>注册</title></head><body>
<h2>注册</h2>
<?php if($msg) echo '<p style="color:red">'.htmlspecialchars($msg).'</p>'; ?>
<form method="post">
    用户名: <input type="text" name="user" required><br>
    密码: <input type="password" name="pass" required><br>
    <button type="submit">注册</button>
</form>
<p><a href="index.php">返回主页</a></p>
</body></html>

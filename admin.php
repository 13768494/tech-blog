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

// 接收外部返回的消息
if(!empty($_GET['err'])) $errorMsg = rawurldecode($_GET['err']);
if(!empty($_GET['msg'])) $successMsg = rawurldecode($_GET['msg']);

// 处理删除操作（GET ?del=ID）
if(isset($_GET['del'])){
    $delId = (int)$_GET['del'];
    if($delId === (int)$me['id']){
        $errorMsg = "删除失败，无法删除当前用户！"; // 红色
    } else {
        $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param("i",$delId);
        $stmt->execute();
        $rres = $stmt->get_result();
        $r = $rres ? $rres->fetch_assoc() : null;
        $stmt->close();
        if($r){
            $delstmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $delstmt->bind_param("i",$delId);
            if($delstmt->execute()){
                write_log($me['username'], $me['role'], "删除用户", "删除用户ID=$delId 用户名=".$r['username']);
                $successMsg = "删除用户成功";
            } else {
                $errorMsg = "删除用户失败";
            }
            $delstmt->close();
        } else {
            $errorMsg = "目标用户不存在";
        }
    }
}

// 处理新增用户（POST action=create）
// 需求1：新增时只在成功时提示“新增用户成功”，不输出“目标用户不存在”之类无关提示
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create'){
    $newu = trim($_POST['new_user'] ?? '');
    $newp = $_POST['new_pass'] ?? '';
    $newrole = $_POST['new_role'] === 'admin' ? 'admin' : 'user';
    if($newu === '' || $newp === ''){
        $errorMsg = "用户名或密码不能为空";
    } else {
        $chk = $db->prepare("SELECT id FROM users WHERE username = ?");
        $chk->bind_param("s",$newu);
        $chk->execute();
        $chk->store_result();
        if($chk->num_rows > 0){
            $errorMsg = "该用户已存在，无法新增";
        } else {
            $hash = password_hash($newp, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO users(username,password,role,avatar) VALUES(?,?,?,?)");
            $defaultAvatar = 'uploads/default.png';
            $ins->bind_param("ssss", $newu, $hash, $newrole, $defaultAvatar);
            if($ins->execute()){
                write_log($me['username'], $me['role'], "新增用户", "新增用户名=$newu role=$newrole");
                $successMsg = "新增用户成功"; // 仅此提示
            } else {
                $errorMsg = "新增用户失败";
            }
            $ins->close();
        }
        $chk->close();
    }
}

// 修改用户名（管理员为用户改名） POST action=edit_name
// 需求2：若目标用户是 admin，则不允许修改用户名，提示“管理员账户不允许修改！”
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_name'){
    $targetId = (int)$_POST['uid'];
    $newname = trim($_POST['newname'] ?? '');
    if($newname === ''){
        $errorMsg = "新用户名不能为空";
    } else {
        // 先读取目标当前用户名及角色
        $q = $db->prepare("SELECT username, role FROM users WHERE id = ?");
        $q->bind_param("i", $targetId);
        $q->execute();
        $qr = $q->get_result();
        $curRow = $qr ? $qr->fetch_assoc() : null;
        $q->close();

        if(!$curRow){
            $errorMsg = "目标用户不存在";
        } else {
            // 如果目标用户是管理员账户，则禁止修改用户名（需求2）
            if(isset($curRow['role']) && $curRow['role'] === 'admin'){
                $errorMsg = "管理员账户不允许修改！";
            } else {
                $currentUsername = $curRow['username'];
                // 如果新用户名与目标当前用户名一致 -> 错误（之前已有保护，这里也保留）
                if($newname === $currentUsername){
                    $errorMsg = "修改失败，用户名与当前一致！";
                } else {
                    $chk = $db->prepare("SELECT id FROM users WHERE username = ? AND id <> ?");
                    $chk->bind_param("si", $newname, $targetId);
                    $chk->execute();
                    $chk->store_result();
                    if($chk->num_rows > 0){
                        $errorMsg = "新用户名冲突，请重新选择！";
                    } else {
                        $up = $db->prepare("UPDATE users SET username = ? WHERE id = ?");
                        $up->bind_param("si", $newname, $targetId);
                        if($up->execute()){
                            write_log($me['username'], $me['role'], "管理员修改用户名", "目标ID=$targetId 新用户名=$newname");
                            $successMsg = "修改用户名成功";
                            if($targetId === (int)$me['id']){
                                $_SESSION['user']['username'] = $newname;
                                $me['username'] = $newname;
                            }
                        } else {
                            $errorMsg = "修改用户名失败";
                        }
                        $up->close();
                    }
                    $chk->close();
                }
            }
        }
    }
}

// 修改用户角色 POST action=change_role
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_role'){
    $targetId = (int)($_POST['uid'] ?? 0);
    $newrole = ($_POST['role'] === 'admin') ? 'admin' : 'user';
    if($targetId <= 0){
        $errorMsg = "目标用户错误";
    } else if($targetId === (int)$me['id']){
        // 禁止修改自己角色
        $errorMsg = "管理员身份无法修改！"; // 之前已有保护
    } else {
        $chk = $db->prepare("SELECT username, role FROM users WHERE id = ?");
        $chk->bind_param("i", $targetId);
        $chk->execute();
        $reschk = $chk->get_result()->fetch_assoc();
        $chk->close();
        if(!$reschk){
            $errorMsg = "目标用户不存在";
        } else {
            if($reschk['role'] === $newrole){
                $successMsg = "角色未变化";
            } else {
                $up = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
                $up->bind_param("si", $newrole, $targetId);
                if($up->execute()){
                    write_log($me['username'], $me['role'], "修改用户角色", "目标ID=$targetId 由{$reschk['role']} => $newrole");
                    $successMsg = "修改角色成功";
                } else {
                    $errorMsg = "修改角色失败";
                }
                $up->close();
            }
        }
    }
}

// 读取用户列表
$res = $db->query("SELECT id, username, role, avatar, created_at FROM users ORDER BY id ASC");
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>用户管理（管理员）</title></head><body>
<p><a href="index.php">返回主页</a> | <a href="dashboard.php">返回后台</a></p>

<h2>用户管理（管理员） - 欢迎 <?php echo htmlspecialchars($me['username']); ?></h2>
<?php if(!empty($me['avatar'])): ?>
    <img src="<?php echo htmlspecialchars($me['avatar']); ?>" style="width:48px;height:48px;border-radius:6px">
<?php endif; ?>

<?php
if($errorMsg) echo '<p style="color:red">'.htmlspecialchars($errorMsg).'</p>';
if($successMsg) echo '<p style="color:green">'.htmlspecialchars($successMsg).'</p>';
?>

<h3>新增用户</h3>
<form method="post">
    <input type="hidden" name="action" value="create">
    用户名: <input type="text" name="new_user" required>
    密码: <input type="password" name="new_pass" required>
    身份: <select name="new_role"><option value="user">普通用户</option><option value="admin">管理员</option></select>
    <button type="submit">新增</button>
</form>

<hr>
<h3>用户列表</h3>
<table border="1" cellpadding="6">
<tr><th>ID</th><th>用户名</th><th>角色</th><th>头像</th><th>创建时间</th><th>操作</th></tr>
<?php while($u = $res->fetch_assoc()): ?>
<tr>
    <td><?php echo $u['id']; ?></td>
    <td>
        <?php echo htmlspecialchars($u['username']); ?>
        <form method="post" style="display:inline;margin-left:8px">
            <input type="hidden" name="action" value="edit_name">
            <input type="hidden" name="uid" value="<?php echo $u['id']; ?>">
            <input type="text" name="newname" placeholder="新用户名">
            <button type="submit">修改用户名</button>
        </form>
    </td>

    <td>
        <!-- 角色修改表单 -->
        <form method="post" style="display:inline">
            <input type="hidden" name="action" value="change_role">
            <input type="hidden" name="uid" value="<?php echo $u['id']; ?>">
            <select name="role">
                <option value="user" <?php if($u['role']==='user') echo 'selected'; ?>>普通用户</option>
                <option value="admin" <?php if($u['role']==='admin') echo 'selected'; ?>>管理员</option>
            </select>
            <button type="submit">修改身份</button>
        </form>
    </td>

    <td>
        <?php if(!empty($u['avatar'])): ?>
            <img src="<?php echo htmlspecialchars($u['avatar']); ?>" style="width:40px;height:40px;border-radius:4px">
        <?php else: ?>
            <img src="uploads/default.png" style="width:40px;height:40px;border-radius:4px">
        <?php endif; ?>
        <form action="admin_upload.php" method="post" enctype="multipart/form-data" style="margin-top:6px">
            <input type="hidden" name="uid" value="<?php echo $u['id']; ?>">
            <input type="file" name="pic" accept="image/*" required>
            <button type="submit">上传头像</button>
        </form>
    </td>
    <td><?php echo $u['created_at']; ?></td>
    <td>
        <a href="?del=<?php echo $u['id']; ?>" onclick="return confirm('确定删除用户吗？')">删除</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

</body></html>

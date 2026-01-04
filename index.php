<?php
session_start();
include "db.php";
include "log.php";

// 读取帖子
$postStmt = $db->prepare("SELECT p.id, p.title, p.content, p.author_id, p.created_at, u.username, u.avatar
                          FROM posts p LEFT JOIN users u ON p.author_id = u.id
                          ORDER BY p.id DESC");
$postStmt->execute();
$res = $postStmt->get_result();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>技术论坛博客</title></head>
<body>
<h1>技术论坛博客</h1>

<?php if(isset($_SESSION['user'])): 
    $cur = $_SESSION['user'];
    $avatar = !empty($cur['avatar']) ? $cur['avatar'] : 'uploads/default.png';
?>
    <img src="<?php echo htmlspecialchars($avatar); ?>" style="width:40px;height:40px;border-radius:6px;vertical-align:middle;margin-right:6px">
    已登录：<?php echo htmlspecialchars($cur['username']); ?> |
    <a href="logout.php">退出</a> |
    <a href="dashboard.php">进入后台</a> |
    <a href="create_post.php">写博文</a>
<?php else: ?>
    <a href="login.php">登录</a> | <a href="register.php">注册</a> |
    <a href="dashboard.php">进入后台</a>
<?php endif; ?>

<hr>

<?php while($p = $res->fetch_assoc()): ?>
    <div style="border:1px solid #ccc;padding:10px;margin:10px">
        <h3><?php echo htmlspecialchars($p['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($p['content'])); ?></p>
        <p>作者：<?php echo htmlspecialchars($p['username'] ?? '匿名'); ?>
           <?php if(!empty($p['avatar'])): ?>
               <img src="<?php echo htmlspecialchars($p['avatar']); ?>" style="width:32px;height:32px;border-radius:3px;vertical-align:middle">
           <?php endif; ?>
           | 发表于：<?php echo $p['created_at']; ?></p>

        <?php
        $pid = (int)$p['id'];
        $cntLike = $db->query("SELECT COUNT(*) AS c FROM likes WHERE post_id=$pid")->fetch_assoc()['c'] ?? 0;
        $cntShare = $db->query("SELECT COUNT(*) AS c FROM shares WHERE post_id=$pid")->fetch_assoc()['c'] ?? 0;
        ?>

        <p>
            <a href="like.php?id=<?php echo $pid; ?>">👍 点赞 (<?php echo $cntLike; ?>)</a> |
            <a href="share.php?id=<?php echo $pid; ?>">🔁 转发 (<?php echo $cntShare; ?>)</a> |
            <a href="comment.php?id=<?php echo $pid; ?>">💬 评论</a>
        </p>

        <!-- 展示评论（包含 user_id，便于显示编辑/删除操作） -->
        <div style="padding-left:10px;border-top:1px dashed #ddd;margin-top:8px">
            <strong>评论：</strong><br>
            <?php
            $cstmt = $db->prepare("SELECT c.id, c.content, c.created_at, c.user_id, u.username, u.avatar FROM comments c LEFT JOIN users u ON c.user_id=u.id WHERE c.post_id=? ORDER BY c.id ASC");
            $cstmt->bind_param("i",$pid);
            $cstmt->execute();
            $cres = $cstmt->get_result();
            if($cres->num_rows==0){
                echo "<em>暂无评论</em>";
            } else {
                while($cc = $cres->fetch_assoc()){
                    echo '<div style="margin:6px 0;padding:6px;border:1px solid #f0f0f0">';
                    if(!empty($cc['avatar'])) echo '<img src="'.htmlspecialchars($cc['avatar']).'" style="width:24px;height:24px;border-radius:3px;vertical-align:middle;margin-right:6px">';
                    echo '<strong>'.htmlspecialchars($cc['username'] ?? '匿名').'</strong> ';
                    echo '<small>['.$cc['created_at'].']</small><br>';
                    echo nl2br(htmlspecialchars($cc['content']));
                    // 如果当前用户已登录，且是该评论作者或当前用户为 admin，则显示 编辑/删除 链接
                    if(isset($_SESSION['user'])){
                        $curUser = $_SESSION['user'];
                        $isAuthor = ($curUser['id'] == $cc['user_id']);
                        $isAdmin = ($curUser['role'] === 'admin');
                        if($isAuthor || $isAdmin){
                            echo '<div style="margin-top:6px">';
                            echo '<a href="edit_comment.php?id='.intval($cc['id']).'">编辑</a> | ';
                            echo '<a href="delete_comment.php?id='.intval($cc['id']).'&post_id='.intval($pid).'" onclick="return confirm(\'确定删除该评论吗？\')">删除</a>';
                            echo '</div>';
                        }
                    }
                    echo '</div>';
                }
            }
            $cstmt->close();
            ?>
        </div>
    </div>
<?php endwhile; ?>
</body>
</html>

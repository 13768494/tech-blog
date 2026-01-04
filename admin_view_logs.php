<?php
session_start();
include "db.php";
include "log.php";
include "logs_module.php"; // 模块化日志工具

// 只允许 admin 访问
if(!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin'){
    http_response_code(403);
    echo "无权限访问";
    exit;
}

$logPath = __DIR__ . '/logs/system.log';

// 处理参数：limit, offset, keyword, action=download
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : null;
$action = isset($_GET['action']) ? $_GET['action'] : null;

// 如果要下载原始日志
if($action === 'download'){
    if(!is_readable($logPath)){
        echo "日志文件不可读或不存在";
        exit;
    }
    // 下载头
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="system.log"');
    // 输出文件
    readfile($logPath);
    exit;
}

// 获取 stats 与行（模块化调用）
$stats = get_log_stats($logPath);
$logResult = get_log_lines($logPath, $limit, $offset, $keyword);

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>系统日志查看（管理员）</title>
<style>
    body{font-family:Arial,Helvetica,sans-serif;font-size:14px;margin:18px;}
    .controls{margin-bottom:12px;}
    .meta{background:#f9f9f9;padding:10px;border:1px solid #eee;margin-bottom:12px;}
    .log-line{font-family:monospace;white-space:pre-wrap;padding:4px 6px;border-bottom:1px solid #f0f0f0;}
    .actions{margin-top:10px;}
    .note{color:#666;font-size:13px;}
</style>
</head>
<body>
<p><a href="dashboard.php">返回后台</a> | <a href="index.php">返回主页</a></p>
<h2>系统日志查看（管理员）</h2>

<div class="meta">
    <?php if(!$stats['exists']): ?>
        <strong>日志文件不存在：</strong> <?php echo htmlspecialchars($logPath); ?>
    <?php else: ?>
        <div>文件：<code><?php echo htmlspecialchars($logPath); ?></code></div>
        <div>大小：<?php echo round($stats['size']/1024,2); ?> KB</div>
        <div>行数（估算）：<?php echo number_format($stats['lines']); ?></div>
        <div>最后修改：<?php echo $stats['mtime'] ? date("Y-m-d H:i:s", $stats['mtime']) : '-'; ?></div>
    <?php endif; ?>
</div>

<form method="get" class="controls">
    <label>关键字搜索：<input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword ?? ''); ?>"></label>
    &nbsp;
    <label>行数 limit：<input type="number" name="limit" value="<?php echo htmlspecialchars($limit); ?>" min="1" max="2000" style="width:80px"></label>
    &nbsp;
    <label>offset（从最新向前偏移行数）：<input type="number" name="offset" value="<?php echo htmlspecialchars($offset); ?>" min="0" style="width:80px"></label>
    &nbsp;
    <button type="submit">刷新</button>
    &nbsp;
    <a href="?action=download">下载原始日志</a>
</form>

<p class="note">提示：大文件时请适当减小 limit（默认 200），并优先使用关键字过滤。</p>

<div style="border:1px solid #ddd;padding:6px;background:#fff;margin-top:8px;max-height:600px;overflow:auto;">
    <?php
    if(empty($logResult['lines'])){
        echo "<div class='log-line'>（没有匹配的日志或文件为空）</div>";
    } else {
        echo "<div style='padding:6px;color:#444;font-size:13px;'>显示行：{$logResult['start_line']} — {$logResult['end_line']} （最新行号：{$logResult['last_line_index']}）</div>";
        foreach($logResult['lines'] as $ln){
            // 为了安全，用 htmlspecialchars
            echo '<div class="log-line">'.htmlspecialchars($ln).'</div>';
        }
    }
    ?>
</div>

<div class="actions">
    <!-- 简单翻页：上一页/下一页（offset 调整） -->
    <?php
    $prevOffset = max(0, $offset + $limit);
    $nextOffset = max(0, $offset - $limit);
    $baseParams = [];
    if($keyword) $baseParams['keyword'] = $keyword;
    $baseParams['limit'] = $limit;
    ?>
    <a href="?<?php echo http_build_query(array_merge($baseParams, ['offset' => $prevOffset])); ?>">上一页（更新更旧的日志）</a> |
    <a href="?<?php echo http_build_query(array_merge($baseParams, ['offset' => $nextOffset])); ?>">下一页（更接近最新）</a>
</div>

</body>
</html>

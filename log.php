<?php
// log.php - 日志函数（全站调用）
function write_log($user, $role, $action, $detail){
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $time = date("Y-m-d H:i:s");
    $line = "[$time] | $user | $role | $action | $detail | $ip\n";
    // 确保 logs 目录存在且可写
    file_put_contents(__DIR__ . "/logs/system.log", $line, FILE_APPEND);
}
?>

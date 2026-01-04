<?php
// logs_module.php
// 模块化的日志读取工具（供 admin_view_logs.php 调用）
// 使用说明：包含本文件后可调用 get_log_stats($path) 与 get_log_lines($path,$limit,$offset,$keyword)

// 获取日志基础信息
function get_log_stats($filepath){
    if(!file_exists($filepath)){
        return [
            'exists' => false,
            'size' => 0,
            'lines' => 0,
            'mtime' => null,
        ];
    }
    $size = filesize($filepath);
    $mtime = filemtime($filepath);
    // 统计行数（逐行遍历，避免一次性载入内存）
    $lines = 0;
    $fh = fopen($filepath, 'r');
    if($fh){
        while(!feof($fh)){
            fgets($fh);
            $lines++;
        }
        fclose($fh);
    }
    return [
        'exists' => true,
        'size' => $size,
        'lines' => $lines,
        'mtime' => $mtime,
    ];
}

// 读取日志指定范围的行（按行号）并支持关键字过滤
// $limit: 返回的最大行数（>=1）
// $offset: 从文件末尾向前的偏移（0 表示最新行），offset 与 limit 在实现中共同决定开始行
// $keyword: 若非 null，则仅返回包含关键字（不区分大小写）的行
// 注意：为了避免一次性载入超大文件，此函数使用 SplFileObject 的行号定位
function get_log_lines($filepath, $limit = 200, $offset = 0, $keyword = null, $max_limit = 2000){
    $limit = (int)$limit;
    $offset = (int)$offset;
    if($limit <= 0) $limit = 50;
    if($limit > $max_limit) $limit = $max_limit; // 最大返回行数保护

    if(!is_readable($filepath)) return ['lines'=>[], 'start_line'=>0, 'end_line'=>0];

    $file = new SplFileObject($filepath, 'r');
    // 将文件指针移动到末尾以获取最后行号
    $file->seek(PHP_INT_MAX);
    $lastLineIndex = $file->key(); // 0-based index of last line
    // 目标结束行为 lastLineIndex - offset
    $endIndex = $lastLineIndex - $offset;
    if($endIndex < 0) $endIndex = 0;
    // 起始行
    $startIndex = $endIndex - $limit + 1;
    if($startIndex < 0) $startIndex = 0;

    $results = [];
    // 逐行读取并应用关键字过滤（返回顺序为从旧到新）
    for($i = $startIndex; $i <= $endIndex; $i++){
        $file->seek($i);
        $line = $file->current();
        if($line === false) continue;
        // trim newline for display
        $trimmed = rtrim($line, "\r\n");
        if($keyword === null || $keyword === ''){
            $results[] = $trimmed;
        } else {
            if(stripos($trimmed, $keyword) !== false){
                $results[] = $trimmed;
            }
        }
    }
    return [
        'lines' => $results,
        'start_line' => $startIndex + 1, // human-friendly 1-based
        'end_line' => $endIndex + 1,
        'last_line_index' => $lastLineIndex + 1,
    ];
}

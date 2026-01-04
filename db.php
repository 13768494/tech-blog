<?php
// db.php - 数据库连接（请根据实际修改 host/user/pass/dbname）
$DB_HOST = "数据库链接地址";
$DB_USER = "数据库账户";
$DB_PASS = "数据库密码";
$DB_NAME = "tech_blog";

$db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($db->connect_error) {
    die("数据库连接错误: " . $db->connect_error);
}
$db->set_charset("utf8mb4");
?>

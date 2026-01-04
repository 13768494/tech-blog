<?php
// db.php - 数据库连接（请根据实际修改 host/user/pass/dbname）
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "root";
$DB_NAME = "tech_blog";

$db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($db->connect_error) {
    die("数据库连接错误: " . $db->connect_error);
}
$db->set_charset("utf8mb4");
?>

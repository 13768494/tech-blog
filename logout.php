<?php
session_start(); include "log.php";
if(isset($_SESSION['user'])){
 write_log($_SESSION['user']['username'],$_SESSION['user']['role'],"退出","用户退出");
}
session_destroy();
header("location:index.php");

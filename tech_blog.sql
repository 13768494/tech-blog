/*
 Navicat Premium Data Transfer

 Source Server         : 5.7.26
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : tech_blog

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 30/12/2025 21:17:41
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for comments -- 评论
-- ----------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL,
  `post_id` int(11) NULL DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of comments
-- ----------------------------
INSERT INTO `comments` VALUES (3, 6, 2, '222222222222', '2025-12-29 22:01:13');
INSERT INTO `comments` VALUES (4, 6, 1, '111111111111', '2025-12-29 22:01:23');
INSERT INTO `comments` VALUES (5, 1, 2, '12312312', '2025-12-29 22:03:00');
INSERT INTO `comments` VALUES (6, 1, 1, '123123213', '2025-12-29 22:03:03');

-- ----------------------------
-- Table structure for likes -- 点赞
-- ----------------------------
DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL,
  `post_id` int(11) NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of likes
-- ----------------------------
INSERT INTO `likes` VALUES (1, 6, 2, '2025-12-29 22:01:07');
INSERT INTO `likes` VALUES (2, 6, 1, '2025-12-29 22:01:18');
INSERT INTO `likes` VALUES (3, 1, 2, '2025-12-29 22:02:56');
INSERT INTO `likes` VALUES (4, 1, 1, '2025-12-29 22:03:06');
INSERT INTO `likes` VALUES (5, 12, 2, '2025-12-29 22:41:05');
INSERT INTO `likes` VALUES (6, 12, 1, '2025-12-29 22:41:07');
INSERT INTO `likes` VALUES (7, 12, 5, '2025-12-30 19:51:49');
INSERT INTO `likes` VALUES (8, 12, 4, '2025-12-30 19:51:55');

-- ----------------------------
-- Table structure for posts -- 博文表
-- ----------------------------
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `author_id` int(11) NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of posts
-- ----------------------------
INSERT INTO `posts` VALUES (2, '一文看懂什么是数据库索引', '很多初学者在使用数据库时，会发现随着数据量变大，查询速度越来越慢。这时候，“索引”就显得尤为重要。\r\n\r\n一、索引是什么？\r\n索引可以理解为数据库中的“目录”，帮助数据库更快地定位到目标数据。\r\n\r\n二、为什么索引能提高查询速度？\r\n没有索引时，数据库需要从第一条记录开始一条一条扫描；\r\n有了索引后，数据库可以通过 B+Tree 快速定位到数据所在位置。\r\n\r\n三、什么时候该建索引？\r\n经常作为查询条件的字段\r\n经常参与排序和分组的字段\r\n\r\n四、总结\r\n合理的索引可以让查询速度提升数十倍，但滥用索引也会降低写入性能，需要平衡使用。', 4, '2025-12-29 21:28:55');
INSERT INTO `posts` VALUES (3, '为什么你的接口总是被攻击？浅谈 API 安全设计', '随着前后端分离架构的普及，API 接口已经成为系统的核心入口。但很多项目在开发初期往往忽略了安全设计，导致接口频繁被刷、被攻击。\r\n\r\n一、常见的接口安全问题\r\n接口无鉴权，任何人都可以访问\r\n参数无校验，存在 SQL 注入、XSS 风险\r\n缺少频率限制，被恶意刷接口\r\n\r\n二、必须要做的三道防线\r\n身份认证机制\r\n常见方式有 Token、JWT、Session 等。\r\n参数校验与过滤\r\n对所有输入进行校验，避免恶意构造参数。\r\n接口限流与风控\r\n防止暴力破解和恶意刷接口。\r\n\r\n三、总结\r\nAPI 安全不是“做不做”的问题，而是“什么时候被打”的问题。安全从来不是锦上添花，而是系统稳定运行的前提。', 16, '2025-12-29 22:48:24');
INSERT INTO `posts` VALUES (4, '初学者必须搞懂的五个 Linux 常用命令', 'Linux 是服务器端开发绕不开的操作系统，很多同学在刚接触 Linux 时会觉得命令行操作“又黑又冷”。其实只要掌握几个核心命令，就能完成 80% 的日常操作。\r\n下面介绍五个最常用、也最实用的 Linux 命令。\r\n\r\n一、ls —— 查看目录内容\r\nls 用于查看当前目录下有哪些文件。\r\n常用参数：\r\nls -l：显示详细信息\r\nls -a：显示隐藏文件\r\n\r\n二、cd —— 切换目录\r\ncd 用来进入不同目录，例如：\r\ncd /home/user\r\n\r\n三、mkdir —— 创建目录\r\nmkdir test\r\n用于新建文件夹。\r\n\r\n四、rm —— 删除文件或目录\r\nrm -rf test\r\n⚠ 注意：这个命令不可逆，请谨慎使用。\r\n\r\n五、cp —— 复制文件\r\ncp a.txt b.txt\r\n用于文件复制。\r\n\r\n六、总结\r\n掌握这些命令后，你已经可以在 Linux 环境下完成绝大多数基础操作，是迈向服务器开发的重要第一步。', 12, '2025-12-29 22:49:48');
INSERT INTO `posts` VALUES (5, '从零搭建一个基于 Flask 的简易博客系统', '在学习 Web 开发的过程中，亲手搭建一个属于自己的博客系统是一个非常经典且高性价比的练习项目。它不仅能帮助我们理解前后端交互流程，还能让我们掌握用户系统、权限控制、数据库设计等一整套完整的 Web 应用开发流程。\r\n本文将带你从零开始，搭建一个基于 Flask 的简易博客系统。\r\n\r\n一、项目功能规划\r\n在动手之前，先明确系统需要实现的基本功能：\r\n用户注册与登录\r\n发布博文\r\n编辑和删除自己的博文\r\n博文列表展示\r\n管理员后台管理功能\r\n这些功能已经覆盖了大部分 Web 项目的核心开发要点。\r\n\r\n二、项目技术选型\r\n后端框架：Flask\r\n数据库：MySQL / SQLite\r\nORM：SQLAlchemy\r\n前端模板：Jinja2\r\n样式框架：Bootstrap\r\n\r\n三、基础项目结构\r\n一个推荐的项目结构如下：\r\nblog/\r\n│─ app.py\r\n│─ models.py\r\n│─ forms.py\r\n│─ templates/\r\n│─ static/\r\n│─ config.py\r\n通过模块化结构，后期维护和扩展会更加方便。\r\n\r\n四、用户系统实现思路\r\n用户系统是博客的基础。通常我们需要：\r\n用户注册（用户名、密码、邮箱）\r\n密码加密存储（如 werkzeug.security）\r\n登录状态保持（Flask-Login）\r\n这一步的重点是安全性，千万不要明文存储密码。\r\n\r\n五、博文模块实现\r\n博文模块包含：\r\n发布博文\r\n编辑博文\r\n删除博文\r\n查询博文列表\r\n每篇博文都需要绑定一个作者字段，用来控制权限，确保“谁发的只能谁改”。\r\n\r\n六、总结\r\n通过这个项目，你可以完整掌握一个 Web 应用从设计、开发到部署的全流程。后续你还可以继续扩展：\r\n评论系统\r\n点赞与收藏\r\nMarkdown 编辑器\r\n博文分类与标签', 17, '2025-12-29 22:59:15');

-- ----------------------------
-- Table structure for shares -- 转发
-- ----------------------------
DROP TABLE IF EXISTS `shares`;
CREATE TABLE `shares`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL,
  `post_id` int(11) NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of shares
-- ----------------------------
INSERT INTO `shares` VALUES (1, 6, 2, '2025-12-29 22:01:08');
INSERT INTO `shares` VALUES (2, 6, 1, '2025-12-29 22:01:21');
INSERT INTO `shares` VALUES (3, 1, 2, '2025-12-29 22:02:57');
INSERT INTO `shares` VALUES (4, 1, 1, '2025-12-29 22:03:05');
INSERT INTO `shares` VALUES (5, 12, 2, '2025-12-29 22:41:05');
INSERT INTO `shares` VALUES (6, 12, 1, '2025-12-29 22:41:08');
INSERT INTO `shares` VALUES (7, 12, 5, '2025-12-30 19:51:51');

-- ----------------------------
-- Table structure for users -- 用户表
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `role` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'user',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'default.png',
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 18 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (16, '123', '$2y$10$1cQdn7YaWh7k.GEwy7DDN.027GJWoIPADahIb0T5816Ezd09A3056', 'user', 'uploads/admin_avatar_1767095485_f91ae25186bd.jpg', '2025-12-29 22:39:32');
INSERT INTO `users` VALUES (17, '111', '$2y$10$i8e6VKdU.UP4i5NaMlB0wOP7thu8iUlEAsjSgloMAMEHpnOQGCqvG', 'user', 'uploads/admin_avatar_1767095488_479b7fc3da0b.png', '2025-12-29 22:58:53');
INSERT INTO `users` VALUES (12, 'admin', '$2y$10$dif1cQPw1fnyJahilkoe/uPigAvU99O.TXq20SUN0qIhqwtDoQ0W6', 'admin', 'uploads/admin_avatar_1767095481_4c39046c1cac.gif', '2025-12-29 22:21:56');

SET FOREIGN_KEY_CHECKS = 1;

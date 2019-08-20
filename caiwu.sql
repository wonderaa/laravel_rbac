-- MySQL dump 10.14  Distrib 5.5.60-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: caiwu
-- ------------------------------------------------------
-- Server version	5.5.60-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `caiwu_account`
--

DROP TABLE IF EXISTS `caiwu_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `bank_card` varchar(30) NOT NULL COMMENT '银行卡号',
  `bank_name` varchar(50) NOT NULL COMMENT '银行名称',
  `create_at` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `realname` varchar(100) NOT NULL COMMENT '收款人姓名',
  PRIMARY KEY (`id`),
  UNIQUE KEY `bank_card` (`bank_card`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_account`
--

LOCK TABLES `caiwu_account` WRITE;
/*!40000 ALTER TABLE `caiwu_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `caiwu_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_admin_nav`
--

DROP TABLE IF EXISTS `caiwu_admin_nav`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_admin_nav` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单表',
  `pid` int(11) unsigned DEFAULT '0' COMMENT '所属菜单',
  `name` varchar(15) DEFAULT '' COMMENT '菜单名称',
  `mca` varchar(255) DEFAULT '' COMMENT '模块、控制器、方法',
  `ico` varchar(20) DEFAULT '' COMMENT 'font-awesome图标',
  `order_number` int(11) unsigned DEFAULT NULL COMMENT '排序',
  `update_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=217 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_admin_nav`
--

LOCK TABLES `caiwu_admin_nav` WRITE;
/*!40000 ALTER TABLE `caiwu_admin_nav` DISABLE KEYS */;
INSERT INTO `caiwu_admin_nav` VALUES (58,0,'后台管理','/Admin/Admin/show_index','',NULL,NULL),(59,58,'角色管理','/Admin/Role/index','',NULL,NULL),(60,58,'权限管理','/Admin/Rule/index','',NULL,NULL),(61,58,'管理员列表','/Admin/Admin/index','',NULL,NULL),(62,58,'菜单管理','/Admin/Menu/index','',NULL,NULL),(197,0,'VIP充值信息','/Admin/Widthdraw/menu','',NULL,NULL),(198,197,'游戏充值','/Admin/Widthdraw/game_recharge','',NULL,NULL),(199,197,'充值记录','/Admin/Widthdraw/recharge_record','',NULL,NULL),(1,0,'系统接单','/Admin/Order/apply','',NULL,NULL),(2,0,'订单管理','/Admin/Order/menu','',NULL,NULL),(202,2,'全部订单','/Admin/Order/index','',NULL,NULL),(203,2,'待处理订单','/Admin/Order/wait','',NULL,NULL),(204,197,'VIP充值','/Admin/Widthdraw/game_recharge_new','',NULL,NULL),(205,206,'绑定银行卡','/Admin/Widthdraw/bind_bank_card','',NULL,NULL),(206,0,'出款管理','/Admin/Widthdraw/withdraw_cli_menu','',NULL,NULL),(207,206,'出款申请','/Admin/Widthdraw/withdraw_cliu','',NULL,NULL),(208,197,'VIP充值记录','/Admin/Widthdraw/recharge_record_new','',NULL,NULL),(209,2,'处理订单/成功','/Admin/Order/disposed','',NULL,NULL),(210,2,'处理订单/失败','/Admin/Order/failed','',NULL,NULL),(211,2,'支付宝订单记录','/Admin/Order/query_ali_res','',NULL,NULL),(212,2,'银行卡自动订单记录','/Admin/Order/query_bank_res','',NULL,NULL),(213,2,'核销','/Admin/Order/check_draw','',NULL,NULL),(214,2,'个人出款记录','/Admin/Order/record_draw','',NULL,NULL),(215,197,'扣除金币','/Admin/widthdraw/reduce_money','',NULL,NULL),(216,197,'扣款记录','/Admin/widthdraw/reduce_money_record','',NULL,NULL);
/*!40000 ALTER TABLE `caiwu_admin_nav` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_admin_recharge_record`
--

DROP TABLE IF EXISTS `caiwu_admin_recharge_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_admin_recharge_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '充值用户id',
  `send_id` int(10) NOT NULL COMMENT '发送人id',
  `diamond` int(10) NOT NULL COMMENT '充值金额',
  `create_at` int(11) DEFAULT NULL COMMENT '充值时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='用户充值记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_admin_recharge_record`
--

LOCK TABLES `caiwu_admin_recharge_record` WRITE;
/*!40000 ALTER TABLE `caiwu_admin_recharge_record` DISABLE KEYS */;
INSERT INTO `caiwu_admin_recharge_record` VALUES (1,90,90,10000,1560393319),(2,93,90,100000,1565922348);
/*!40000 ALTER TABLE `caiwu_admin_recharge_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_admin_users`
--

DROP TABLE IF EXISTS `caiwu_admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_admin_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `realname` varchar(100) DEFAULT NULL,
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '登录密码；mb_password加密',
  `phone` bigint(11) unsigned DEFAULT NULL COMMENT '手机号',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户状态 0：禁用； 1：正常 ；2：未验证',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `last_login_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `last_login_time` int(10) unsigned NOT NULL COMMENT '最后登录时间',
  `updated_at` int(11) DEFAULT '0' COMMENT '最后修改时间',
  `session_id` varchar(200) DEFAULT NULL,
  `type` tinyint(4) DEFAULT '0' COMMENT '1:ys支付宝手工;2:ys银行卡手工',
  `email` varchar(100) DEFAULT NULL,
  `administrator` tinyint(4) DEFAULT '2' COMMENT '是否是超管1是',
  `creator_id` int(10) DEFAULT NULL COMMENT '创建者id',
  PRIMARY KEY (`id`),
  KEY `user_login_key` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_admin_users`
--

LOCK TABLES `caiwu_admin_users` WRITE;
/*!40000 ALTER TABLE `caiwu_admin_users` DISABLE KEYS */;
INSERT INTO `caiwu_admin_users` VALUES (1,'admin@admin.com',NULL,'$2y$10$QOtlXJ5mTdOJtOh9VVXGIekS2k2OzNdiMcq.F5Cnlr8CnWdq980ha',NULL,1,0,'',0,2019,'7xQkwLPk8UU1UtjOP1DamnirW0x051MXlwL5p79h',0,NULL,1,NULL),(90,'15928657951','qq','$2y$10$HB3xjBooWmDAnwFHqHedveDXsvVXZf9DBRGsEpip7oyqFd3V/jZaC',NULL,1,0,'',0,2019,'7xQkwLPk8UU1UtjOP1DamnirW0x051MXlwL5p79h',0,NULL,2,NULL),(92,'123123','123123','$2y$10$37GfWls9Z2gQNPGbV9/OLeMpo6IUic1gWuTyFnL2KB4Z0WpTs5y42',NULL,1,2019,'',0,2019,NULL,0,NULL,2,1),(93,'13312341234','测试','$2y$10$z3sHVle9.ntXXgD/aikhNuYM6rc0HC/MzdY3RtNFBQ21AunVLMYSa',NULL,1,2019,'',0,2019,'7xQkwLPk8UU1UtjOP1DamnirW0x051MXlwL5p79h',0,NULL,2,1);
/*!40000 ALTER TABLE `caiwu_admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_apply_widthdraw_diamond`
--

DROP TABLE IF EXISTS `caiwu_apply_widthdraw_diamond`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_apply_widthdraw_diamond` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '申请游戏ID',
  `diamond` int(11) NOT NULL DEFAULT '0' COMMENT '申请转账元宝',
  `order_sn` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '申请可转账金额',
  `service_fee` decimal(4,0) DEFAULT '0' COMMENT '手续费',
  `apply_amount` int(11) DEFAULT '0' COMMENT '申请提现金额',
  `create_at` int(11) NOT NULL DEFAULT '0' COMMENT '申请时间',
  `receive_id` int(11) DEFAULT '0' COMMENT '接收人ID',
  `state` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态 0 待处理   1处理中   2已转账 3转账失败',
  `bank_name` varchar(50) NOT NULL COMMENT '银行名称',
  `bank_account` varchar(100) NOT NULL COMMENT '银行账号',
  `realname` varchar(100) NOT NULL COMMENT '收款人',
  `update_at` int(11) DEFAULT '0' COMMENT '转账时间',
  `widthdraw_type` tinyint(4) DEFAULT '0' COMMENT '0 玩家提现  1 推广员提现',
  `receive_at` int(11) DEFAULT '0' COMMENT '接单时间',
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_slovenian_ci COMMENT '备注',
  `keyid` varchar(100) DEFAULT NULL,
  `is_draw` tinyint(4) DEFAULT '0',
  `insert_at` int(11) DEFAULT NULL COMMENT '入库时间',
  `sub_at` int(11) DEFAULT NULL COMMENT '提交处理订单时间',
  `is_hand` tinyint(4) DEFAULT '0' COMMENT '是否手工出款  0不是 1 是',
  `mer_type` tinyint(4) DEFAULT '0' COMMENT '1手工;2支付宝1;3支付宝2;4支付宝3;5支付宝4;6支付宝5;7支付宝6;8易之宝;9KKbank;10AI',
  PRIMARY KEY (`id`),
  KEY `idx_keyid` (`keyid`) USING BTREE,
  KEY `idx_create_at` (`create_at`) USING BTREE,
  KEY `idx_receive_id` (`receive_id`) USING BTREE,
  KEY `idx_state` (`state`) USING BTREE,
  KEY `idx_bank_name` (`bank_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='申请转正元宝总额';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_apply_widthdraw_diamond`
--

LOCK TABLES `caiwu_apply_widthdraw_diamond` WRITE;
/*!40000 ALTER TABLE `caiwu_apply_widthdraw_diamond` DISABLE KEYS */;
INSERT INTO `caiwu_apply_widthdraw_diamond` VALUES (1,662041,100,'100903_12930129_100_100_012_123123',100,200,10,0,90,2,'支付宝','qwe@123.com','ceshi',1561630476,0,1561619099,'手工转款',NULL,1,NULL,1561630476,1,11);
/*!40000 ALTER TABLE `caiwu_apply_widthdraw_diamond` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_apply_widthdraw_record`
--

DROP TABLE IF EXISTS `caiwu_apply_widthdraw_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_apply_widthdraw_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'vip用户id',
  `apply_money` int(11) NOT NULL DEFAULT '0' COMMENT '申请转正金额',
  `apply_at` int(11) NOT NULL DEFAULT '0' COMMENT '申请时间',
  `bank_name` varchar(50) DEFAULT NULL COMMENT '银行账户',
  `bank_card` varchar(50) DEFAULT '' COMMENT '银行卡号',
  `realname` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `state` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态 0 处理中  1 转账成功  2 转账失败已返还',
  `operator_id` int(11) DEFAULT NULL,
  `operator_ip` varchar(30) DEFAULT NULL,
  `operator_at` int(11) DEFAULT '0' COMMENT '处理时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='vip用户申请转账';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_apply_widthdraw_record`
--

LOCK TABLES `caiwu_apply_widthdraw_record` WRITE;
/*!40000 ALTER TABLE `caiwu_apply_widthdraw_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `caiwu_apply_widthdraw_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_auth_rule`
--

DROP TABLE IF EXISTS `caiwu_auth_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_auth_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `name` char(80) DEFAULT '' COMMENT '规则唯一标识',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则中文名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `condition` char(100) NOT NULL DEFAULT '' COMMENT '规则表达式，为空表示存在就验证，不为空表示按照条件验证',
  `update_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=307 DEFAULT CHARSET=utf8 COMMENT='规则表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_auth_rule`
--

LOCK TABLES `caiwu_auth_rule` WRITE;
/*!40000 ALTER TABLE `caiwu_auth_rule` DISABLE KEYS */;
INSERT INTO `caiwu_auth_rule` VALUES (22,20,'admin.recharge.old_recharge_record','充值记录',1,1,'',1565853407),(21,20,'admin.recharge.game_recharge','游戏充值',1,1,'',1565853407),(20,0,'admin.menu.menu_oldviprecharge','老版VIP充值',1,1,'',1565853407),(19,13,'admin.order.self_record','个人出款记录',1,1,'',1565853407),(18,13,'admin.order.bank_record','银行卡自动订单记录',1,1,'',1565853407),(17,13,'admin.order.failed','处理订单/失败',1,1,'',1565853407),(16,13,'admin.order.disposed','处理订单/成功',1,1,'',1565853407),(15,13,'admin.order.wait','待处理订单',1,1,'',1565853407),(14,13,'admin.order.index','全部订单',1,1,'',1565853407),(13,0,'admin.menu.menu_order','订单管理',1,1,'',1565853407),(12,0,'admin.order.apply','系统接单',1,1,'',1565853407),(11,6,'admin.rules.index','权限管理',1,1,'',1565853407),(9,6,'admin.permission.index','角色管理',1,1,'',1565853407),(8,6,'admin.user.index','管理员列表',1,1,'',1565853407),(7,6,'admin.menu.index','菜单管理',1,1,'',1565853407),(6,0,'admin.menu.menu_permission','后台管理',1,1,'',1565853407),(23,0,'admin.menu.menu_viprecharge','新VIP充值',1,1,'',1565853407),(24,23,'admin.recharge.index','游戏充值',1,1,'',1565853407),(25,23,'admin.recharge.recharge_record','充值记录',1,1,'',1565853407),(26,0,'admin.menu.menu_widthdraw','出款管理',1,1,'',1565853407),(27,26,'admin.widthdraw.bind_bank_card','绑定银行卡',1,1,'',1565853407),(28,26,'admin.widthdraw.index','出款申请',1,1,'',1565853407),(30,20,'admin.recharge.reduce_money','扣除金币',1,1,'',1565853407),(31,20,'admin.recharge.reduce_record','扣除记录',1,1,'',1565853407);
/*!40000 ALTER TABLE `caiwu_auth_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_check_draw`
--

DROP TABLE IF EXISTS `caiwu_check_draw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_check_draw` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `operator_id` int(10) NOT NULL DEFAULT '0' COMMENT '记录人id',
  `amount` int(10) NOT NULL DEFAULT '0' COMMENT '出款金额',
  `create_at` int(11) NOT NULL COMMENT '记录时间',
  `remark` text COMMENT '备注',
  `order_sn` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_check_draw`
--

LOCK TABLES `caiwu_check_draw` WRITE;
/*!40000 ALTER TABLE `caiwu_check_draw` DISABLE KEYS */;
/*!40000 ALTER TABLE `caiwu_check_draw` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_disposed_diamond`
--

DROP TABLE IF EXISTS `caiwu_disposed_diamond`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_disposed_diamond` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `diamond` bigint(20) NOT NULL DEFAULT '0' COMMENT '受理元宝总额',
  `amount` decimal(20,4) NOT NULL DEFAULT '0.0000' COMMENT '转账总额',
  `widthdraw` int(11) NOT NULL DEFAULT '0' COMMENT '已付款总额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='已受理元宝总额';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_disposed_diamond`
--

LOCK TABLES `caiwu_disposed_diamond` WRITE;
/*!40000 ALTER TABLE `caiwu_disposed_diamond` DISABLE KEYS */;
INSERT INTO `caiwu_disposed_diamond` VALUES (1,90,10000,10000.0000,800),(2,1,10000,10000.0000,0),(3,93,100000,100000.0000,0);
/*!40000 ALTER TABLE `caiwu_disposed_diamond` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_drawalirecord`
--

DROP TABLE IF EXISTS `caiwu_drawalirecord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_drawalirecord` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `draw_money` decimal(10,2) DEFAULT '0.00' COMMENT '下发金额',
  `order_sn` varchar(50) DEFAULT NULL COMMENT '下发单号',
  `t_order_sn` varchar(50) DEFAULT NULL COMMENT '第三方订单号',
  `state` tinyint(4) DEFAULT '0' COMMENT '下发状态 0 成功1 失败',
  `create_at` int(10) DEFAULT '0' COMMENT '下发时间',
  `operator_id` int(10) DEFAULT NULL COMMENT '下发id',
  `user_type` tinyint(4) DEFAULT NULL COMMENT '用户类型',
  `order_state` tinyint(4) DEFAULT NULL COMMENT '后台处理状态',
  `realname` varchar(50) DEFAULT NULL,
  `ali_account` varchar(50) DEFAULT NULL COMMENT '支付宝账号',
  `remark` varchar(200) DEFAULT NULL,
  `ali_type` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`state`) USING BTREE,
  KEY `idx_create_at` (`create_at`) USING BTREE,
  KEY `idx_ali_type` (`ali_type`) USING BTREE,
  KEY `idx_operator_id` (`operator_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_drawalirecord`
--

LOCK TABLES `caiwu_drawalirecord` WRITE;
/*!40000 ALTER TABLE `caiwu_drawalirecord` DISABLE KEYS */;
INSERT INTO `caiwu_drawalirecord` VALUES (1,662041,100.00,'100903_12930129_100_100_012_123123',NULL,0,1561630476,90,0,NULL,'ceshi','qwe@123.com',NULL,11);
/*!40000 ALTER TABLE `caiwu_drawalirecord` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_drawbankrecord`
--

DROP TABLE IF EXISTS `caiwu_drawbankrecord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_drawbankrecord` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `draw_money` decimal(10,2) DEFAULT '0.00' COMMENT '下发金额',
  `order_sn` varchar(50) DEFAULT NULL COMMENT '下发单号',
  `t_order_sn` varchar(50) DEFAULT NULL COMMENT '第三方单号',
  `state` tinyint(4) DEFAULT '0' COMMENT '下发状态 0 成功1 失败 6 出款中',
  `create_at` int(10) DEFAULT '0' COMMENT '下发时间',
  `operator_id` int(10) DEFAULT NULL COMMENT '下发id',
  `user_type` tinyint(4) DEFAULT NULL COMMENT '用户类型',
  `realname` varchar(50) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL COMMENT '银行账号',
  `bank_name` varchar(50) DEFAULT NULL COMMENT '银行名称',
  `remark` varchar(200) DEFAULT NULL,
  `mer_type` tinyint(4) DEFAULT NULL COMMENT '商户类型 1 艾付 2 太极 3柬易付',
  `return_res` varchar(200) DEFAULT NULL COMMENT '返回结果',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_drawbankrecord`
--

LOCK TABLES `caiwu_drawbankrecord` WRITE;
/*!40000 ALTER TABLE `caiwu_drawbankrecord` DISABLE KEYS */;
/*!40000 ALTER TABLE `caiwu_drawbankrecord` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_login_log`
--

DROP TABLE IF EXISTS `caiwu_login_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_login_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `create_at` int(11) unsigned NOT NULL,
  `login_ip` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_login_log`
--

LOCK TABLES `caiwu_login_log` WRITE;
/*!40000 ALTER TABLE `caiwu_login_log` DISABLE KEYS */;
INSERT INTO `caiwu_login_log` VALUES (1,1,1565852530,'127.0.0.1'),(2,1,1565859664,'127.0.0.1'),(3,90,1565859686,'127.0.0.1'),(4,1,1565859698,'127.0.0.1'),(5,90,1565859799,'127.0.0.1'),(6,90,1565921814,'119.4.240.10'),(7,1,1565921942,'119.4.240.10'),(8,93,1565922109,'119.4.240.10'),(9,1,1565922641,'119.4.240.10');
/*!40000 ALTER TABLE `caiwu_login_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_menu`
--

DROP TABLE IF EXISTS `caiwu_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '名称',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父id',
  `route` varchar(50) DEFAULT NULL COMMENT '路由',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COMMENT='菜单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_menu`
--

LOCK TABLES `caiwu_menu` WRITE;
/*!40000 ALTER TABLE `caiwu_menu` DISABLE KEYS */;
INSERT INTO `caiwu_menu` VALUES (6,'后台管理',0,'admin.menu.menu_permission',NULL,NULL),(7,'菜单管理',6,'admin.menu.index',NULL,NULL),(8,'管理员列表',6,'admin.user.index','2019-08-15 07:09:29','2019-08-15 07:09:29'),(9,'角色管理',6,'admin.permission.index','2019-08-15 07:11:31','2019-08-15 07:11:31'),(11,'权限管理',6,'admin.rules.index','2019-08-15 07:16:10','2019-08-15 07:16:10'),(12,'系统接单',0,'admin.order.apply','2019-08-15 07:17:54','2019-08-15 07:17:54'),(13,'订单管理',0,'admin.menu.menu_order','2019-08-15 07:19:17','2019-08-15 07:19:17'),(14,'全部订单',13,'admin.order.index','2019-08-15 07:19:17','2019-08-15 07:19:17'),(15,'待处理订单',13,'admin.order.wait','2019-08-15 07:19:17','2019-08-15 07:19:17'),(16,'处理订单/成功',13,'admin.order.disposed','2019-08-15 07:19:17','2019-08-15 07:19:17'),(17,'处理订单/失败',13,'admin.order.failed','2019-08-15 07:19:17','2019-08-15 07:19:17'),(18,'银行卡自动订单记录',13,'admin.order.bank_record','2019-08-15 07:19:17','2019-08-15 07:19:17'),(19,'个人出款记录',13,'admin.order.self_record','2019-08-15 07:19:17','2019-08-15 07:19:17'),(20,'老版VIP充值',0,'admin.menu.menu_oldviprecharge','2019-08-15 07:19:17','2019-08-15 07:19:17'),(21,'游戏充值',20,'admin.recharge.game_recharge','2019-08-15 07:19:17','2019-08-15 07:19:17'),(22,'充值记录',20,'admin.recharge.old_recharge_record','2019-08-15 07:19:17','2019-08-15 07:19:17'),(23,'新VIP充值',0,'admin.menu.menu_viprecharge','2019-08-15 07:19:17','2019-08-15 07:19:17'),(24,'游戏充值',23,'admin.recharge.index','2019-08-15 07:19:17','2019-08-15 07:19:17'),(25,'充值记录',23,'admin.recharge.recharge_record','2019-08-15 07:19:17','2019-08-15 07:19:17'),(26,'出款管理',0,'admin.menu.menu_widthdraw','2019-08-15 07:19:17','2019-08-15 07:19:17'),(27,'绑定银行卡',26,'admin.widthdraw.bind_bank_card','2019-08-15 07:19:17','2019-08-15 07:19:17'),(28,'出款申请',26,'admin.widthdraw.index','2019-08-15 07:19:17','2019-08-15 07:19:17'),(30,'扣除金币',20,'admin.recharge.reduce_money','2019-08-15 07:19:17','2019-08-15 07:19:17'),(31,'扣除记录',20,'admin.recharge.reduce_record','2019-08-15 07:19:17','2019-08-15 07:19:17');
/*!40000 ALTER TABLE `caiwu_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_mer_withdraw_record`
--

DROP TABLE IF EXISTS `caiwu_mer_withdraw_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_mer_withdraw_record` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `operate_id` int(10) NOT NULL COMMENT '操作人ID',
  `amount` int(10) NOT NULL COMMENT '提现金额',
  `realname` varchar(100) DEFAULT NULL COMMENT 'p_name真实姓名',
  `bank_account` varchar(100) DEFAULT NULL COMMENT '银行卡号',
  `bank_name` varchar(50) DEFAULT NULL COMMENT '开户行',
  `state` tinyint(4) DEFAULT '0' COMMENT '状态 :0已提交,1成功 2 失败',
  `mer_type` tinyint(4) DEFAULT '1' COMMENT '1川流 2 DX银联 3 HC银联 4达达',
  `reason` varchar(200) DEFAULT NULL COMMENT '错误原因',
  `notify_res` varchar(200) DEFAULT NULL COMMENT '回调结果',
  `create_at` int(11) DEFAULT '0' COMMENT '操作时间',
  `update_at` int(11) DEFAULT NULL COMMENT '回调时间',
  `order_sn` varchar(50) DEFAULT NULL COMMENT '单号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_mer_withdraw_record`
--

LOCK TABLES `caiwu_mer_withdraw_record` WRITE;
/*!40000 ALTER TABLE `caiwu_mer_withdraw_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `caiwu_mer_withdraw_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_opereator_log`
--

DROP TABLE IF EXISTS `caiwu_opereator_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_opereator_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0',
  `operator_name` varchar(20) DEFAULT NULL,
  `operator_ip` varchar(20) DEFAULT NULL COMMENT '操作人ip地址',
  `content` varchar(255) DEFAULT NULL COMMENT '操作内容',
  `params` text COMMENT '操作参数',
  `create_at` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `operate_realname` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=47631 DEFAULT CHARSET=utf8 COMMENT='后台日志操作';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_opereator_log`
--

LOCK TABLES `caiwu_opereator_log` WRITE;
/*!40000 ALTER TABLE `caiwu_opereator_log` DISABLE KEYS */;
INSERT INTO `caiwu_opereator_log` VALUES (47581,1,'15928657951','127.0.0.1','管理员登录','{\"username\":\"15928657951\",\"password\":\"123456\"}',1560331244,NULL),(47582,90,'15928657951','127.0.0.1','管理员登录','{\"username\":\"15928657951\",\"password\":\"123456\"}',1560331291,NULL),(47583,90,'15928657951','127.0.0.1','添加后台菜单','{\"ico\":\"\",\"mca\":\"\\/Admin\\/Widthdraw\\/menu\",\"name\":\"VIP\\u5145\\u503c\"}',1560331689,NULL),(47584,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"VIP\\u5145\\u503c\",\"mca\":\"\\/Admin\\/Widthdraw\\/index\",\"pid\":\"197\"}',1560331734,NULL),(47585,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"VIP\\u5145\\u503c\",\"name\":\"\\/Admin\\/Widthdraw\\/menu\"}',1560331757,NULL),(47586,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"VIP\\u5145\\u503c\",\"name\":\"\\/Admin\\/Widthdraw\\/index\",\"pid\":\"284\"}',1560331767,NULL),(47587,90,'15928657951','127.0.0.1','分配权限','{\"id\":\"6\",\"rule_ids\":[\"137\",\"140\",\"141\",\"143\",\"144\",\"284\",\"285\"]}',1560331775,NULL),(47588,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"\\u5145\\u503c\\u8bb0\\u5f55\",\"mca\":\"\\/Admin\\/Widthdraw\\/recharge_record\",\"pid\":\"197\"}',1560331927,NULL),(47589,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u5145\\u503c\\u8bb0\\u5f55\",\"name\":\"\\/Admin\\/Widthdraw\\/recharge_record\",\"pid\":\"284\"}',1560331953,NULL),(47590,90,'15928657951','127.0.0.1','分配权限','{\"id\":\"6\",\"rule_ids\":[\"137\",\"140\",\"141\",\"143\",\"144\",\"284\",\"285\",\"286\"]}',1560331963,NULL),(47591,90,'15928657951','127.0.0.1','添加后台菜单','{\"ico\":\"\",\"mca\":\"\\/Admin\\/Order\\/apply\",\"name\":\"\\u7cfb\\u7edf\\u63a5\\u5355\"}',1560332034,NULL),(47592,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u7cfb\\u7edf\\u63a5\\u5355\",\"name\":\"\\/Admin\\/Order\\/apply\"}',1560332053,NULL),(47593,90,'15928657951','127.0.0.1','分配权限','{\"id\":\"6\",\"rule_ids\":[\"137\",\"140\",\"141\",\"143\",\"144\",\"284\",\"285\",\"286\",\"287\"]}',1560332061,NULL),(47594,90,'15928657951','127.0.0.1','添加后台菜单','{\"ico\":\"\",\"mca\":\"\\/Admin\\/Order\\/menu\",\"name\":\"\\u8ba2\\u5355\\u7ba1\\u7406\"}',1560332143,NULL),(47595,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"\\u5168\\u90e8\\u8ba2\\u5355\",\"mca\":\"\\/Admin\\/Order\\/index\",\"pid\":\"201\"}',1560332171,NULL),(47596,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"\\u5f85\\u5904\\u7406\\u8ba2\\u5355\",\"mca\":\"\\/Admin\\/Order\\/wait\",\"pid\":\"201\"}',1560332214,NULL),(47597,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u8ba2\\u5355\\u7ba1\\u7406\",\"name\":\"\\/Admin\\/Order\\/menu\"}',1560332244,NULL),(47598,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u5168\\u90e8\\u8ba2\\u5355\",\"name\":\"\\/Admin\\/Order\\/index\",\"pid\":\"288\"}',1560332260,NULL),(47599,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u5f85\\u5904\\u7406\\u8ba2\\u5355\",\"name\":\"\\/Admin\\/Order\\/wait\",\"pid\":\"288\"}',1560332278,NULL),(47600,90,'15928657951','127.0.0.1','分配权限','{\"id\":\"6\",\"rule_ids\":[\"137\",\"140\",\"141\",\"143\",\"144\",\"284\",\"285\",\"286\",\"287\",\"288\",\"289\",\"290\"]}',1560332286,NULL),(47601,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"VIP\\u5145\\u503c\",\"mca\":\"\\/Admin\\/Widthdrwa\\/game_recharge_new\",\"pid\":\"197\"}',1560392405,NULL),(47602,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"VIP\\u5145\\u503c\",\"name\":\"\\/Admin\\/Widthdrwa\\/game_recharge_new\",\"pid\":\"284\"}',1560392425,NULL),(47603,90,'15928657951','127.0.0.1','分配权限','{\"id\":\"6\",\"rule_ids\":[\"137\",\"140\",\"141\",\"143\",\"144\",\"284\",\"285\",\"286\",\"291\",\"287\",\"288\",\"289\",\"290\"]}',1560392435,NULL),(47604,90,'15928657951','127.0.0.1','分配权限','{\"id\":\"6\",\"rule_ids\":[\"137\",\"140\",\"141\",\"143\",\"144\",\"284\",\"285\",\"286\",\"291\",\"287\",\"288\",\"289\",\"290\",\"292\",\"293\",\"294\"]}',1560395413,NULL),(47605,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"VIP\\u5145\\u503c\\u8bb0\\u5f55\",\"mca\":\"\\/Admin\\/Widthdraw\\/recharge_record_new\",\"pid\":\"197\"}',1560397829,NULL),(47606,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"VIP\\u5145\\u503c\\u8bb0\\u5f55\",\"name\":\"\\/Admin\\/Widthdraw\\/recharge_record_new\",\"pid\":\"284\"}',1560397848,NULL),(47607,90,'15928657951','127.0.0.1','分配权限','{\"id\":\"6\",\"rule_ids\":[\"137\",\"140\",\"141\",\"143\",\"144\",\"284\",\"285\",\"286\",\"291\",\"296\",\"287\",\"288\",\"289\",\"290\",\"292\",\"293\",\"294\"]}',1560397854,NULL),(47608,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\" \\u5904\\u7406\\u8ba2\\u5355\\/\\u6210\\u529f\",\"mca\":\"\\/Admin\\/Order\\/disposed\",\"pid\":\"2\"}',1560408487,NULL),(47609,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u5904\\u7406\\u8ba2\\u5355\\/\\u6210\\u529f\",\"name\":\"\\/Admin\\/Order\\/disposed\",\"pid\":\"288\"}',1560408504,NULL),(47610,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u5904\\u7406\\u8ba2\\u5355\\/\\u5931\\u8d25\",\"name\":\"\\/Admin\\/Order\\/failed\",\"pid\":\"288\"}',1560408522,NULL),(47611,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"\\u5904\\u7406\\u8ba2\\u5355\\/\\u5931\\u8d25\",\"mca\":\"\\/Admin\\/Order\\/failed\",\"pid\":\"2\"}',1560408549,NULL),(47612,90,'15928657951','127.0.0.1','添加权限','{\"title\":\" \\u652f\\u4ed8\\u5b9d\\u8ba2\\u5355\\u8bb0\\u5f55\",\"name\":\"\\/Admin\\/Order\\/query_ali_res\",\"pid\":\"288\"}',1560408571,NULL),(47613,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\" \\u652f\\u4ed8\\u5b9d\\u8ba2\\u5355\\u8bb0\\u5f55\",\"mca\":\"\\/Admin\\/Order\\/query_ali_res\",\"pid\":\"2\"}',1560408574,NULL),(47614,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u94f6\\u884c\\u5361\\u81ea\\u52a8\\u8ba2\\u5355\\u8bb0\\u5f55\",\"name\":\"\\/Admin\\/Order\\/query_bank_res\",\"pid\":\"288\"}',1560408614,NULL),(47615,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"\\u94f6\\u884c\\u5361\\u81ea\\u52a8\\u8ba2\\u5355\\u8bb0\\u5f55\",\"mca\":\"\\/Admin\\/Order\\/query_bank_res\",\"pid\":\"2\"}',1560408618,NULL),(47616,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u6838\\u9500\",\"name\":\"\\/Admin\\/Order\\/check_draw\",\"pid\":\"288\"}',1560408638,NULL),(47617,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"\\u6838\\u9500\",\"mca\":\"\\/Admin\\/Order\\/check_draw\",\"pid\":\"2\"}',1560408640,NULL),(47618,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u4e2a\\u4eba\\u51fa\\u6b3e\\u8bb0\\u5f55\",\"name\":\"\\/Admin\\/Order\\/record_draw\",\"pid\":\"288\"}',1560408660,NULL),(47619,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"\\u4e2a\\u4eba\\u51fa\\u6b3e\\u8bb0\\u5f55\",\"mca\":\"\\/Admin\\/Order\\/record_draw\",\"pid\":\"2\"}',1560408663,NULL),(47620,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u6263\\u9664\\u91d1\\u5e01\",\"name\":\"\\/Admin\\/widthdraw\\/reduce_money\",\"pid\":\"284\"}',1560408705,NULL),(47621,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"\\u6263\\u9664\\u91d1\\u5e01\",\"mca\":\"\\/Admin\\/widthdraw\\/reduce_money\",\"pid\":\"197\"}',1560408707,NULL),(47622,90,'15928657951','127.0.0.1','添加权限','{\"title\":\"\\u6263\\u6b3e\\u8bb0\\u5f55\",\"name\":\"\\/Admin\\/widthdraw\\/reduce_money_record\",\"pid\":\"284\"}',1560408730,NULL),(47623,90,'15928657951','127.0.0.1','添加子菜单','{\"ico\":\"\",\"name\":\"\\u6263\\u6b3e\\u8bb0\\u5f55\",\"mca\":\"\\/Admin\\/widthdraw\\/reduce_money_record\",\"pid\":\"197\"}',1560408733,NULL),(47624,90,'15928657951','127.0.0.1','分配权限','{\"id\":\"6\",\"rule_ids\":[\"137\",\"140\",\"141\",\"143\",\"144\",\"284\",\"285\",\"286\",\"291\",\"296\",\"303\",\"304\",\"287\",\"288\",\"289\",\"290\",\"297\",\"298\",\"299\",\"300\",\"301\",\"302\",\"292\",\"293\",\"294\"]}',1560408750,NULL),(47625,90,'15928657951','127.0.0.1','管理员登录','{\"username\":\"15928657951\",\"password\":\"123456\"}',1561518684,NULL),(47626,90,'15928657951','127.0.0.1','管理员登录','{\"username\":\"15928657951\",\"password\":\"123456\"}',1561617338,NULL),(47627,90,'15928657951','127.0.0.1','管理员登录','{\"username\":\"15928657951\",\"password\":\"123456\"}',1561704504,NULL),(47628,90,'15928657951','127.0.0.1','管理员登录','{\"username\":\"15928657951\",\"password\":\"123456\"}',1561810523,NULL),(47629,90,'15928657951','127.0.0.1','管理员登录','{\"username\":\"15928657951\",\"password\":\"123456\"}',1561948142,NULL),(47630,90,'15928657951','127.0.0.1','管理员登录','{\"username\":\"15928657951\",\"password\":\"123456\"}',1564716675,NULL);
/*!40000 ALTER TABLE `caiwu_opereator_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_order_log`
--

DROP TABLE IF EXISTS `caiwu_order_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_order_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ori_state` tinyint(4) NOT NULL COMMENT '原始状态',
  `up_state` tinyint(4) DEFAULT NULL COMMENT '修改后状态',
  `operator_id` int(10) NOT NULL COMMENT '操作人id',
  `order_num` varchar(50) DEFAULT '' COMMENT '订单号',
  `user_id` int(10) DEFAULT NULL COMMENT '用户id',
  `withdraw` int(10) DEFAULT NULL COMMENT '出款金额',
  `create_at` int(10) DEFAULT NULL COMMENT '操作时间',
  `user_type` tinyint(4) DEFAULT NULL,
  `remark` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_order_log`
--

LOCK TABLES `caiwu_order_log` WRITE;
/*!40000 ALTER TABLE `caiwu_order_log` DISABLE KEYS */;
INSERT INTO `caiwu_order_log` VALUES (1,1,3,90,'100903_12930129_100_100_012_123123',662041,100,1561619099,0,'123'),(2,1,2,90,'100903_12930129_100_100_012_123123',662041,100,1561630476,0,'failed');
/*!40000 ALTER TABLE `caiwu_order_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_permission`
--

DROP TABLE IF EXISTS `caiwu_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `routes` text COMMENT '路由别名，逗号分隔',
  `name` varchar(50) NOT NULL COMMENT '名称',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='permission权限组';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_permission`
--

LOCK TABLES `caiwu_permission` WRITE;
/*!40000 ALTER TABLE `caiwu_permission` DISABLE KEYS */;
INSERT INTO `caiwu_permission` VALUES (1,'22,21,20,15,14,13,23,24,25','测试','2019-08-15 09:03:02','2019-08-15 09:03:02'),(2,'22,21,20,19,18,17,16,15,14,13,12,23,24,25,26,27,28','内部测试','2019-08-16 02:21:24','2019-08-16 02:31:14');
/*!40000 ALTER TABLE `caiwu_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_recharge_record`
--

DROP TABLE IF EXISTS `caiwu_recharge_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_recharge_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '充值用户id',
  `send_id` int(10) NOT NULL COMMENT '发送人id',
  `diamond` int(10) NOT NULL COMMENT '充值金额',
  `create_at` int(11) DEFAULT NULL COMMENT '充值时间',
  `order_sn` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户充值记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_recharge_record`
--

LOCK TABLES `caiwu_recharge_record` WRITE;
/*!40000 ALTER TABLE `caiwu_recharge_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `caiwu_recharge_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_reduce_money_record`
--

DROP TABLE IF EXISTS `caiwu_reduce_money_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_reduce_money_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reason` varchar(200) NOT NULL COMMENT '扣除原因',
  `money_type` tinyint(4) DEFAULT '0' COMMENT '扣除类型 1房卡 2元宝 3金豆',
  `money` int(11) NOT NULL COMMENT '扣除数量',
  `rid` int(11) NOT NULL DEFAULT '0' COMMENT '扣除游戏id',
  `operate_name` varchar(200) NOT NULL COMMENT '操作人账号',
  `operate_realname` varchar(200) NOT NULL COMMENT '操作人账号',
  `operate_ip` varchar(30) NOT NULL COMMENT '操作人ip',
  `create_at` int(11) DEFAULT NULL COMMENT '发送时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '发送状态:0待返回，1扣除成功 2扣除失败',
  `unionid` varchar(32) DEFAULT NULL COMMENT '唯一单号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台扣除元宝记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_reduce_money_record`
--

LOCK TABLES `caiwu_reduce_money_record` WRITE;
/*!40000 ALTER TABLE `caiwu_reduce_money_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `caiwu_reduce_money_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_users`
--

DROP TABLE IF EXISTS `caiwu_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `realname` varchar(100) DEFAULT NULL,
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '登录密码；mb_password加密',
  `phone` bigint(11) unsigned DEFAULT NULL COMMENT '手机号',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户状态 0：禁用； 1：正常 ；2：未验证',
  `register_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `last_login_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `last_login_time` int(10) unsigned NOT NULL COMMENT '最后登录时间',
  `update_at` int(11) DEFAULT '0' COMMENT '最后修改时间',
  `session_id` varchar(200) DEFAULT NULL,
  `type` tinyint(4) DEFAULT '0' COMMENT '1:kg支付宝手工;2:kg银行卡手工;3:ys支付宝手工;4:ys银行卡手工',
  PRIMARY KEY (`id`),
  KEY `user_login_key` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_users`
--

LOCK TABLES `caiwu_users` WRITE;
/*!40000 ALTER TABLE `caiwu_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `caiwu_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caiwu_users_permission`
--

DROP TABLE IF EXISTS `caiwu_users_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caiwu_users_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_id` int(10) unsigned NOT NULL COMMENT '角色id',
  `permission_id` int(10) unsigned NOT NULL COMMENT '权限组id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='角色-权限关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caiwu_users_permission`
--

LOCK TABLES `caiwu_users_permission` WRITE;
/*!40000 ALTER TABLE `caiwu_users_permission` DISABLE KEYS */;
INSERT INTO `caiwu_users_permission` VALUES (1,90,1,'2019-08-15 09:03:10','2019-08-15 09:03:10'),(2,93,2,'2019-08-16 02:21:37','2019-08-16 02:21:37');
/*!40000 ALTER TABLE `caiwu_users_permission` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-08-20 17:31:44

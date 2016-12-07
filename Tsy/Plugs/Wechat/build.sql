/*
Navicat MySQL Data Transfer

Source Server         : 江苏服务器
Source Server Version : 50634
Source Host           : js.s.tansuyun.cn:3306
Source Database       : chengyuan

Target Server Type    : MYSQL
Target Server Version : 50634
File Encoding         : 65001

Date: 2016-12-07 12:21:57
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for prefix_wechat_log
-- ----------------------------
DROP TABLE IF EXISTS `prefix_wechat_log`;
CREATE TABLE `prefix_wechat_log` (
  `LID` int(11) NOT NULL AUTO_INCREMENT,
  `To` char(50) DEFAULT NULL,
  `From` char(50) DEFAULT NULL,
  `Time` int(10) DEFAULT NULL,
  `MsgTypeID` int(11) NOT NULL,
  `Content` text,
  `MsgID` char(50) DEFAULT NULL,
  PRIMARY KEY (`LID`),
  KEY `from` (`From`),
  KEY `FK_Reference_2` (`MsgTypeID`),
  CONSTRAINT `FK_Reference_2` FOREIGN KEY (`MsgTypeID`) REFERENCES `prefix_wechat_msg_type_dic` (`MsgTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for prefix_wechat_match
-- ----------------------------
DROP TABLE IF EXISTS `prefix_wechat_match`;
CREATE TABLE `prefix_wechat_match` (
  `ConfigID` int(11) NOT NULL AUTO_INCREMENT,
  `Rule` text,
  `MsgTypeID` int(11) NOT NULL,
  `Method` char(50) DEFAULT NULL,
  `Order` int(11) DEFAULT NULL COMMENT '查询时按Order的大小查询',
  `Success` char(250) DEFAULT NULL,
  `ReplyID` int(11) NOT NULL,
  `StartTime` int(10) DEFAULT NULL,
  `EndTime` int(10) DEFAULT NULL,
  `MatchTimes` int(11) DEFAULT NULL,
  `Name` char(250) DEFAULT NULL,
  `Open` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`ConfigID`),
  UNIQUE KEY `unique_match_rule_name` (`Name`),
  KEY `start` (`StartTime`),
  KEY `end` (`EndTime`),
  KEY `times` (`MatchTimes`),
  KEY `order` (`Order`),
  KEY `FK_Reference_4` (`ReplyID`),
  KEY `FK_Reference_5` (`MsgTypeID`),
  CONSTRAINT `FK_Reference_4` FOREIGN KEY (`ReplyID`) REFERENCES `prefix_wechat_reply` (`ReplyID`),
  CONSTRAINT `FK_Reference_5` FOREIGN KEY (`MsgTypeID`) REFERENCES `prefix_wechat_msg_type_dic` (`MsgTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of prefix_wechat_match
-- ----------------------------
INSERT INTO `prefix_wechat_match` VALUES ('1', '.', '21', 'DEFAULT', '99', null, '1', null, null, null, '默认返回内容', '1');

-- ----------------------------
-- Table structure for prefix_wechat_member
-- ----------------------------
DROP TABLE IF EXISTS `prefix_wechat_member`;
CREATE TABLE `prefix_wechat_member` (
  `MemberID` int(11) NOT NULL AUTO_INCREMENT,
  `OpenID` char(250) DEFAULT NULL,
  `SubscribeTime` int(10) DEFAULT NULL,
  `NickName` char(250) DEFAULT NULL,
  `Sex` tinyint(1) DEFAULT NULL,
  `Language` char(50) DEFAULT NULL,
  `City` char(50) DEFAULT NULL,
  `Province` char(50) DEFAULT NULL,
  `Country` char(50) DEFAULT NULL,
  `HeadImgUrl` char(250) DEFAULT NULL,
  `Subscribe` tinyint(1) DEFAULT NULL,
  `Unionid` char(250) DEFAULT NULL,
  `Remark` char(250) DEFAULT NULL,
  `GroupID` int(11) DEFAULT NULL,
  PRIMARY KEY (`MemberID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of prefix_wechat_member
-- ----------------------------

-- ----------------------------
-- Table structure for prefix_wechat_msg_type_dic
-- ----------------------------
DROP TABLE IF EXISTS `prefix_wechat_msg_type_dic`;
CREATE TABLE `prefix_wechat_msg_type_dic` (
  `MsgTypeID` int(11) NOT NULL AUTO_INCREMENT,
  `MsgType` char(50) DEFAULT NULL,
  `Name` char(50) DEFAULT NULL,
  `Method` char(20) NOT NULL,
  `ReplyMethod` char(50) DEFAULT NULL,
  PRIMARY KEY (`MsgTypeID`),
  UNIQUE KEY `msg_type_value` (`MsgType`,`Method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of prefix_wechat_msg_type_dic
-- ----------------------------
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('1', 'text', '文本消息', 'TYPE', 'replyText');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('2', 'image', '图片消息', 'TYPE', 'replyImage');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('3', 'voice', '音频消息', 'TYPE', 'replyVoice');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('4', 'video', '视频消息', 'TYPE', 'replyVideo');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('5', 'shortvideo', '短视频消息', 'TYPE', '');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('6', 'location', '位置消息', 'TYPE', '');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('7', 'link', '连接消息', 'TYPE', '');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('8', 'music', '音乐消息', 'TYPE', 'replyMusic');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('9', 'news', '图文消息', 'TYPE', 'replyNews');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('10', 'event', '事件消息', 'TYPE', '');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('11', 'transfer_customer_service', '多客服转发', 'RETURN', 'replyKf');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('12', 'subscribe', '订阅', 'EVENT', '');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('13', 'unsubscribe', '取消订阅', 'EVENT', '');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('14', 'SCAN', '二维码扫码', 'EVENT', '');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('16', 'CLICK', '菜单点击', 'EVENT', '');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('17', 'VIEW', '菜单跳转', 'EVENT', '');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('20', 'LOCATION', '报告位置', 'EVENT', '');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('21', 'DEFAULT', '默认处理方案', 'TYPE', '');
INSERT INTO `prefix_wechat_msg_type_dic` VALUES ('22', 'NewsOnce', '一次回复', 'RETURN', 'replyNewsOnce');

-- ----------------------------
-- Table structure for prefix_wechat_reply
-- ----------------------------
DROP TABLE IF EXISTS `prefix_wechat_reply`;
CREATE TABLE `prefix_wechat_reply` (
  `ReplyID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` char(250) DEFAULT NULL,
  `MsgTypeID` int(11) NOT NULL,
  `Config` text,
  `Method` char(50) DEFAULT NULL COMMENT 'Func:函数回调\r\n            Assign:模板渲染\r\n            TEXT:文本',
  PRIMARY KEY (`ReplyID`),
  UNIQUE KEY `name` (`Name`),
  KEY `FK_Reference_1` (`MsgTypeID`),
  CONSTRAINT `FK_Reference_1` FOREIGN KEY (`MsgTypeID`) REFERENCES `prefix_wechat_msg_type_dic` (`MsgTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of prefix_wechat_reply
-- ----------------------------
INSERT INTO `prefix_wechat_reply` VALUES ('1', '文字', '1', '您好，欢迎关注', 'TEXT');

-- ----------------------------
-- Table structure for prefix_wechat_reply_log
-- ----------------------------
DROP TABLE IF EXISTS `prefix_wechat_reply_log`;
CREATE TABLE `prefix_wechat_reply_log` (
  `RLID` int(11) NOT NULL AUTO_INCREMENT,
  `LID` int(11) DEFAULT NULL,
  `To` char(250) DEFAULT NULL,
  `MsgTypeID` int(11) NOT NULL,
  `Content` text,
  `ReplyID` int(11) DEFAULT NULL,
  `Time` int(10) DEFAULT NULL,
  `MatchID` int(11) DEFAULT NULL,
  PRIMARY KEY (`RLID`),
  KEY `to` (`To`),
  KEY `time` (`Time`),
  KEY `FK_Reference_3` (`MsgTypeID`),
  CONSTRAINT `FK_Reference_3` FOREIGN KEY (`MsgTypeID`) REFERENCES `prefix_wechat_msg_type_dic` (`MsgTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of prefix_wechat_reply_log
-- ----------------------------

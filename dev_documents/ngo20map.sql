

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `events`
-- ----------------------------
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `longitude` double DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `begin_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `item_field` varchar(100) DEFAULT NULL,
  `progress` int(11) DEFAULT '0',
  `province` varchar(20) DEFAULT NULL,
  `city` varchar(20) DEFAULT NULL,
  `place` text,
  `origin` varchar(500) DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `is_commentable` tinyint(4) DEFAULT '1',
  `is_checked` tinyint(4) DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `host` varchar(200) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `edit_time` datetime DEFAULT NULL,
  `enabled` tinyint(4) DEFAULT '1',
  `tag_id` varchar(200) DEFAULT NULL,
  `res_tags` varchar(50) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `outcome` text,
  `req_description` text,
  `contact_name` text,
  `contact_phone` text,
  `contact_email` text,
  `contact_qq` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5406 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `follow`
-- ----------------------------
DROP TABLE IF EXISTS `follow`;
CREATE TABLE `follow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` int(11) NOT NULL,
  `to` int(11) DEFAULT '0',
  `type` varchar(10) NOT NULL DEFAULT 'user',
  `extra` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=159 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `forget_password`
-- ----------------------------
DROP TABLE IF EXISTS `forget_password`;
CREATE TABLE `forget_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `link` varchar(50) DEFAULT NULL,
  `expire_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `medal`
-- ----------------------------
DROP TABLE IF EXISTS `medal`;
CREATE TABLE `medal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `score` int(11) DEFAULT '0',
  `image` varchar(100) DEFAULT NULL,
  `image_gray` varchar(100) DEFAULT NULL,
  `description` text,
  `type` varchar(10) NOT NULL DEFAULT 'user',
  `extra` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `medalmap`
-- ----------------------------
DROP TABLE IF EXISTS `medalmap`;
CREATE TABLE `medalmap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `medal_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `media`
-- ----------------------------
DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `url` varchar(500) NOT NULL,
  `url2` varchar(500) NOT NULL,
  `type` varchar(20) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=212 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `messages`
-- ----------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `content` text,
  `create_time` datetime NOT NULL,
  `is_read` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=70 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `related_links`
-- ----------------------------
DROP TABLE IF EXISTS `related_links`;
CREATE TABLE `related_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `url` varchar(500) NOT NULL,
  `label` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=214 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `reviews`
-- ----------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_checked` tinyint(4) NOT NULL DEFAULT '1',
  `create_time` datetime NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=77 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `tagmap`
-- ----------------------------
DROP TABLE IF EXISTS `tagmap`;
CREATE TABLE `tagmap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1416 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
--  Table structure for `tags`
-- ----------------------------
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL,
  `change_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=547 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `english_name` varchar(100) DEFAULT NULL,
  `password` varchar(50) NOT NULL,
  `api_vendor` varchar(20) NOT NULL,
  `api_id` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `introduction` text,
  `image` varchar(100) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `aim` text,
  `work_field` varchar(200) DEFAULT NULL,
  `register_year` varchar(10) DEFAULT NULL,
  `service_area` varchar(100) DEFAULT NULL,
  `staff_fulltime` int(11) DEFAULT NULL,
  `staff_parttime` int(11) DEFAULT NULL,
  `staff_volunteer` int(11) DEFAULT NULL,
  `website` varchar(50) DEFAULT NULL,
  `public_email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `province` varchar(20) DEFAULT NULL,
  `city` varchar(20) DEFAULT NULL,
  `place` text,
  `is_admin` tinyint(4) DEFAULT '0',
  `is_checked` tinyint(4) DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `last_login_ip` varchar(35) DEFAULT NULL,
  `enabled` tinyint(4) DEFAULT '1',
  `past_projects` text,
  `fax` varchar(20) DEFAULT NULL,
  `contact_name` varchar(30) DEFAULT NULL,
  `post_code` varchar(20) DEFAULT NULL,
  `media_link` varchar(200) DEFAULT NULL,
  `weibo_provider` varchar(20) DEFAULT NULL,
  `weibo` varchar(50) DEFAULT NULL,
  `is_vip` tinyint(4) DEFAULT '0',
  `is_blocked` tinyint(4) DEFAULT '0',
  `fund_source` text,
  `login_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1259 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

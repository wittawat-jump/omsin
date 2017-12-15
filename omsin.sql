-- phpMyAdmin SQL Dump
-- version 4.0.8
-- http://www.phpmyadmin.net
--
-- โฮสต์: localhost
-- เวลาในการสร้าง: 23 เม.ย. 2017  15:14น.
-- เวอร์ชั่นของเซิร์ฟเวอร์: 5.1.73-log
-- รุ่นของ PHP: 5.4.45

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- ฐานข้อมูล: `acc_app`
--

-- --------------------------------------------------------

--
-- โครงสร้างตาราง `omsin_category`
--

CREATE TABLE IF NOT EXISTS `omsin_category` (
  `owner_id` int(11) unsigned NOT NULL,
  `id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL,
  `topic` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`owner_id`,`id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- โครงสร้างตาราง `omsin_emailtemplate`
--

CREATE TABLE IF NOT EXISTS `omsin_emailtemplate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `email_id` int(10) unsigned NOT NULL,
  `language` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `from_email` text COLLATE utf8_unicode_ci NOT NULL,
  `copy_to` text COLLATE utf8_unicode_ci NOT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `subject` text COLLATE utf8_unicode_ci NOT NULL,
  `detail` text COLLATE utf8_unicode_ci NOT NULL,
  `last_update` int(11) unsigned NOT NULL,
  `last_send` datetime NOT NULL,
  `email_count` int(10) unsigned NOT NULL,
  `email_limit` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- dump ตาราง `omsin_emailtemplate`
--

INSERT INTO `omsin_emailtemplate` (`id`, `module`, `email_id`, `language`, `from_email`, `copy_to`, `name`, `subject`, `detail`, `last_update`, `last_send`, `email_count`, `email_limit`) VALUES
(1, 'member', 2, 'th', '', '', 'ตอบรับการสมัครสมาชิกใหม่ (ไม่ต้องยืนยันสมาชิก)', 'ตอบรับการสมัครสมาชิก %WEBTITLE%', '<div style="padding: 10px; background-color: rgb(247, 247, 247);">\n<table style="border-collapse: collapse;">\n	<tbody>\n		<tr>\n			<th style="border-width: 1px; border-style: none solid; border-color: rgb(59, 89, 152); padding: 5px; text-align: left; color: rgb(255, 255, 255); font-family: tahoma; font-size: 9pt; background-color: rgb(59, 89, 152);">ยินดีต้อนรับสมาชิกใหม่ %WEBTITLE%</th>\n		</tr>\n		<tr>\n			<td style="border-width: 1px; border-style: none solid solid; border-color: rgb(204, 204, 204) rgb(204, 204, 204) rgb(59, 89, 152); padding: 15px; line-height: 1.8em; font-family: tahoma; font-size: 9pt;">ขอขอบคุณสำหรับการลงทะเบียนกับเรา บัญชีใหม่ของคุณได้รับการติดตั้งเรียบร้อยแล้วและคุณสามารถเข้าระบบได้โดยใช้รายละเอียดด้านล่างนี้<br />\n			<br />\n			ที่อยู่อีเมล์ : <strong>%EMAIL%</strong><br />\n			รหัสผ่าน&nbsp; : <strong>%PASSWORD%</strong><br />\n			<br />\n			คุณสามารถกลับไปเข้าระบบได้ที่ <a href="%WEBURL%">%WEBURL%</a></td>\n		</tr>\n		<tr>\n			<td style="padding: 15px; color: rgb(153, 153, 153); font-family: tahoma; font-size: 8pt;">ด้วยความขอบคุณ <a href="mailto:%ADMINEMAIL%">เว็บมาสเตอร์</a></td>\n		</tr>\n	</tbody>\n</table>\n</div>\n', 1378257485, '0000-00-00 00:00:00', 0, 0),
(2, 'member', 3, 'th', '', '', 'ขอรหัสผ่านใหม่', 'รหัสผ่านของคุณใน %WEBTITLE%', '<div style="padding: 10px;  background-color: rgb(247, 247, 247);">\r\n<table style=" border-collapse: collapse;">\r\n	<tbody>\r\n		<tr>\r\n			<th style="border-width: 1px; border-style: none solid; border-color: rgb(59, 89, 152); padding: 5px; text-align: left; color: rgb(255, 255, 255); font-family: tahoma; font-size: 9pt; background-color: rgb(59, 89, 152);">รหัสผ่านของคุณใน %WEBTITLE%</th>\r\n		</tr>\r\n		<tr>\r\n			<td style="border-width: 1px; border-style: none solid solid; border-color: rgb(204, 204, 204) rgb(204, 204, 204) rgb(59, 89, 152); padding: 15px; line-height: 1.8em; font-family: tahoma; font-size: 9pt;">รหัสผ่านใหม่ของคุณถูกส่งมาจากระบบอัตโนมัติ เมื่อ %TIME%<br />\r\n			ไม่ว่าคุณจะได้ทำการขอรหัสผ่านใหม่หรือไม่ก็ตาม โปรดใช้รหัสผ่านใหม่นี้กับบัญชีของคุณ<br />\r\n			(ถ้าคุณไม่ได้ดำเนินการนี้ด้วยตัวเอง อาจมีผู้พยายามเข้าไปเปลี่ยนแปลงข้อมูลส่วนตัวของคุณ)<br />\r\n			<br />\r\n			ที่อยู่อีเมล์ : <strong>%EMAIL%</strong><br />\r\n			รหัสผ่าน : <strong>%PASSWORD%</strong><br />\r\n			<br />\r\n			คุณสามารถกลับไปเข้าระบบและแก้ไขข้อมูลส่วนตัวของคุณใหม่ได้ที่ <a href="%WEBURL%">%WEBURL%</a></td>\r\n		</tr>\r\n		<tr>\r\n			<td style="padding: 15px; color: rgb(153, 153, 153); font-family: tahoma; font-size: 8pt;">ด้วยความขอบคุณ <a href="mailto:%ADMINEMAIL%">เว็บมาสเตอร์</a></td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n</div>\r\n', 1377480357, '0000-00-00 00:00:00', 0, 0);
-- --------------------------------------------------------

--
-- โครงสร้างตาราง `omsin_ierecord`
--

CREATE TABLE IF NOT EXISTS `omsin_ierecord` (
  `owner_id` int(11) unsigned NOT NULL,
  `id` int(11) unsigned NOT NULL,
  `status` enum('IN','OUT','TRANSFER','INIT') COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) NOT NULL,
  `wallet` int(11) unsigned NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` date NOT NULL,
  `income` decimal(10,2) NOT NULL,
  `expense` decimal(10,2) NOT NULL,
  `transfer_to` int(11) NOT NULL,
  PRIMARY KEY (`owner_id`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- โครงสร้างตาราง `omsin_user`
--

CREATE TABLE IF NOT EXISTS `omsin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `fb` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` int(11) unsigned NOT NULL,
  `visited` int(11) unsigned DEFAULT NULL,
  `lastvisited` int(11) unsigned DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session_id` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

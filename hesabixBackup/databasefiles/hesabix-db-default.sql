-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 28, 2023 at 04:54 PM
-- Server version: 8.2.0
-- PHP Version: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hesabix`
--

-- --------------------------------------------------------

--
-- Table structure for table `apidocument`
--

DROP TABLE IF EXISTS `apidocument`;
CREATE TABLE IF NOT EXISTS `apidocument` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apidocument`
--

INSERT INTO `apidocument` (`id`, `title`, `body`, `date_submit`) VALUES
(1, 'api/user/login', '<p>برای ورود به حسابیکس استفاده می شود.</p>\r\n\r\n<p><strong>پارامتر&zwnj;های ورودی:</strong></p>\r\n\r\n<ul>\r\n	<li><strong>email</strong></li>\r\n	<li><strong>password</strong></li>\r\n</ul>\r\n\r\n<p><strong>پارامترهای خروجی:</strong></p>\r\n\r\n<ul>\r\n	<li><strong>X-AUTH-TOKEN</strong></li>\r\n</ul>\r\n\r\n<p><strong>از پارامتر خروجی برای ارسال درخواستهای بعدی استفاده خواهد شد. برای استفاده از سایر توابع کد بازگشتی از X-AUTH-TOKEN باید در هدر درخواست برای شناسایی کاربر قرار بگیرد.</strong></p>\r\n\r\n<p><strong>در صورت بروز خطا پارامتر ERROR به همراه متن خطا بازگشت داده می شود.</strong></p>', NULL),
(2, '/api/bank/list', '<p>برای گرفتن لیست حساب های بانکی استفاده می شود.</p>\r\n\r\n<p><strong>پارامتر&zwnj;های ورودی:</strong></p>\r\n\r\n<p><span style=\"color:#c0392b\"><strong>ندارد</strong></span></p>\r\n\r\n<p><strong>پارامترهای خروجی:</strong></p>\r\n\r\n<ul>\r\n	<li>id</li>\r\n	<li>name</li>\r\n	<li>cardNum</li>\r\n	<li>shaba</li>\r\n	<li>accountNum</li>\r\n	<li>owner</li>\r\n	<li>shobe</li>\r\n	<li>posNum</li>\r\n	<li>des</li>\r\n	<li>mobileInternetBank</li>\r\n	<li>code</li>\r\n</ul>', NULL),
(3, 'api/bank/info/{code}', '<p>برای دریافت اطلاعات مربوط به یک حساب بانکی استفاده می شود.</p>\r\n\r\n<p>پارامترهای همراه لینک:</p>\r\n\r\n<ul>\r\n	<li>code : کد حسابداری مربوط به بانک مورد نظر</li>\r\n</ul>\r\n\r\n<p><strong>پارامترهای ورودی:</strong></p>\r\n\r\n<p><span style=\"color:#c0392b\">ندارد</span></p>\r\n\r\n<p><strong>پارامترهای خروجی:</strong></p>\r\n\r\n<ul>\r\n	<li>id</li>\r\n	<li>name</li>\r\n	<li>cardNum</li>\r\n	<li>shaba</li>\r\n	<li>accountNum</li>\r\n	<li>owner</li>\r\n	<li>shobe</li>\r\n	<li>posNum</li>\r\n	<li>des</li>\r\n	<li>mobileInternetBank</li>\r\n	<li>code</li>\r\n</ul>', NULL),
(4, 'api/bank/mod/{code}', '<p>برای افزودن یا ویرایش حساب بانکی استفاده می شود.</p>\r\n\r\n<p>پارامترهای همراه لینک:</p>\r\n\r\n<ul>\r\n	<li>code : کد حسابداری مربوط به بانک است. در صورتی که ارسال نشود api در مود افزودن مورد جدید خواهد بود و در صورت ارسال این کد حسابدای با مقادیر ارسال ویرایش خواهد شد.</li>\r\n</ul>\r\n\r\n<p>پارامترهای ارسالی:</p>\r\n\r\n<ul>\r\n	<li>id</li>\r\n	<li>name</li>\r\n	<li>cardNum</li>\r\n	<li>shaba</li>\r\n	<li>accountNum</li>\r\n	<li>owner</li>\r\n	<li>shobe</li>\r\n	<li>posNum</li>\r\n	<li>des</li>\r\n	<li>mobileInternetBank</li>\r\n</ul>\r\n\r\n<p>پارامترهای دریافتی:</p>\r\n\r\n<ul>\r\n	<li>result: مقدار عددی</li>\r\n</ul>\r\n\r\n<p>نتایج به صورت جدول زیر است:</p>\r\n\r\n<table border=\"1\" cellpadding=\"1\" cellspacing=\"1\" dir=\"rtl\" style=\"width:500px\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"text-align:center\"><strong><span style=\"color:#c0392b\">عدد خروجی result</span></strong></td>\r\n			<td style=\"text-align:center\"><strong><span style=\"color:#c0392b\">نتیجه</span></strong></td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align:center\">-1</td>\r\n			<td style=\"text-align:center\">نام تکراری است</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align:center\">3</td>\r\n			<td style=\"text-align:center\">نام وارد شده بسیار کوتاه است.</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align:center\">1</td>\r\n			<td style=\"text-align:center\">عملیات با موفقیت انجام شده</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<p>&nbsp;</p>', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `apitoken`
--

DROP TABLE IF EXISTS `apitoken`;
CREATE TABLE IF NOT EXISTS `apitoken` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int DEFAULT NULL,
  `submitter_id` int NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_expire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_23E5A7D34D9866B8` (`bid_id`),
  KEY `IDX_23E5A7D3919E5513` (`submitter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_file`
--

DROP TABLE IF EXISTS `archive_file`;
CREATE TABLE IF NOT EXISTS `archive_file` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_mod` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `public` tinyint(1) DEFAULT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_doc_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_doc_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BCBAE08B4D9866B8` (`bid_id`),
  KEY `IDX_BCBAE08B919E5513` (`submitter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_orders`
--

DROP TABLE IF EXISTS `archive_orders`;
CREATE TABLE IF NOT EXISTS `archive_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gate_pay` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_pan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expire_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `month` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_182AE9FB4D9866B8` (`bid_id`),
  KEY `IDX_182AE9FB919E5513` (`submitter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_account`
--

DROP TABLE IF EXISTS `bank_account`;
CREATE TABLE IF NOT EXISTS `bank_account` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `card_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shaba` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shobe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pos_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mobile_internet_bank` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `balance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_53A23E0A4D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_cat`
--

DROP TABLE IF EXISTS `blog_cat`;
CREATE TABLE IF NOT EXISTS `blog_cat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_cat`
--

INSERT INTO `blog_cat` (`id`, `label`, `code`) VALUES
(1, 'عمومی', 'general');

-- --------------------------------------------------------

--
-- Table structure for table `blog_comment`
--

DROP TABLE IF EXISTS `blog_comment`;
CREATE TABLE IF NOT EXISTS `blog_comment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7882EFEF4B89032C` (`post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_post`
--

DROP TABLE IF EXISTS `blog_post`;
CREATE TABLE IF NOT EXISTS `blog_post` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submitter_id` int NOT NULL,
  `cat_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_submit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `views` bigint NOT NULL,
  `img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `intero` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BA5AE01D919E5513` (`submitter_id`),
  KEY `IDX_BA5AE01DE6ADA943` (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `business`
--

DROP TABLE IF EXISTS `business`;
CREATE TABLE IF NOT EXISTS `business` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `legal_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `money_id` int DEFAULT NULL,
  `field` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shenasemeli` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `codeeghtesadi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shomaresabt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ostan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shahrestan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `postalcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tel` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mobile` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wesite` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `maliyatafzode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `person_code` bigint DEFAULT NULL,
  `bank_code` bigint DEFAULT NULL,
  `receive_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `accounting_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commodity_code` bigint DEFAULT NULL,
  `salary_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cashdesk_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zarinpal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `store_online` tinyint(1) DEFAULT NULL,
  `store_username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sms_charge` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shortlinks` tinyint(1) DEFAULT NULL,
  `wallet_match_bank_id` int DEFAULT NULL,
  `wallet_enable` tinyint(1) DEFAULT NULL,
  `storeroom_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `archive_size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8D36E387E3C61F9` (`owner_id`),
  KEY `IDX_8D36E38BF29332C` (`money_id`),
  KEY `IDX_8D36E38574F80DE` (`wallet_match_bank_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashdesk`
--

DROP TABLE IF EXISTS `cashdesk`;
CREATE TABLE IF NOT EXISTS `cashdesk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `des` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `balance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_165987F94D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `change_report`
--

DROP TABLE IF EXISTS `change_report`;
CREATE TABLE IF NOT EXISTS `change_report` (
  `id` int NOT NULL AUTO_INCREMENT,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commodity`
--

DROP TABLE IF EXISTS `commodity`;
CREATE TABLE IF NOT EXISTS `commodity` (
  `id` int NOT NULL AUTO_INCREMENT,
  `unit_id` int NOT NULL,
  `bid_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `code` bigint NOT NULL,
  `price_buy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price_sell` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `khadamat` tinyint(1) DEFAULT NULL,
  `cat_id` int DEFAULT NULL,
  `order_point` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commodity_count_check` tinyint(1) DEFAULT NULL,
  `min_order_count` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `day_loading` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `speed_access` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5E8D2F74F8BD700D` (`unit_id`),
  KEY `IDX_5E8D2F744D9866B8` (`bid_id`),
  KEY `IDX_5E8D2F74E6ADA943` (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commodity_cat`
--

DROP TABLE IF EXISTS `commodity_cat`;
CREATE TABLE IF NOT EXISTS `commodity_cat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `upper` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `root` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_687F6B14D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commodity_drop`
--

DROP TABLE IF EXISTS `commodity_drop`;
CREATE TABLE IF NOT EXISTS `commodity_drop` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `udprice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `udprice_percent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `can_edit` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_14E674574D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `commodity_drop`
--

INSERT INTO `commodity_drop` (`id`, `bid_id`, `name`, `udprice`, `udprice_percent`, `can_edit`) VALUES
(1, NULL, 'رنگ', '0', '0', NULL),
(2, NULL, 'سایز', '0', '0', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `commodity_drop_link`
--

DROP TABLE IF EXISTS `commodity_drop_link`;
CREATE TABLE IF NOT EXISTS `commodity_drop_link` (
  `id` int NOT NULL AUTO_INCREMENT,
  `commoditydrop_id` int NOT NULL,
  `commodity_id` int NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8626B6BDC15B0809` (`commoditydrop_id`),
  KEY `IDX_8626B6BDB4ACC212` (`commodity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commodity_unit`
--

DROP TABLE IF EXISTS `commodity_unit`;
CREATE TABLE IF NOT EXISTS `commodity_unit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commodity_unit`
--

INSERT INTO `commodity_unit` (`id`, `name`) VALUES
(1, 'عدد'),
(2, 'جین'),
(3, 'دستگاه'),
(4, 'کیلوگرم'),
(5, 'لیتر'),
(6, 'متر'),
(7, 'پالت'),
(8, 'متر مکعب'),
(9, 'سانتی متر'),
(10, 'میلی لیتر'),
(11, 'گرم'),
(12, 'بسته'),
(13, 'دست'),
(14, 'ورق'),
(15, 'کیسه'),
(16, 'حلقه'),
(17, 'رول'),
(18, 'برگ'),
(19, 'توپ'),
(20, 'شاخه'),
(21, 'بشكه'),
(22, 'نسخه'),
(23, 'جلد'),
(24, 'قوطي'),
(25, 'بطري'),
(26, 'جفت'),
(27, 'پرس'),
(29, 'دفعه'),
(30, 'ساعت/کارکرد'),
(31, 'روزانه'),
(32, 'ماه'),
(33, 'روز'),
(34, 'سال'),
(35, 'ترابایت'),
(36, 'گیگابایت'),
(37, 'مگابایت'),
(38, 'گیگا بیت/ثانیه');

-- --------------------------------------------------------

--
-- Table structure for table `email_history`
--

DROP TABLE IF EXISTS `email_history`;
CREATE TABLE IF NOT EXISTS `email_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submitter_id` int DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_9A7A1884919E5513` (`submitter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guide_content`
--

DROP TABLE IF EXISTS `guide_content`;
CREATE TABLE IF NOT EXISTS `guide_content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submiter_id` int NOT NULL,
  `cat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `date_submit` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CAD3AA81A2251D63` (`submiter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hesabdari_doc`
--

DROP TABLE IF EXISTS `hesabdari_doc`;
CREATE TABLE IF NOT EXISTS `hesabdari_doc` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int DEFAULT NULL,
  `submitter_id` int NOT NULL,
  `year_id` int NOT NULL,
  `money_id` int NOT NULL,
  `date_submit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `code` bigint NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mdate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `plugin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ref_data` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shortlink` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wallet_transaction_id` int DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `temp_status` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  KEY `IDX_81C3CD534D9866B8` (`bid_id`),
  KEY `IDX_81C3CD53919E5513` (`submitter_id`),
  KEY `IDX_81C3CD5340C1FEA7` (`year_id`),
  KEY `IDX_81C3CD53BF29332C` (`money_id`),
  KEY `IDX_81C3CD53924C1837` (`wallet_transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=206 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hesabdari_doc_hesabdari_doc`
--

DROP TABLE IF EXISTS `hesabdari_doc_hesabdari_doc`;
CREATE TABLE IF NOT EXISTS `hesabdari_doc_hesabdari_doc` (
  `hesabdari_doc_source` int NOT NULL,
  `hesabdari_doc_target` int NOT NULL,
  PRIMARY KEY (`hesabdari_doc_source`,`hesabdari_doc_target`),
  KEY `IDX_BE675746E2A225E5` (`hesabdari_doc_source`),
  KEY `IDX_BE675746FB47756A` (`hesabdari_doc_target`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hesabdari_row`
--

DROP TABLE IF EXISTS `hesabdari_row`;
CREATE TABLE IF NOT EXISTS `hesabdari_row` (
  `id` int NOT NULL AUTO_INCREMENT,
  `doc_id` int NOT NULL,
  `ref_id` int NOT NULL,
  `person_id` int DEFAULT NULL,
  `bank_id` int DEFAULT NULL,
  `bs` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `bd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bid_id` int NOT NULL,
  `year_id` int NOT NULL,
  `commodity_id` int DEFAULT NULL,
  `commdity_count` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `salary_id` int DEFAULT NULL,
  `cashdesk_id` int DEFAULT NULL,
  `referral` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ref_data` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `plugin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `temp_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  KEY `IDX_83B2C6EC895648BC` (`doc_id`),
  KEY `IDX_83B2C6EC21B741A9` (`ref_id`),
  KEY `IDX_83B2C6EC217BBB47` (`person_id`),
  KEY `IDX_83B2C6EC11C8FB41` (`bank_id`),
  KEY `IDX_83B2C6EC4D9866B8` (`bid_id`),
  KEY `IDX_83B2C6EC40C1FEA7` (`year_id`),
  KEY `IDX_83B2C6ECB4ACC212` (`commodity_id`),
  KEY `IDX_83B2C6ECB0FDF16E` (`salary_id`),
  KEY `IDX_83B2C6ECBA216AA5` (`cashdesk_id`)
) ENGINE=InnoDB AUTO_INCREMENT=502 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hesabdari_table`
--

DROP TABLE IF EXISTS `hesabdari_table`;
CREATE TABLE IF NOT EXISTS `hesabdari_table` (
  `id` int NOT NULL AUTO_INCREMENT,
  `upper_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `entity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_40F7185C77153098` (`code`),
  KEY `IDX_40F7185C6F3C117F` (`upper_id`)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hesabdari_table`
--

INSERT INTO `hesabdari_table` (`id`, `upper_id`, `name`, `type`, `code`, `entity`) VALUES
(1, NULL, 'جدول جساب', 'calc', '1', NULL),
(2, 23, 'دارایی‌های جاری', 'calc', '2', NULL),
(3, 2, 'حساب‌های دریافتی', 'person', '3', NULL),
(4, 2, 'موجودی نقد و بانک', 'calc', '4', NULL),
(5, 4, 'حساب‌های بانکی', 'bank', '5', NULL),
(6, 24, 'بدهی‌های جاری', 'calc', '6', NULL),
(7, 6, ' حساب ها و اسناد پرداختنی ', 'calc', '7', NULL),
(8, 7, 'اسناد پرداختنی ', 'person', '8', NULL),
(9, 7, 'حساب‌های پرداختنی ', 'bank', '9', NULL),
(10, 23, 'دارایی های غیر جاری ', 'calc', '10', NULL),
(11, 10, 'دارایی های ثابت ', 'calc', '11', NULL),
(13, 11, ' زمین ', 'calc', '12', NULL),
(15, 11, 'ساختمان', 'calc', '13', NULL),
(16, 11, ' وسائل نقلیه ', 'calc', '14', NULL),
(17, 11, ' اثاثیه اداری ', 'calc', '15', NULL),
(18, 10, ' استهلاک انباشته ', 'calc', '16', NULL),
(20, 18, ' استهلاک انباشته ساختمان ', 'calc', '17', NULL),
(21, 18, ' استهلاک انباشته وسائل نقلیه ', 'calc', '18', NULL),
(22, 18, 'استهلاک انباشته اثاثیه اداری ', 'calc', '19', NULL),
(23, 1, 'دارایی ها', 'calc', '20', NULL),
(24, 1, ' بدهی ها ', 'calc', '21', NULL),
(25, 6, ' سایر حساب های پرداختنی ', 'calc', '22', NULL),
(26, 25, ' ذخیره مالیات بر درآمد پرداختنی ', 'calc', '23', NULL),
(27, 25, 'مالیات بر درآمد پرداختنی ', 'calc', '24', NULL),
(28, 25, ' مالیات حقوق و دستمزد پرداختنی ', 'calc', '25', NULL),
(29, 25, ' حق بیمه پرداختنی ', 'calc', '26', NULL),
(31, 25, ' حقوق و دستمزد پرداختنی ', 'calc', '27', NULL),
(32, 25, 'عیدی و پاداش پرداختنی ', 'calc', '28', NULL),
(33, 25, ' سایر هزینه های پرداختنی ', 'calc', '29', NULL),
(34, 6, 'پیش دریافت ها ', 'calc', '30', NULL),
(35, 34, ' پیش دریافت فروش ', 'calc', '31', NULL),
(36, 34, ' سایر پیش دریافت ها ', 'calc', '32', NULL),
(37, 6, 'مالیات بر ارزش افزوده فروش ', 'calc', '33', NULL),
(38, 24, 'بدهیهای غیر جاری ', 'calc', '34', NULL),
(39, 38, 'حساب ها و اسناد پرداختنی بلندمدت ', 'calc', '35', NULL),
(40, 39, ' حساب های پرداختنی بلندمدت ', 'calc', '36', NULL),
(41, 39, 'اسناد پرداختنی بلندمدت ', 'calc', '37', NULL),
(44, 38, 'ذخیره مزایای پایان خدمت کارکنان ', 'calc', '38', NULL),
(45, 38, 'وام پرداختنی ', 'calc', '39', NULL),
(46, 1, 'حقوق صاحبان سهام ', 'calc', '40', NULL),
(47, 46, 'سرمایه ', 'calc', '41', NULL),
(48, 47, ' سرمایه اولیه ', 'calc', '42', NULL),
(49, 47, 'افزایش یا کاهش سرمایه ', 'calc', '43', NULL),
(50, 47, ' اندوخته قانونی ', 'calc', '44', NULL),
(51, 47, 'برداشت ها ', 'calc', '45', NULL),
(52, 47, 'سهم سود و زیان ', 'calc', '46', NULL),
(53, 47, 'سود یا زیان انباشته (سنواتی) ', 'calc', '47', NULL),
(54, 1, 'بهای تمام شده کالای فروخته شده ', 'calc', '48', NULL),
(55, 54, 'بهای تمام شده کالای فروخته شده ', 'calc', '49', NULL),
(56, 54, 'برگشت از خرید ', 'calc', '50', NULL),
(57, 54, ' تخفیفات نقدی خرید ', 'calc', '51', NULL),
(58, 1, ' فروش ', 'calc', '52', NULL),
(59, 58, ' فروش کالا  ', 'calc', '53', NULL),
(60, 58, 'برگشت از فروش ', 'calc', '54', NULL),
(61, 58, 'تخفیفات نقدی فروش ', 'calc', '55', NULL),
(64, 1, ' درآمد ', 'calc', '56', NULL),
(66, 64, ' درآمد های عملیاتی ', 'calc', '57', NULL),
(67, 66, 'درآمد حاصل از فروش خدمات ', 'calc', '58', NULL),
(68, 66, 'برگشت از خرید خدمات ', 'calc', '59', NULL),
(69, 66, 'درآمد اضافه کالا ', 'calc', '60', NULL),
(70, 66, 'درآمد حمل کالا ', 'calc', '61', NULL),
(72, 64, 'درآمد های غیر عملیاتی ', 'calc', '62', NULL),
(73, 72, ' درآمد حاصل از سرمایه گذاری ', 'calc', '63', NULL),
(74, 72, 'درآمد سود سپرده ها ', 'calc', '64', NULL),
(75, 72, ' سایر درآمد ها ', 'calc', '65', NULL),
(76, 72, 'درآمد تسعیر ارز ', 'calc', '66', NULL),
(77, 1, 'هزینه ها ', 'calc', '67', NULL),
(78, 77, 'هزینه های پرسنلی ', 'calc', '68', NULL),
(79, 78, ' هزینه حقوق و دستمزد ', 'calc', '69', NULL),
(80, 79, ' حقوق پایه ', 'calc', '70', NULL),
(81, 79, ' اضافه کار ', 'calc', '71', NULL),
(82, 79, ' حق شیفت و شب کاری ', 'calc', '72', NULL),
(83, 79, ' حق نوبت کاری ', 'calc', '73', NULL),
(84, 79, ' حق ماموریت ', 'calc', '74', NULL),
(85, 79, ' فوق العاده مسکن و خاروبار ', 'calc', '75', NULL),
(86, 79, ' حق اولاد ', 'calc', '76', NULL),
(87, 79, ' عیدی و پاداش ', 'calc', '77', NULL),
(88, 79, ' بازخرید سنوات خدمت کارکنان ', 'calc', '78', NULL),
(89, 79, ' بازخرید مرخصی ', 'calc', '79', NULL),
(90, 79, ' بیمه سهم کارفرما ', 'calc', '80', NULL),
(91, 79, ' بیمه بیکاری ', 'calc', '81', NULL),
(92, 79, ' حقوق مزایای متفرقه ', 'calc', '82', NULL),
(93, 78, 'سایر هزینه های کارکنان ', 'calc', '83', NULL),
(94, 93, ' سفر و ماموریت ', 'calc', '84', NULL),
(95, 93, ' ایاب و ذهاب ', 'calc', '85', NULL),
(96, 93, ' سایر هزینه های کارکنان ', 'calc', '86', NULL),
(97, 77, ' هزینه های عملیاتی ', 'calc', '87', NULL),
(98, 97, ' خرید خدمات ', 'calc', '88', NULL),
(99, 97, ' برگشت از فروش خدمات ', 'calc', '89', NULL),
(100, 97, 'هزینه حمل کالا ', 'calc', '90', NULL),
(101, 97, ' تعمیر و نگهداری اموال و اثاثیه ', 'calc', '91', NULL),
(102, 97, ' هزینه اجاره محل ', 'calc', '92', NULL),
(103, 97, ' هزینه های عمومی ', 'calc', '93', NULL),
(104, 97, ' هزینه ملزومات مصرفی ', 'calc', '94', NULL),
(105, 97, ' هزینه کسری و ضایعات کالا', 'calc', '95', NULL),
(106, 97, ' بیمه دارایی های ثابت ', 'calc', '96', NULL),
(107, 77, 'هزینه های استهلاک ', 'calc', '97', NULL),
(108, 107, ' هزینه استهلاک ساختمان ', 'calc', '98', NULL),
(109, 107, ' هزینه استهلاک وسائل نقلیه ', 'calc', '99', NULL),
(110, 107, ' هزینه استهلاک اثاثیه ', 'calc', '100', NULL),
(114, 77, ' هزینه های بازاریابی و توزیع و فروش ', 'calc', '101', NULL),
(115, 114, 'هزینه آگهی و تبلیغات ', 'calc', '102', NULL),
(116, 114, ' هزینه بازاریابی و پورسانت ', 'calc', '103', NULL),
(117, 114, ' سایر هزینه های توزیع و فروش ', 'calc', '104', NULL),
(118, 77, 'هزینه های غیرعملیاتی ', 'calc', '105', NULL),
(119, 118, 'هزینه های بانکی ', 'calc', '106', NULL),
(120, 119, ' سود و کارمزد وامها ', 'calc', '107', NULL),
(121, 119, 'کارمزد خدمات بانکی ', 'calc', '108', NULL),
(122, 119, ' جرائم دیرکرد بانکی ', 'calc', '109', NULL),
(123, 118, 'هزینه تسعیر ارز ', 'calc', '110', NULL),
(124, 118, ' هزینه مطالبات سوخت شده ', 'calc', '111', NULL),
(125, 1, 'سایر حساب ها ', 'calc', '112', NULL),
(126, 125, 'حساب های انتظامی ', 'calc', '113', NULL),
(127, 126, ' حساب های انتظامی ', 'calc', '114', NULL),
(128, 126, ' طرف حساب های انتظامی ', 'calc', '115', NULL),
(129, 125, 'حساب های کنترلی ', 'calc', '116', NULL),
(130, 129, ' کنترل کسری و اضافه کالا ', 'calc', '117', NULL),
(132, 125, 'حساب خلاصه سود و زیان ', 'calc', '118', NULL),
(133, 132, 'خلاصه سود و زیان ', 'calc', '119', NULL),
(137, 2, 'موجودی کالا ', 'calc', '120', NULL),
(138, 4, 'صندوق', 'cashdesk', '121', NULL),
(139, 4, 'تنخواه گردان', 'salary', '122', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
CREATE TABLE IF NOT EXISTS `log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `bid_id` int DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `part` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ipaddress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8F3F68C5A76ED395` (`user_id`),
  KEY `IDX_8F3F68C54D9866B8` (`bid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
CREATE TABLE IF NOT EXISTS `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `queue_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `money`
--

DROP TABLE IF EXISTS `money`;
CREATE TABLE IF NOT EXISTS `money` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `money`
--

INSERT INTO `money` (`id`, `name`, `label`) VALUES
(1, 'IRR', 'ریال ایران');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `bid_id` int NOT NULL,
  `url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `viewed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BF5476CAA76ED395` (`user_id`),
  KEY `IDX_BF5476CA4D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pay_info_temp`
--

DROP TABLE IF EXISTS `pay_info_temp`;
CREATE TABLE IF NOT EXISTS `pay_info_temp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `doc_id` int DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gate_pay` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_pan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7F36E8384D9866B8` (`bid_id`),
  KEY `IDX_7F36E838895648BC` (`doc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

DROP TABLE IF EXISTS `permission`;
CREATE TABLE IF NOT EXISTS `permission` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `bid_id` int NOT NULL,
  `owner` tinyint(1) DEFAULT NULL,
  `settings` tinyint(1) DEFAULT NULL,
  `person` tinyint(1) DEFAULT NULL,
  `commodity` tinyint(1) DEFAULT NULL,
  `getpay` tinyint(1) DEFAULT NULL,
  `banks` tinyint(1) DEFAULT NULL,
  `bank_transfer` tinyint(1) DEFAULT NULL,
  `buy` tinyint(1) DEFAULT NULL,
  `sell` tinyint(1) DEFAULT NULL,
  `cost` tinyint(1) DEFAULT NULL,
  `income` tinyint(1) DEFAULT NULL,
  `accounting` tinyint(1) DEFAULT NULL,
  `report` tinyint(1) DEFAULT NULL,
  `log` tinyint(1) DEFAULT NULL,
  `permission` tinyint(1) DEFAULT NULL,
  `salary` tinyint(1) DEFAULT NULL,
  `cashdesk` tinyint(1) DEFAULT NULL,
  `plug_noghre_admin` tinyint(1) DEFAULT NULL,
  `plug_noghre_sell` tinyint(1) DEFAULT NULL,
  `plug_ccadmin` tinyint(1) DEFAULT NULL,
  `store` tinyint(1) DEFAULT NULL,
  `wallet` tinyint(1) DEFAULT NULL,
  `archive_upload` tinyint(1) DEFAULT NULL,
  `archive_mod` tinyint(1) DEFAULT NULL,
  `archive_delete` tinyint(1) DEFAULT NULL,
  `shareholder` tinyint(1) DEFAULT NULL,
  `archive_view` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E04992AAA76ED395` (`user_id`),
  KEY `IDX_E04992AA4D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `person`
--

DROP TABLE IF EXISTS `person`;
CREATE TABLE IF NOT EXISTS `person` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `code` bigint NOT NULL,
  `nikename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tel` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mobile` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `plug_noghre_morsa` tinyint(1) DEFAULT NULL,
  `plug_noghre_hakak` tinyint(1) DEFAULT NULL,
  `plug_noghre_tarash` tinyint(1) DEFAULT NULL,
  `employe` tinyint(1) DEFAULT NULL,
  `plug_noghre_ghalam` tinyint(1) DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shenasemeli` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `codeeghtesadi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sabt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keshvar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ostan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shahr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `postalcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fax` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `birthday` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `speed_access` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_34DCD1764D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plugin`
--

DROP TABLE IF EXISTS `plugin`;
CREATE TABLE IF NOT EXISTS `plugin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `date_expire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gate_pay` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ref_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `card_pan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E96E27944D9866B8` (`bid_id`),
  KEY `IDX_E96E2794919E5513` (`submitter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plugin_prodect`
--

DROP TABLE IF EXISTS `plugin_prodect`;
CREATE TABLE IF NOT EXISTS `plugin_prodect` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `timelabel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plugin_prodect`
--

INSERT INTO `plugin_prodect` (`id`, `name`, `code`, `timestamp`, `timelabel`, `price`, `icon`) VALUES
(1, 'افزونه کارگاه نقره سازی', 'noghre', '32104000', 'سالیانه', '2999000', 'noghrekoob.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `plug_noghre_order`
--

DROP TABLE IF EXISTS `plug_noghre_order`;
CREATE TABLE IF NOT EXISTS `plug_noghre_order` (
  `id` int NOT NULL AUTO_INCREMENT,
  `doc_id` int NOT NULL,
  `bid_id` int NOT NULL,
  `morsa_id` int DEFAULT NULL,
  `tarash_id` int DEFAULT NULL,
  `hakak_id` int DEFAULT NULL,
  `ghalam_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `delivery_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `place` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `negin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `noghre_amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `negin_fee` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ring_model` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ring_size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `noghre_fee` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EEEE085E895648BC` (`doc_id`),
  KEY `IDX_EEEE085E4D9866B8` (`bid_id`),
  KEY `IDX_EEEE085EB130EC9E` (`morsa_id`),
  KEY `IDX_EEEE085E36B8627E` (`tarash_id`),
  KEY `IDX_EEEE085EF8ABEE72` (`hakak_id`),
  KEY `IDX_EEEE085E7BECA6BC` (`ghalam_id`),
  KEY `IDX_EEEE085E9395C3F3` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `printer_queue`
--

DROP TABLE IF EXISTS `printer_queue`;
CREATE TABLE IF NOT EXISTS `printer_queue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submitter_id` int NOT NULL,
  `bid_id` int DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `view` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_93F2764B919E5513` (`submitter_id`),
  KEY `IDX_93F2764B4D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary`
--

DROP TABLE IF EXISTS `salary`;
CREATE TABLE IF NOT EXISTS `salary` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `des` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `balance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9413BB714D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `payamak_username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payamak_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `active_send_sms` tinyint(1) DEFAULT NULL,
  `zarinpal_merchant` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `app_site` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage_price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `melipayamak_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `site_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `discription` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `scripts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `payamak_username`, `payamak_password`, `active_send_sms`, `zarinpal_merchant`, `app_site`, `storage_price`, `melipayamak_token`, `site_keywords`, `discription`, `scripts`) VALUES
(1, 'username', 'password', 1, 'dsdsds', 'http://localhost:5173', '650000', 'apimelimayamak', 'hesabix,حسابیکس,حسابداری ابری رایگان,حسابداری آنلاین رایگان,حسابداری,نرم افزار حسابداری,نرم افزار حسابداری مغازه,نرم افزار حسابداری تحت وب رایگان', 'حسابیکس اولین نرم افزار حسابداری ابری رایگان و متن‌باز است که امور مالی شما را به صورت سریع و ساده انجام می‌دهد.حسابیکس کاملا متن باز است.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shareholder`
--

DROP TABLE IF EXISTS `shareholder`;
CREATE TABLE IF NOT EXISTS `shareholder` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `person_id` int NOT NULL,
  `percent` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D5FE68CC4D9866B8` (`bid_id`),
  KEY `IDX_D5FE68CC217BBB47` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `smspays`
--

DROP TABLE IF EXISTS `smspays`;
CREATE TABLE IF NOT EXISTS `smspays` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_pan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gate_pay` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5F2F70E14D9866B8` (`bid_id`),
  KEY `IDX_5F2F70E1919E5513` (`submitter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `smssettings`
--

DROP TABLE IF EXISTS `smssettings`;
CREATE TABLE IF NOT EXISTS `smssettings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `send_after_sell` tinyint(1) DEFAULT NULL,
  `send_after_sell_pay_online` tinyint(1) DEFAULT NULL,
  `send_after_buy` tinyint(1) DEFAULT NULL,
  `send_after_buy_to_user` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_61178A624D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stack_cat`
--

DROP TABLE IF EXISTS `stack_cat`;
CREATE TABLE IF NOT EXISTS `stack_cat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stack_cat`
--

INSERT INTO `stack_cat` (`id`, `name`, `code`) VALUES
(1, 'عمومی', 'general'),
(2, 'درخواست قابلیت جدید', 'update'),
(3, 'گزارش خطا', 'bug_report'),
(4, 'حسابداری', 'accounting');

-- --------------------------------------------------------

--
-- Table structure for table `stack_content`
--

DROP TABLE IF EXISTS `stack_content`;
CREATE TABLE IF NOT EXISTS `stack_content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submitter_id` int NOT NULL,
  `cat_id` int NOT NULL,
  `upper_id` int DEFAULT NULL,
  `date_submit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `views` bigint NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B5150B0C919E5513` (`submitter_id`),
  KEY `IDX_B5150B0CE6ADA943` (`cat_id`),
  KEY `IDX_B5150B0C6F3C117F` (`upper_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statment`
--

DROP TABLE IF EXISTS `statment`;
CREATE TABLE IF NOT EXISTS `statment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storeroom`
--

DROP TABLE IF EXISTS `storeroom`;
CREATE TABLE IF NOT EXISTS `storeroom` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `manager` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3E2092A84D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storeroom_item`
--

DROP TABLE IF EXISTS `storeroom_item`;
CREATE TABLE IF NOT EXISTS `storeroom_item` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `commodity_id` int NOT NULL,
  `bid_id` int NOT NULL,
  `storeroom_id` int NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `count` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6CA8F5E0700047D2` (`ticket_id`),
  KEY `IDX_6CA8F5E0B4ACC212` (`commodity_id`),
  KEY `IDX_6CA8F5E04D9866B8` (`bid_id`),
  KEY `IDX_6CA8F5E0C9330186` (`storeroom_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storeroom_ticket`
--

DROP TABLE IF EXISTS `storeroom_ticket`;
CREATE TABLE IF NOT EXISTS `storeroom_ticket` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `person_id` int DEFAULT NULL,
  `doc_id` int DEFAULT NULL,
  `year_id` int NOT NULL,
  `storeroom_id` int NOT NULL,
  `transfer_type_id` int NOT NULL,
  `date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `transfer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `referral` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9B4CC0F74D9866B8` (`bid_id`),
  KEY `IDX_9B4CC0F7919E5513` (`submitter_id`),
  KEY `IDX_9B4CC0F7217BBB47` (`person_id`),
  KEY `IDX_9B4CC0F7895648BC` (`doc_id`),
  KEY `IDX_9B4CC0F740C1FEA7` (`year_id`),
  KEY `IDX_9B4CC0F7C9330186` (`storeroom_id`),
  KEY `IDX_9B4CC0F77AF9FED8` (`transfer_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storeroom_transfer_type`
--

DROP TABLE IF EXISTS `storeroom_transfer_type`;
CREATE TABLE IF NOT EXISTS `storeroom_transfer_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `storeroom_transfer_type`
--

INSERT INTO `storeroom_transfer_type` (`id`, `name`) VALUES
(1, 'تحویل درب انبار'),
(2, 'پست عادی'),
(3, 'پست پیشتاز'),
(4, 'باربری'),
(5, 'اتوبوس'),
(6, 'تیپاکس'),
(7, 'پیک');

-- --------------------------------------------------------

--
-- Table structure for table `support`
--

DROP TABLE IF EXISTS `support`;
CREATE TABLE IF NOT EXISTS `support` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submitter_id` int NOT NULL,
  `main` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8004EBA5919E5513` (`submitter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_register` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mobile` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `verify_code_time` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_token`
--

DROP TABLE IF EXISTS `user_token`;
CREATE TABLE IF NOT EXISTS `user_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BDF55A63A76ED395` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transaction`
--

DROP TABLE IF EXISTS `wallet_transaction`;
CREATE TABLE IF NOT EXISTS `wallet_transaction` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `submitter_id` int DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shaba` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_pan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gate_pay` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7DAF9724D9866B8` (`bid_id`),
  KEY `IDX_7DAF972919E5513` (`submitter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `year`
--

DROP TABLE IF EXISTS `year`;
CREATE TABLE IF NOT EXISTS `year` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bid_id` int NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `head` tinyint(1) DEFAULT NULL,
  `start` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `end` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `now` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BB8273374D9866B8` (`bid_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `apitoken`
--
ALTER TABLE `apitoken`
  ADD CONSTRAINT `FK_23E5A7D34D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_23E5A7D3919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `archive_file`
--
ALTER TABLE `archive_file`
  ADD CONSTRAINT `FK_BCBAE08B4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_BCBAE08B919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `archive_orders`
--
ALTER TABLE `archive_orders`
  ADD CONSTRAINT `FK_182AE9FB4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_182AE9FB919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `bank_account`
--
ALTER TABLE `bank_account`
  ADD CONSTRAINT `FK_53A23E0A4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `blog_comment`
--
ALTER TABLE `blog_comment`
  ADD CONSTRAINT `FK_7882EFEF4B89032C` FOREIGN KEY (`post_id`) REFERENCES `blog_post` (`id`);

--
-- Constraints for table `blog_post`
--
ALTER TABLE `blog_post`
  ADD CONSTRAINT `FK_BA5AE01D919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_BA5AE01DE6ADA943` FOREIGN KEY (`cat_id`) REFERENCES `blog_cat` (`id`);

--
-- Constraints for table `business`
--
ALTER TABLE `business`
  ADD CONSTRAINT `FK_8D36E38574F80DE` FOREIGN KEY (`wallet_match_bank_id`) REFERENCES `bank_account` (`id`),
  ADD CONSTRAINT `FK_8D36E387E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_8D36E38BF29332C` FOREIGN KEY (`money_id`) REFERENCES `money` (`id`);

--
-- Constraints for table `cashdesk`
--
ALTER TABLE `cashdesk`
  ADD CONSTRAINT `FK_165987F94D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `commodity`
--
ALTER TABLE `commodity`
  ADD CONSTRAINT `FK_5E8D2F744D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_5E8D2F74E6ADA943` FOREIGN KEY (`cat_id`) REFERENCES `commodity_cat` (`id`),
  ADD CONSTRAINT `FK_5E8D2F74F8BD700D` FOREIGN KEY (`unit_id`) REFERENCES `commodity_unit` (`id`);

--
-- Constraints for table `commodity_cat`
--
ALTER TABLE `commodity_cat`
  ADD CONSTRAINT `FK_687F6B14D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `commodity_drop`
--
ALTER TABLE `commodity_drop`
  ADD CONSTRAINT `FK_14E674574D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `commodity_drop_link`
--
ALTER TABLE `commodity_drop_link`
  ADD CONSTRAINT `FK_8626B6BDB4ACC212` FOREIGN KEY (`commodity_id`) REFERENCES `commodity` (`id`),
  ADD CONSTRAINT `FK_8626B6BDC15B0809` FOREIGN KEY (`commoditydrop_id`) REFERENCES `commodity_drop` (`id`);

--
-- Constraints for table `email_history`
--
ALTER TABLE `email_history`
  ADD CONSTRAINT `FK_9A7A1884919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `guide_content`
--
ALTER TABLE `guide_content`
  ADD CONSTRAINT `FK_CAD3AA81A2251D63` FOREIGN KEY (`submiter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `hesabdari_doc`
--
ALTER TABLE `hesabdari_doc`
  ADD CONSTRAINT `FK_81C3CD5340C1FEA7` FOREIGN KEY (`year_id`) REFERENCES `year` (`id`),
  ADD CONSTRAINT `FK_81C3CD534D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_81C3CD53919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_81C3CD53924C1837` FOREIGN KEY (`wallet_transaction_id`) REFERENCES `wallet_transaction` (`id`),
  ADD CONSTRAINT `FK_81C3CD53BF29332C` FOREIGN KEY (`money_id`) REFERENCES `money` (`id`);

--
-- Constraints for table `hesabdari_doc_hesabdari_doc`
--
ALTER TABLE `hesabdari_doc_hesabdari_doc`
  ADD CONSTRAINT `FK_BE675746E2A225E5` FOREIGN KEY (`hesabdari_doc_source`) REFERENCES `hesabdari_doc` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_BE675746FB47756A` FOREIGN KEY (`hesabdari_doc_target`) REFERENCES `hesabdari_doc` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hesabdari_row`
--
ALTER TABLE `hesabdari_row`
  ADD CONSTRAINT `FK_83B2C6EC11C8FB41` FOREIGN KEY (`bank_id`) REFERENCES `bank_account` (`id`),
  ADD CONSTRAINT `FK_83B2C6EC217BBB47` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_83B2C6EC21B741A9` FOREIGN KEY (`ref_id`) REFERENCES `hesabdari_table` (`id`),
  ADD CONSTRAINT `FK_83B2C6EC40C1FEA7` FOREIGN KEY (`year_id`) REFERENCES `year` (`id`),
  ADD CONSTRAINT `FK_83B2C6EC4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_83B2C6EC895648BC` FOREIGN KEY (`doc_id`) REFERENCES `hesabdari_doc` (`id`),
  ADD CONSTRAINT `FK_83B2C6ECB0FDF16E` FOREIGN KEY (`salary_id`) REFERENCES `salary` (`id`),
  ADD CONSTRAINT `FK_83B2C6ECB4ACC212` FOREIGN KEY (`commodity_id`) REFERENCES `commodity` (`id`),
  ADD CONSTRAINT `FK_83B2C6ECBA216AA5` FOREIGN KEY (`cashdesk_id`) REFERENCES `cashdesk` (`id`);

--
-- Constraints for table `hesabdari_table`
--
ALTER TABLE `hesabdari_table`
  ADD CONSTRAINT `FK_40F7185C6F3C117F` FOREIGN KEY (`upper_id`) REFERENCES `hesabdari_table` (`id`);

--
-- Constraints for table `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `FK_8F3F68C54D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_8F3F68C5A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `FK_BF5476CA4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_BF5476CAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `pay_info_temp`
--
ALTER TABLE `pay_info_temp`
  ADD CONSTRAINT `FK_7F36E8384D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_7F36E838895648BC` FOREIGN KEY (`doc_id`) REFERENCES `hesabdari_doc` (`id`);

--
-- Constraints for table `permission`
--
ALTER TABLE `permission`
  ADD CONSTRAINT `FK_E04992AA4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_E04992AAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `person`
--
ALTER TABLE `person`
  ADD CONSTRAINT `FK_34DCD1764D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `plugin`
--
ALTER TABLE `plugin`
  ADD CONSTRAINT `FK_E96E27944D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_E96E2794919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `plug_noghre_order`
--
ALTER TABLE `plug_noghre_order`
  ADD CONSTRAINT `FK_EEEE085E36B8627E` FOREIGN KEY (`tarash_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_EEEE085E4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_EEEE085E7BECA6BC` FOREIGN KEY (`ghalam_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_EEEE085E895648BC` FOREIGN KEY (`doc_id`) REFERENCES `hesabdari_doc` (`id`),
  ADD CONSTRAINT `FK_EEEE085E9395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_EEEE085EB130EC9E` FOREIGN KEY (`morsa_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_EEEE085EF8ABEE72` FOREIGN KEY (`hakak_id`) REFERENCES `person` (`id`);

--
-- Constraints for table `printer_queue`
--
ALTER TABLE `printer_queue`
  ADD CONSTRAINT `FK_93F2764B4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_93F2764B919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `salary`
--
ALTER TABLE `salary`
  ADD CONSTRAINT `FK_9413BB714D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `shareholder`
--
ALTER TABLE `shareholder`
  ADD CONSTRAINT `FK_D5FE68CC217BBB47` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_D5FE68CC4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `smspays`
--
ALTER TABLE `smspays`
  ADD CONSTRAINT `FK_5F2F70E14D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_5F2F70E1919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `smssettings`
--
ALTER TABLE `smssettings`
  ADD CONSTRAINT `FK_61178A624D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `stack_content`
--
ALTER TABLE `stack_content`
  ADD CONSTRAINT `FK_B5150B0C6F3C117F` FOREIGN KEY (`upper_id`) REFERENCES `stack_content` (`id`),
  ADD CONSTRAINT `FK_B5150B0C919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_B5150B0CE6ADA943` FOREIGN KEY (`cat_id`) REFERENCES `stack_cat` (`id`);

--
-- Constraints for table `storeroom`
--
ALTER TABLE `storeroom`
  ADD CONSTRAINT `FK_3E2092A84D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `storeroom_item`
--
ALTER TABLE `storeroom_item`
  ADD CONSTRAINT `FK_6CA8F5E04D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_6CA8F5E0700047D2` FOREIGN KEY (`ticket_id`) REFERENCES `storeroom_ticket` (`id`),
  ADD CONSTRAINT `FK_6CA8F5E0B4ACC212` FOREIGN KEY (`commodity_id`) REFERENCES `commodity` (`id`),
  ADD CONSTRAINT `FK_6CA8F5E0C9330186` FOREIGN KEY (`storeroom_id`) REFERENCES `storeroom` (`id`);

--
-- Constraints for table `storeroom_ticket`
--
ALTER TABLE `storeroom_ticket`
  ADD CONSTRAINT `FK_9B4CC0F7217BBB47` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_9B4CC0F740C1FEA7` FOREIGN KEY (`year_id`) REFERENCES `year` (`id`),
  ADD CONSTRAINT `FK_9B4CC0F74D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_9B4CC0F77AF9FED8` FOREIGN KEY (`transfer_type_id`) REFERENCES `storeroom_transfer_type` (`id`),
  ADD CONSTRAINT `FK_9B4CC0F7895648BC` FOREIGN KEY (`doc_id`) REFERENCES `hesabdari_doc` (`id`),
  ADD CONSTRAINT `FK_9B4CC0F7919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_9B4CC0F7C9330186` FOREIGN KEY (`storeroom_id`) REFERENCES `storeroom` (`id`);

--
-- Constraints for table `support`
--
ALTER TABLE `support`
  ADD CONSTRAINT `FK_8004EBA5919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `user_token`
--
ALTER TABLE `user_token`
  ADD CONSTRAINT `FK_BDF55A63A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `wallet_transaction`
--
ALTER TABLE `wallet_transaction`
  ADD CONSTRAINT `FK_7DAF9724D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_7DAF972919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `year`
--
ALTER TABLE `year`
  ADD CONSTRAINT `FK_BB8273374D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

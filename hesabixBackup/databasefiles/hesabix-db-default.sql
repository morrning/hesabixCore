-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 21, 2025 at 08:34 PM
-- Server version: 8.0.41-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

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
-- Table structure for table `accounting_package_order`
--

CREATE TABLE `accounting_package_order` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `date_submit` bigint NOT NULL,
  `date_expire` bigint NOT NULL,
  `month` int NOT NULL,
  `price` bigint NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gate_pay` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int DEFAULT NULL,
  `ref_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_pan` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `apitoken`
--

CREATE TABLE `apitoken` (
  `id` int NOT NULL,
  `bid_id` int DEFAULT NULL,
  `submitter_id` int NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_expire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_file`
--

CREATE TABLE `archive_file` (
  `id` int NOT NULL,
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
  `file_size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_orders`
--

CREATE TABLE `archive_orders` (
  `id` int NOT NULL,
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
  `month` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_account`
--

CREATE TABLE `bank_account` (
  `id` int NOT NULL,
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
  `money_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `business`
--

CREATE TABLE `business` (
  `id` int NOT NULL,
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
  `plug_repservice_code` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `archive_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commodity_update_sell_price_auto` tinyint(1) DEFAULT NULL,
  `commodity_update_buy_price_auto` tinyint(1) DEFAULT NULL,
  `profit_calc_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `seal_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `business_money`
--

CREATE TABLE `business_money` (
  `business_id` int NOT NULL,
  `money_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashdesk`
--

CREATE TABLE `cashdesk` (
  `id` int NOT NULL,
  `bid_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `des` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `balance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `money_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `change_report`
--

CREATE TABLE `change_report` (
  `id` int NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cheque`
--

CREATE TABLE `cheque` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `bank_id` int DEFAULT NULL,
  `person_id` int DEFAULT NULL,
  `ref_id` int DEFAULT NULL,
  `date_submit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sayad_num` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_stamp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pay_date` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_oncheque` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locked` tinyint(1) DEFAULT NULL,
  `date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rejected` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commodity`
--

CREATE TABLE `commodity` (
  `id` int NOT NULL,
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
  `without_tax` tinyint(1) DEFAULT NULL,
  `barcodes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commodity_cat`
--

CREATE TABLE `commodity_cat` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `upper` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `root` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commodity_drop`
--

CREATE TABLE `commodity_drop` (
  `id` int NOT NULL,
  `bid_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `udprice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `udprice_percent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `can_edit` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commodity_drop_link`
--

CREATE TABLE `commodity_drop_link` (
  `id` int NOT NULL,
  `commoditydrop_id` int NOT NULL,
  `commodity_id` int NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commodity_unit`
--

CREATE TABLE `commodity_unit` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `float_number` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commodity_unit`
--

INSERT INTO `commodity_unit` (`id`, `name`, `float_number`) VALUES
(1, 'عدد', 0),
(2, 'جین', 0),
(3, 'دستگاه', 0),
(4, 'کیلوگرم', 3),
(5, 'لیتر', 2),
(6, 'متر', 2),
(7, 'پالت', 0),
(8, 'متر مکعب', 2),
(9, 'سانتی متر', 0),
(10, 'میلی لیتر', 0),
(11, 'گرم', 0),
(12, 'بسته', 0),
(13, 'دست', 0),
(14, 'ورق', 1),
(15, 'کیسه', 0),
(16, 'حلقه', 0),
(17, 'رول', 0),
(18, 'برگ', 0),
(19, 'توپ', 0),
(20, 'شاخه', 1),
(21, 'بشكه', 0),
(22, 'نسخه', 0),
(23, 'جلد', 0),
(24, 'قوطي', 0),
(25, 'بطري', 0),
(26, 'جفت', 0),
(27, 'پرس', 0),
(29, 'دفعه', 0),
(30, 'ساعت/کارکرد', 1),
(31, 'روزانه', 0),
(32, 'ماه', 0),
(33, 'روز', 0),
(34, 'سال', 0),
(35, 'ترابایت', 3),
(36, 'گیگابایت', 3),
(37, 'مگابایت', 1),
(38, 'گیگا بیت/ثانیه', 0),
(39, 'تخته', 0),
(40, 'نوبت', 0),
(41, 'مرتبه', 0),
(42, 'سوت', 0),
(43, 'پاکت', 0);

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_settings`
--

CREATE TABLE `dashboard_settings` (
  `id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `bid_id` int NOT NULL,
  `wallet` tinyint(1) DEFAULT NULL,
  `banks` tinyint(1) DEFAULT NULL,
  `acc_docs` tinyint(1) DEFAULT NULL,
  `commodities` tinyint(1) DEFAULT NULL,
  `persons` tinyint(1) DEFAULT NULL,
  `buys` tinyint(1) DEFAULT NULL,
  `sells` tinyint(1) DEFAULT NULL,
  `accounting_total` tinyint(1) DEFAULT NULL,
  `notif` tinyint(1) DEFAULT NULL,
  `sell_chart` tinyint(1) DEFAULT NULL,
  `top_commodities_chart` tinyint(1) DEFAULT NULL,
  `costs` tinyint(1) DEFAULT NULL,
  `top_cost_centers` tinyint(1) DEFAULT NULL,
  `incomes` tinyint(1) DEFAULT NULL,
  `top_incomes_chart` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_history`
--

CREATE TABLE `email_history` (
  `id` int NOT NULL,
  `submitter_id` int DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hesabdari_doc`
--

CREATE TABLE `hesabdari_doc` (
  `id` int NOT NULL,
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
  `invoice_label_id` int DEFAULT NULL,
  `project_id` int DEFAULT NULL,
  `salesman_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hesabdari_doc_hesabdari_doc`
--

CREATE TABLE `hesabdari_doc_hesabdari_doc` (
  `hesabdari_doc_source` int NOT NULL,
  `hesabdari_doc_target` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hesabdari_row`
--

CREATE TABLE `hesabdari_row` (
  `id` int NOT NULL,
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
  `cheque_id` int DEFAULT NULL,
  `discount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tax` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hesabdari_table`
--

CREATE TABLE `hesabdari_table` (
  `id` int NOT NULL,
  `upper_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `entity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bid_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hesabdari_table`
--

INSERT INTO `hesabdari_table` (`id`, `upper_id`, `name`, `type`, `code`, `entity`, `bid_id`) VALUES
(1, NULL, 'جدول جساب', 'calc', '1', NULL, NULL),
(2, 23, 'دارایی‌های جاری', 'calc', '2', NULL, NULL),
(3, 2, 'حساب‌های دریافتی', 'person', '3', NULL, NULL),
(4, 2, 'موجودی نقد و بانک', 'calc', '4', NULL, NULL),
(5, 4, 'حساب‌های بانکی', 'bank', '5', NULL, NULL),
(6, 24, 'بدهی‌های جاری', 'calc', '6', NULL, NULL),
(7, 6, ' حساب ها و اسناد پرداختنی ', 'calc', '7', NULL, NULL),
(8, 7, 'اسناد پرداختنی ', 'person', '8', NULL, NULL),
(9, 7, 'حساب‌های پرداختنی ', 'bank', '9', NULL, NULL),
(10, 23, 'دارایی های غیر جاری ', 'calc', '10', NULL, NULL),
(11, 10, 'دارایی های ثابت ', 'calc', '11', NULL, NULL),
(13, 11, ' زمین ', 'calc', '12', NULL, NULL),
(15, 11, 'ساختمان', 'calc', '13', NULL, NULL),
(16, 11, ' وسائل نقلیه ', 'calc', '14', NULL, NULL),
(17, 11, ' اثاثیه اداری ', 'calc', '15', NULL, NULL),
(18, 10, ' استهلاک انباشته ', 'calc', '16', NULL, NULL),
(20, 18, ' استهلاک انباشته ساختمان ', 'calc', '17', NULL, NULL),
(21, 18, ' استهلاک انباشته وسائل نقلیه ', 'calc', '18', NULL, NULL),
(22, 18, 'استهلاک انباشته اثاثیه اداری ', 'calc', '19', NULL, NULL),
(23, 1, 'دارایی ها', 'calc', '20', NULL, NULL),
(24, 1, ' بدهی ها ', 'calc', '21', NULL, NULL),
(25, 6, ' سایر حساب های پرداختنی ', 'calc', '22', NULL, NULL),
(26, 25, ' ذخیره مالیات بر درآمد پرداختنی ', 'calc', '23', NULL, NULL),
(27, 25, 'مالیات بر درآمد پرداختنی ', 'calc', '24', NULL, NULL),
(28, 25, ' مالیات حقوق و دستمزد پرداختنی ', 'calc', '25', NULL, NULL),
(29, 25, ' حق بیمه پرداختنی ', 'calc', '26', NULL, NULL),
(31, 25, ' حقوق و دستمزد پرداختنی ', 'calc', '27', NULL, NULL),
(32, 25, 'عیدی و پاداش پرداختنی ', 'calc', '28', NULL, NULL),
(33, 25, ' سایر هزینه های پرداختنی ', 'calc', '29', NULL, NULL),
(34, 6, 'پیش دریافت ها ', 'calc', '30', NULL, NULL),
(35, 34, ' پیش دریافت فروش ', 'calc', '31', NULL, NULL),
(36, 34, ' سایر پیش دریافت ها ', 'calc', '32', NULL, NULL),
(37, 6, 'مالیات بر ارزش افزوده فروش ', 'calc', '33', NULL, NULL),
(38, 24, 'بدهیهای غیر جاری ', 'calc', '34', NULL, NULL),
(39, 38, 'حساب ها و اسناد پرداختنی بلندمدت ', 'calc', '35', NULL, NULL),
(40, 39, ' حساب های پرداختنی بلندمدت ', 'calc', '36', NULL, NULL),
(41, 39, 'اسناد پرداختنی بلندمدت ', 'calc', '37', NULL, NULL),
(44, 38, 'ذخیره مزایای پایان خدمت کارکنان ', 'calc', '38', NULL, NULL),
(45, 38, 'وام پرداختنی ', 'calc', '39', NULL, NULL),
(46, 1, 'حقوق صاحبان سهام ', 'calc', '40', NULL, NULL),
(47, 46, 'سرمایه ', 'calc', '41', NULL, NULL),
(48, 47, ' سرمایه اولیه ', 'calc', '42', NULL, NULL),
(49, 47, 'افزایش یا کاهش سرمایه ', 'calc', '43', NULL, NULL),
(50, 47, ' اندوخته قانونی ', 'calc', '44', NULL, NULL),
(51, 47, 'برداشت ها ', 'calc', '45', NULL, NULL),
(52, 47, 'سهم سود و زیان ', 'calc', '46', NULL, NULL),
(53, 47, 'سود یا زیان انباشته (سنواتی) ', 'calc', '47', NULL, NULL),
(54, 1, 'بهای تمام شده کالای فروخته شده ', 'calc', '48', NULL, NULL),
(55, 54, 'بهای تمام شده کالای فروخته شده ', 'calc', '49', NULL, NULL),
(56, 54, 'برگشت از خرید ', 'calc', '50', NULL, NULL),
(57, 54, ' تخفیفات نقدی خرید ', 'calc', '51', NULL, NULL),
(58, 1, ' فروش ', 'calc', '52', NULL, NULL),
(59, 58, ' فروش کالا  ', 'calc', '53', NULL, NULL),
(60, 58, 'برگشت از فروش ', 'calc', '54', NULL, NULL),
(61, 58, 'تخفیفات نقدی فروش ', 'calc', '55', NULL, NULL),
(64, 1, ' درآمد ', 'calc', '56', NULL, NULL),
(66, 64, ' درآمد های عملیاتی ', 'calc', '57', NULL, NULL),
(67, 66, 'درآمد حاصل از فروش خدمات ', 'calc', '58', NULL, NULL),
(68, 66, 'برگشت از خرید خدمات ', 'calc', '59', NULL, NULL),
(69, 66, 'درآمد اضافه کالا ', 'calc', '60', NULL, NULL),
(70, 66, 'درآمد حمل کالا ', 'calc', '61', NULL, NULL),
(72, 64, 'درآمد های غیر عملیاتی ', 'calc', '62', NULL, NULL),
(73, 72, ' درآمد حاصل از سرمایه گذاری ', 'calc', '63', NULL, NULL),
(74, 72, 'درآمد سود سپرده ها ', 'calc', '64', NULL, NULL),
(75, 72, ' سایر درآمد ها ', 'calc', '65', NULL, NULL),
(76, 72, 'درآمد تسعیر ارز ', 'calc', '66', NULL, NULL),
(77, 1, 'هزینه ها ', 'calc', '67', NULL, NULL),
(78, 77, 'هزینه های پرسنلی ', 'calc', '68', NULL, NULL),
(79, 78, ' هزینه حقوق و دستمزد ', 'calc', '69', NULL, NULL),
(80, 79, ' حقوق پایه ', 'calc', '70', NULL, NULL),
(81, 79, ' اضافه کار ', 'calc', '71', NULL, NULL),
(82, 79, ' حق شیفت و شب کاری ', 'calc', '72', NULL, NULL),
(83, 79, ' حق نوبت کاری ', 'calc', '73', NULL, NULL),
(84, 79, ' حق ماموریت ', 'calc', '74', NULL, NULL),
(85, 79, ' فوق العاده مسکن و خاروبار ', 'calc', '75', NULL, NULL),
(86, 79, ' حق اولاد ', 'calc', '76', NULL, NULL),
(87, 79, ' عیدی و پاداش ', 'calc', '77', NULL, NULL),
(88, 79, ' بازخرید سنوات خدمت کارکنان ', 'calc', '78', NULL, NULL),
(89, 79, ' بازخرید مرخصی ', 'calc', '79', NULL, NULL),
(90, 79, ' بیمه سهم کارفرما ', 'calc', '80', NULL, NULL),
(91, 79, ' بیمه بیکاری ', 'calc', '81', NULL, NULL),
(92, 79, ' حقوق مزایای متفرقه ', 'calc', '82', NULL, NULL),
(93, 78, 'سایر هزینه های کارکنان ', 'calc', '83', NULL, NULL),
(94, 93, ' سفر و ماموریت ', 'calc', '84', NULL, NULL),
(95, 93, ' ایاب و ذهاب ', 'calc', '85', NULL, NULL),
(96, 93, ' سایر هزینه های کارکنان ', 'calc', '86', NULL, NULL),
(97, 77, ' هزینه های عملیاتی ', 'calc', '87', NULL, NULL),
(98, 97, ' خرید خدمات ', 'calc', '88', NULL, NULL),
(99, 97, ' برگشت از فروش خدمات ', 'calc', '89', NULL, NULL),
(100, 97, 'هزینه حمل کالا ', 'calc', '90', NULL, NULL),
(101, 97, ' تعمیر و نگهداری اموال و اثاثیه ', 'calc', '91', NULL, NULL),
(102, 97, ' هزینه اجاره محل ', 'calc', '92', NULL, NULL),
(103, 97, ' هزینه های عمومی ', 'calc', '93', NULL, NULL),
(104, 97, ' هزینه ملزومات مصرفی ', 'calc', '94', NULL, NULL),
(105, 97, ' هزینه کسری و ضایعات کالا', 'calc', '95', NULL, NULL),
(106, 97, ' بیمه دارایی های ثابت ', 'calc', '96', NULL, NULL),
(107, 77, 'هزینه های استهلاک ', 'calc', '97', NULL, NULL),
(108, 107, ' هزینه استهلاک ساختمان ', 'calc', '98', NULL, NULL),
(109, 107, ' هزینه استهلاک وسائل نقلیه ', 'calc', '99', NULL, NULL),
(110, 107, ' هزینه استهلاک اثاثیه ', 'calc', '100', NULL, NULL),
(114, 77, ' هزینه های بازاریابی و توزیع و فروش ', 'calc', '101', NULL, NULL),
(115, 114, 'هزینه آگهی و تبلیغات ', 'calc', '102', NULL, NULL),
(116, 114, ' هزینه بازاریابی و پورسانت ', 'calc', '103', NULL, NULL),
(117, 114, ' سایر هزینه های توزیع و فروش ', 'calc', '104', NULL, NULL),
(118, 77, 'هزینه های غیرعملیاتی ', 'calc', '105', NULL, NULL),
(119, 118, 'هزینه های بانکی ', 'calc', '106', NULL, NULL),
(120, 119, ' سود و کارمزد وامها ', 'calc', '107', NULL, NULL),
(121, 119, 'کارمزد خدمات بانکی ', 'calc', '108', NULL, NULL),
(122, 119, ' جرائم دیرکرد بانکی ', 'calc', '109', NULL, NULL),
(123, 118, 'هزینه تسعیر ارز ', 'calc', '110', NULL, NULL),
(124, 118, ' هزینه مطالبات سوخت شده ', 'calc', '111', NULL, NULL),
(125, 1, 'سایر حساب ها ', 'calc', '112', NULL, NULL),
(126, 125, 'حساب های انتظامی ', 'calc', '113', NULL, NULL),
(127, 126, ' حساب های انتظامی ', 'calc', '114', NULL, NULL),
(128, 126, ' طرف حساب های انتظامی ', 'calc', '115', NULL, NULL),
(129, 125, 'حساب های کنترلی ', 'calc', '116', NULL, NULL),
(130, 129, ' کنترل کسری و اضافه کالا ', 'calc', '117', NULL, NULL),
(132, 125, 'حساب خلاصه سود و زیان ', 'calc', '118', NULL, NULL),
(133, 132, 'خلاصه سود و زیان ', 'calc', '119', NULL, NULL),
(137, 2, 'موجودی کالا ', 'calc', '120', NULL, NULL),
(138, 4, 'صندوق', 'cashdesk', '121', NULL, NULL),
(139, 4, 'تنخواه گردان', 'salary', '122', NULL, NULL),
(140, 7, 'تنخواه گردان', 'salary', '124', NULL, NULL),
(141, 7, 'صندوق', 'cashdesk', '123', NULL, NULL),
(142, 10, 'چک‌های دریافتی', 'cheque', '125', 'Cheque', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hook`
--

CREATE TABLE `hook` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_type`
--

CREATE TABLE `invoice_type` (
  `id` int NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `checked` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_type`
--

INSERT INTO `invoice_type` (`id`, `label`, `code`, `checked`, `type`) VALUES
(1, 'تسویه شده', 'payed', NULL, 'sell'),
(2, 'مرجوع شده', 'returned', NULL, 'sell'),
(3, 'تایید شده', 'accepted', NULL, 'sell'),
(4, 'تسویه شده', 'payed', NULL, 'buy'),
(5, 'مرجوع شده', 'returned', NULL, 'buy'),
(6, 'تایید شده', 'accepted', NULL, 'buy');

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `bid_id` int DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `part` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ipaddress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `doc_id` int DEFAULT NULL,
  `repservice_order_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `queue_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `money`
--

CREATE TABLE `money` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `symbol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `short_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `money`
--

INSERT INTO `money` (`id`, `name`, `label`, `symbol`, `short_name`) VALUES
(1, 'IRR', 'ریال ایران', 'ریال', 'ریال'),
(2, 'USD', 'دلار آمریکا', '$', 'دلار'),
(3, 'AFN', 'افغانی افغانستان', '؋', 'افغانی'),
(4, 'IQD', 'دینار عراق', 'ع.د', 'دینار');

-- --------------------------------------------------------

--
-- Table structure for table `most_des`
--

CREATE TABLE `most_des` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `note`
--

CREATE TABLE `note` (
  `id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `bid_id` int NOT NULL,
  `doc_id` int DEFAULT NULL,
  `des` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `bid_id` int DEFAULT NULL,
  `url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `viewed` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pairDoc`
--

CREATE TABLE `pairDoc` (
  `hesabdari_doc_source` int NOT NULL,
  `hesabdari_doc_target` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pay_info_temp`
--

CREATE TABLE `pay_info_temp` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `doc_id` int DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gate_pay` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_pan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

CREATE TABLE `permission` (
  `id` int NOT NULL,
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
  `cheque` tinyint(1) DEFAULT NULL,
  `plug_accpro_rfbuy` tinyint(1) DEFAULT NULL,
  `plug_accpro_rfsell` tinyint(1) DEFAULT NULL,
  `plug_accpro_accounting` tinyint(1) DEFAULT NULL,
  `plug_accpro_close_year` tinyint(1) DEFAULT NULL,
  `plug_repservice` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `person`
--

CREATE TABLE `person` (
  `id` int NOT NULL,
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
  `mobile2` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `prelabel_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `person_card`
--

CREATE TABLE `person_card` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `person_id` int NOT NULL,
  `bank` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `card_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shaba_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `person_person_type`
--

CREATE TABLE `person_person_type` (
  `person_id` int NOT NULL,
  `person_type_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `person_prelabel`
--

CREATE TABLE `person_prelabel` (
  `id` int NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `person_prelabel`
--

INSERT INTO `person_prelabel` (`id`, `label`, `code`) VALUES
(1, 'آقای', 'mr'),
(2, 'خانم', 'mrs'),
(3, 'شرکت', 'co'),
(4, 'مهندس', 'eng'),
(5, 'دکتر', 'doc');

-- --------------------------------------------------------

--
-- Table structure for table `person_type`
--

CREATE TABLE `person_type` (
  `id` int NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `person_type`
--

INSERT INTO `person_type` (`id`, `label`, `code`) VALUES
(1, 'مشتری', 'customer'),
(2, 'بازاریاب', 'marketer'),
(3, 'کارمند', 'emplyee'),
(4, 'تامین‌کننده', 'supplier'),
(5, 'همکار', 'colleague'),
(6, 'فروشنده', 'salesman');

-- --------------------------------------------------------

--
-- Table structure for table `plugin`
--

CREATE TABLE `plugin` (
  `id` int NOT NULL,
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
  `card_pan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plugin_prodect`
--

CREATE TABLE `plugin_prodect` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `timelabel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `default_on` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plugin_prodect`
--

INSERT INTO `plugin_prodect` (`id`, `name`, `code`, `timestamp`, `timelabel`, `price`, `icon`, `default_on`) VALUES
(4, 'بسته حسابداری پیشرفته', 'accpro', '32104000', 'یک سال (نسخه توسعه دهندگان)', '155000', 'accpro.png', NULL),
(6, 'افزونه مدیریت تعمیرگاه(تعمیرکاران)', 'repservice', '32104000', 'یک سال', '200000', 'repservice.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `plug_noghre_order`
--

CREATE TABLE `plug_noghre_order` (
  `id` int NOT NULL,
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
  `noghre_fee` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plug_repservice_order`
--

CREATE TABLE `plug_repservice_order` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `commodity_id` int NOT NULL,
  `person_id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `state_id` int NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pelak` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shortlink` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `motaleghat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_out` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plug_repservice_order_state`
--

CREATE TABLE `plug_repservice_order_state` (
  `id` int NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plug_repservice_order_state`
--

INSERT INTO `plug_repservice_order_state` (`id`, `label`, `code`) VALUES
(1, 'تحویل توسط تعمیرگاه', 'get'),
(2, 'تعمیر شده', 'repaired'),
(3, 'غیرقابل تعمیر', 'unrepired'),
(4, 'تحویل شده به مشتری', 'getback'),
(5, 'آغاز فرایند ساخت', 'creating'),
(6, 'پایان فرآیند ساخت', 'created');

-- --------------------------------------------------------

--
-- Table structure for table `pre_invoice_doc`
--

CREATE TABLE `pre_invoice_doc` (
  `id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `person_id` int NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bid_id` int NOT NULL,
  `money_id` int NOT NULL,
  `year_id` int NOT NULL,
  `invoice_label_id` int DEFAULT NULL,
  `salesman_id` int DEFAULT NULL,
  `date` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mdate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plugin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_data` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shortlink` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pre_invoice_item`
--

CREATE TABLE `pre_invoice_item` (
  `id` int NOT NULL,
  `commodity_id` int NOT NULL,
  `person_id` int DEFAULT NULL,
  `bank_id` int DEFAULT NULL,
  `cashdesk_id` int DEFAULT NULL,
  `salary_id` int DEFAULT NULL,
  `bid_id` int NOT NULL,
  `year_id` int NOT NULL,
  `ref_id_id` int NOT NULL,
  `commodity_count` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bs` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bd` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `price_list`
--

CREATE TABLE `price_list` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `price_list_detail`
--

CREATE TABLE `price_list_detail` (
  `id` int NOT NULL,
  `list_id` int NOT NULL,
  `commodity_id` int NOT NULL,
  `money_id` int NOT NULL,
  `price_buy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_sell` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `printer`
--

CREATE TABLE `printer` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `printer_queue`
--

CREATE TABLE `printer_queue` (
  `id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `bid_id` int DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `view` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `posprint` tinyint(1) DEFAULT NULL,
  `paper_size` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `footer` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `print_item`
--

CREATE TABLE `print_item` (
  `id` int NOT NULL,
  `printer_id` int NOT NULL,
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `printed` tinyint(1) DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `print_options`
--

CREATE TABLE `print_options` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `sell_bid_info` tinyint(1) DEFAULT NULL,
  `sell_note` tinyint(1) DEFAULT NULL,
  `sell_tax_info` tinyint(1) DEFAULT NULL,
  `sell_discount_info` tinyint(1) DEFAULT NULL,
  `sell_pays` tinyint(1) DEFAULT NULL,
  `sell_note_string` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `buy_bid_info` tinyint(1) DEFAULT NULL,
  `buy_tax_info` tinyint(1) DEFAULT NULL,
  `buy_discount_info` tinyint(1) DEFAULT NULL,
  `buy_note` tinyint(1) DEFAULT NULL,
  `buy_note_string` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `buy_pays` tinyint(1) DEFAULT NULL,
  `rfbuy_bid_info` tinyint(1) DEFAULT NULL,
  `rfbuy_tax_info` tinyint(1) DEFAULT NULL,
  `rfbuy_discount_info` tinyint(1) DEFAULT NULL,
  `rfbuy_note` tinyint(1) DEFAULT NULL,
  `rfbuy_note_string` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rf_buy_pays` tinyint(1) DEFAULT NULL,
  `rfsell_bid_info` tinyint(1) DEFAULT NULL,
  `rfsell_tax_info` tinyint(1) DEFAULT NULL,
  `rfsell_discount_info` tinyint(1) DEFAULT NULL,
  `rfsell_note` tinyint(1) DEFAULT NULL,
  `rfsell_note_string` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rfsell_pays` tinyint(1) DEFAULT NULL,
  `sell_paper` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buy_paper` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rfbuy_paper` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rfsell_paper` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repservice_note_string` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `paper` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repservice_paper` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fastsell_invoice` tinyint(1) DEFAULT NULL,
  `fastsell_pdf` tinyint(1) DEFAULT NULL,
  `fastsell_cashdesk_ticket` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `print_template`
--

CREATE TABLE `print_template` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `fast_sell_invoice` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cashdesk_ticket` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registry`
--

CREATE TABLE `registry` (
  `id` int NOT NULL,
  `root` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value_of_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `registry`
--

INSERT INTO `registry` (`id`, `root`, `name`, `value_of_key`) VALUES
(1, 'system_settings', 'can_register', '1'),
(2, 'sms', 'f2a', ''),
(3, 'sms', 'plan', ''),
(4, 'system', 'sponsers', ''),
(5, 'system_settings', 'gift_credit', ''),
(6, 'system_settings', 'can_free_accounting', ''),
(7, 'system_settings', 'canFreeAccounting', ''),
(8, 'system_settings', 'accounting_doc_price', '');

-- --------------------------------------------------------

--
-- Table structure for table `salary`
--

CREATE TABLE `salary` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `des` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `balance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `money_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `active_send_sms` tinyint(1) DEFAULT NULL,
  `app_site` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `site_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `discription` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `scripts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `footer_scripts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `footer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `active_send_sms`, `app_site`, `site_keywords`, `discription`, `scripts`, `footer_scripts`, `footer`) VALUES
(1, 1, 'https://app.hesabix.ir', 'hesabix,حسابیکس,حسابداری ابری رایگان,حسابداری آنلاین رایگان,حسابداری,نرم افزار حسابداری,نرم افزار حسابداری مغازه,نرم افزار حسابداری تحت وب رایگان', 'حسابیکس اولین نرم افزار حسابداری ابری رایگان و متن‌باز است که امور مالی شما را به صورت سریع و ساده انجام می‌دهد.حسابیکس کاملا متن باز است.', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shareholder`
--

CREATE TABLE `shareholder` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `person_id` int NOT NULL,
  `percent` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `smspays`
--

CREATE TABLE `smspays` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_pan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gate_pay` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `smssettings`
--

CREATE TABLE `smssettings` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `send_after_sell` tinyint(1) DEFAULT NULL,
  `send_after_sell_pay_online` tinyint(1) DEFAULT NULL,
  `send_after_buy` tinyint(1) DEFAULT NULL,
  `send_after_buy_to_user` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statment`
--

CREATE TABLE `statment` (
  `id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storeroom`
--

CREATE TABLE `storeroom` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `manager` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storeroom_item`
--

CREATE TABLE `storeroom_item` (
  `id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `commodity_id` int NOT NULL,
  `bid_id` int NOT NULL,
  `storeroom_id` int NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `count` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storeroom_ticket`
--

CREATE TABLE `storeroom_ticket` (
  `id` int NOT NULL,
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
  `sender_tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `can_share` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storeroom_transfer_type`
--

CREATE TABLE `storeroom_transfer_type` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `support` (
  `id` int NOT NULL,
  `submitter_id` int NOT NULL,
  `main` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_submit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bid_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_register` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mobile` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `verify_code_time` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invited_by_id` int DEFAULT NULL,
  `invate_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_token`
--

CREATE TABLE `user_token` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_active` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transaction`
--

CREATE TABLE `wallet_transaction` (
  `id` int NOT NULL,
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
  `transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `year`
--

CREATE TABLE `year` (
  `id` int NOT NULL,
  `bid_id` int NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `head` tinyint(1) DEFAULT NULL,
  `start` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `end` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `now` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounting_package_order`
--
ALTER TABLE `accounting_package_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_CAA1774D4D9866B8` (`bid_id`),
  ADD KEY `IDX_CAA1774D919E5513` (`submitter_id`);

--
-- Indexes for table `apitoken`
--
ALTER TABLE `apitoken`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_23E5A7D34D9866B8` (`bid_id`),
  ADD KEY `IDX_23E5A7D3919E5513` (`submitter_id`);

--
-- Indexes for table `archive_file`
--
ALTER TABLE `archive_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BCBAE08B4D9866B8` (`bid_id`),
  ADD KEY `IDX_BCBAE08B919E5513` (`submitter_id`);

--
-- Indexes for table `archive_orders`
--
ALTER TABLE `archive_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_182AE9FB4D9866B8` (`bid_id`),
  ADD KEY `IDX_182AE9FB919E5513` (`submitter_id`);

--
-- Indexes for table `bank_account`
--
ALTER TABLE `bank_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_53A23E0A4D9866B8` (`bid_id`),
  ADD KEY `IDX_53A23E0ABF29332C` (`money_id`);

--
-- Indexes for table `business`
--
ALTER TABLE `business`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8D36E387E3C61F9` (`owner_id`),
  ADD KEY `IDX_8D36E38BF29332C` (`money_id`),
  ADD KEY `IDX_8D36E38574F80DE` (`wallet_match_bank_id`);

--
-- Indexes for table `business_money`
--
ALTER TABLE `business_money`
  ADD PRIMARY KEY (`business_id`,`money_id`),
  ADD KEY `IDX_C93EF45BA89DB457` (`business_id`),
  ADD KEY `IDX_C93EF45BBF29332C` (`money_id`);

--
-- Indexes for table `cashdesk`
--
ALTER TABLE `cashdesk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_165987F94D9866B8` (`bid_id`),
  ADD KEY `IDX_165987F9BF29332C` (`money_id`);

--
-- Indexes for table `change_report`
--
ALTER TABLE `change_report`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cheque`
--
ALTER TABLE `cheque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A0BBFDE94D9866B8` (`bid_id`),
  ADD KEY `IDX_A0BBFDE9919E5513` (`submitter_id`),
  ADD KEY `IDX_A0BBFDE911C8FB41` (`bank_id`),
  ADD KEY `IDX_A0BBFDE9217BBB47` (`person_id`),
  ADD KEY `IDX_A0BBFDE921B741A9` (`ref_id`);

--
-- Indexes for table `commodity`
--
ALTER TABLE `commodity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5E8D2F74F8BD700D` (`unit_id`),
  ADD KEY `IDX_5E8D2F744D9866B8` (`bid_id`),
  ADD KEY `IDX_5E8D2F74E6ADA943` (`cat_id`);

--
-- Indexes for table `commodity_cat`
--
ALTER TABLE `commodity_cat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_687F6B14D9866B8` (`bid_id`);

--
-- Indexes for table `commodity_drop`
--
ALTER TABLE `commodity_drop`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_14E674574D9866B8` (`bid_id`);

--
-- Indexes for table `commodity_drop_link`
--
ALTER TABLE `commodity_drop_link`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8626B6BDC15B0809` (`commoditydrop_id`),
  ADD KEY `IDX_8626B6BDB4ACC212` (`commodity_id`);

--
-- Indexes for table `commodity_unit`
--
ALTER TABLE `commodity_unit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dashboard_settings`
--
ALTER TABLE `dashboard_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A02B1862919E5513` (`submitter_id`),
  ADD KEY `IDX_A02B18624D9866B8` (`bid_id`);

--
-- Indexes for table `email_history`
--
ALTER TABLE `email_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_9A7A1884919E5513` (`submitter_id`);

--
-- Indexes for table `hesabdari_doc`
--
ALTER TABLE `hesabdari_doc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_81C3CD534D9866B8` (`bid_id`),
  ADD KEY `IDX_81C3CD53919E5513` (`submitter_id`),
  ADD KEY `IDX_81C3CD5340C1FEA7` (`year_id`),
  ADD KEY `IDX_81C3CD53BF29332C` (`money_id`),
  ADD KEY `IDX_81C3CD53924C1837` (`wallet_transaction_id`),
  ADD KEY `IDX_81C3CD532C917561` (`invoice_label_id`),
  ADD KEY `IDX_81C3CD53166D1F9C` (`project_id`),
  ADD KEY `IDX_81C3CD539F7F22E2` (`salesman_id`);

--
-- Indexes for table `hesabdari_doc_hesabdari_doc`
--
ALTER TABLE `hesabdari_doc_hesabdari_doc`
  ADD PRIMARY KEY (`hesabdari_doc_source`,`hesabdari_doc_target`),
  ADD KEY `IDX_BE675746E2A225E5` (`hesabdari_doc_source`),
  ADD KEY `IDX_BE675746FB47756A` (`hesabdari_doc_target`);

--
-- Indexes for table `hesabdari_row`
--
ALTER TABLE `hesabdari_row`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_83B2C6EC895648BC` (`doc_id`),
  ADD KEY `IDX_83B2C6EC21B741A9` (`ref_id`),
  ADD KEY `IDX_83B2C6EC217BBB47` (`person_id`),
  ADD KEY `IDX_83B2C6EC11C8FB41` (`bank_id`),
  ADD KEY `IDX_83B2C6EC4D9866B8` (`bid_id`),
  ADD KEY `IDX_83B2C6EC40C1FEA7` (`year_id`),
  ADD KEY `IDX_83B2C6ECB4ACC212` (`commodity_id`),
  ADD KEY `IDX_83B2C6ECB0FDF16E` (`salary_id`),
  ADD KEY `IDX_83B2C6ECBA216AA5` (`cashdesk_id`),
  ADD KEY `IDX_83B2C6EC3DD3DB4B` (`cheque_id`);

--
-- Indexes for table `hesabdari_table`
--
ALTER TABLE `hesabdari_table`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_40F7185C77153098` (`code`),
  ADD KEY `IDX_40F7185C6F3C117F` (`upper_id`),
  ADD KEY `IDX_40F7185C4D9866B8` (`bid_id`);

--
-- Indexes for table `hook`
--
ALTER TABLE `hook`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A45843554D9866B8` (`bid_id`),
  ADD KEY `IDX_A4584355919E5513` (`submitter_id`);

--
-- Indexes for table `invoice_type`
--
ALTER TABLE `invoice_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8F3F68C5A76ED395` (`user_id`),
  ADD KEY `IDX_8F3F68C54D9866B8` (`bid_id`),
  ADD KEY `IDX_8F3F68C5895648BC` (`doc_id`),
  ADD KEY `IDX_8F3F68C529A5743C` (`repservice_order_id`);

--
-- Indexes for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Indexes for table `money`
--
ALTER TABLE `money`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `most_des`
--
ALTER TABLE `most_des`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_DFE0AC034D9866B8` (`bid_id`),
  ADD KEY `IDX_DFE0AC03919E5513` (`submitter_id`);

--
-- Indexes for table `note`
--
ALTER TABLE `note`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_CFBDFA14919E5513` (`submitter_id`),
  ADD KEY `IDX_CFBDFA144D9866B8` (`bid_id`),
  ADD KEY `IDX_CFBDFA14895648BC` (`doc_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BF5476CAA76ED395` (`user_id`),
  ADD KEY `IDX_BF5476CA4D9866B8` (`bid_id`);

--
-- Indexes for table `pairDoc`
--
ALTER TABLE `pairDoc`
  ADD PRIMARY KEY (`hesabdari_doc_source`,`hesabdari_doc_target`),
  ADD KEY `IDX_A6F5CC17E2A225E5` (`hesabdari_doc_source`),
  ADD KEY `IDX_A6F5CC17FB47756A` (`hesabdari_doc_target`);

--
-- Indexes for table `pay_info_temp`
--
ALTER TABLE `pay_info_temp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7F36E8384D9866B8` (`bid_id`),
  ADD KEY `IDX_7F36E838895648BC` (`doc_id`);

--
-- Indexes for table `permission`
--
ALTER TABLE `permission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E04992AAA76ED395` (`user_id`),
  ADD KEY `IDX_E04992AA4D9866B8` (`bid_id`);

--
-- Indexes for table `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_34DCD1764D9866B8` (`bid_id`),
  ADD KEY `IDX_34DCD17627768201` (`prelabel_id`);

--
-- Indexes for table `person_card`
--
ALTER TABLE `person_card`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_9CF21CF84D9866B8` (`bid_id`),
  ADD KEY `IDX_9CF21CF8217BBB47` (`person_id`);

--
-- Indexes for table `person_person_type`
--
ALTER TABLE `person_person_type`
  ADD PRIMARY KEY (`person_id`,`person_type_id`),
  ADD KEY `IDX_6BD38C8A217BBB47` (`person_id`),
  ADD KEY `IDX_6BD38C8AE7D23F1A` (`person_type_id`);

--
-- Indexes for table `person_prelabel`
--
ALTER TABLE `person_prelabel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `person_type`
--
ALTER TABLE `person_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plugin`
--
ALTER TABLE `plugin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E96E27944D9866B8` (`bid_id`),
  ADD KEY `IDX_E96E2794919E5513` (`submitter_id`);

--
-- Indexes for table `plugin_prodect`
--
ALTER TABLE `plugin_prodect`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plug_noghre_order`
--
ALTER TABLE `plug_noghre_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_EEEE085E895648BC` (`doc_id`),
  ADD KEY `IDX_EEEE085E4D9866B8` (`bid_id`),
  ADD KEY `IDX_EEEE085EB130EC9E` (`morsa_id`),
  ADD KEY `IDX_EEEE085E36B8627E` (`tarash_id`),
  ADD KEY `IDX_EEEE085EF8ABEE72` (`hakak_id`),
  ADD KEY `IDX_EEEE085E7BECA6BC` (`ghalam_id`),
  ADD KEY `IDX_EEEE085E9395C3F3` (`customer_id`);

--
-- Indexes for table `plug_repservice_order`
--
ALTER TABLE `plug_repservice_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C9F0B5F64D9866B8` (`bid_id`),
  ADD KEY `IDX_C9F0B5F6B4ACC212` (`commodity_id`),
  ADD KEY `IDX_C9F0B5F6217BBB47` (`person_id`),
  ADD KEY `IDX_C9F0B5F6919E5513` (`submitter_id`),
  ADD KEY `IDX_C9F0B5F65D83CC1` (`state_id`);

--
-- Indexes for table `plug_repservice_order_state`
--
ALTER TABLE `plug_repservice_order_state`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pre_invoice_doc`
--
ALTER TABLE `pre_invoice_doc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C28A21D8919E5513` (`submitter_id`),
  ADD KEY `IDX_C28A21D8217BBB47` (`person_id`),
  ADD KEY `IDX_C28A21D84D9866B8` (`bid_id`),
  ADD KEY `IDX_C28A21D8BF29332C` (`money_id`),
  ADD KEY `IDX_C28A21D840C1FEA7` (`year_id`),
  ADD KEY `IDX_C28A21D82C917561` (`invoice_label_id`),
  ADD KEY `IDX_C28A21D89F7F22E2` (`salesman_id`);

--
-- Indexes for table `pre_invoice_item`
--
ALTER TABLE `pre_invoice_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_DD881165B4ACC212` (`commodity_id`),
  ADD KEY `IDX_DD881165217BBB47` (`person_id`),
  ADD KEY `IDX_DD88116511C8FB41` (`bank_id`),
  ADD KEY `IDX_DD881165BA216AA5` (`cashdesk_id`),
  ADD KEY `IDX_DD881165B0FDF16E` (`salary_id`),
  ADD KEY `IDX_DD8811654D9866B8` (`bid_id`),
  ADD KEY `IDX_DD88116540C1FEA7` (`year_id`),
  ADD KEY `IDX_DD881165C8FFB95` (`ref_id_id`);

--
-- Indexes for table `price_list`
--
ALTER TABLE `price_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_399A0AA24D9866B8` (`bid_id`);

--
-- Indexes for table `price_list_detail`
--
ALTER TABLE `price_list_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B00FF1AB3DAE168B` (`list_id`),
  ADD KEY `IDX_B00FF1ABB4ACC212` (`commodity_id`),
  ADD KEY `IDX_B00FF1ABBF29332C` (`money_id`);

--
-- Indexes for table `printer`
--
ALTER TABLE `printer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8D4C79ED4D9866B8` (`bid_id`);

--
-- Indexes for table `printer_queue`
--
ALTER TABLE `printer_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_93F2764B919E5513` (`submitter_id`),
  ADD KEY `IDX_93F2764B4D9866B8` (`bid_id`);

--
-- Indexes for table `print_item`
--
ALTER TABLE `print_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_CB14675A46EC494A` (`printer_id`);

--
-- Indexes for table `print_options`
--
ALTER TABLE `print_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_F556EF594D9866B8` (`bid_id`);

--
-- Indexes for table `print_template`
--
ALTER TABLE `print_template`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_F62E83454D9866B8` (`bid_id`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_2FB3D0EE4D9866B8` (`bid_id`);

--
-- Indexes for table `registry`
--
ALTER TABLE `registry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salary`
--
ALTER TABLE `salary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_9413BB714D9866B8` (`bid_id`),
  ADD KEY `IDX_9413BB71BF29332C` (`money_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shareholder`
--
ALTER TABLE `shareholder`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D5FE68CC4D9866B8` (`bid_id`),
  ADD KEY `IDX_D5FE68CC217BBB47` (`person_id`);

--
-- Indexes for table `smspays`
--
ALTER TABLE `smspays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5F2F70E14D9866B8` (`bid_id`),
  ADD KEY `IDX_5F2F70E1919E5513` (`submitter_id`);

--
-- Indexes for table `smssettings`
--
ALTER TABLE `smssettings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_61178A624D9866B8` (`bid_id`);

--
-- Indexes for table `statment`
--
ALTER TABLE `statment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `storeroom`
--
ALTER TABLE `storeroom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3E2092A84D9866B8` (`bid_id`);

--
-- Indexes for table `storeroom_item`
--
ALTER TABLE `storeroom_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6CA8F5E0700047D2` (`ticket_id`),
  ADD KEY `IDX_6CA8F5E0B4ACC212` (`commodity_id`),
  ADD KEY `IDX_6CA8F5E04D9866B8` (`bid_id`),
  ADD KEY `IDX_6CA8F5E0C9330186` (`storeroom_id`);

--
-- Indexes for table `storeroom_ticket`
--
ALTER TABLE `storeroom_ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_9B4CC0F74D9866B8` (`bid_id`),
  ADD KEY `IDX_9B4CC0F7919E5513` (`submitter_id`),
  ADD KEY `IDX_9B4CC0F7217BBB47` (`person_id`),
  ADD KEY `IDX_9B4CC0F7895648BC` (`doc_id`),
  ADD KEY `IDX_9B4CC0F740C1FEA7` (`year_id`),
  ADD KEY `IDX_9B4CC0F7C9330186` (`storeroom_id`),
  ADD KEY `IDX_9B4CC0F77AF9FED8` (`transfer_type_id`);

--
-- Indexes for table `storeroom_transfer_type`
--
ALTER TABLE `storeroom_transfer_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support`
--
ALTER TABLE `support`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8004EBA5919E5513` (`submitter_id`),
  ADD KEY `IDX_8004EBA54D9866B8` (`bid_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  ADD KEY `IDX_8D93D649A7B4A7E3` (`invited_by_id`);

--
-- Indexes for table `user_token`
--
ALTER TABLE `user_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BDF55A63A76ED395` (`user_id`);

--
-- Indexes for table `wallet_transaction`
--
ALTER TABLE `wallet_transaction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7DAF9724D9866B8` (`bid_id`),
  ADD KEY `IDX_7DAF972919E5513` (`submitter_id`);

--
-- Indexes for table `year`
--
ALTER TABLE `year`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BB8273374D9866B8` (`bid_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounting_package_order`
--
ALTER TABLE `accounting_package_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `apitoken`
--
ALTER TABLE `apitoken`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `archive_file`
--
ALTER TABLE `archive_file`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `archive_orders`
--
ALTER TABLE `archive_orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_account`
--
ALTER TABLE `bank_account`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `business`
--
ALTER TABLE `business`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cashdesk`
--
ALTER TABLE `cashdesk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `change_report`
--
ALTER TABLE `change_report`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cheque`
--
ALTER TABLE `cheque`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commodity`
--
ALTER TABLE `commodity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `commodity_cat`
--
ALTER TABLE `commodity_cat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `commodity_drop`
--
ALTER TABLE `commodity_drop`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commodity_drop_link`
--
ALTER TABLE `commodity_drop_link`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commodity_unit`
--
ALTER TABLE `commodity_unit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `dashboard_settings`
--
ALTER TABLE `dashboard_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email_history`
--
ALTER TABLE `email_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hesabdari_doc`
--
ALTER TABLE `hesabdari_doc`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hesabdari_row`
--
ALTER TABLE `hesabdari_row`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hesabdari_table`
--
ALTER TABLE `hesabdari_table`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT for table `hook`
--
ALTER TABLE `hook`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `invoice_type`
--
ALTER TABLE `invoice_type`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `money`
--
ALTER TABLE `money`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `most_des`
--
ALTER TABLE `most_des`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `note`
--
ALTER TABLE `note`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pay_info_temp`
--
ALTER TABLE `pay_info_temp`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `permission`
--
ALTER TABLE `permission`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `person`
--
ALTER TABLE `person`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `person_card`
--
ALTER TABLE `person_card`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `person_prelabel`
--
ALTER TABLE `person_prelabel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `person_type`
--
ALTER TABLE `person_type`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `plugin`
--
ALTER TABLE `plugin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plugin_prodect`
--
ALTER TABLE `plugin_prodect`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `plug_noghre_order`
--
ALTER TABLE `plug_noghre_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `plug_repservice_order`
--
ALTER TABLE `plug_repservice_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plug_repservice_order_state`
--
ALTER TABLE `plug_repservice_order_state`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pre_invoice_doc`
--
ALTER TABLE `pre_invoice_doc`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pre_invoice_item`
--
ALTER TABLE `pre_invoice_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `price_list`
--
ALTER TABLE `price_list`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `price_list_detail`
--
ALTER TABLE `price_list_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `printer`
--
ALTER TABLE `printer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `printer_queue`
--
ALTER TABLE `printer_queue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `print_item`
--
ALTER TABLE `print_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `print_options`
--
ALTER TABLE `print_options`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `print_template`
--
ALTER TABLE `print_template`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registry`
--
ALTER TABLE `registry`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `salary`
--
ALTER TABLE `salary`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shareholder`
--
ALTER TABLE `shareholder`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `smspays`
--
ALTER TABLE `smspays`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `smssettings`
--
ALTER TABLE `smssettings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statment`
--
ALTER TABLE `statment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `storeroom`
--
ALTER TABLE `storeroom`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `storeroom_item`
--
ALTER TABLE `storeroom_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `storeroom_ticket`
--
ALTER TABLE `storeroom_ticket`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `storeroom_transfer_type`
--
ALTER TABLE `storeroom_transfer_type`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `support`
--
ALTER TABLE `support`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_token`
--
ALTER TABLE `user_token`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wallet_transaction`
--
ALTER TABLE `wallet_transaction`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `year`
--
ALTER TABLE `year`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounting_package_order`
--
ALTER TABLE `accounting_package_order`
  ADD CONSTRAINT `FK_CAA1774D4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_CAA1774D919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

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
  ADD CONSTRAINT `FK_53A23E0A4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_53A23E0ABF29332C` FOREIGN KEY (`money_id`) REFERENCES `money` (`id`);

--
-- Constraints for table `business`
--
ALTER TABLE `business`
  ADD CONSTRAINT `FK_8D36E38574F80DE` FOREIGN KEY (`wallet_match_bank_id`) REFERENCES `bank_account` (`id`),
  ADD CONSTRAINT `FK_8D36E387E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_8D36E38BF29332C` FOREIGN KEY (`money_id`) REFERENCES `money` (`id`);

--
-- Constraints for table `business_money`
--
ALTER TABLE `business_money`
  ADD CONSTRAINT `FK_C93EF45BA89DB457` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_C93EF45BBF29332C` FOREIGN KEY (`money_id`) REFERENCES `money` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cashdesk`
--
ALTER TABLE `cashdesk`
  ADD CONSTRAINT `FK_165987F94D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_165987F9BF29332C` FOREIGN KEY (`money_id`) REFERENCES `money` (`id`);

--
-- Constraints for table `cheque`
--
ALTER TABLE `cheque`
  ADD CONSTRAINT `FK_A0BBFDE911C8FB41` FOREIGN KEY (`bank_id`) REFERENCES `bank_account` (`id`),
  ADD CONSTRAINT `FK_A0BBFDE9217BBB47` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_A0BBFDE921B741A9` FOREIGN KEY (`ref_id`) REFERENCES `hesabdari_table` (`id`),
  ADD CONSTRAINT `FK_A0BBFDE94D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_A0BBFDE9919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

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
-- Constraints for table `dashboard_settings`
--
ALTER TABLE `dashboard_settings`
  ADD CONSTRAINT `FK_A02B18624D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_A02B1862919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `email_history`
--
ALTER TABLE `email_history`
  ADD CONSTRAINT `FK_9A7A1884919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `hesabdari_doc`
--
ALTER TABLE `hesabdari_doc`
  ADD CONSTRAINT `FK_81C3CD53166D1F9C` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `FK_81C3CD532C917561` FOREIGN KEY (`invoice_label_id`) REFERENCES `invoice_type` (`id`),
  ADD CONSTRAINT `FK_81C3CD5340C1FEA7` FOREIGN KEY (`year_id`) REFERENCES `year` (`id`),
  ADD CONSTRAINT `FK_81C3CD534D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_81C3CD53919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_81C3CD53924C1837` FOREIGN KEY (`wallet_transaction_id`) REFERENCES `wallet_transaction` (`id`),
  ADD CONSTRAINT `FK_81C3CD539F7F22E2` FOREIGN KEY (`salesman_id`) REFERENCES `person` (`id`),
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
  ADD CONSTRAINT `FK_83B2C6EC3DD3DB4B` FOREIGN KEY (`cheque_id`) REFERENCES `cheque` (`id`),
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
  ADD CONSTRAINT `FK_40F7185C4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_40F7185C6F3C117F` FOREIGN KEY (`upper_id`) REFERENCES `hesabdari_table` (`id`);

--
-- Constraints for table `hook`
--
ALTER TABLE `hook`
  ADD CONSTRAINT `FK_A45843554D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_A4584355919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `FK_8F3F68C529A5743C` FOREIGN KEY (`repservice_order_id`) REFERENCES `plug_repservice_order` (`id`),
  ADD CONSTRAINT `FK_8F3F68C54D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_8F3F68C5895648BC` FOREIGN KEY (`doc_id`) REFERENCES `hesabdari_doc` (`id`),
  ADD CONSTRAINT `FK_8F3F68C5A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `most_des`
--
ALTER TABLE `most_des`
  ADD CONSTRAINT `FK_DFE0AC034D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_DFE0AC03919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `note`
--
ALTER TABLE `note`
  ADD CONSTRAINT `FK_CFBDFA144D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_CFBDFA14895648BC` FOREIGN KEY (`doc_id`) REFERENCES `hesabdari_doc` (`id`),
  ADD CONSTRAINT `FK_CFBDFA14919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `FK_BF5476CA4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_BF5476CAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `pairDoc`
--
ALTER TABLE `pairDoc`
  ADD CONSTRAINT `FK_A6F5CC17E2A225E5` FOREIGN KEY (`hesabdari_doc_source`) REFERENCES `hesabdari_doc` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_A6F5CC17FB47756A` FOREIGN KEY (`hesabdari_doc_target`) REFERENCES `hesabdari_doc` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `FK_34DCD17627768201` FOREIGN KEY (`prelabel_id`) REFERENCES `person_prelabel` (`id`),
  ADD CONSTRAINT `FK_34DCD1764D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `person_card`
--
ALTER TABLE `person_card`
  ADD CONSTRAINT `FK_9CF21CF8217BBB47` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_9CF21CF84D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `person_person_type`
--
ALTER TABLE `person_person_type`
  ADD CONSTRAINT `FK_6BD38C8A217BBB47` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_6BD38C8AE7D23F1A` FOREIGN KEY (`person_type_id`) REFERENCES `person_type` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `plug_repservice_order`
--
ALTER TABLE `plug_repservice_order`
  ADD CONSTRAINT `FK_C9F0B5F6217BBB47` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_C9F0B5F64D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_C9F0B5F65D83CC1` FOREIGN KEY (`state_id`) REFERENCES `plug_repservice_order_state` (`id`),
  ADD CONSTRAINT `FK_C9F0B5F6919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_C9F0B5F6B4ACC212` FOREIGN KEY (`commodity_id`) REFERENCES `commodity` (`id`);

--
-- Constraints for table `pre_invoice_doc`
--
ALTER TABLE `pre_invoice_doc`
  ADD CONSTRAINT `FK_C28A21D8217BBB47` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_C28A21D82C917561` FOREIGN KEY (`invoice_label_id`) REFERENCES `invoice_type` (`id`),
  ADD CONSTRAINT `FK_C28A21D840C1FEA7` FOREIGN KEY (`year_id`) REFERENCES `year` (`id`),
  ADD CONSTRAINT `FK_C28A21D84D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_C28A21D8919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_C28A21D89F7F22E2` FOREIGN KEY (`salesman_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_C28A21D8BF29332C` FOREIGN KEY (`money_id`) REFERENCES `money` (`id`);

--
-- Constraints for table `pre_invoice_item`
--
ALTER TABLE `pre_invoice_item`
  ADD CONSTRAINT `FK_DD88116511C8FB41` FOREIGN KEY (`bank_id`) REFERENCES `bank_account` (`id`),
  ADD CONSTRAINT `FK_DD881165217BBB47` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`),
  ADD CONSTRAINT `FK_DD88116540C1FEA7` FOREIGN KEY (`year_id`) REFERENCES `year` (`id`),
  ADD CONSTRAINT `FK_DD8811654D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_DD881165B0FDF16E` FOREIGN KEY (`salary_id`) REFERENCES `salary` (`id`),
  ADD CONSTRAINT `FK_DD881165B4ACC212` FOREIGN KEY (`commodity_id`) REFERENCES `commodity` (`id`),
  ADD CONSTRAINT `FK_DD881165BA216AA5` FOREIGN KEY (`cashdesk_id`) REFERENCES `cashdesk` (`id`),
  ADD CONSTRAINT `FK_DD881165C8FFB95` FOREIGN KEY (`ref_id_id`) REFERENCES `hesabdari_table` (`id`);

--
-- Constraints for table `price_list`
--
ALTER TABLE `price_list`
  ADD CONSTRAINT `FK_399A0AA24D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `price_list_detail`
--
ALTER TABLE `price_list_detail`
  ADD CONSTRAINT `FK_B00FF1AB3DAE168B` FOREIGN KEY (`list_id`) REFERENCES `price_list` (`id`),
  ADD CONSTRAINT `FK_B00FF1ABB4ACC212` FOREIGN KEY (`commodity_id`) REFERENCES `commodity` (`id`),
  ADD CONSTRAINT `FK_B00FF1ABBF29332C` FOREIGN KEY (`money_id`) REFERENCES `money` (`id`);

--
-- Constraints for table `printer`
--
ALTER TABLE `printer`
  ADD CONSTRAINT `FK_8D4C79ED4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `printer_queue`
--
ALTER TABLE `printer_queue`
  ADD CONSTRAINT `FK_93F2764B4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_93F2764B919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `print_item`
--
ALTER TABLE `print_item`
  ADD CONSTRAINT `FK_CB14675A46EC494A` FOREIGN KEY (`printer_id`) REFERENCES `printer` (`id`);

--
-- Constraints for table `print_options`
--
ALTER TABLE `print_options`
  ADD CONSTRAINT `FK_F556EF594D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `print_template`
--
ALTER TABLE `print_template`
  ADD CONSTRAINT `FK_F62E83454D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `FK_2FB3D0EE4D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`);

--
-- Constraints for table `salary`
--
ALTER TABLE `salary`
  ADD CONSTRAINT `FK_9413BB714D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_9413BB71BF29332C` FOREIGN KEY (`money_id`) REFERENCES `money` (`id`);

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
  ADD CONSTRAINT `FK_8004EBA54D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `business` (`id`),
  ADD CONSTRAINT `FK_8004EBA5919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D649A7B4A7E3` FOREIGN KEY (`invited_by_id`) REFERENCES `user` (`id`);

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

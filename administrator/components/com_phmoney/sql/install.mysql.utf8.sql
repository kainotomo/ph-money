SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP TABLE IF EXISTS `#__phmoney_accounts`;
CREATE TABLE `#__phmoney_accounts` (
  `id` int(11) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `path` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `extension` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `params` text COLLATE utf8mb4_unicode_ci,
  `metadesc` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'The meta keywords for the page.',
  `metadata` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `version` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `portfolio_id` int(11) UNSIGNED NOT NULL COMMENT 'FK to the #__phmoney_portfolios',
  `account_type_id` int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'FK to the #__phmoney_account_types table.',
  `currency_id` int(11) UNSIGNED NOT NULL DEFAULT '139' COMMENT 'FK to the #__phmoney_currencys'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__phmoney_accounts` VALUES(1, 0, 0, 0, 0, '', '', 'ROOT', 'root', NULL, '', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, '', 1, 1, 1, 139);

DROP TABLE IF EXISTS `#__phmoney_account_types`;
CREATE TABLE `#__phmoney_account_types` (
  `id` int(11) UNSIGNED NOT NULL,
  `value` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__phmoney_account_types` VALUES(1, 'asset', 'COM_PHMONEY_ASSET');
INSERT INTO `#__phmoney_account_types` VALUES(2, 'share', 'COM_PHMONEY_SHARE');
INSERT INTO `#__phmoney_account_types` VALUES(3, 'liability', 'COM_PHMONEY_LIABILITY');
INSERT INTO `#__phmoney_account_types` VALUES(4, 'equity', 'COM_PHMONEY_EQUITY');
INSERT INTO `#__phmoney_account_types` VALUES(5, 'income', 'COM_PHMONEY_INCOME');
INSERT INTO `#__phmoney_account_types` VALUES(6, 'expense', 'COM_PHMONEY_EXPENSE');

DROP TABLE IF EXISTS `#__phmoney_currencys`;
CREATE TABLE `#__phmoney_currencys` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(13) DEFAULT NULL,
  `code` varchar(3) NOT NULL,
  `symbol` varchar(5) DEFAULT NULL,
  `denom` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__phmoney_currencys` VALUES(1, 'AED ( د.إ.‏ )', 'AED', 'د.إ.‏', 100);
INSERT INTO `#__phmoney_currencys` VALUES(2, 'NIO ( C$ )', 'NIO', 'C$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(3, 'NOK ( kr )', 'NOK', 'kr', 100);
INSERT INTO `#__phmoney_currencys` VALUES(4, 'NPR ( रु )', 'NPR', 'रु', 100);
INSERT INTO `#__phmoney_currencys` VALUES(5, 'NZD ( $ )', 'NZD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(6, 'OMR ( ر.ع.‏ )', 'OMR', 'ر.ع.‏', 1000);
INSERT INTO `#__phmoney_currencys` VALUES(7, 'PAB ( B/. )', 'PAB', 'B/.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(8, 'PEN ( S/ )', 'PEN', 'S/', 100);
INSERT INTO `#__phmoney_currencys` VALUES(9, 'PGK ( K )', 'PGK', 'K', 100);
INSERT INTO `#__phmoney_currencys` VALUES(10, 'PHP ( ₱ )', 'PHP', '₱', 100);
INSERT INTO `#__phmoney_currencys` VALUES(11, 'PKR ( Rs )', 'PKR', 'Rs', 100);
INSERT INTO `#__phmoney_currencys` VALUES(12, 'PLN ( zł )', 'PLN', 'zł', 100);
INSERT INTO `#__phmoney_currencys` VALUES(13, 'PYG ( ₲ )', 'PYG', '₲', 1);
INSERT INTO `#__phmoney_currencys` VALUES(14, 'QAR ( ر.ق.‏ )', 'QAR', 'ر.ق.‏', 100);
INSERT INTO `#__phmoney_currencys` VALUES(15, 'RON ( lei )', 'RON', 'lei', 100);
INSERT INTO `#__phmoney_currencys` VALUES(16, 'RSD ( дин. )', 'RSD', 'дин.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(17, 'NGN ( ₦ )', 'NGN', '₦', 100);
INSERT INTO `#__phmoney_currencys` VALUES(18, 'RUB ( ₽ )', 'RUB', '₽', 100);
INSERT INTO `#__phmoney_currencys` VALUES(19, 'NAD ( $ )', 'NAD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(20, 'MYR ( RM )', 'MYR', 'RM', 100);
INSERT INTO `#__phmoney_currencys` VALUES(21, 'LKR ( රු. )', 'LKR', 'රු.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(22, 'LRD ( $ )', 'LRD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(23, 'LYD ( د.ل.‏ )', 'LYD', 'د.ل.‏', 1000);
INSERT INTO `#__phmoney_currencys` VALUES(24, 'MAD ( د.م.‏ )', 'MAD', 'د.م.‏', 100);
INSERT INTO `#__phmoney_currencys` VALUES(25, 'MDL ( L )', 'MDL', 'L', 100);
INSERT INTO `#__phmoney_currencys` VALUES(26, 'MGA ( Ar )', 'MGA', 'Ar', 100);
INSERT INTO `#__phmoney_currencys` VALUES(27, 'MKD ( ден )', 'MKD', 'ден', 100);
INSERT INTO `#__phmoney_currencys` VALUES(28, 'MMK ( K )', 'MMK', 'K', 100);
INSERT INTO `#__phmoney_currencys` VALUES(29, 'MNT ( ₮ )', 'MNT', '₮', 100);
INSERT INTO `#__phmoney_currencys` VALUES(30, 'MOP ( MOP$ )', 'MOP', 'MOP$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(31, 'MRO ( أ.م.‏ )', 'MRO', 'أ.م.‏', 100);
INSERT INTO `#__phmoney_currencys` VALUES(32, 'MUR ( Rs )', 'MUR', 'Rs', 100);
INSERT INTO `#__phmoney_currencys` VALUES(33, 'MVR ( ރ. )', 'MVR', 'ރ.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(34, 'MWK ( MK )', 'MWK', 'MK', 100);
INSERT INTO `#__phmoney_currencys` VALUES(35, 'MXN ( $ )', 'MXN', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(36, 'MZN ( MTn )', 'MZN', 'MTn', 100);
INSERT INTO `#__phmoney_currencys` VALUES(37, 'RWF ( RF )', 'RWF', 'RF', 1);
INSERT INTO `#__phmoney_currencys` VALUES(38, 'SAR ( ر.س.‏ )', 'SAR', 'ر.س.‏', 100);
INSERT INTO `#__phmoney_currencys` VALUES(39, 'SBD ( $ )', 'SBD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(40, 'TZS ( TSh )', 'TZS', 'TSh', 100);
INSERT INTO `#__phmoney_currencys` VALUES(41, 'UAH ( ₴ )', 'UAH', '₴', 100);
INSERT INTO `#__phmoney_currencys` VALUES(42, 'UGX ( USh )', 'UGX', 'USh', 1);
INSERT INTO `#__phmoney_currencys` VALUES(43, 'USD ( $ )', 'USD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(44, 'UYU ( $ )', 'UYU', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(45, 'UZS ( сўм )', 'UZS', 'сўм', 100);
INSERT INTO `#__phmoney_currencys` VALUES(46, 'VEF ( Bs. )', 'VEF', 'Bs.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(47, 'VND ( ₫ )', 'VND', '₫', 1);
INSERT INTO `#__phmoney_currencys` VALUES(48, 'VUV ( VT )', 'VUV', 'VT', 1);
INSERT INTO `#__phmoney_currencys` VALUES(49, 'WST ( WS$ )', 'WST', 'WS$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(50, 'XAF ( FCFA )', 'XAF', 'FCFA', 1);
INSERT INTO `#__phmoney_currencys` VALUES(51, 'XCD ( $ )', 'XCD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(52, 'XOF ( CFA )', 'XOF', 'CFA', 1);
INSERT INTO `#__phmoney_currencys` VALUES(53, 'XPF ( FCFP )', 'XPF', 'FCFP', 1);
INSERT INTO `#__phmoney_currencys` VALUES(54, 'YER ( ر.ي.‏ )', 'YER', 'ر.ي.‏', 100);
INSERT INTO `#__phmoney_currencys` VALUES(55, 'TWD ( NT$ )', 'TWD', 'NT$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(56, 'TTD ( $ )', 'TTD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(57, 'TRY ( ₺ )', 'TRY', '₺', 100);
INSERT INTO `#__phmoney_currencys` VALUES(58, 'TOP ( T$ )', 'TOP', 'T$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(59, 'SCR ( SR )', 'SCR', 'SR', 100);
INSERT INTO `#__phmoney_currencys` VALUES(60, 'SDG ( ج.س. )', 'SDG', 'ج.س.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(61, 'SEK ( kr )', 'SEK', 'kr', 100);
INSERT INTO `#__phmoney_currencys` VALUES(62, 'SGD ( $ )', 'SGD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(63, 'SHP ( £ )', 'SHP', '£', 100);
INSERT INTO `#__phmoney_currencys` VALUES(64, 'SLL ( Le )', 'SLL', 'Le', 100);
INSERT INTO `#__phmoney_currencys` VALUES(65, 'SOS ( S )', 'SOS', 'S', 100);
INSERT INTO `#__phmoney_currencys` VALUES(66, 'LBP ( ل.ل.‏ )', 'LBP', 'ل.ل.‏', 100);
INSERT INTO `#__phmoney_currencys` VALUES(67, 'SRD ( $ )', 'SRD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(68, 'STD ( Db )', 'STD', 'Db', 100);
INSERT INTO `#__phmoney_currencys` VALUES(69, 'SYP ( ل.س.‏ )', 'SYP', 'ل.س.‏', 100);
INSERT INTO `#__phmoney_currencys` VALUES(70, 'SZL ( E )', 'SZL', 'E', 100);
INSERT INTO `#__phmoney_currencys` VALUES(71, 'THB ( ฿ )', 'THB', '฿', 100);
INSERT INTO `#__phmoney_currencys` VALUES(72, 'TJS ( смн )', 'TJS', 'смн', 100);
INSERT INTO `#__phmoney_currencys` VALUES(73, 'TMT ( m. )', 'TMT', 'm.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(74, 'TND ( د.ت.‏ )', 'TND', 'د.ت.‏', 1000);
INSERT INTO `#__phmoney_currencys` VALUES(75, 'SSP ( £ )', 'SSP', '£', 100);
INSERT INTO `#__phmoney_currencys` VALUES(76, 'ZAR ( R )', 'ZAR', 'R', 100);
INSERT INTO `#__phmoney_currencys` VALUES(77, 'LAK ( ₭ )', 'LAK', '₭', 100);
INSERT INTO `#__phmoney_currencys` VALUES(78, 'KYD ( $ )', 'KYD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(79, 'BSD ( $ )', 'BSD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(80, 'BTN ( Nu. )', 'BTN', 'Nu.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(81, 'BWP ( P )', 'BWP', 'P', 100);
INSERT INTO `#__phmoney_currencys` VALUES(82, 'BYN ( Br )', 'BYN', 'Br', 100);
INSERT INTO `#__phmoney_currencys` VALUES(83, 'BZD ( $ )', 'BZD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(84, 'CAD ( $ )', 'CAD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(85, 'CDF ( FC )', 'CDF', 'FC', 100);
INSERT INTO `#__phmoney_currencys` VALUES(86, 'CHF ( CHF )', 'CHF', 'CHF', 100);
INSERT INTO `#__phmoney_currencys` VALUES(87, 'CLP ( $ )', 'CLP', '$', 1);
INSERT INTO `#__phmoney_currencys` VALUES(88, 'CNY ( ¥ )', 'CNY', '¥', 100);
INSERT INTO `#__phmoney_currencys` VALUES(89, 'COP ( $ )', 'COP', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(90, 'CRC ( ₡ )', 'CRC', '₡', 100);
INSERT INTO `#__phmoney_currencys` VALUES(91, 'CUP ( $ )', 'CUP', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(92, 'CVE ( ​ )', 'CVE', '​', 100);
INSERT INTO `#__phmoney_currencys` VALUES(93, 'CZK ( Kč )', 'CZK', 'Kč', 100);
INSERT INTO `#__phmoney_currencys` VALUES(94, 'BRL ( R$ )', 'BRL', 'R$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(95, 'DJF ( Fdj )', 'DJF', 'Fdj', 1);
INSERT INTO `#__phmoney_currencys` VALUES(96, 'BOB ( Bs )', 'BOB', 'Bs', 100);
INSERT INTO `#__phmoney_currencys` VALUES(97, 'BMD ( $ )', 'BMD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(98, 'AFN ( ؋ )', 'AFN', '؋', 100);
INSERT INTO `#__phmoney_currencys` VALUES(99, 'ALL ( Lekë )', 'ALL', 'Lekë', 100);
INSERT INTO `#__phmoney_currencys` VALUES(100, 'AMD ( ֏ )', 'AMD', '֏', 100);
INSERT INTO `#__phmoney_currencys` VALUES(101, 'ANG ( NAf. )', 'ANG', 'NAf.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(102, 'AOA ( Kz )', 'AOA', 'Kz', 100);
INSERT INTO `#__phmoney_currencys` VALUES(103, 'ARS ( $ )', 'ARS', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(104, 'AUD ( $ )', 'AUD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(105, 'AWG ( Afl. )', 'AWG', 'Afl.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(106, 'AZN ( ₼ )', 'AZN', '₼', 100);
INSERT INTO `#__phmoney_currencys` VALUES(107, 'BAM ( КМ )', 'BAM', 'КМ', 100);
INSERT INTO `#__phmoney_currencys` VALUES(108, 'BBD ( $ )', 'BBD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(109, 'BDT ( ৳ )', 'BDT', '৳', 100);
INSERT INTO `#__phmoney_currencys` VALUES(110, 'BGN ( лв. )', 'BGN', 'лв.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(111, 'BHD ( د.ب.‏ )', 'BHD', 'د.ب.‏', 1000);
INSERT INTO `#__phmoney_currencys` VALUES(112, 'BIF ( FBu )', 'BIF', 'FBu', 1);
INSERT INTO `#__phmoney_currencys` VALUES(113, 'BND ( $ )', 'BND', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(114, 'DKK ( kr. )', 'DKK', 'kr.', 100);
INSERT INTO `#__phmoney_currencys` VALUES(115, 'DOP ( $ )', 'DOP', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(116, 'DZD ( د.ج.‏ )', 'DZD', 'د.ج.‏', 100);
INSERT INTO `#__phmoney_currencys` VALUES(117, 'ILS ( ₪ )', 'ILS', '₪', 100);
INSERT INTO `#__phmoney_currencys` VALUES(118, 'INR ( ₹ )', 'INR', '₹', 100);
INSERT INTO `#__phmoney_currencys` VALUES(119, 'IQD ( د.ع.‏ )', 'IQD', 'د.ع.‏', 1000);
INSERT INTO `#__phmoney_currencys` VALUES(120, 'IRR ( ريال )', 'IRR', 'ريال', 100);
INSERT INTO `#__phmoney_currencys` VALUES(121, 'ISK ( ISK )', 'ISK', 'ISK', 1);
INSERT INTO `#__phmoney_currencys` VALUES(122, 'JMD ( $ )', 'JMD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(123, 'JOD ( د.ا.‏ )', 'JOD', 'د.ا.‏', 1000);
INSERT INTO `#__phmoney_currencys` VALUES(124, 'JPY ( ¥ )', 'JPY', '¥', 1);
INSERT INTO `#__phmoney_currencys` VALUES(125, 'KES ( Ksh )', 'KES', 'Ksh', 100);
INSERT INTO `#__phmoney_currencys` VALUES(126, 'KGS ( сом )', 'KGS', 'сом', 100);
INSERT INTO `#__phmoney_currencys` VALUES(127, 'KHR ( ៛ )', 'KHR', '៛', 100);
INSERT INTO `#__phmoney_currencys` VALUES(128, 'KMF ( CF )', 'KMF', 'CF', 1);
INSERT INTO `#__phmoney_currencys` VALUES(129, 'KPW ( ₩ )', 'KPW', '₩', 100);
INSERT INTO `#__phmoney_currencys` VALUES(130, 'KRW ( ₩ )', 'KRW', '₩', 1);
INSERT INTO `#__phmoney_currencys` VALUES(131, 'KWD ( د.ك.‏ )', 'KWD', 'د.ك.‏', 1000);
INSERT INTO `#__phmoney_currencys` VALUES(132, 'IDR ( Rp )', 'IDR', 'Rp', 100);
INSERT INTO `#__phmoney_currencys` VALUES(133, 'HUF ( Ft )', 'HUF', 'Ft', 100);
INSERT INTO `#__phmoney_currencys` VALUES(134, 'HTG ( G )', 'HTG', 'G', 100);
INSERT INTO `#__phmoney_currencys` VALUES(135, 'HRK ( kn )', 'HRK', 'kn', 100);
INSERT INTO `#__phmoney_currencys` VALUES(136, 'EGP ( ج.م.‏ )', 'EGP', 'ج.م.‏', 100);
INSERT INTO `#__phmoney_currencys` VALUES(137, 'ERN ( Nfk )', 'ERN', 'Nfk', 100);
INSERT INTO `#__phmoney_currencys` VALUES(138, 'ETB ( Br )', 'ETB', 'Br', 100);
INSERT INTO `#__phmoney_currencys` VALUES(139, 'EUR ( € )', 'EUR', '€', 100);
INSERT INTO `#__phmoney_currencys` VALUES(140, 'FJD ( $ )', 'FJD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(141, 'FKP ( £ )', 'FKP', '£', 100);
INSERT INTO `#__phmoney_currencys` VALUES(142, 'GBP ( £ )', 'GBP', '£', 100);
INSERT INTO `#__phmoney_currencys` VALUES(143, 'KZT ( ₸ )', 'KZT', '₸', 100);
INSERT INTO `#__phmoney_currencys` VALUES(144, 'GEL ( ₾ )', 'GEL', '₾', 100);
INSERT INTO `#__phmoney_currencys` VALUES(145, 'GIP ( £ )', 'GIP', '£', 100);
INSERT INTO `#__phmoney_currencys` VALUES(146, 'GMD ( D )', 'GMD', 'D', 100);
INSERT INTO `#__phmoney_currencys` VALUES(147, 'GNF ( FG )', 'GNF', 'FG', 1);
INSERT INTO `#__phmoney_currencys` VALUES(148, 'GTQ ( Q )', 'GTQ', 'Q', 100);
INSERT INTO `#__phmoney_currencys` VALUES(149, 'GYD ( $ )', 'GYD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(150, 'HKD ( $ )', 'HKD', '$', 100);
INSERT INTO `#__phmoney_currencys` VALUES(151, 'HNL ( L )', 'HNL', 'L', 100);
INSERT INTO `#__phmoney_currencys` VALUES(152, 'GHS ( GH₵ )', 'GHS', 'GH₵', 100);
INSERT INTO `#__phmoney_currencys` VALUES(153, 'ZMW ( K )', 'ZMW', 'K', 100);

DROP TABLE IF EXISTS `#__phmoney_portfolios`;
CREATE TABLE `#__phmoney_portfolios` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `currency_id` int(11) UNSIGNED NOT NULL DEFAULT '139' COMMENT 'FK to the #__phmoney_currencys',
  `user_id` int(11) DEFAULT NULL COMMENT 'FK to the #__users',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `user_default` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Indicates if this portfolio is the default for the user',
  `params` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__phmoney_portfolios` VALUES(1, 'ROOT', 'root', NULL, 139, NULL, 0, 0, NULL);

DROP TABLE IF EXISTS `#__phmoney_prices`;
CREATE TABLE `#__phmoney_prices` (
  `id` int(11) UNSIGNED NOT NULL,
  `account_id` int(11) UNSIGNED NOT NULL COMMENT 'FK to the #__phmoney_accounts',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` double NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `#__phmoney_rates`;
CREATE TABLE `#__phmoney_rates` (
  `id` int(11) UNSIGNED NOT NULL,
  `portfolio_id` int(11) UNSIGNED NOT NULL COMMENT 'FK to the #__phmoney_portfolios',
  `currency_id` int(11) UNSIGNED NOT NULL COMMENT 'FK to the #__phmoney_currencys',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` double NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `#__phmoney_splits`;
CREATE TABLE `#__phmoney_splits` (
  `id` int(10) UNSIGNED NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__phmoney_accounts',
  `transaction_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__phmoney_transactions',
  `value` bigint(10) NOT NULL DEFAULT '0',
  `shares` decimal(10,5) NOT NULL DEFAULT '0.00000',
  `split_type_id` int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'FK to the #__phmoney_account_types table.',
  `reconcile_state` tinyint(1) NOT NULL DEFAULT '0',
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `attribs` TEXT NULL,
  `version` int(11) NOT NULL DEFAULT '1' 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__phmoney_split_types` (
  `id` int(11) UNSIGNED NOT NULL,
  `value` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__phmoney_split_types` VALUES
(1, 'nan', 'COM_PHMONEY_NAN_LINES'),
(2, 'buy', 'COM_PHMONEY_BUY'),
(3, 'sell', 'COM_PHMONEY_SELL'),
(4, 'dividend', 'COM_PHMONEY_DIVIDEND'),
(5, 'fee', 'COM_PHMONEY_FEE'),
(6, 'price', 'COM_PHMONEY_PRICE');

DROP TABLE IF EXISTS `#__phmoney_transactions`;
CREATE TABLE `#__phmoney_transactions` (
  `id` int(11) UNSIGNED NOT NULL,
  `portfolio_id` int(11) UNSIGNED NOT NULL COMMENT 'FK to the #__phmoney_portfolios',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `num` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `attribs` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `checked_out` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `version` int(11) NOT NULL DEFAULT '1' 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `#__phmoney_imports`;
CREATE TABLE `#__phmoney_imports` (
  `id` int(11) UNSIGNED NOT NULL,
  `percent` float DEFAULT NULL COMMENT 'Confidence percentage',
  `portfolio_id` INT(11) UNSIGNED NOT NULL,
  `account_id_source` int(11) UNSIGNED DEFAULT NULL COMMENT 'FK to accounts',
  `account_id_destination` int(11) UNSIGNED DEFAULT NULL COMMENT 'FK to accounts',
  `split_type_id_destination` INT(11) UNSIGNED NOT NULL DEFAULT '1',
  `post_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shares` decimal(10,5) NOT NULL DEFAULT '0.00000',
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `params` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__phmoney_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cat_idx` (`extension`,`published`,`access`),
  ADD KEY `idx_access` (`access`),
  ADD KEY `idx_checkout` (`checked_out`),
  ADD KEY `idx_path` (`path`(100)),
  ADD KEY `idx_left_right` (`lft`,`rgt`),
  ADD KEY `idx_alias` (`alias`(100)),
  ADD KEY `idx_language` (`language`),
  ADD KEY `currency_id` (`currency_id`),
  ADD KEY `account_type_id` (`account_type_id`),
  ADD KEY `portfolio_id` (`portfolio_id`);

ALTER TABLE `#__phmoney_account_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__phmoney_currencys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ISOCurrencySymbol` (`code`);

ALTER TABLE `#__phmoney_portfolios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_state` (`published`),
  ADD KEY `currency_id` (`currency_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_alias` (`alias`(191));

ALTER TABLE `#__phmoney_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `portfolio_id` (`account_id`);

ALTER TABLE `#__phmoney_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `portfolio_id` (`portfolio_id`),
  ADD KEY `currency_id` (`currency_id`) USING BTREE;

ALTER TABLE `#__phmoney_splits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_catid` (`account_id`),
  ADD KEY `idx_trxid` (`transaction_id`) USING BTREE,
  ADD KEY `idx_featured_catid` (`account_id`) USING BTREE,
  ADD KEY `idx_split_type_id` (`split_type_id`) USING BTREE;

ALTER TABLE `#__phmoney_split_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__phmoney_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_state` (`state`),
  ADD KEY `portfolio_id` (`portfolio_id`);

 ALTER TABLE `#__phmoney_imports`
  ADD PRIMARY KEY (`id`);

 ALTER TABLE `#__phmoney_imports`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__phmoney_accounts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__phmoney_account_types`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__phmoney_currencys`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__phmoney_portfolios`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__phmoney_prices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__phmoney_rates`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__phmoney_splits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__phmoney_split_types`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__phmoney_transactions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `#__phmoney_accounts`
  ADD CONSTRAINT `#__phmoney_accounts_ibfk_2` FOREIGN KEY (`currency_id`) REFERENCES `#__phmoney_currencys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `#__phmoney_accounts_ibfk_3` FOREIGN KEY (`account_type_id`) REFERENCES `#__phmoney_account_types` (`id`),
  ADD CONSTRAINT `#__phmoney_accounts_ibfk_4` FOREIGN KEY (`portfolio_id`) REFERENCES `#__phmoney_portfolios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__phmoney_portfolios`
  ADD CONSTRAINT `#__phmoney_portfolios_ibfk_1` FOREIGN KEY (`currency_id`) REFERENCES `#__phmoney_currencys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `#__phmoney_portfolios_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__phmoney_prices`
  ADD CONSTRAINT `#__phmoney_prices_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `#__phmoney_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__phmoney_rates`
  ADD CONSTRAINT `#__phmoney_rates_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `#__phmoney_portfolios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `#__phmoney_rates_ibfk_2` FOREIGN KEY (`currency_id`) REFERENCES `#__phmoney_currencys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__phmoney_splits`
  ADD CONSTRAINT `#__phmoney_splits_ibfk_2` FOREIGN KEY (`transaction_id`) REFERENCES `#__phmoney_transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `#__phmoney_splits_ibfk_3` FOREIGN KEY (`account_id`) REFERENCES `#__phmoney_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__phmoney_transactions`
  ADD CONSTRAINT `#__phmoney_transactions_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `#__phmoney_portfolios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/*
INSERT INTO `#__content_types` VALUES
(0, 'Account', 'com_phmoney.account', '{\"special\":{\"dbtable\":\"#__phmoney_accounts\",\"key\":\"id\",\"type\":\"Account\",\"prefix\":\"JTable\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"JTable\",\"config\":\"array()\"}}', '', '{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created_time\",\"core_modified_time\":\"modified_time\",\"core_body\":\"description\",\"core_hits\":\"null\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"core_access\":\"access\",\"core_params\":\"params\",\"core_featured\":\"null\",\"core_metadata\":\"null\",\"core_language\":\"language\",\"core_images\":\"null\",\"core_urls\":\"null\",\"core_version\":\"null\",\"core_ordering\":\"null\",\"core_metakey\":\"metakey\",\"core_metadesc\":\"metadesc\",\"core_catid\":\"null\",\"core_xreference\":\"null\",\"asset_id\":\"asset_id\"},\"special\":{\"path\":\"path\",\"code\":\"code\",\"note\":\"note\",\"portfolio_id\":\"portfolio_id\",\"account_type_id\":\"account_type_id\",\"currency_id\":\"currency_id\"}}', '', NULL),
(0, 'Transaction', 'com_phmoney.transaction', '{\"special\":{\"dbtable\":\"#__phmoney_transactions\",\"key\":\"id\",\"type\":\"Transaction\",\"prefix\":\"TransactionTable\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"JTable\",\"config\":\"array()\"}}', '', '\r\n{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"state\",\"core_alias\":\"alias\",\"core_created_time\":\"created\",\"core_modified_time\":\"modified\",\"core_body\":\"introtext\", \"core_hits\":\"hits\",\"core_publish_up\":\"publish_up\",\"core_publish_down\":\"publish_down\",\"core_access\":\"access\", \"core_params\":\"null\", \"core_featured\":\"featured\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"images\", \"core_urls\":\"urls\", \"core_version\":\"version\", \"core_ordering\":\"Ordering\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"catid\", \"core_xreference\":\"xreference\", \"asset_id\":\"asset_id\"}, \"special\":{\"portfolio_id\":\"portfolio_id\",\"num\":\"num\",\"description\":\"description\",\"checked_out\":\"checked_out\",\"checked_out_time\":\"checked_out_time\"}}', 'PhmoneyHelperRoute::getPhmoneyRoute', '{\"formFile\":\"administrator\\/components\\/com_phmoney\\/forms\\/transaction.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\"],\"ignoreChanges\":[\"modified_by\", \"modified\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\"],\"convertToInt\":[\"publish_up\", \"publish_down\", \"featured\", \"ordering\"],\"displayLookup\":[{\"sourceColumn\":\"catid\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"created_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"} ]}'),
(0, 'Split', 'com_phmoney.split', '{\"special\":{\"dbtable\":\"#__phmoney_splits\",\"key\":\"id\",\"type\":\"Split\",\"prefix\":\"SplitTable\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"JTable\",\"config\":\"array()\"}}', '', '\r\n{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"state\",\"core_alias\":\"alias\",\"core_created_time\":\"created\",\"core_modified_time\":\"modified\",\"core_body\":\"introtext\", \"core_hits\":\"hits\",\"core_publish_up\":\"publish_up\",\"core_publish_down\":\"publish_down\",\"core_access\":\"access\", \"core_params\":\"attribs\", \"core_featured\":\"featured\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"images\", \"core_urls\":\"urls\", \"core_version\":\"version\", \"core_ordering\":\"ordering\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"catid\", \"core_xreference\":\"xreference\", \"asset_id\":\"asset_id\"}, \"special\":{\"transaction_id\":\"transaction_id\",\"value\":\"value\",\"description\":\"description\",\"rate\":\"rate\",\"shares\":\"shares\",\"price\":\"price\",\"reconcile_state\":\"reconcile_state\"}}', 'PhmoneyHelperRoute::getPhmoneyRoute', '{\"formFile\":\"administrator\\/components\\/com_phmoney\\/forms\\/split.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\"],\"ignoreChanges\":[\"version\"],\"convertToInt\":[\"publish_up\", \"publish_down\", \"featured\", \"ordering\"],\"displayLookup\":[{\"sourceColumn\":\"catid\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"created_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"} ]}');
*/

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
('Account', 'com_phmoney.account', '{\"special\":{\"dbtable\":\"#__phmoney_accounts\",\"key\":\"id\",\"type\":\"Account\",\"prefix\":\"JTable\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"JTable\",\"config\":\"array()\"}}', '', '{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created_time\",\"core_modified_time\":\"modified_time\",\"core_body\":\"description\",\"core_hits\":\"null\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"core_access\":\"access\",\"core_params\":\"params\",\"core_featured\":\"null\",\"core_metadata\":\"null\",\"core_language\":\"language\",\"core_images\":\"null\",\"core_urls\":\"null\",\"core_version\":\"null\",\"core_ordering\":\"null\",\"core_metakey\":\"metakey\",\"core_metadesc\":\"metadesc\",\"core_catid\":\"null\",\"core_xreference\":\"null\",\"asset_id\":\"asset_id\"},\"special\":{\"path\":\"path\",\"code\":\"code\",\"note\":\"note\",\"portfolio_id\":\"portfolio_id\",\"account_type_id\":\"account_type_id\",\"currency_id\":\"currency_id\"}}', '', NULL),
('Transaction', 'com_phmoney.transaction', '{\"special\":{\"dbtable\":\"#__phmoney_transactions\",\"key\":\"id\",\"type\":\"Transaction\",\"prefix\":\"TransactionTable\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"JTable\",\"config\":\"array()\"}}', '', '\r\n{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"state\",\"core_alias\":\"alias\",\"core_created_time\":\"created\",\"core_modified_time\":\"modified\",\"core_body\":\"introtext\", \"core_hits\":\"hits\",\"core_publish_up\":\"publish_up\",\"core_publish_down\":\"publish_down\",\"core_access\":\"access\", \"core_params\":\"null\", \"core_featured\":\"featured\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"images\", \"core_urls\":\"urls\", \"core_version\":\"version\", \"core_ordering\":\"Ordering\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"catid\", \"core_xreference\":\"xreference\", \"asset_id\":\"asset_id\"}, \"special\":{\"portfolio_id\":\"portfolio_id\",\"num\":\"num\",\"description\":\"description\",\"checked_out\":\"checked_out\",\"checked_out_time\":\"checked_out_time\"}}', 'PhmoneyHelperRoute::getPhmoneyRoute', '{\"formFile\":\"administrator\\/components\\/com_phmoney\\/forms\\/transaction.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\"],\"ignoreChanges\":[\"modified_by\", \"modified\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\"],\"convertToInt\":[\"publish_up\", \"publish_down\", \"featured\", \"ordering\"],\"displayLookup\":[{\"sourceColumn\":\"catid\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"created_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"} ]}'),
('Split', 'com_phmoney.split', '{\"special\":{\"dbtable\":\"#__phmoney_splits\",\"key\":\"id\",\"type\":\"Split\",\"prefix\":\"SplitTable\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"JTable\",\"config\":\"array()\"}}', '', '\r\n{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"state\",\"core_alias\":\"alias\",\"core_created_time\":\"created\",\"core_modified_time\":\"modified\",\"core_body\":\"introtext\", \"core_hits\":\"hits\",\"core_publish_up\":\"publish_up\",\"core_publish_down\":\"publish_down\",\"core_access\":\"access\", \"core_params\":\"attribs\", \"core_featured\":\"featured\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"images\", \"core_urls\":\"urls\", \"core_version\":\"version\", \"core_ordering\":\"ordering\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"catid\", \"core_xreference\":\"xreference\", \"asset_id\":\"asset_id\"}, \"special\":{\"transaction_id\":\"transaction_id\",\"value\":\"value\",\"description\":\"description\",\"rate\":\"rate\",\"shares\":\"shares\",\"price\":\"price\",\"reconcile_state\":\"reconcile_state\"}}', 'PhmoneyHelperRoute::getPhmoneyRoute', '{\"formFile\":\"administrator\\/components\\/com_phmoney\\/forms\\/split.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\"],\"ignoreChanges\":[\"version\"],\"convertToInt\":[\"publish_up\", \"publish_down\", \"featured\", \"ordering\"],\"displayLookup\":[{\"sourceColumn\":\"catid\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"created_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"} ]}');

COMMIT;

-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vygenerováno: Pátek 11. září 2009, 12:34
-- Verze MySQL: 5.1.33
-- Verze PHP: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `dictionary`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `dic_default_dic_merge_group`
--

CREATE TABLE IF NOT EXISTS `dic_default_lang` (
  `id_dic` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `word` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_dic`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Vypisuji data pro tabulku `dic_default_lang`
--

INSERT INTO `dic_default_lang` (`id_dic`, `key`, `word`) VALUES
(1, 'hi', 'Aho'),
(2, 'name', 'Jméno'),
(3, 'partners', 'partenri'),
(4, 'contact', 'kontakt'),
(5, 'nnotFound', 'Neprelozeno'),
(6, 'dog1', '%d pes'),
(7, 'dog2', '%d psi'),
(8, 'dog3', '%d psů'),
(9, 'dog', '%s psa');

-- --------------------------------------------------------

--
-- Struktura tabulky `dic_other_langs`
--

CREATE TABLE IF NOT EXISTS `dic_other_langs` (
  `id_dic` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `word` text COLLATE utf8_unicode_ci NOT NULL,
  KEY `id_dic` (`id_dic`,`id_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `dic_other_langs`
--

INSERT INTO `dic_other_langs` (`id_dic`, `id_lang`, `word`) VALUES
(1, 2, 'Hi'),
(1, 3, 'Hallo'),
(2, 2, 'Name'),
(2, 3, 'Name'),
(3, 2, 'partner'),
(3, 3, 'Paartnerr'),
(4, 2, 'Contact'),
(4, 3, 'KKontaKKt'),
(6, 3, '%s Hund'),
(7, 3, '%s Hunde'),
(8, 3, '%s Hunde'),
(6, 2, '%s dog'),
(7, 2, '%s dogs'),
(8, 2, '%s dogs');

-- --------------------------------------------------------

--
-- Struktura tabulky `dic_lang`
--

CREATE TABLE IF NOT EXISTS `dic_lang` (
  `id_lang` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `web_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `declension` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'sklonovani',
  PRIMARY KEY (`id_lang`),
  UNIQUE KEY `lang` (`lang`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Vypisuji data pro tabulku `dic_lang`
--

INSERT INTO `dic_lang` (`id_lang`, `lang`, `web_name`, `declension`) VALUES
(1, 'cs', 'Cz', 'Declension::czechDeclension'),
(2, 'en', 'En', NULL),
(3, 'ge', 'D', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `merge_dic_group`
--

CREATE TABLE IF NOT EXISTS `dic_merge_group` (
  `id_dic` int(10) unsigned NOT NULL,
  `id_group` int(10) unsigned NOT NULL,
  KEY `id_dic` (`id_dic`,`id_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `dic_merge_group`
--

INSERT INTO `dic_merge_group` (`id_dic`, `id_group`) VALUES
(1, 1),
(2, 2),
(3, 3),
(3, 4),
(4, 2),
(4, 3),
(4, 5),
(5, 2),
(6, 3),
(7, 3),
(8, 3),
(9, 3);

-- --------------------------------------------------------

--
-- Struktura tabulky `dic_group`
--

CREATE TABLE IF NOT EXISTS `dic_group` (
  `id_group` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Vypisuji data pro tabulku `dic_group`
--

INSERT INTO `dic_group` (`id_group`, `group`) VALUES
(1, 'globalWords'),
(2, 'formular'),
(3, 'title'),
(4, 'masakr'),
(5, 'kontakty');

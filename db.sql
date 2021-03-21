-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 21, 2021 at 02:20 PM
-- Server version: 10.1.48-MariaDB-cll-lve
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `julesnet_x`
--

-- --------------------------------------------------------

--
-- Table structure for table `catcodes`
--

CREATE TABLE `catcodes` (
  `sortBy` varchar(2) COLLATE latin1_general_ci NOT NULL,
  `parentId` varchar(2) COLLATE latin1_general_ci DEFAULT NULL,
  `title` varchar(32) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `categoryId` int(10) UNSIGNED NOT NULL,
  `category` text COLLATE latin1_general_ci NOT NULL,
  `description` text COLLATE latin1_general_ci
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checklist`
--

CREATE TABLE `checklist` (
  `checklistId` int(10) UNSIGNED NOT NULL,
  `title` text COLLATE latin1_general_ci NOT NULL,
  `categoryId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `premiseA` varchar(1024) COLLATE latin1_general_ci DEFAULT NULL,
  `premiseB` varchar(1024) COLLATE latin1_general_ci DEFAULT NULL,
  `conclusion` varchar(1024) COLLATE latin1_general_ci DEFAULT NULL,
  `behaviour` text COLLATE latin1_general_ci,
  `standard` text COLLATE latin1_general_ci,
  `conditions` text COLLATE latin1_general_ci,
  `metaphor` text COLLATE latin1_general_ci,
  `hyperlink` varchar(256) COLLATE latin1_general_ci DEFAULT NULL,
  `sortBy` char(4) COLLATE latin1_general_ci NOT NULL DEFAULT '00',
  `frequency` int(4) DEFAULT NULL,
  `effort` int(4) DEFAULT NULL,
  `scored` enum('y','n') COLLATE latin1_general_ci NOT NULL DEFAULT 'n',
  `menu` enum('y','n') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'y',
  `prioritise` int(3) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checklistitems`
--

CREATE TABLE `checklistitems` (
  `checklistItemId` int(10) UNSIGNED NOT NULL,
  `item` text COLLATE latin1_general_ci NOT NULL,
  `notes` text COLLATE latin1_general_ci,
  `hyperlink` text COLLATE latin1_general_ci,
  `checklistId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `checked` enum('y','n') COLLATE latin1_general_ci NOT NULL DEFAULT 'n',
  `ignored` enum('y','n') COLLATE latin1_general_ci NOT NULL DEFAULT 'n',
  `score` int(8) NOT NULL DEFAULT '0',
  `assessed` int(8) NOT NULL DEFAULT '0',
  `effort` int(4) DEFAULT NULL,
  `expect` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checklistitemsinst`
--

CREATE TABLE `checklistitemsinst` (
  `checklistItemId` int(10) UNSIGNED NOT NULL,
  `checklistId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `instanceId` int(4) NOT NULL DEFAULT '1',
  `checked` enum('y','n') COLLATE latin1_general_ci NOT NULL DEFAULT 'n',
  `ignored` enum('y','n') COLLATE latin1_general_ci NOT NULL DEFAULT 'n',
  `score` int(8) NOT NULL DEFAULT '0',
  `assessed` int(8) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `context`
--

CREATE TABLE `context` (
  `contextId` int(10) UNSIGNED NOT NULL,
  `name` text COLLATE latin1_general_ci NOT NULL,
  `description` text COLLATE latin1_general_ci
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instance`
--

CREATE TABLE `instance` (
  `instanceId` int(3) NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` varchar(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `itemattributes`
--

CREATE TABLE `itemattributes` (
  `itemId` int(10) UNSIGNED NOT NULL,
  `type` enum('m','v','o','g','p','a','r','w','i') COLLATE latin1_general_ci NOT NULL DEFAULT 'i',
  `isSomeday` enum('y','n') COLLATE latin1_general_ci NOT NULL DEFAULT 'n',
  `categoryId` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `contextId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `timeframeId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `deadline` date DEFAULT NULL,
  `repeat` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `suppress` enum('y','n') COLLATE latin1_general_ci NOT NULL DEFAULT 'n',
  `suppressUntil` int(10) UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `itemId` int(10) UNSIGNED NOT NULL,
  `title` text COLLATE latin1_general_ci NOT NULL,
  `description` longtext COLLATE latin1_general_ci,
  `premiseA` varchar(1024) COLLATE latin1_general_ci DEFAULT NULL,
  `premiseB` varchar(1024) COLLATE latin1_general_ci DEFAULT NULL,
  `conclusion` varchar(1024) COLLATE latin1_general_ci DEFAULT NULL,
  `behaviour` text COLLATE latin1_general_ci,
  `standard` text COLLATE latin1_general_ci,
  `conditions` text COLLATE latin1_general_ci,
  `metaphor` text COLLATE latin1_general_ci,
  `hyperlink` text COLLATE latin1_general_ci,
  `sortBy` char(4) CHARACTER SET latin2 NOT NULL DEFAULT '00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `itemstatus`
--

CREATE TABLE `itemstatus` (
  `itemId` int(10) UNSIGNED NOT NULL,
  `dateCreated` date DEFAULT NULL,
  `lastModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dateCompleted` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `list`
--

CREATE TABLE `list` (
  `listId` int(10) UNSIGNED NOT NULL,
  `title` text COLLATE latin1_general_ci NOT NULL,
  `categoryId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `premiseA` varchar(1024) COLLATE latin1_general_ci DEFAULT NULL,
  `premiseB` varchar(1024) COLLATE latin1_general_ci DEFAULT NULL,
  `conclusion` varchar(1024) COLLATE latin1_general_ci DEFAULT NULL,
  `behaviour` text COLLATE latin1_general_ci,
  `standard` text COLLATE latin1_general_ci,
  `conditions` text COLLATE latin1_general_ci,
  `metaphor` text COLLATE latin1_general_ci,
  `hyperlink` varchar(256) COLLATE latin1_general_ci DEFAULT NULL,
  `sortBy` char(2) COLLATE latin1_general_ci NOT NULL DEFAULT '00',
  `menu` enum('y','n') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'y',
  `prioritise` int(3) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listitems`
--

CREATE TABLE `listitems` (
  `listItemId` int(10) UNSIGNED NOT NULL,
  `item` text COLLATE latin1_general_ci NOT NULL,
  `notes` text COLLATE latin1_general_ci,
  `hyperlink` varchar(256) COLLATE latin1_general_ci DEFAULT NULL,
  `listId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `dateCompleted` date DEFAULT NULL,
  `expect` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lookup`
--

CREATE TABLE `lookup` (
  `parentId` int(11) NOT NULL DEFAULT '0',
  `itemId` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lookuplist`
--

CREATE TABLE `lookuplist` (
  `pKey` int(11) NOT NULL,
  `parentId` int(11) NOT NULL,
  `listId` int(11) NOT NULL,
  `listType` varchar(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lookupqualities`
--

CREATE TABLE `lookupqualities` (
  `qaId` int(8) NOT NULL,
  `visId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `qId` int(11) NOT NULL,
  `itemType` varchar(8) NOT NULL,
  `value` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `nextactions`
--

CREATE TABLE `nextactions` (
  `parentId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `nextaction` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qualities`
--

CREATE TABLE `qualities` (
  `qId` int(10) UNSIGNED NOT NULL,
  `parId` int(8) DEFAULT NULL,
  `probId` int(8) DEFAULT NULL,
  `qType` enum('angle','quality','attribute','variable','itemMeta') COLLATE latin1_general_ci NOT NULL,
  `format` enum('score','probability','integer','text','meta','link','unqvalues','unqvaluessum','unqhoursresearch','unqhours','unqyears','unqyearsprob','unqyearstart','unqbrainless','unqhoursyear','unqhoursyearbrainless','unqtimeline','unqhourstravel','unqvaluessumhrs','someday','unqprobability','unqyearend','unqcata','unqcatb','unqcontext','unqcontextssum','unqcontextssumhrs','unqoptimise','unqoptimisepref','unqoptimisebala','unqcorrelpref','unqcorrelbala') COLLATE latin1_general_ci NOT NULL,
  `style` varchar(16) COLLATE latin1_general_ci DEFAULT NULL,
  `filter` enum('range','text','check','empty') COLLATE latin1_general_ci DEFAULT NULL,
  `typeReq` char(8) COLLATE latin1_general_ci DEFAULT NULL,
  `typeNoEd` char(8) COLLATE latin1_general_ci DEFAULT NULL,
  `existTable` varchar(1024) COLLATE latin1_general_ci DEFAULT NULL,
  `title` varchar(32) COLLATE latin1_general_ci DEFAULT NULL,
  `description` varchar(1024) COLLATE latin1_general_ci DEFAULT NULL,
  `disp` varchar(128) COLLATE latin1_general_ci DEFAULT NULL,
  `weight` int(1) DEFAULT NULL,
  `sort` int(2) DEFAULT NULL,
  `formulaSum1` text COLLATE latin1_general_ci,
  `formulaSum2` text COLLATE latin1_general_ci,
  `formulaVis1` text COLLATE latin1_general_ci,
  `formulaVis2` text COLLATE latin1_general_ci
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timeitems`
--

CREATE TABLE `timeitems` (
  `timeframeId` int(10) UNSIGNED NOT NULL,
  `timeframe` text COLLATE latin1_general_ci NOT NULL,
  `description` text COLLATE latin1_general_ci,
  `type` enum('v','o','g','p','a') COLLATE latin1_general_ci NOT NULL DEFAULT 'a'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `catcodes`
--
ALTER TABLE `catcodes`
  ADD PRIMARY KEY (`sortBy`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoryId`),
  ADD KEY `category` (`category`(10)),
  ADD KEY `description` (`description`(10));

--
-- Indexes for table `checklist`
--
ALTER TABLE `checklist`
  ADD PRIMARY KEY (`checklistId`),
  ADD KEY `checklistId` (`checklistId`);

--
-- Indexes for table `checklistitems`
--
ALTER TABLE `checklistitems`
  ADD PRIMARY KEY (`checklistItemId`),
  ADD KEY `checklistId` (`checklistId`),
  ADD KEY `notes` (`notes`(10)),
  ADD KEY `item` (`item`(10));

--
-- Indexes for table `checklistitemsinst`
--
ALTER TABLE `checklistitemsinst`
  ADD KEY `checklistId` (`checklistId`),
  ADD KEY `INDEX` (`checklistItemId`) USING BTREE;

--
-- Indexes for table `context`
--
ALTER TABLE `context`
  ADD PRIMARY KEY (`contextId`),
  ADD KEY `name` (`name`(10)),
  ADD KEY `description` (`description`(10));

--
-- Indexes for table `instance`
--
ALTER TABLE `instance`
  ADD PRIMARY KEY (`instanceId`);

--
-- Indexes for table `itemattributes`
--
ALTER TABLE `itemattributes`
  ADD PRIMARY KEY (`itemId`),
  ADD KEY `contextId` (`contextId`),
  ADD KEY `suppress` (`suppress`),
  ADD KEY `type` (`type`),
  ADD KEY `timeframeId` (`timeframeId`),
  ADD KEY `isSomeday` (`isSomeday`),
  ADD KEY `categoryId` (`categoryId`),
  ADD KEY `isSomeday_2` (`isSomeday`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`itemId`),
  ADD KEY `title` (`title`(10)),
  ADD KEY `desiredOutcome` (`behaviour`(10)),
  ADD KEY `description` (`description`(10));

--
-- Indexes for table `itemstatus`
--
ALTER TABLE `itemstatus`
  ADD PRIMARY KEY (`itemId`);

--
-- Indexes for table `list`
--
ALTER TABLE `list`
  ADD PRIMARY KEY (`listId`),
  ADD KEY `categoryId` (`categoryId`),
  ADD KEY `title` (`title`(10));

--
-- Indexes for table `listitems`
--
ALTER TABLE `listitems`
  ADD PRIMARY KEY (`listItemId`),
  ADD KEY `listId` (`listId`),
  ADD KEY `notes` (`notes`(10)),
  ADD KEY `item` (`item`(10));

--
-- Indexes for table `lookup`
--
ALTER TABLE `lookup`
  ADD PRIMARY KEY (`parentId`,`itemId`);

--
-- Indexes for table `lookuplist`
--
ALTER TABLE `lookuplist`
  ADD PRIMARY KEY (`pKey`);

--
-- Indexes for table `lookupqualities`
--
ALTER TABLE `lookupqualities`
  ADD UNIQUE KEY `key` (`qaId`) USING BTREE,
  ADD KEY `visId` (`visId`,`itemId`,`qId`) USING BTREE;

--
-- Indexes for table `nextactions`
--
ALTER TABLE `nextactions`
  ADD PRIMARY KEY (`parentId`,`nextaction`);

--
-- Indexes for table `qualities`
--
ALTER TABLE `qualities`
  ADD PRIMARY KEY (`qId`);

--
-- Indexes for table `timeitems`
--
ALTER TABLE `timeitems`
  ADD PRIMARY KEY (`timeframeId`),
  ADD KEY `type` (`type`),
  ADD KEY `timeframe` (`timeframe`(10)),
  ADD KEY `description` (`description`(10));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checklist`
--
ALTER TABLE `checklist`
  MODIFY `checklistId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checklistitems`
--
ALTER TABLE `checklistitems`
  MODIFY `checklistItemId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checklistitemsinst`
--
ALTER TABLE `checklistitemsinst`
  MODIFY `checklistItemId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `context`
--
ALTER TABLE `context`
  MODIFY `contextId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instance`
--
ALTER TABLE `instance`
  MODIFY `instanceId` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `itemattributes`
--
ALTER TABLE `itemattributes`
  MODIFY `itemId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `itemId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `itemstatus`
--
ALTER TABLE `itemstatus`
  MODIFY `itemId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `list`
--
ALTER TABLE `list`
  MODIFY `listId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listitems`
--
ALTER TABLE `listitems`
  MODIFY `listItemId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lookuplist`
--
ALTER TABLE `lookuplist`
  MODIFY `pKey` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lookupqualities`
--
ALTER TABLE `lookupqualities`
  MODIFY `qaId` int(8) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qualities`
--
ALTER TABLE `qualities`
  MODIFY `qId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timeitems`
--
ALTER TABLE `timeitems`
  MODIFY `timeframeId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

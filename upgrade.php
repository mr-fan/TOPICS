<?php

/*

 Website Baker Project <http://www.websitebaker.org/>
 Copyright (C) 2004-2007, Ryan Djurovich

 Website Baker is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Website Baker is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Website Baker; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

//require('../../config.php');

if (!defined('WB_PATH')) { die('Sopperlott!'); }

global $database;
global $admin;

// create the RSS count table
$SQL = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_topics_rss_count` ( ".
    "`id` INT(11) NOT NULL AUTO_INCREMENT, ".
    "`section_id` INT(11) NOT NULL DEFAULT '-1', ".
    "`md5_ip` VARCHAR(32) NOT NULL DEFAULT '', ".
    "`count` INT(11) NOT NULL DEFAULT '0', ".
    "`date` DATE NOT NULL DEFAULT '0000-00-00', ".
    "`timestamp` TIMESTAMP, ".
    "PRIMARY KEY (`id`), ".
    "KEY (`md5_ip`, `date`) ".
    ") ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
if (!$database->query($SQL))
  $admin->print_error($database->get_error());

// create the RSS statistics table
$SQL = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_topics_rss_statistic` ( ".
    "`id` INT(11) NOT NULL AUTO_INCREMENT, ".
    "`section_id` INT(11) NOT NULL DEFAULT '-1', ".
    "`date` DATE NOT NULL DEFAULT '0000-00-00', ".
    "`callers` INT(11) NOT NULL DEFAULT '0', ".
    "`views` INT(11) NOT NULL DEFAULT '0', ".
    "`timestamp` TIMESTAMP, ".
    "PRIMARY KEY (`id`), ".
    "KEY (`date`) ".
    ") ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
if (!$database->query($SQL))
  $admin->print_error($database->get_error());

$mod_dir = basename(dirname(__FILE__));
$tablename = $mod_dir;
require_once(WB_PATH.'/modules/'.$mod_dir.'/inc/upgrade.inc.php');


?>
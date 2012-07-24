<?php

/**
 * TOPICS
 *
 * @author Chio Maisriml <media@beesign.com>
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://websitebaker.at
 * @link https://addons.phpmanufaktur.de/topics
 * @copyright Chio Maisriml http://websitebaker.at
 * @copyright phpManufaktur by Ralf Hertsch
 * @license http://creativecommons.org/licenses/by/3.0/ Creative Commons Attribution 3.0
 */

if (!defined('WB_PATH'))
	require_once("../../config.php");

global $database;

// load the default settings
require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/defaults/module_settings.default.php');
require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/module_settings.php');

// the Section ID is not set yet
$section_id = 0;

// use the parameter 's_id' as Section ID
if (isset($_GET['s_id']) AND is_numeric($_GET['s_id'])) {
	$section_id = $_GET['s_id'];
}

if ($section_id == 0) {
  // read the first entry of the TOPICS settings to get a valid Section ID
  $SQL = "SELECT `section_id` FROM `".TABLE_PREFIX."mod_topics_settings` LIMIT 1";
  if (null == ($section_id = $database->get_one($SQL, MYSQL_ASSOC)))
    die(sprintf('[%s] %s', __LINE__, $database->get_error()));
  if ($section_id < 1)
    die(sprintf('[%s] %s', __LINE__, 'no section_id defined'));
}

// get the TOPICS settings for the Section ID
$SQL = sprintf("SELECT `sort_topics`,`section_title`,`section_description`,".
    "`use_timebased_publishing`,`page_id`,`picture_dir` FROM `%smod_topics_settings` WHERE ".
    "`section_id`='%d'", TABLE_PREFIX, $section_id);
if (null == ($query = $database->query($SQL)))
  die(sprintf('[%s] %s', __LINE__, $database->get_error()));

if ($query->numRows() == 1) {
	$settings = $query->fetchRow();
	$page_id = $settings['page_id'];
	$sort_topics = $settings['sort_topics'];
	$section_title = $settings['section_title'];
	$section_description = strip_tags($settings['section_description']);
	$use_timebased_publishing = $settings['use_timebased_publishing'];
	$picture_url = WB_URL.$settings['picture_dir'].'/';
}
else {
  // settings not found
 	die (sprintf('[%s] %s', __LINE__, "no data found"));
}

$query_extra = '';
if ($use_timebased_publishing > 1) {
  $t = time();
  $query_extra = " AND (`published_when`='0' OR `published_when` <= '$t') AND ".
      "(`published_until`='0' OR `published_until` >= '$t')";
}

$SQL = sprintf("SELECT * FROM `%smod_topics` WHERE `section_id`='%s' AND `active`>'3'%s ".
    "ORDER BY `active` DESC, `published_when` DESC LIMIT 50",
    TABLE_PREFIX, $section_id, $query_extra);
if (null == ($query = $database->query($SQL)))
  die(sprintf('[%s] %s', __LINE__, $database->get_error()));

$topics = '';
$image_width = 100;
$image_width_px = $image_width.'px';
// loop through the topics
while (false !== ($topic = $query->fetchRow())) {
  $topic_link = WB_URL.$topics_virtual_directory.$topic['link'].PAGE_EXTENSION;
  $rfcdate = date('D, d M Y H:i:s O', (int) $topic["published_when"]);
  $title = stripslashes($topic["title"]);
  $content = stripslashes($topic["content_short"]);
  // we don't want any dbGlossary entries here...
  $content = str_replace('||', '', $content);
  // @todo the CMS output filter should be executed here!
  if (!empty($topic['picture'])) {
    // add a image to the content
    $img_url = $picture_url.$topic['picture'];
$content = <<<EOD
<div>
  <img style="float:left;width:$image_width_px;height:auto;margin:0;padding:0 20px 20px 0;" src="$img_url" width="$image_width" alt="$title" />
  $content
</div>
EOD;
  } // image
  // add the topic to the $topics placeholder
$topics .= <<<EOD
    <item>
    	<title><![CDATA[$title]]></title>
    	<pubDate><![CDATA[$rfcdate]]></pubDate>
    	<description><![CDATA[$content]]></description>
    	<guid>$topic_link</guid>
    	<link>$topic_link</link>
    </item>
EOD;
} // while

$link = WB_URL;
$language = DEFAULT_LANGUAGE;
$category = WEBSITE_TITLE;
// @todo adding parameters to the $atom_link
$atom_link = WB_URL.'/modules/topics/rss.php';
$charset = defined('DEFAULT_CHARSET') ? DEFAULT_CHARSET : 'utf-8';

// create the XML body with the topics
$xml_body = <<<EOD
<?xml version="1.0" encoding="$charset"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>$section_title</title>
    <link>$link</link>
    <description>$section_description</description>
    <language>$language</language>
    <category>$category</category>
    <generator>TOPICS for WebsiteBaker and LEPTON CMS</generator>
    <atom:link href="$atom_link" rel="self" type="application/rss+xml" />
    $topics
  </channel>
</rss>
EOD;

// Sending XML header
header("Content-type: text/xml; charset=$charset");
// output XML content
echo $xml_body;
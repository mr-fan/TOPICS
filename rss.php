<?php

/*
Modified by mjm4842 - Michael Milette (2010-12-29)
 * Fixed RSS: Enabled auto-detection of default section_id (if there is only a
   single section) thereby eliminating the "no section_id defined" in most cases
   where there is only one Topics section on a website. s_id and section_id
   override still available. Also corrected fetch_row() syntax error.
   RSS listing is no longer always empty.
*/

if(!defined('WB_PATH')) { 
	require("../../config.php");
	if(!defined('WB_PATH')) { exit("Cannot access this file directly"); }
}


$mod_dir = basename(dirname(__FILE__));
$tablename = $mod_dir;
$usesettings = 0;
//Default settings
require_once(WB_PATH.'/modules/'.$mod_dir.'/defaults/module_settings.default.php');
require_once(WB_PATH.'/modules/'.$mod_dir.'/module_settings.php');

if(isset($_GET['s_id']) AND is_numeric($_GET['s_id'])) {
	$s_id = $_GET['s_id'];
	$usesettings = $s_id;	
} 
if (isset($section_id) AND is_numeric($section_id)) {$usesettings  = $section_id;}
if ($usesettings == 0) {
	$query_settings = $database->query("SELECT section_id FROM ".TABLE_PREFIX."mod_".$tablename."_settings LIMIT 1");
  if ( $query_settings->numRows() == 1) {
  	$fetch_settings = $query_settings->fetchRow();
    $s_id = $fetch_settings['section_id'];
  	$usesettings = $s_id;
  } else {
    die("no section_id defined");
  }
}
// Include WB files

require_once(WB_PATH.'/framework/class.frontend.php');
$database = new database();
//Query Settings
$query_settings = $database->query("SELECT sort_topics, section_title, section_description, use_timebased_publishing, page_id FROM ".TABLE_PREFIX."mod_".$tablename."_settings WHERE section_id = '$usesettings'");

if ( $query_settings->numRows() == 1) {	
	$fetch_settings = $query_settings->fetchRow();
	$page_id = $fetch_settings['page_id'];	
	$sort_topics = $fetch_settings['sort_topics'];
	$section_title = $fetch_settings['section_title'];
	$section_description = strip_tags($fetch_settings['section_description']);
	$use_timebased_publishing = $fetch_settings['use_timebased_publishing'];

} else {
 	die ("no data found");
}

$wb = new frontend();
$wb->page_id = $page_id;
$wb->get_page_details();
$wb->get_website_settings();

/*$sort_topics_by = ' position DESC';
if ($sort_topics == 1) {$sort_topics_by =  ' published_when DESC';}
if ($sort_topics == 2) {$sort_topics_by =   ' topic_score DESC';}*/

$sort_topics_by =  ' active DESC, published_when DESC';

$use_timebased_publishing = $fetch_settings['use_timebased_publishing'];		
$t = time();	
if ($use_timebased_publishing > 1) {$query_extra = " AND (published_when = '0' OR published_when <= $t) AND (published_until = 0 OR published_until >= $t)";} else {$query_extra = '';}
$qactive = " active > '3' ";
$limit_sql = " LIMIT 50";

if(isset($s_id)) {
	$theq = "SELECT * FROM ".TABLE_PREFIX."mod_".$tablename." WHERE section_id = '".$s_id."' AND ". $qactive.$query_extra." ORDER BY ".$sort_topics_by.$limit_sql;
} else {
	$theq = "SELECT * FROM ".TABLE_PREFIX."mod_".$tablename." WHERE ". $qactive.$query_extra." ORDER BY ".$sort_topics_by.$limit_sql;
	$section_title = $_SERVER['SERVER_NAME'];
	$section_description = '';
}

//echo "bis hier2";

//checkout if a charset is defined otherwise use UTF-8
if(defined('DEFAULT_CHARSET')) {
	$charset=DEFAULT_CHARSET;
} else {
	$charset='utf-8';
}
// Sending XML header
header("Content-type: text/xml; charset=$charset" );

// Header info
// Required by CSS 2.0
echo '<?xml version="1.0" encoding="'.$charset.'"?>';
?> 
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title><?php echo $section_title; ?></title>
<link>http://<?php echo $_SERVER['SERVER_NAME']; ?></link>
<description><?php echo $section_description; ?></description>

<?php
// Optional header info 
?>
<language><?php echo DEFAULT_LANGUAGE; ?></language>
<category><?php echo WEBSITE_TITLE; ?></category>
<generator>Website Baker Content Management System</generator>
<atom:link href="<?php echo WB_URL.$topics_virtual_directory; ?>rss.php" rel="self" type="application/rss+xml" />

<?php

/*<copyright><?php echo WB_URL.$_SERVER['REQUEST_URI']; ?></copyright>
<description> <?php echo $section_description; ?></description>
<managingEditor><?php echo SERVER_EMAIL; ?></managingEditor>
<webMaster><?php echo SERVER_EMAIL; ?></webMaster>*/
// Get topics items from database

//Query
$result = $database->query($theq);

//Generating the topics items
while($topic = $result->fetchRow()){
	$topic_link = WB_URL.$topics_virtual_directory.$topic['link'].PAGE_EXTENSION;	
	$rfcdate = date('D, d M Y H:i:s O', (int)$topic["published_when"]);
	?>
	
	<item>
	<title><![CDATA[<?php echo stripslashes($topic["title"]); ?>]]></title>
	<pubDate><![CDATA[<?php echo $rfcdate; ?>]]></pubDate>
	<description><![CDATA[<?php echo stripslashes($topic["content_short"]); ?>]]></description>
	<guid><?php echo $topic_link; ?></guid>
	<link><?php echo $topic_link; ?></link>
	</item>
	
	<?php } ?>

</channel>
</rss>
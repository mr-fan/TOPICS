<?php

/*

 Website Baker Project <http://www.websitebaker.org/>
 Copyright (C) 2004-2008, Ryan Djurovich

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
// Must include code to stop this file being access directly
if(defined('WB_PATH') == false) { exit("Cannot access this file directly"); }
if (defined("TOPIC_BLOCK2")) return ''; //prevent from more than one topic per page

require_once (WB_PATH.'/modules/'.$mod_dir.'/functions_small.php');

$mod_dir = basename(dirname(__FILE__));
$tablename = $mod_dir;

// Show topic page (single topic)

	
// Get settings
$setting_topic_header = $settings_fetch['topic_header'];
$setting_topic_footer = $settings_fetch['topic_footer'];
$setting_topic_block2 = $settings_fetch['topic_block2'];
		
$setting_comments_header = $settings_fetch['comments_header'];
$setting_comments_loop = $settings_fetch['comments_loop'];
$setting_comments_footer = $settings_fetch['comments_footer'];
	
$setting_pnsa_string = $settings_fetch['pnsa_string'];
$showmax_prev_next_links = (int) $settings_fetch['pnsa_max'];
	
	
// Get page info
$query_page = $database->query("SELECT link, parent, position FROM ".TABLE_PREFIX."pages WHERE page_id = '".PAGE_ID."'");
if($query_page->numRows() < 1) { exit('Page not found'); }
	
$page = $query_page->fetchRow();
$page_link = page_link($page['link']);
if ($page['parent'] == 0 AND $page['position'] == 1) {$page_link = WB_URL.'/';} //Seem to be the homepage
if($showoffset > 0) { $page_link .= '?p='.$showoffset; }
	
$qactive = " active > '1' ";	
if ($wb->is_authenticated()) {$qactive = " active > '0' ";}

if ($sort_topics == 3) {$query_extra = '';} //Evencalendar: Dont hide
// Get this topic
$theq = "SELECT * FROM ".TABLE_PREFIX."mod_".$tablename." WHERE topic_id = '".TOPIC_ID."' AND ".$qactive. $query_extra;
$query_topic = $database->query($theq);

if($query_topic->numRows() > 0) {
	$checkwhatstring = $setting_topic_header.$setting_topic_footer.$setting_topic_block2;
	
	$topic = $query_topic->fetchRow();
	
	// Workout date and time of last modified topic
	//NOTE: Topics use the local time as set in the WB-options			
	$thet =  $topic['posted_modified'];
	$posted_modi_date = gmdate(DATE_FORMAT, $thet);
	$posted_modi_time = gmdate(TIME_FORMAT, $thet);
				
	$thetp =  $topic['published_when'];
	if ($thetp == 0) {$thetp =  $topic['posted_first'];}
	$posted_publ_date = gmdate(DATE_FORMAT, $thetp);
	$posted_publ_time = gmdate(TIME_FORMAT, $thetp);
	
	
	//Handle Users:	
	$uid = $topic['posted_by']; // User who first posted the topic
	if ($uid == 0) {$uid = 1;} //None? Lets say: the admin.
	
	$user_changed_info = '';
	//Are there are PlaceHolders for Users in the settings?, so check them:
	if ( strpos($checkwhatstring, '[USER_') !== false ) { 
		$query_users = $database->query("SELECT user_id,username,display_name,email FROM ".TABLE_PREFIX."users WHERE user_id = '".$uid."'");
		$user_arr = array();
		if($query_users->numRows() == 1) {
			$user = $query_users->fetchRow();			
			$user_arr['username'] = $user['username'];
			$user_arr['display_name'] = $user['display_name'];
			$user_arr['email'] = $user['email'];
		} else { //User has been deleted in the meantime?
			$user_arr['username'] = '';
			$user_arr['display_name'] = '';
			$user_arr['email'] = '';		
		}
		
		
		if ( strpos($checkwhatstring, '[USER_MODIFIEDINFO]') !== false ) { 	
			$modified_byArr = explode(',',$topic['modified_by']);
			$user_idmod = $modified_byArr[(count($modified_byArr) - 1)];
			if ($user_idmod != $uid)  {
				$query_users = $database->query("SELECT user_id,username,display_name,email FROM ".TABLE_PREFIX."users WHERE user_id = '".$user_idmod."'");
				if($query_users->numRows() == 1) {
					$user = $query_users->fetchRow();
					$user_changed_info =  '<div class="tp_modified">'.$MOD_TOPICS['LAST_MODIFIED'] .' '.$user['display_name'];
					if ($thet > $thetp) {$user_changed_info .= ' '. $MOD_TOPICS['MODIFIED_DATE'].' '.$posted_modi_date.' '. $MOD_TOPICS['MODIFIED_TIME'].' '.$posted_modi_time;}
					$user_changed_info .= '</div>';
				}		
			}
		}
	}
	
	
			
	$position = $topic['position'];
	$sortkrit =  " position > (".$position.")";
	
	switch ($sort_topics) {
		case 0: $sortkrit =  " position > (".$position.")"; break;			
		case 1: $sortkrit =  " published_when > (".$thetp.")"; break;			
		case 2: $sortkrit =  " topic_score  > (".$topic['topic_score'].")"; break;
		case 3: $sortkrit =  " published_when > (".$thetp.")"; break;
		case 4: $sortkrit =  " title < '".addslashes($topic['title'])."'"; break;			
	
		case -1: $sortkrit =  " position < (".$position.")"; break;			
		case -2: $sortkrit =  " published_when < (".$thetp.")"; break;			
		case -3: $sortkrit =  " topic_score  < (".$topic['topic_score'].")"; break;
		case -4: $sortkrit =  " published_when < (".$thetp.")"; break;
		case -5: $sortkrit =  " title > '".addslashes($topic['title'])."'"; break;			
	}
	
	
	$frombackend = 0;
	
	//Get See_also Topics
	$see_also_text = $topic['see_also'];	
	$see_also_output = '';
	if ( $see_also_text != '' AND strpos($checkwhatstring, '{SEE_ALSO}') === false ) { $see_also_text = '';} //No Placeholder? No need to query
	$see_prevnext_output = '';			
	$show_prevnext_links = 1;
	if ( strpos($checkwhatstring, '{SEE_PREVNEXT}') === false ) { $show_prevnext_links = 0;} //No Placeholder? No need to query
	if ($show_prevnext_links == 1 OR  $see_also_text != '') {				
		include(WB_PATH.'/modules/'.$mod_dir.'/inc/find_pnsa_links.inc.php');
	}
			
	//the full link List
	if ( strpos($checkwhatstring, '{FULL_TOPICS_LIST}') === false ) {
		$topics_linkslist = '';
	} else {
		include(WB_PATH.'/modules/'.$mod_dir.'/inc/link_list.inc.php');			
	}
	
	$eventplaceholders = false;
	if ( strpos($checkwhatstring, '[EVENT_') !== false) {$eventplaceholders = true;}	
	
	//Extra Fields
	$txtr1 = ($topic['txtr1']);	
	$txtr2 = ($topic['txtr2']);
	$txtr3 = ($topic['txtr3']);
	
	//Handle pictures and thumbs:
	$pictureplaceholders = false;
	if ( strpos($checkwhatstring, 'PICTURE') !== false) {$pictureplaceholders = true;}
	if ( strpos($checkwhatstring, 'THUMB') !== false) {$pictureplaceholders = true;}
		
	$picture = $topic['picture'];
	$thumb = $picture;
	$picture_tag = '';
	$thumb_tag = '';
	
	if ( $pictureplaceholders == true AND $picture != '') {
		$picturelink = '';
		if ($extrafield_1_name == 'Picture Link') {$picturelink = $txtr1;}
		if ($extrafield_2_name == 'Picture Link') {$picturelink = $txtr2;}
		if ($extrafield_3_name == 'Picture Link') {$picturelink = $txtr3;}
		if ($picturelink != '') { $picturelink = '<a href="'.$picturelink.'" target="_blank" class="tp_piclink">'; }
					
		if (substr($picture, 0, 7) == 'http://') {
			//external file:
			$picture_tag = '<img class="tp_pic tp_pic'.$page_id.'" src="'.$picture.'" alt="'.$topic['short_description'].'" />';
			$thumb_tag = '<img class="tp_thumb tp_thumb'.$page_id.'" src="'.$picture.'" alt="" />';
		} else {
			if ($picture_dir != '') {			
				$picture_tag = '<img class="tp_pic tp_pic'.$page_id.'" src="'.$picture_dir.'/'.$picture.'" alt="'.$topic['short_description'].'" />';
				if ($zoomclass != '' AND $picturelink == '') {
					//Check if there is a picture in folder "zoom"
					$zoompic = WB_PATH.$settings_fetch['picture_dir'].'/zoom/'.$picture;			
					if (file_exists($zoompic)) { $picturelink = '<a href="'.$picture_dir.'/zoom/'.$picture.'" target="_blank" class="'.$zoomclass.'">'; }		
				}
			}
		}
		//something to link:
		if ($picturelink != '') { $picture_tag = $picturelink.$picture_tag.'</a>'; }
		
		$thumb_tag = '<img class="tp_thumb tp_thumb'.$page_id.'" src="'.$picture_dir.'/thumbs/'.$picture.'" alt="" />';
	}
		
	
	
	$edit_link = '';
	if ($authoronly) {
		$authors = $topic['authors'];		
		$pos = strpos ($authors,','.$user_id.',');
		if ($pos !== false){$makeeditlink = true;}	
	}
	if ($makeeditlink) { $edit_link = '<div class="mod_topic_edit"><a class="tp_editlink" target="_blank" href="'.WB_URL.'/modules/'.$mod_dir.'/modify_topic.php?page_id='.PAGE_ID.$paramdelimiter.'section_id='.$section_id.$paramdelimiter.'topic_id='.TOPIC_ID.$paramdelimiter.'fredit='.$fredit.'">Edit</a></div>'; }
			
	if ($short_textareaheight > 0) {		
		$topic_short=$topic['content_short'];
		if( $topics_use_plain_text > 0) {$topic_short = nl2br($topic_short);}
		$wb->preprocess($topic_short);
	} else {
		$topic_short = '';
	}
			
	if ($long_textareaheight > 0) {		
		$topic_long = $topic['content_long'];
		if( $topics_use_plain_text > 0) {$topic_long = nl2br($topic_long);}
		$wb->preprocess($topic_long);
	} else {
		$topic_long = '';
	}
	
	if ($extra_textareaheight > 0) {
		$topic_extra = $topic['content_extra'];
		if( $topics_use_plain_text > 0) {$topic_extra = nl2br($topic_extra);}
		$wb->preprocess($topic_extra);
	} else {
		$topic_extra = '';
	}
	
	
	
	$topic_score = $topic['topic_score'];
	
	$comments_count = 0;
	$commentsclass = 0;
	//Check, if there are placeholders for Comments:
	if ( strpos($checkwhatstring, '[COMMENTS') !== false) { 
		$comments_count = $topic['comments_count'];			
		if ($comments_count > 0) {$commentsclass = 1;
			if ($comments_count > 2) {$commentsclass = 2;
				if ($comments_count > 5) {$commentsclass = 3;
					if ($comments_count > 8) {$commentsclass = 4;}
				}
			}
		}						
	} 
	
	//Fetch comments:
	//------------------------------------------------------------------------------------------------------------------------------------------
	// Show comments section if we have to
	$commenting = $topic['commenting'];
	if ($use_commenting_settings == 1) {$commenting = $settings_fetch['commenting'];}
	if ($commenting < 0) {$use_commenting = -1;}
	
	$allcomments = '';
	if ($use_commenting >= 0) {
		
	
		//$t = time();
		$minimum_commentedtime = $t - $topics_comment_cookie; //Seconds		
		if ($commenting == 3) {$minimum_commentedtime = $t + 100;} //sofort
	
		$sort_comments = $settings_fetch['sort_comments'];
		$sort_comments_by = ' commented_when ASC';
		if ($sort_comments == 1) {$sort_comments_by = ' commented_when DESC';} 
		
		$qtime = '';
		if ($commenting < 3) $qtime =  " AND commented_when < '".$minimum_commentedtime."'";
	
		$comment_id = 0; //Set as default
		$allcomments = '';
		// Query for comments
		
		$theq = "SELECT * FROM ".TABLE_PREFIX."mod_".$tablename."_comments WHERE topic_id = '".TOPIC_ID."'" . $qtime ." AND active>'0' ORDER BY ".$sort_comments_by;
	
		$query_comments = $database->query($theq);
		if($query_comments->numRows() > 0) {
		
			
			// comments header		
			while($comment = $query_comments->fetchRow()) {
				// Display Comments without slashes, but with new-line characters
				$comment_id = ($comment['comment_id']);
				$comment['comment'] = nl2br(($comment['comment']));				
				$cwebsite = ($comment['website']);
				$cshow_link = ($comment['show_link']);
			
				$nameLink = $comment['name'];
				if ($comment['website'] != '') {
					if ($cshow_link == 1) {
						$cshow_linklen = strlen($cwebsite);
						$cshow_linklenh = $cshow_linklen / 2;
						$cshow1 = substr($cwebsite, 0, $cshow_linklenh);
						$cshow2 = substr($cwebsite, $cshow_linklenh, $cshow_linklen);
					
						$nameLink = "<a href=\"javascript:showcommenturl('".$cshow2."','".$cshow1."');\">".$nameLink."</a>";
					} //Javascript
					if ($cshow_link == 2) {$nameLink = '<a href="'.$cwebsite.'" target="_blank" rel="nofollow">'.$nameLink.'</a>';} //rel=nofollow
					if ($cshow_link == 3) {$nameLink = '<a href="'.$cwebsite.'" target="_blank">'.$nameLink.'</a>';}
				}
				
				
				// Print comments loop
				$commentedtime = $comment['commented_when'];
				$commented_date = gmdate(DATE_FORMAT, $commentedtime);
				$commented_time = gmdate(TIME_FORMAT, $commentedtime);
				$cuid = (int) $comment['commented_by'];
				$vars = array('[NAME]','[EMAIL]','[WEBSITE]','[COMMENT]','[DATE]','[TIME]', '{NAME}', '[USER_ID]');
				$values = array(($comment['name']), ($comment['email']), ($comment['website']), ($comment['comment']), $commented_date, $commented_time, $nameLink, $cuid);			
				
				$allcomments .= str_replace($vars, $values, $setting_comments_loop);
			} // end while
			$allcomments = $setting_comments_header . $allcomments . $setting_comments_footer;
	 	} // END numRows() > 0)
		
		
		if ($sort_comments == 1)  { $allcomments = '<div id="lastcomment"></div>'.$allcomments;} else { $allcomments = $allcomments.'<div id="lastcomment"></div>';}
	} //End fetching comments
	
	//Comment Frame
	$commentframe = '';
	//var_dump($_SESSION);
	if (!isset($_SESSION)) {$commenting = 0;}	
	if ($commenting > 0 AND $use_commenting > 0) {		
		$commentframe = '<script type="text/javascript">		
		lastcommentid='.(0+$comment_id).';
		thecommurl=\''.WB_URL.'\'+\'/modules/\';
		document.write(\'<iframe src="\'+thecommurl+\''.$mod_dir.'/comment.php?id='.TOPIC_ID.'&amp;sid='.$section_id.'" frameborder="0" class="mod_topic_comment_iframe" scrolling="no" id="extrasager"></iframe>\');
		</script>';
				
	}
	
	
	//-------------------------------------------------------------------------------------------
	//Make the final output:		
	if (file_exists(WB_PATH.'/modules/'.$mod_dir.'/view.final.custom.php')) {
		include(WB_PATH.'/modules/'.$mod_dir.'/view.final.custom.php');
	} else {
		include(WB_PATH.'/modules/'.$mod_dir.'/view.final.php');
	}
	//-------------------------------------------------------------------------------------------
	
	if(ENABLED_ASP) {
		$_SESSION['comes_from_view'] = TOPIC_ID;
		$_SESSION['comes_from_view_time'] = time();
	}
			
} else {
	$wb->print_error($MESSAGE['FRONTEND']['SORRY_NO_ACTIVE_SECTIONS'], "javascript: history.go(-1);", false);
	exit(0);
}
	
	

?>
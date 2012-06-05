<?php


// Must include code to stop this file being access directly
if(!defined('WB_PATH')) { exit("Cannot access this file directly"); }



if (!$wb->is_authenticated()) { 
	echo '<h1>Hi!</h1><a href="'.WB_URL.'/account/login.php">Login</a>' ;

} else {

	$mod_dir = basename(dirname(__FILE__));
	$tablename = $mod_dir;

	
	// Load Language file
	if(LANGUAGE_LOADED) {
		if(!file_exists(WB_PATH.'/modules/'.$mod_dir.'/languages/'.LANGUAGE.'.php')) {
			require_once(WB_PATH.'/modules/'.$mod_dir.'/languages/EN.php');
		} else {
			require_once(WB_PATH.'/modules/'.$mod_dir.'/languages/'.LANGUAGE.'.php');
		}
	}



	// include module_settings
	require_once(WB_PATH.'/modules/'.$mod_dir.'/defaults/module_settings.default.php');
	require_once(WB_PATH.'/modules/'.$mod_dir.'/module_settings.php');
	require_once(WB_PATH.'/modules/'.$mod_dir.'/functions.php');


	$user_id = $wb->get_user_id();
	$user_in_groups = $wb->get_groups_id();
	
	$makeeditlink = false;
	$authoronly = false;
	$fredit = $fredit_default;
	if ($authorsgroup > 0) { //Care about users	
		if (in_array($authorsgroup, $user_in_groups)) {$authoronly = true; $fredit = 1;} //Ist nur Autor
	}
	
	if (in_array(1, $user_in_groups)) {	
		$authoronly = false; //An admin cannot be autor only
		$makeeditlink = true;
	}
	
	if (!isset($sectionquery)) {$sectionquery = ' ';}
	$queryextra = '';
	$queryorder = ' ORDER BY posted_modified DESC';
	$querylimit = ' LIMIT 30 ';
	
	
	//Get latest Topics by posted_modified 
	if ($authoronly) {
		$user_idstr = '%,'.$user_id.',%';
		$queryextra = " AND authors LIKE '".$user_idstr."' ";
	}
	
	$counter = 0;
	$public = 0;
	$comments_count = 0;
	$picsurl = WB_URL.'/modules/'.$mod_dir.'/img/';

	$output1 = '';
	$output2 = '';
	$commentssearch = '';
	$mycommentsArr = array();
		
	$theq = "SELECT * FROM `".TABLE_PREFIX."mod_".$tablename."` WHERE section_id > 0 ".$sectionquery.$queryextra . $queryorder . $querylimit;		
	$query_topics = $database->query($theq);
	$num_topics = $query_topics->numRows();
	if($num_topics > 0) {
		while($topic = $query_topics->fetchRow()) {
	
			$posted_by  = $topic['posted_by']; //Owner
			$p_id = $topic['page_id'];
			$s_id = $topic['section_id'];
			$t_id = $topic['topic_id'];
		
			if ($commentssearch == '') {$commentssearch .= $t_id;} else {$commentssearch .= ','.$t_id;}
			$allcommentsArr[$t_id] = array($p_id, $s_id);
			$counter++;
		
			$active = $topic['active']; if ($active > 2) $public += 1;			
			$trclass = '';
		
			$edit_link = '<a class="tp_editlink" target="_blank" href="'.WB_URL.'/modules/'.$mod_dir.'/modify_topic.php?page_id='.$topic['page_id'].$paramdelimiter.'section_id='.$topic['section_id'].$paramdelimiter.'topic_id='.$t_id.$paramdelimiter.'fredit='.$fredit.'">';

			if ($authoronly) {
				$authors = $topic['authors'];
				$pos = strpos ($authors,','.$user_id.',');
				if ($pos === false){$edit_link = ''; $trclass .= ' noedit';}
			}
			
			$tr = '<tr class="'.$trclass.'" valign="top">
			<td width="40" align="right">';
			// Get number of comments
			$query_comments = $database->query("SELECT name FROM ".TABLE_PREFIX."mod_".$tablename."_comments WHERE topic_id = '".$topic['topic_id']."'");
			$comments_count = $query_comments->numRows();		
			$commentsclass = 0;
			if ($comments_count > 0) {$commentsclass = 1;
				if ($comments_count > 2) {$commentsclass = 2;
					if ($comments_count > 5) {$commentsclass = 3;
						if ($comments_count > 8) {$commentsclass = 4;}
					}
				}
			}
			$tr .= '<img src="'.$picsurl.'comments'.$commentsclass.'.gif" alt="'.$comments_count.' comments" /> ';
		
			$alt='ACTIVE_'.$active; $tr .=  '<img src="'.$picsurl.'active'.$active.'.gif" alt="'.$MOD_TOPICS[$alt].'" /></a>';
			$tr .= '</td><td>';
		
			$title = stripslashes($topic['title']);
			if ($title == '') {$title = 'Untitled';}
			$tr .= '<strong>'.$edit_link.$title.'</a></strong>'; if ($topic['short_description'] !='') {$tr .= '<div class="shortdesc">'.$topic['short_description'].'</div>';}
			
			$tr .= '</td><td class="topicprops" style="width:50px;">';
			
		
			if ($topic['hascontent'] > 0 AND $active > 0) { 
				$topic_link = WB_URL.$topics_directory.$topic['link'].PAGE_EXTENSION;
		 		$tr .=  '<a href="'.$topic_link.'" target="_blank" ><img src="'.THEME_URL.'/images/view_16.png" class="viewbutton" alt="View" /></a>';
			} 
		
			$tr .= '</td></tr>';
		
			if ($posted_by == $user_id) {
				$output1 .= $tr;
				$mycommentsArr[] = $t_id;
			} else {
				$output2 .= $tr;
			}
		}
		if (isset($newpage_id) AND isset($newsection_id)) {
			echo '<h2><a class="tp_editlink" target="_blank" href="'.WB_URL.'/modules/'.$mod_dir.'/add_topic.php?page_id='.$newpage_id.$paramdelimiter.'section_id='.$newsection_id.$paramdelimiter.'fredit='.$fredit.'">'.$MOD_TOPICS['NEWTOPIC']."</a>";
		}
		if ($output1 != '') {echo '<h2>You are owner of:</h2><table width="100%" border="0" cellspacing="0" cellpadding="3">'.$output1.'</table>';}
		if ($output2 != '') {echo '<h2>You can edit also:</h2><table width="100%" border="0" cellspacing="0" cellpadding="3">'.$output2.'</table>';}
	
		//-------------------------------------------------------------------------------------------------------------------------------------------------
		//Find the comments	
		$theq = "SELECT * FROM ".TABLE_PREFIX."mod_".$tablename."_comments WHERE topic_id IN (".$commentssearch.") ORDER BY comment_id DESC";
	
	
		//echo '<h2>Comments</h2>';
	
		$output1 = '';
		$output2 = '';
		$query_comment = $database->query($theq); // 
		while($comment = $query_comment->fetchRow()) {
			$t_id = $comment['topic_id'];
			$editlink = WB_URL.'/modules/'.$mod_dir.'/modify_comment.php?page_id='.$allcommentsArr[$t_id][0].$paramdelimiter.'section_id='.$allcommentsArr[$t_id][1].$paramdelimiter.'comment_id='.$comment['comment_id'].$paramdelimiter.'fredit='.$fredit;
			$cwebsite = ($comment['website']);
			$nameLink = $comment['name'];
			if ($cwebsite != '') { $nameLink = '<a href="'.$cwebsite.'" target="_blank">'.$nameLink.'</a>';}
			$cout = '<p><strong>'.$nameLink. '</strong> ('.$comment['email'].') <strong><a href="'.$editlink.'" target="blank" class="tp_editlink">EDIT</a></strong><br/>';
			$cout .= nl2br($comment['comment']).'</p>';
		
			if (in_array($t_id, $mycommentsArr)) {
				$output1 .= $cout;			
			} else {
				$output2 .= $cout;
			}
		}
	
		if ($output1 != '') {echo '<h3>Comments about your topics:</h3><table width="100%" border="0" cellspacing="0" cellpadding="3">'.$output1.'</table>';}
		if ($output2 != '') {echo '<h3>You can edit also:</h3><table width="100%" border="0" cellspacing="0" cellpadding="3">'.$output2.'</table>';}
		
	
	} else {
		echo $TEXT['NONE_FOUND'].'<hr/>';
	}
	
}
?>


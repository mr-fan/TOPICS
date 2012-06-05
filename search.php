<?php

// $Id: search.php 600 2008-01-26 11:26:54Z thorn $

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

function topics_search($func_vars) {
	extract($func_vars, EXTR_PREFIX_ALL, 'func');
	
	$mod_dir = basename(dirname(__FILE__));
	$tablename = $mod_dir;
	//$mod_dir = 'topics';
	
	// include module_settings
	require(WB_PATH.'/modules/'.$mod_dir.'/defaults/module_settings.default.php');
	require(WB_PATH.'/modules/'.$mod_dir.'/module_settings.php');

	// how many lines of excerpt we want to have at most
	$max_excerpt_num = $func_default_max_excerpt;
	// do we want excerpt from comments?
	$excerpt_from_comments = false; // TODO: make this configurable
	$divider = ".";
	$result = false;

	// fetch all active topics-posts (from active groups) in this section.
	$t = time();
	$table_topics = TABLE_PREFIX."mod_".$tablename;	
	$query = $func_database->query("
		SELECT p.topic_id, p.title, p.short_description, p.description, p.content_long, p.link, p.posted_first, p.posted_by
		FROM $table_topics AS p 
		WHERE p.section_id='$func_section_id' AND p.active > '3' 
		ORDER BY p.topic_id DESC
	");
	
	
	
	// now call print_excerpt() for every single post
	if($query->numRows() > 0) {
		while($res = $query->fetchRow()) {
			$text = $res['title'].$divider.$res['description'].$divider.$res['content_long'].$divider;
			// fetch comments and add to $text
			/*if($excerpt_from_comments) {
				$table = TABLE_PREFIX."mod_topics_comments";
				$commentquery = $func_database->query("
					SELECT name, comment
					FROM $table
					WHERE topic_id='{$res['topic_id']}'
					ORDER BY commented_when ASC
				");
				if($commentquery->numRows() > 0) {
					while($c_res = $commentquery->fetchRow()) {
						$text .= $c_res['name'].$divider.$c_res['comment'].$divider;
					}
				}
			}*/
			$mod_vars = array(
				'page_link' => $topics_search_directory.$res['link'], // use direct link to topics-item
				'page_link_target' => "",
				'page_title' => $res['title'], //$func_page_title,
				'page_description' => $res['short_description'], // use topics-title as description
				'page_modified_when' => $res['posted_first'],
				'page_modified_by' => $res['posted_by'],
				'text' => $text,
				'max_excerpt_num' => $max_excerpt_num
			);
			if(print_excerpt2($mod_vars, $func_vars)) {
				$result = true;
			}
		}
	}
	
	
	return $result;
}

?>

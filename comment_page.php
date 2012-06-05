<?php


// Make sure page cannot be accessed directly
$mod_dir = basename(dirname(__FILE__));
$tablename = $mod_dir;
if(!defined('WB_URL')) { 
	header("Location: ".WB_URL."/modules/".$mod_dir."/nopage.php");
	exit(0);
}
$thedelimiter = "&amp;";

require_once(WB_PATH.'/include/captcha/captcha.php');

// Get comments page template details from db
$query_settings = $database->query("SELECT use_captcha,commenting FROM ".TABLE_PREFIX."mod_".$tablename."_settings WHERE section_id = '".SECTION_ID."'");
if($query_settings->numRows() == 0) {
	header("Location: ".WB_URL.'/modules/'.$mod_dir.'/nopage.php');
	exit(0);
} else {
// Load Language file
	if(LANGUAGE_LOADED) {
		if(!file_exists(WB_PATH.'/modules/'.$mod_dir.'/languages/'.LANGUAGE.'.php')) {
			require_once(WB_PATH.'/modules/'.$mod_dir.'/languages/EN.php');
		} else {
			require_once(WB_PATH.'/modules/'.$mod_dir.'/languages/'.LANGUAGE.'.php');
		}
	}
	$settings = $query_settings->fetchRow();
	?>
   
	
    <script language="JavaScript" type="text/JavaScript">
<!--
function validateForm() { 	
	erc = 0;
	err = '<?php echo $MOD_TOPICS['JS_ERROR']; ?>\n';
	if (document.comment.thenome.value.length < 3) {erc ++; err += '- <?php echo $MOD_TOPICS['JS_NAME']; ?>'+'\n';}
	
	m = document.comment.themoil.value;
	//if (m != '') { //Wenn vorhanden, dann muss gültig sein
		p1=m.indexOf('@');
		p2=m.indexOf('.');
		if ((p1 * p2) < 6) {erc ++; err += '- eMa'+'il\n';} 
	//}
	
	
	ct = document.comment.c0mment.value.length+1;
	//alert (ct);	
	if (ct < 10)  {erc ++; err += '- <?php echo $MOD_TOPICS['JS_TOO_SHORT']; ?>'+'\n';}
	
	
	try {
	if ( document.comment.captcha) {
		c = document.comment.captcha.value;
		if (c=='') {erc ++; err += '- <?php echo $MOD_TOPICS['JS_VERIFICATION']; ?>'+'\n';}	
	}
	} finally {}
	
	
	if (erc > 0) {
		alert(err);
		
		document.returnValue = false; //(err == '');
	} else {
	document.returnValue = true;
	}

}

//-->
    </script>
   
    
    
    <table id="wraptable"><tr><td>
	<div class="topicsc_the_f">
	<h3><?php echo $TEXT['COMMENT']; ?></h3>
	<form name="comment" action="<?php echo WB_URL.'/modules/'.$mod_dir.'/submit_comment.php?page_id='.PAGE_ID.$thedelimiter.'section_id='.SECTION_ID.$thedelimiter.'topic_id='.TOPIC_ID; ?>" method="post" onsubmit="validateForm(); return document.returnValue">
	
	<?php if(ENABLED_ASP) { // add some honeypot-fields  // 
	?>
	<input type="hidden" name="submitted_when" value="<?php $t=time(); echo $t; $_SESSION['submitted_when']=$t; ?>" />
	<p class="nixhier">
	email address:
	<label for="email">Leave this field email blank:</label>
	<input id="email" name="email" size="60" value="" /><br />
	Homepage:
	<label for="homepage">Leave this field homepage blank:</label>
	<input id="homepage" name="homepage" size="60" value="" /><br />
	URL:
	<label for="url">Leave this field url blank:</label>
	<input id="url" name="url" size="60" value="" /><br />
	Comment:
	<label for="comment">Leave this field comment blank:</label>
	<input id="comment" name="comment" size="60" value="" /><br />
	</p>
	<?php }
	
	
	$theemail = '';
	$thesite = '';
	$thename = '';
	if (isset($_COOKIE['commentdetails']) AND is_numeric($_COOKIE['commentdetails'])) {
		$query_comments = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_".$tablename."_comments WHERE comment_id = '".$_COOKIE['commentdetails']."'");
		if($query_comments->numRows() == 1) {
			$commentfetch = $query_comments->fetchRow();
			$thename = $commentfetch['name'];				
			$theemail =  $commentfetch['email'];
			$thesite = $commentfetch['website'];
		} 
		
	}
	
	
	
	
	
	?>
	<p><?php echo $TEXT['NAME']; ?>:<br />
	<input type="text" name="thenome" maxlength="255" value="<?php echo $thename; ?>" />
	</p>
	<p><?php echo $TEXT['EMAIL']; ?> (required, not public):<br />
	<input type="text" name="themoil" maxlength="255" value="<?php echo  $theemail; ?>" />
	</p>
	<p><?php echo $TEXT['WEBSITE']; ?>:<br />
	<input type="text" name="thesote" maxlength="255" value="<?php echo  $thesite; ?>" />
	</p>
	<p><?php echo $TEXT['COMMENT']; ?> :<br />	
	<?php if(ENABLED_ASP) { ?>
		<textarea onchange="doresize();" rows="10" cols="1" id="c0mment" name="c0mment_<?php echo date('W'); ?>"><?php if(isset($_SESSION['comment_body'])) { echo $_SESSION['comment_body']; unset($_SESSION['comment_body']); } ?></textarea>
	<?php } else { ?>
		<textarea onchange="doresize();" rows="10" cols="1" id="c0mment" name="comment"><?php if(isset($_SESSION['comment_body'])) { echo $_SESSION['comment_body']; unset($_SESSION['comment_body']); } ?></textarea>
	<?php } ?>
	</p>
	<?php
	if(isset($_SESSION['captcha_error'])) {
		echo '<font color="#FF0000">'.$_SESSION['captcha_error'].'</font><br />';
		$_SESSION['captcha_retry_topics'] = true;
	}
	// Captcha
	if($settings['use_captcha']) {
	?>
	<table cellpadding="2" cellspacing="0" border="0">
	<tr>
		<td><?php echo $TEXT['VERIFICATION']; ?>:</td>
		<td><?php call_captcha(); ?></td>
	</tr></table>
	<br />
	<?php
	if(isset($_SESSION['captcha_error'])) {
		unset($_SESSION['captcha_error']);
		?><script>document.comment.captcha.focus();</script>
	<?php
	}?>
	<?php
	}
	?>
	<div ><input type="submit" name="submit" class="submitbutton" value="<?php echo $TEXT['ADD']; ?> <?php echo $TEXT['COMMENT']; ?>" /></div>
	</form></div>	
	<?php
}

?>
</td></tr></table>
 <script language="JavaScript" type="text/JavaScript">
<!--
	h = document.getElementById('wraptable').offsetHeight;
	if (h > 300 && h < 800) {parent.resizeframe(h); }
//-->
 </script>	

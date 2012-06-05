<?php
// Direkter Zugriff verhindern
if (!defined('WB_PATH')) die (header('Location: index.php'));

 
function resizepic($filepath, $newfilepath, $newfile_maxw, $newfile_maxh, $checksize = 0){

	
	if(!is_file($filepath))  {echo '<b>Missing file:</b> '.$filepath.'<br/>'; return 0;} 
	
	
	$bg = "cccccc"; //"D1F6FF";
	
	$positionX = 0;
	$positionY = 0;
	$positionW = 0;
	$positionH = 0;
	
	//$fullpercent = 50;
	$fullpercent = 100;
	
	
		
	list($width, $height, $type, $attr) = getimagesize($filepath);
	
	/*
	Megapixel-Image?
	$fl = ceil(($width * $height) / 1000000);
	if ($fl > $megapixel_limit){
		if ($showmessage==1) { echo '<br/><b>'.$fl. ' Megapixel; skipped!</b>';}
		return -1;
	}*/
	
	
	
	//------------------------------------------------------------//
	//Werte berechnen:
	
	
	
	//ratio > 1: Querformat
	$orig_ratio = $width / $height;
	if  ($newfile_maxw > 0 AND  $newfile_maxh > 0) {
		$new_ratio = $newfile_maxw / $newfile_maxh;
	} else {
		$new_ratio = $orig_ratio;
	}
	if ($newfile_maxw < 1) {$newfile_maxw = $newfile_maxh * $new_ratio; }
	if ($newfile_maxh < 1) {$newfile_maxh = $newfile_maxw / $new_ratio; }
	
	
	if ($orig_ratio > $new_ratio) {
		//Bild ist breiter als der Rahmen erlaubt
		//echo '<p>breiter: ' .$orig_ratio.' '.$file.'</p>';
		
		$smallheight = $newfile_maxh;
		$smallwidth = $smallheight * $orig_ratio;			
		$ofx = ($newfile_maxw - $smallwidth) / 2;		
		$ofy = 0;
		
		//values without crop:
		$smallwidth2 = $newfile_maxw;	
		$smallheight2 = $smallwidth2 / $orig_ratio;
		$ofx2 = 0;
		$ofy2 = ($newfile_maxh - $smallheight2) / 2; 
		
	} else {
		//Bild ist hoeher als der Rahmen erlaubt
		//echo '<p>hoeher: ' .$orig_ratio.' '.$file.'</p>';
		
		$smallwidth = $newfile_maxw;
		$smallheight = $smallwidth / $orig_ratio;
		$ofx = 0;
		$ofy = ($newfile_maxh - $smallheight) / 3; //Eher oberen Teil, dh /3
		
		//values without crop:
		$smallheight2 = $newfile_maxh;
		$smallwidth2 = $smallheight2 * $orig_ratio;	
		$ofy2 = 0;
		$ofx2 = ($newfile_maxw - $smallwidth2) / 2; 			
	}
	
	
	//mix crped and non-cropped values by percent:
	$f1 = 0.01 * $fullpercent;
	$f2 = 1.0 - $f1;
	$smallwidth = floor(($f1 * $smallwidth) + ($f2 * $smallwidth2));
	$smallheight = floor(($f1 * $smallheight) + ($f2 * $smallheight2));
	$ofx = floor(($f1 * $ofx) + ($f2 * $ofx2));
	$ofy = floor(($f1 * $ofy) + ($f2 * $ofy2));
	
	$newfile_maxw = floor($newfile_maxw);
	$newfile_maxh = floor($newfile_maxh);
	
	//Ausnahme: Bild ist kleiner als newfile
	if ($width <=  $smallwidth AND $height <=  $smallheight) {
		$ofx = 0; $ofy = 0; $smallwidth = $width;  $smallheight = $height; 
		$ofx = floor(($newfile_maxw - $width) / 2);	
		$ofy = floor(($newfile_maxh - $height) / 2);			
	}

	//echo '<br/>resizing: '.$newfilepath. ' to: '.$newfile_maxw.'/'.$newfile_maxh;
	//---------------------------------------------------------
	//Check if resizing is neccessary
	if(is_file($newfilepath) AND $checksize == 1)  {		
		list($nwidth, $nheight, $ntype, $nattr) = getimagesize($newfilepath);
		if ($newfile_maxw == $nwidth AND $newfile_maxh == $nheight) { return 0;}
	} 


	//---------------------------------------------------------
	//resize:

		
	if ($type == 1) { $original = @imagecreatefromgif($filepath); }
	if ($type == 2) { $original = @imagecreatefromjpeg($filepath);}			
	if ($type == 3) { $original = @imagecreatefrompng($filepath); }
		
	
	if ( !isset($original) )  { die('Could not create image'); } //Problem
		

	
	//Now: creat newfile image:
	
	
	if (function_exists('imagecreatetruecolor')) {
		$small = imagecreatetruecolor($newfile_maxw, $newfile_maxh);		
	} else {
		$small = imagecreate($newfile_maxw, $newfile_maxh);
	}
	
	sscanf($bg, '%2x%2x%2x', $red, $green, $blue);
	$b = imagecolorallocate($small, $red, $green, $blue);
	imagefill($small, 0, 0, $b);
	
	//Änderungen der Variablen die für JCrop newfileerstellung anderst sein müssen
	if (!empty ($positionW) && !empty($positionH)) {
		$width = $positionW;
		$height = $positionH;
	}
	
	if (function_exists('imagecopyresampled')) {
		imagecopyresampled($small, $original, $ofx, $ofy, $positionX, $positionY, $smallwidth, $smallheight, $width, $height);
	} else {
		imagecopyresized($small, $original, $ofx, $ofy, $positionX, $positionY, $smallwidth, $smallheight, $width, $height);
	}	
	
	//if(is_file($newfile)) { unlink($newfile); }
	if ($type == 1) { imagegif($small,$newfilepath); }
	if ($type == 2) { imagejpeg($small,$newfilepath); }	
	if ($type == 3) { imagepng($small,$newfilepath); }
		
		
	imagedestroy($original);
	imagedestroy($small);
	return 1;


}

?>
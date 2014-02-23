<?php

function imagettftext_cr(&$im, $size, $angle, $x, $y, $color, $fontfile, $text)
{
	// retrieve boundingbox
	$bbox = imagettfbbox($size, $angle, $fontfile, $text);
	// calculate deviation
	$dx = ($bbox[2]-$bbox[0])/2.0 - ($bbox[2]-$bbox[4])/2.0;         // deviation left-right
	$dy = ($bbox[3]-$bbox[1])/2.0 + ($bbox[7]-$bbox[1])/2.0;        // deviation top-bottom
	// new pivotpoint
	$px = $x-$dx;
	$py = $y-$dy;
	return imagettftext($im, $size, $angle, $px, $y, $color, $fontfile, $text);
}

if(! session_id() ) session_start();
$alphanum = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
$rand = substr( str_shuffle( $alphanum ), 0, 5 );

$image = imagecreate(120,36);
$black = imagecolorallocate($image,0,0,0);
$grey_shade = imagecolorallocate($image,80,80,80);
$white = imagecolorallocate($image,255,255,255);

$otherFont = 'fonts/StardosStencil-Regular.ttf';
$font = 'fonts/StardosStencil-Bold.ttf';

//imagestring( $image, 5, 28, 4, $rand, $white );
//BG text for Name
$i =1;
while($i<10){
	imagettftext_cr($image,rand(2,20),rand(-50,50),rand(10,120),rand(0,40),$grey_shade,$font,$rand);
	$i++;
}

imagettftext_cr($image,14,0,60,26,$white,$font, $rand );
$_SESSION['captcha_random_value'] = md5( $rand );

header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header( "Last-Modified: ".gmdate( "D, d M Y H:i:s" )." GMT" );
header( "Cache-Control: no-store, no-cache, must-revalidate" );
header( "Cache-Control: post-check=0, pre-check=0", false );
header( "Pragma: no-cache" );
header( "Content-type: image/png");
//header( "Content-type: image/jpeg");
imagepng($image);
imagedestroy( $image );
/*
if(! session_id() ) session_start();
$alphanum = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
$rand = substr( str_shuffle( $alphanum ), 0, 5 );
$image = imagecreatefrompng( "images/captcha.png" );
$textColor = imagecolorallocate( $image, 0, 0, 0 );
imagestring( $image, 5, 28, 4, $rand, $textColor );
$_SESSION['image_random_value'] = md5( $rand );
header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header( "Last-Modified: ".gmdate( "D, d M Y H:i:s" )." GMT" );
header( "Cache-Control: no-store, no-cache, must-revalidate" );
header( "Cache-Control: post-check=0, pre-check=0", false );
header( "Pragma: no-cache" );
header( "Content-type: image/png" );
imagepng( $image );
imagedestroy( $image );
*/
?>
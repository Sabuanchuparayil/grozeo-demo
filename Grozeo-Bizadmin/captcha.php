<?php


session_start();
session_regenerate_id();

$num1 = rand(1, 9); //Generate First number between 1 and 9  
$num2 = rand(1, 9); //Generate Second number between 1 and 9  
$captcha_total = $num1 + $num2;

$math = "$num1" . " + " . "$num2" . "=";


$_SESSION['rand_code'] = $captcha_total;

$dir = 'resources/fonts/';

$bk = 'resources/cb/'.((time() % 5) + 1).'.jpg';
//$image = imagecreatetruecolor(120, 25); //Change the numbers to adjust the size of the image
$image = imagecreatefromjpeg($bk);
$black = imagecolorallocate($image, 0, 0, 0);
$color = imagecolorallocate($image, 255,255,255);

//$white = imagecolorallocate($image, 255, 255, 255);
//imagefilledrectangle($image, 0, 0, 399, 99, $white);
imagettftext($image, 15, 0, 20, 18, $color, $dir . "georgia.ttf", $math); //Change the numbers to adjust the font-size

header("Content-type: image/png");
imagepng($image);
?>

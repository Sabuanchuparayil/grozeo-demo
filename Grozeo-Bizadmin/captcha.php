<?php

require_once __DIR__ . '/includes/session_init.php';
grozeoStartSession(false);

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$num1 = rand(1, 9);
$num2 = rand(1, 9);
$captcha_total = $num1 + $num2;

$math = "$num1" . " + " . "$num2" . "=";

$_SESSION['rand_code'] = $captcha_total;

$dir = 'resources/fonts/';

$bk = 'resources/cb/'.((time() % 5) + 1).'.jpg';
$image = imagecreatefromjpeg($bk);
$color = imagecolorallocate($image, 255,255,255);

imagettftext($image, 15, 0, 20, 18, $color, $dir . "georgia.ttf", $math);

header("Content-type: image/png");
imagepng($image);

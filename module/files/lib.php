<?php

function getUploadedFileName($f)
{
	$fn = $_FILES[$f]['tmp_name'];
	if (!$_FILES[$f]['error'] and is_uploaded_file($fn))
		return $fn;
	return false;
}

function imageLoadAny($fn)
{
	if (($im = @imagecreatefromgif($fn)) or ($im = @imagecreatefromjpeg($fn)) or ($im = @imagecreatefrompng($fn)))
		return $im;
	else
		return false;
}

function imageResize($im, $w, $h)
{
	$im2 = imagecreatetruecolor($w, $h);
	imagefilledrectangle($im2, 0, 0, $w, $h, imagecolorallocate($im2, 255, 255, 255));
	$imw = imagesx($im);
	$imh = imagesy($im);
	if (($imw < 10) or ($imh < 10))
		return $im2;
	$coef = min($w / $imw, $h / $imh);
	$im2w = round($imw * $coef);
	$im2h = round($imh * $coef);
	imagecopyresampled($im2, $im, 0, 0, 0, 0, $im2w, $im2h, $imw, $imh);
	return $im2;
}

function imageLoad($f)
{
	if ($fn = getUploadedFileName($f))
		if ($im = imageLoadAny($fn))
			return $im;
	return false;
}

?>
<?php
if (!extension_loaded("gd"))
{
	$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
	dl($prefix . 'gd.' . PHP_SHLIB_SUFFIX);
}

function VN_gif_display(string $str)
{
	$im = @imagecreate(60, 25)
		or die ("Cannot Initialize new GD image stream");
	$background_color = imagecolorallocate($im, 230, 230, 230);
	for ($i = 0; $i < strlen($str); $i++)
	{
		mt_srand(intval(microtime(true) * 1000000));
		$text_color = imagecolorallocate($im, 30 + mt_rand(0, 100), 30 + mt_rand(0, 100), 30 + mt_rand(0, 100));
		imagestring($im, 10 + mt_rand(0, 4), $i * 14 + mt_rand(5, 10), mt_rand(2, 7), $str[$i], $text_color);
	}

	//output image
	header("Content-type: image/png");
	imagepng($im);
}

function VN_gen_str(int $len) : string
{
	$str = "";
	for ($i = 0; $i < $len; $i++)
	{
		mt_srand(intval(microtime(true) * 1000000));
		$num = mt_rand(0, 61);
		$str .= chr($num < 10 ? (ord("0") + $num) : ($num < 36 ? (ord("A") + $num - 10) : (ord("a") + $num - 36)));
	}
	return $str;
}

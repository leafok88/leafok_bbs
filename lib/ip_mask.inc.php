<?php
function ip_mask(string $ip, int $level = 2, string $mask = "*") : string
{
	if ($level <= 0)
	{
		return $ip;
	}
	if ($level > 4)
	{
		$level = 4;
	}

	$ips = explode(".", $ip);
	$ret = "";

	for ($i = 0; $i < 4 - $level; $i++)
	{
		$ret .= ($ips[$i] . ($i < 3 - $level ? "." : ""));
	}

	$ret .= str_repeat("." . $mask, $level);

	return $ret;
}
?>

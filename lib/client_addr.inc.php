<?php
	require_once "../lib/ip_mask.inc.php";
?>
<?php
function client_addr(int $mask_level = 0) : string
{
	$ip = (isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "");

	return ip_mask($ip, $mask_level, "%");
}
?>

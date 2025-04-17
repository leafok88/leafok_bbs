<?
	require_once "./session_init.inc.php";

	force_login();

	$cache_path = "../stat/stat.html";
	$buffer = false;
	if (file_exists($cache_path))
	{
		if (time() - filemtime($cache_path) < $BBS_stat_gen_interval)
		{
			$buffer = file_get_contents($cache_path);
		}
	}
	if ($buffer == false)
	{
		ob_start();

		include "./stat_gen.inc.php";

		$buffer = ob_get_clean();

		file_put_contents($cache_path, $buffer);
	}

	echo $buffer;
?>

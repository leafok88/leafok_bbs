<?
	require_once "../lib/common.inc.php";
?>
<?
function user_level($e)
{
	global $BBS_exp;
	global $BBS_level;

	$l = 0;
	$r = count($BBS_exp) - 1;

	while ($l + 1 < $r)
	{
		$m = intdiv(($l + $r), 2);

		if ($e < $BBS_exp[$m])
		{
			$r = $m;
		}
		else
		{
			$l = $m;
		}
	}

	return $BBS_level[$l];
}
?>

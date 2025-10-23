<?php
require_once "../lib/common.inc.php";

function user_level($e)
{
	global $BBS_exp;
	global $BBS_level;

	$l = 0;
	$r = count($BBS_exp) - 1;

	while ($l < $r)
	{
		$m = intdiv(($l + $r), 2);
		if ($e < $BBS_exp[$m + 1])
		{
			$r = $m;
		}
		else if ($e > $BBS_exp[$m + 1])
		{
			$l = $m + 1;
		}
		else // if ($e == $BBS_exp[$m + 1])
		{
			$l = $m + 1;
			break;
		}
	}

	return $BBS_level[$l];
}

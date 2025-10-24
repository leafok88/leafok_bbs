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
		if ($e < $BBS_exp[$m])
		{
			$r = $m - 1;
		}
		else if ($e > $BBS_exp[$m])
		{
			$l = $m + 1;
		}
		else // if ($e == $BBS_exp[$m])
		{
			$l = $m;
			break;
		}
	}

	if ($e < $BBS_exp[$l])
	{
		$l--;
	}

	return $BBS_level[$l];
}

function test_user_level()
{
	$user_points = array(
		-50, -1, 0, 1, 49, 50, 51, 499, 500, 501, 99999, 100000, 100001);

	for ($i = 0; $i < count($user_points); $i++)
	{
		printf("%10d\t\t%s\n", $user_points[$i], user_level($user_points[$i]));
	}
}

// test_user_level();

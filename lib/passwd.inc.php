<?php
function verify_pass_complexity($password, $username, $min_len)
{
	$num_count = 0;
	$upper_case = 0;
	$lower_case = 0;
	$len = strlen($password);

	if ($len < $min_len)
	{
		return false;
	}

	if (stristr($password, $username) !== false)
	{
		return false;
	}

	for ($i = 0; $i < $len; $i++)
	{
		$c = $password[$i];

		if (ctype_digit($c))
		{
			$num_count++;
		}

		if (ctype_upper($c))
		{
			$upper_case++;
		}

		if (ctype_lower($c))
		{
			$lower_case++;
		}
	}

	if ($upper_case == 0 || $lower_case == 0 || $num_count == 0)
	{
		return false;
	}

	return true;
}

function gen_passwd($len)
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

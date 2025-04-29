<?php
function Date2Astro(int $month, int $day) : string
{
	$dates = array(21, 20, 21, 21, 22, 22, 23, 24, 24, 24, 23, 22);
	$astro = array("摩羯", "水瓶", "双鱼", "白羊", "金牛", "双子", "巨蟹", "狮子", "处女", "天秤", "天蝎", "射手", "摩羯");

	if ($month < 1 || $month >12 || $day < 1 || $day > 31)
	{
		return $astro[0];
	}

	if ($day < $dates[$month - 1])
	{
		return $astro[$month - 1];
	}

	return $astro[$month];
}

<?php
	require_once "../lib/db_open.inc.php";
	require_once "../bbs/session_init.inc.php";

	if (!extension_loaded("gd"))
	{
		$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
		dl($prefix . 'gd.' . PHP_SHLIB_SUFFIX);
	}
?>
<?php
	force_login();

	set_time_limit(60);

	if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		echo ("没有权限！");
		exit();
	}
	 
	$output_str = "";
	$count_max = 0;

	$sql = "SELECT UID, count(*) AS c_count FROM bbs GROUP BY UID ORDER BY c_count DESC LIMIT 10";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query error: " . mysqli_error($db_conn));
		exit();
	}

	$output_str .= "==================\n";
	$output_str .= "UID\tCount\t(Top 10)\n";
	$output_str .= "==================\n";

	while ($row = mysqli_fetch_array($rs))
	{
		$output_str .= $row["UID"] . "\t" . $row["c_count"] . "\n";
		$count_max = ($count_max > $row["c_count"] ? $count_max : $row["c_count"]);
	}

	mysqli_free_result($rs);

	$im_width = 600;
	$im_height = 500;
	$im_zero_x = 50;
	$im_zero_y = 50;
	$last_x = 0;
	$last_y = 0;
	$im_x_ratio = 2;
	$im_y_ratio = 10;
	$last_count_u = 0;
	$user_ratio = 100;

	$im = @imagecreate($im_width, $im_height)
		or die ("Cannot Initialize new GD image stream");
	$background_color = imagecolorallocate($im, 230, 230, 230);
	$color = imagecolorallocate($im, 30, 30, 30);
	$arc_color = imagecolorallocate($im, 255, 30, 30);
	$mark_color = imagecolorallocate($im, 30, 30, 255);

	$sql = "SELECT c_count, COUNT(*) AS d_count FROM
			(SELECT COUNT(*) AS c_count FROM bbs GROUP BY UID ORDER BY c_count DESC) AS topic_count
			GROUP BY c_count ORDER BY c_count DESC, d_count DESC;";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query error: " . mysqli_error($db_conn));
		exit();
	}

	$total_d = 0;
	$total = 0;

	while($row = mysqli_fetch_array($rs))
	{
		$total_d += $row["d_count"];
		$total += ($row["c_count"] * $row["d_count"]);
	}

	mysqli_data_seek($rs, 0);

	$output_str .= "==================\n";
	$output_str .= "Count\t\tTotal\tPercent\tTotal Percent\n";
	$output_str .= "$total_d\t\t$total\t\t100%\n";
	$output_str .= "==================\n";

	$count_a = 0;
	$count_u = 0;

	while ($row = mysqli_fetch_array($rs))
	{
		$i = $row["c_count"];
		$count_d = $row["d_count"];
		$count_a += ($i * $count_d);
		$count_u += $count_d;

		$output_str .= ($count_d . "|" . $count_u . "\t" . $i . "\t" . $count_a . "\t" .
			round(100.0 * $i * $count_d / $total, 1) . "%\t" . round(100.0 * $count_a / $total, 1) . "%\n");

		if ($last_x && $last_y)
		{
			imageline(
				$im,
				$last_x,
				$last_y,
				intdiv(($count_u - $count_d), $im_x_ratio) + $im_zero_x,
				$im_height - intdiv($i, $im_y_ratio) - $im_zero_y,
				$arc_color
			);
		}
		imageline(
			$im,
			intdiv(($count_u - $count_d), $im_x_ratio) + $im_zero_x,
			$im_height - intdiv($i, $im_y_ratio) - $im_zero_y,
			intdiv($count_u, $im_x_ratio) + $im_zero_x,
			$im_height - intdiv($i, $im_y_ratio) - $im_zero_y,
			$arc_color
		);
		$last_x = intdiv($count_u, $im_x_ratio) + $im_zero_x;
		$last_y = $im_height - intdiv($i, $im_y_ratio) - $im_zero_y;
		if (intdiv($count_u, $user_ratio) > $last_count_u)
		{
			imagearc($im, $last_x, $last_y, 3, 3, 1, 360, $mark_color);
			imagestring($im, 1, ($last_x > $im_zero_x ? $last_x + 5 : $im_zero_x + 10), ($last_y > 20 ? $last_y - 20 : 0),
				round(100.0 * $count_a / $total, 1) . "%", $mark_color);
			imagestring($im, 1, ($last_x > $im_zero_x ? $last_x : $im_zero_x + 5), ($last_y > 20 ? $last_y - 10 : 10),
				"($count_u,$i)", $mark_color);
			$last_count_u = intdiv($count_u, $user_ratio) + 1;
		}
	}

	mysqli_free_result($rs);

	$output_str .= "==================\n";

	mysqli_close($db_conn);

	if (file_put_contents("../stat/article_stat.txt", $output_str) == false)
	{
		echo ("Write output file error!");
		exit();
	}

	imageline($im, $im_zero_x, $im_zero_x, $im_zero_x, $im_height - $im_zero_y, $color);
	imageline($im, $im_zero_x, $im_height - $im_zero_y, $im_width - $im_zero_x, $im_height - $im_zero_y, $color);
	
	for ($x = $im_zero_x, $i=0; $x <= $im_width - $im_zero_x; $x += 100, $i += (100 * $im_x_ratio))
	{
		imagearc($im, $x, $im_height - $im_zero_y, 3, 3, 0, 360, $color);
		imagestring($im, 1, $x-5, $im_height - $im_zero_y + 5, $i, $color);
	}
	imagestring($im, 2, $im_width - $im_zero_x - 10, $im_height - $im_zero_y + 15, "User", $color);
	for ($y = $im_height - $im_zero_y, $i = 0; $y >= $im_zero_y; $y -= 100, $i += (100 * $im_y_ratio))
	{
		imagearc($im, $im_zero_x, $y, 3, 3, 0, 360, $color);
		imagestring($im, 1, $im_zero_x - 25, $y - 5, $i, $color);
	}
	imagestring($im, 2, $im_zero_x + 10, $im_zero_y - 5, "Article", $color);

	imagestring($im, 5, 350, 50, "BBS Article Statistics", $color);
	imagestring($im, 2, 350, 75, $BBS_host_name, $color);
	imagestring($im, 2, 350, 100, "Date: " . date("Y-m-d H:i:s"), $color);
	imagestring($im, 2, 350, 120, "Total Users: " . $total_d, $color);
	imagestring($im, 2, 350, 140, "Total Articles: " . $total, $color);

	imagepng($im, "../stat/article_stat.png");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>发帖统计</title>
<link rel="stylesheet" href="css/default.css" type="text/css">
</head>
<body>
<P style="color:brown">发帖统计完成！<br>
<a href="/stat/article_stat.txt" target=_blank>统计报告</a><br>
<a href="/stat/article_stat.png" target=_blank>统计图</a><br>
</P>
</body>
</html>

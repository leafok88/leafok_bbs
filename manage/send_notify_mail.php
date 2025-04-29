<?php
	if (isset($_SERVER["argv"]))
	{
		chdir(dirname($_SERVER["argv"][0]));
	}

	require_once "../lib/common.inc.php";
	require_once "../lib/db_open.inc.php";
	require_once "../lib/send_mail.inc.php";

	// Begin transaction
	$rs = mysqli_query($db_conn, "SET autocommit=0");
	if ($rs == false)
	{
		echo ("Mysqli error: " . mysqli_error($db_conn));
		mysqli_close($db_conn);
		exit();
	}

	$rs = mysqli_query($db_conn, "BEGIN");
	if ($rs == false)
	{
		echo ("Mysqli error: " . mysqli_error($db_conn));
		mysqli_close($db_conn);
		exit();
	}

	$sql = "SELECT user_pubinfo.UID, username, email, life,
			DATEDIFF(NOW(), last_login_dt) AS day,
			DATEDIFF(NOW(), login_notify_dt) AS notify_interval
			FROM user_pubinfo
			INNER JOIN user_list ON user_pubinfo.UID = user_list.UID
			WHERE last_login_dt <= SUBDATE(NOW(), INTERVAL (life - 14) DAY)
			AND login_notify_dt < SUBDATE(NOW(), INTERVAL 1 DAY)
			AND user_list.enable AND verified and p_login
			ORDER BY day DESC";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query user error: " . mysqli_error($db_conn));
		mysqli_close($db_conn);
		exit();
	}

	while ($row = mysqli_fetch_array($rs))
	{
		$remaining_life = $row["life"] - $row["day"];

		if ($remaining_life < 0)
		{
			// Skip dead user
			if (!in_array($row["life"], $BBS_life_immortal))
			{
				continue;
			}

			// Immortal user
			if ($row["notify_interval"] < 14) // Don't notify too frequent
			{
				continue;
			}
			$remaining_life = $row["life"];
		}

		//Send mail
		$from = "";
		$fromname = $BBS_name;
		$to = $row["email"];
		$toname = $row["username"];
		$subject = "相会在枫林";
		$body = $row["username"] . ":\n    您好！\n" .
			"    虽然自从您不辞而别至今已有" . $row["day"] .
			"天了，并且您的帐号将在" . $remaining_life .
			"天后被终止，但我们依然衷心期盼您的归来。\n" .
			"    请点击以下链接访问我们的论坛\n" .
			"https://$BBS_host_name/bbs\n" .
			"    如果您忘记了登陆密码，请使用重置密码功能。\n\n" .
			"$BBS_name\n" . date("Y年m月d日") . "\n";

		$ret = send_mail($from, $fromname, $to, $toname, $subject, $body, $db_conn);

		if ($ret == false)
		{
			echo ("Add email error: " . mysqli_error($db_conn));
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$sql = "UPDATE user_pubinfo SET login_notify_dt = NOW()
				WHERE UID = " . $row["UID"];

		$rs_update = mysqli_query($db_conn, $sql);
		if ($rs_update == false)
		{
			echo ("Update status error: " . mysqli_error($db_conn));
			mysqli_close($db_conn);
			exit();
		}
	}
	mysqli_free_result($rs);

	// Commit transaction
	$rs = mysqli_query($db_conn, "COMMIT");
	if ($rs == false)
	{
		echo ("Mysqli error: " . mysqli_error($db_conn));
		mysqli_close($db_conn);
		exit();
	}

	mysqli_close($db_conn);

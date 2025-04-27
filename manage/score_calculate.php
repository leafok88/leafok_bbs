<?php
	if (isset($_SERVER["argv"]))
	{
		chdir(dirname($_SERVER["argv"][0]));
	}

	require_once "../lib/common.inc.php";
	require_once "../lib/db_open.inc.php";

	if (!isset($_SERVER["argc"]))
	{
		require_once "../bbs/session_init.inc.php";

		force_login();
	}

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	if (!(isset($_SESSION["BBS_priv"]) && $_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
		&& !isset($_SERVER["argc"]))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

   	// Begin transaction
	$rs = mysqli_query($db_conn, "SET autocommit=0");
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Mysqli error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$rs = mysqli_query($db_conn, "BEGIN");
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Mysqli error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "SELECT user_list.UID, exp FROM user_list
			INNER JOIN user_pubinfo ON user_list.UID = user_pubinfo.UID
			WHERE enable";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query exp error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	while ($row = mysqli_fetch_array($rs))
	{
		$sql = "SELECT last_exp, exp_left FROM user_score WHERE UID = " .
				$row["UID"] . " FOR UPDATE";

		$rs_score = mysqli_query($db_conn, $sql);
		if ($rs_score == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query score error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($row_score = mysqli_fetch_array($rs_score))
		{
			$exp_left = $row["exp"] - $row_score["last_exp"] + $row_score["exp_left"];

			$sql = "UPDATE user_score SET last_exp = " . $row["exp"] .
					" WHERE UID = " . $row["UID"];

			$ret = mysqli_query($db_conn, $sql);
			if ($ret == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Update score error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
		else
		{
			$exp_left = $row["exp"];

			$sql = "INSERT INTO user_score(UID, last_exp) VALUES(" .
					$row["UID"] . ", " . $row["exp"] . ")";

			$ret = mysqli_query($db_conn, $sql);
			if ($ret == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Insert score error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}

		mysqli_free_result($rs_score);

		$score_change = intdiv($exp_left, $BBS_exp_score_rate);
		$exp_left %= $BBS_exp_score_rate;

		if ($score_change != 0)
		{
			$sql = "UPDATE user_score SET score = score + ($score_change), exp_left = $exp_left
					WHERE UID = " . $row["UID"];

			$ret = mysqli_query($db_conn, $sql);
			if ($ret == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Update score error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			$sql = "INSERT INTO user_score_log(UID, score_change, reason, dt)
					VALUES(" . $row["UID"] . ", $score_change, '积分计算', NOW())";
			$ret = mysqli_query($db_conn, $sql);
			if ($ret == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Add log error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
	}

	mysqli_free_result($rs);

   	// Commit transaction
	$rs = mysqli_query($db_conn, "COMMIT");
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Mysqli error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_close($db_conn);
	exit(json_encode($result_set));
?>

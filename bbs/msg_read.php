<?php
	require_once "../lib/db_open.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "./session_init.inc.php";
	require_once "./theme.inc.php";

	force_login();

	$uid = (isset($_GET["uid"]) ? intval($_GET["uid"]) : 0);
	$sent = (isset($_GET["sent"]) && $_GET["sent"] == "1");
	$page = (isset($_GET["page"]) ? intval($_GET["page"]) : 1);
	$rpp = (isset($_GET["rpp"]) ? intval($_GET["rpp"]) : 10);

	if (!in_array($rpp, $BBS_msg_rpp_options))
	{
		$rpp = $BBS_msg_rpp_options[0];
	}

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	$nickname = "";
	if ($uid > 0)
	{
		$sql = "SELECT nickname FROM user_pubinfo WHERE UID = $uid";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query user error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($row = mysqli_fetch_array($rs))
		{
			$nickname = $row["nickname"];
		}
		else
		{
			$result_set["return"]["code"] = -1;
			$result_set["return"]["message"] = "用户不存在";
		}
		mysqli_free_result($rs);
	}

	$unread_msg_count = 0;
	if (!$sent)
	{
		$sql = "SELECT COUNT(MID) AS msg_count FROM bbs_msg WHERE " . ($sent ? "fromUID" : "toUID") .
				" = " .	$_SESSION["BBS_uid"] . " AND new AND " . ($sent ? "s_deleted" : "deleted") . " = 0";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query msg count error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($row = mysqli_fetch_array($rs))
		{
			$unread_msg_count = $row["msg_count"];
		}
		mysqli_free_result($rs);
	}


	$sql = "SELECT COUNT(MID) AS msg_count FROM bbs_msg WHERE " . ($sent ? "fromUID" : "toUID") .
			" = " . $_SESSION["BBS_uid"] . " AND " . ($sent ? "s_deleted" : "deleted") . " = 0";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query msg count error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$toa = 0;
	if ($row = mysqli_fetch_array($rs))
	{
		$toa = $row["msg_count"];
	}
	mysqli_free_result($rs);

	$page_total = ceil($toa / $rpp);
	if ($page > $page_total)
	{
		$page = $page_total;
	}

	if ($page <= 0)
	{
		$page = 1;
	}

	// Fill up result data
	$result_set["data"] = array(
		"uid" => $uid,
		"nickname" => $nickname,
		"sent" => $sent,
		"page" => $page,
		"rpp" => $rpp,
		"page_total" => $page_total,
		"msg_count" => $toa,
		"unread_msg_count" => $unread_msg_count,

		"messages" => array(),
	);

	$sql = "SELECT bbs_msg.*, nickname FROM bbs_msg LEFT JOIN user_pubinfo ON " .
			($sent ? "toUID" : "fromUID") . " = user_pubinfo.UID WHERE " .
			($sent ? "fromUID" : "toUID") . " = " . $_SESSION["BBS_uid"] .
			" AND " . ($sent ? "s_deleted" : "deleted") .
			" = 0 ORDER BY MID DESC LIMIT " .
			(($page - 1) * $rpp) . ", $rpp";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query message error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$unread_mid_list = "-1";
	while ($row = mysqli_fetch_array($rs))
	{
		array_push($result_set["data"]["messages"], array(
			"mid" => $row["MID"],
			"uid" => ($sent ? $row["toUID"] : $row["fromUID"]),
			"content" => $row["content"],
			"send_dt" => (new DateTimeImmutable($row["send_dt"]))->setTimezone($_SESSION["BBS_user_tz"]),
			"new" => $row["new"],
			"nickname" => $row["nickname"],
		));

		if (!$sent && $row["new"])
		{
			$unread_mid_list .= (", " . $row["MID"]);
		}

	}
	mysqli_free_result($rs);

	if (!$sent && $unread_mid_list != "-1")
	{
		$sql = "UPDATE bbs_msg SET new = 0 WHERE MID IN ($unread_mid_list)";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update message error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	mysqli_close($db_conn);

	// Cleanup
	unset($uid);
	unset($nickname);
	unset($sent);
	unset($page);
	unset($rpp);
	unset($page_total);
	unset($toa);
	unset($unread_msg_count);
	unset($unread_mid_list);

	// Output with theme view
	$theme_view_file = get_theme_file("view/msg_read", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;

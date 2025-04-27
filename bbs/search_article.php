<?php
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";
	require_once "./theme.inc.php";

	force_login();

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	$uid = (isset($_GET["uid"]) ? intval($_GET["uid"]) : 0);
	$username = (isset($_GET["username"]) ? trim($_GET["username"]) : "");
	$nickname = (isset($_GET["nickname"]) ? trim($_GET["nickname"]) : "");
	$title = (isset($_GET["title"]) ? trim($_GET["title"]) : "");
	$content = (isset($_GET["content"]) ? trim($_GET["content"]) : "");

	if (!isset($_GET["begin_dt"]) || ($begin_dt = DateTimeImmutable::createFromFormat("Y-m-d", $_GET["begin_dt"], $_SESSION["BBS_user_tz"])) == false)
	{
		$begin_dt = new DateTimeImmutable("-1 month", $_SESSION["BBS_user_tz"]);
	}
	if (!isset($_GET["end_dt"]) || ($end_dt = DateTimeImmutable::createFromFormat("Y-m-d", $_GET["end_dt"], $_SESSION["BBS_user_tz"])) == false)
	{
		$end_dt = new DateTimeImmutable("1 day", $_SESSION["BBS_user_tz"]);
	}

	$sid = (isset($_GET["sid"]) ? intval($_GET["sid"]) : 0);
	$ex = (isset($_GET["ex"]) ? intval($_GET["ex"]) : 0);
	$reply = (isset($_GET["reply"]) && $_GET["reply"] == "1" ? 1 : 0);
	$use_nick = (isset($_GET["use_nick"]) && $_GET["use_nick"] == "1" ? 1 : 0);
	$original = (isset($_GET["original"]) && $_GET["original"] == "1" ? 1 : 0);
	$trash = (isset($_GET["trash"]) && $_GET["trash"] == "1" ? 1 : 0);
	$page = (isset($_GET["page"]) ? intval($_GET["page"]) : 1);
	$rpp = (isset($_GET["rpp"]) ? intval($_GET["rpp"]) : 10);

	if ($username != "")
	{
		if (!preg_match("/^[A-Za-z][A-Za-z0-9]{2,11}$/", $username))
		{
			$result_set["return"]["code"] = -1;
			$result_set["return"]["message"] = "用户名格式非法";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$sql = "SELECT UID FROM user_list WHERE LOWER(username) = LOWER('$username')";

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
			$uid = $row["UID"];
		}
		else
		{
			$result_set["return"]["code"] = -1;
			$result_set["return"]["message"] = "用户不存在";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
		mysqli_free_result($rs);
	}

	$sid_list = "-1";
	if ($sid > 0)
	{
		if ($_SESSION["BBS_priv"]->checkpriv($sid, S_LIST))
		{
			if (!$trash || ($trash && ($_SESSION["BBS_priv"]->checkpriv($sid, S_MAN_S))))
			{
				$sid_list = $sid;
			}
		}
	}
	else
	{
		$rs = mysqli_query($db_conn, "SELECT SID FROM section_config WHERE enable" .
			($sid < 0 ? " AND CID = " . (-$sid) : ""));
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query section error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		while ($row = mysqli_fetch_array($rs))
		{
			if ($_SESSION["BBS_priv"]->checkpriv($row["SID"], S_LIST))
			{
				if (!$trash || ($trash && ($_SESSION["BBS_priv"]->checkpriv($row["SID"], S_MAN_S))))
				{
					$sid_list .= (", " . $row["SID"]);
				}
			}
		}
		mysqli_free_result($rs);
	}

	$sql = "SELECT count(*) AS article_count FROM bbs" .
		($content == "" ? "" : " INNER JOIN bbs_content ON bbs.CID = bbs_content.CID") .
		" WHERE SID in ($sid_list) AND " .
		($reply ? "" : "TID = 0 AND ") .
		($uid ? "UID = $uid AND ":"") .
		"visible = " . ($trash ? 0 : 1) . " AND " .
		($nickname == "" ? "" : "nickname LIKE '%" . mysqli_real_escape_string($db_conn, $nickname) . "%' AND ") .
		($ex == 1 ? "excerption AND " : "") .
		($ex == 2 ? "gen_ex AND " : "") .
		($original ? "transship = 0 AND " : "") .
		"(sub_dt BETWEEN '" . $begin_dt->setTimezone($BBS_runtime_tz)->format("Y-m-d") . "' AND '" . $end_dt->setTimezone($BBS_runtime_tz)->format("Y-m-d") . "') AND " .
		"title LIKE '%" . mysqli_real_escape_string($db_conn, $title) . "%'" .
		($content == "" ? "" : " AND content LIKE '%" . mysqli_real_escape_string($db_conn, $content) . "%'");

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query article error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$toa = 0;
	if ($row = mysqli_fetch_array($rs))
	{
		$toa = $row["article_count"];
	}
	mysqli_free_result($rs);

	if (!in_array($rpp, $BBS_list_rpp_options))
	{
		$rpp = $BBS_list_rpp_options[0];
	}

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
		"sid" => $sid,
		"ex" => $ex,
		"reply" => $reply,
		"use_nick" => $use_nick,
		"original" => $original,
		"username" => $username,
		"nickname" => $nickname,
		"title" => $title,
		"content" => $content,
		"begin_dt" => $begin_dt,
		"end_dt" => $end_dt,
		"trash" => $trash,
		"toa" => $toa,
		"page" => $page,
		"rpp" => $rpp,
		"page_total" => $page_total,

		"articles" => array(),
	);

	// Query articles
	$sql = "SELECT bbs.*, section_class.cname, section_class.title AS c_title,
		section_config.sname, section_config.title AS s_title FROM bbs
		INNER JOIN section_config ON bbs.SID = section_config.SID
		INNER JOIN section_class ON section_config.CID = section_class.CID" .
		($content == "" ? "" : " INNER JOIN bbs_content ON bbs.CID = bbs_content.CID") .
		" WHERE bbs.SID in ($sid_list) AND " .
		($reply ? "" : "TID = 0 AND ") .
		($uid ? "UID = $uid AND ":"") .
		"visible = " . ($trash ? 0 : 1) . " AND " .
		($nickname == "" ? "" : "nickname LIKE '%" . mysqli_real_escape_string($db_conn, $nickname) . "%' AND ") .
		($ex == 1 ? "excerption AND " : "") .
		($ex == 2 ? "gen_ex AND " : "") .
		($original ? "transship = 0 AND " : "") .
		"(sub_dt BETWEEN '" . $begin_dt->setTimezone($BBS_runtime_tz)->format("Y-m-d") . "' AND '" . $end_dt->setTimezone($BBS_runtime_tz)->format("Y-m-d") . "') AND " .
		"bbs.title LIKE '%" . mysqli_real_escape_string($db_conn, $title) . "%'" .
		($content == "" ? "" : " AND content LIKE '%" . mysqli_real_escape_string($db_conn, $content) . "%'") .
		" ORDER BY sub_dt DESC" .
		" LIMIT " . (($page - 1) * $rpp) . ", $rpp";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query article list error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$visited_aid_list = array();

	if ($_SESSION["BBS_uid"] > 0)
	{
		$aid_list = "-1";

		while ($row = mysqli_fetch_array($rs))
		{
			if ((new DateTimeImmutable("-" . $BBS_new_article_period . " day")) < (new DateTimeImmutable($row["sub_dt"])))
			{
				$aid_list .= (", " . $row["AID"]);
			}
			else
			{
				array_push($visited_aid_list, $row["AID"]);
			}
		}

		mysqli_data_seek($rs, 0);

		if ($aid_list != "-1")
		{
			$sql = "SELECT AID FROM view_article_log WHERE AID IN ($aid_list) AND UID = " . $_SESSION["BBS_uid"];

			$rs_view = mysqli_query($db_conn, $sql);
			if ($rs_view == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Query view_article_log error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			while ($row_view = mysqli_fetch_array($rs_view))
			{
				array_push($visited_aid_list, $row_view["AID"]);
			}

			mysqli_free_result($rs_view);
		}
	}

	$author_list = array();

	while ($row = mysqli_fetch_array($rs))
	{
		array_push($result_set["data"]["articles"], array(
			"aid" => $row["AID"],
			"tid" => $row["TID"],
			"title" => $row["title"],
			"sub_dt" => (new DateTimeImmutable($row["sub_dt"]))->setTimezone($_SESSION["BBS_user_tz"]),
			"length" => $row["length"],
			"icon" => $row["icon"],
			"uid" => $row["UID"],
			"username" => $row["username"],
			"nickname" => $row["nickname"],
			"reply_count" => $row["reply_count"],
			"view_count" => $row["view_count"],
			"transship" => $row["transship"],
			"lock" => $row["lock"],
			"ontop" => $row["ontop"],
			"excerption" => $row["excerption"],
			"gen_ex" => $row["gen_ex"],
			"last_reply_dt" => (new DateTimeImmutable($row["last_reply_dt"]))->setTimezone($_SESSION["BBS_user_tz"]),
			"last_reply_uid" => $row["last_reply_UID"],
			"last_reply_username" => $row["last_reply_username"],
			"last_reply_nickname" => $row["last_reply_nickname"],
			"class_name" => $row["cname"],
			"class_title" => $row["c_title"],
			"section_name" => $row["sname"],
			"section_title" => $row["s_title"],
			"visited" => (($_SESSION["BBS_uid"] > 0 && in_array($row["AID"], $visited_aid_list)) ? 1 : 0),
		));

		if (!isset($author_list[$row["UID"]]))
		{
			$author_list[$row["UID"]] = true;
		}
		if (!isset($author_list[$row["last_reply_UID"]]))
		{
			$author_list[$row["last_reply_UID"]] = true;
		}
	}
	mysqli_free_result($rs);

	$uid_list = "-1";
	foreach ($author_list as $uid => $status)
	{
		$uid_list .= (", " . $uid);
	}
	unset($author_list);

	$author_list = array();

	$sql = "SELECT UID FROM user_list WHERE UID IN ($uid_list) AND enable";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query user list error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	while ($row = mysqli_fetch_array($rs))
	{
		$author_list[$row["UID"]] = true;
	}
	mysqli_free_result($rs);

	$result_set["data"]["author_list"] = $author_list;
	unset($author_list);

	mysqli_close($db_conn);

	// Cleanup
	unset($uid);
	unset($sid);
	unset($ex);
	unset($reply);
	unset($use_nick);
	unset($original);
	unset($username);
	unset($nickname);
	unset($title);
	unset($content);
	unset($begin_dt);
	unset($end_dt);
	unset($search_topic);
	unset($trash);
	unset($toa);
	unset($page);
	unset($rpp);
	unset($page_total);

	// Output with theme view
	$theme_view_file = get_theme_file("view/search_article", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;
?>

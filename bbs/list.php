<?php
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";
	require_once "./section_list_gen.inc.php";
	require_once "./message.inc.php";
	require_once "./theme.inc.php";

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	$redir = $_SERVER["SCRIPT_NAME"] .
		(isset($_SERVER["QUERY_STRING"]) ? "?" . urlencode($_SERVER["QUERY_STRING"]) : "");

	$sid = (isset($_GET["sid"]) ? intval($_GET["sid"]) : $BBS_default_sid);
	$ex = (isset($_GET["ex"]) && $_GET["ex"] == "1" ? 1 : 0);
	$reply = (isset($_GET["reply"]) && $_GET["reply"] == "1" ? 1 : 0);
	$use_nick = (isset($_GET["use_nick"]) && $_GET["use_nick"] == "0" ? 0 : 1);
	$sort = (isset($_GET["sort"]) ? $_GET["sort"] : "topic");
	$search_text = (isset($_GET["search_text"]) ? $_GET["search_text"] : "");
	$page = (isset($_GET["page"]) ? intval($_GET["page"]) : 1);
	$rpp = (isset($_GET["rpp"]) ? intval($_GET["rpp"]) : 20);

	if (!in_array($rpp, $BBS_list_rpp_options))
	{
		$rpp = $BBS_list_rpp_options[0];
	}

	if (!$_SESSION["BBS_priv"]->checkpriv($sid, S_LIST))
	{
		force_login();
	}

	switch($sort)
	{
		case "topic":
			$sort_sql = "sub_dt DESC"; //sub_dt
			break;
		case "reply":
			$sort_sql = "last_reply_dt DESC";
			break;
		case "hot":
			$sort_sql = "(view_count + reply_count) DESC";
			break;
		default:
			$result_set["return"]["code"] = -1;
			$result_set["return"]["message"] = "不支持的排序方式";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
	}

	$sql = "SELECT section_config.sname, section_config.title AS s_title,
			section_config.announcement, section_class.title AS c_title, section_class.cname
			FROM section_config INNER JOIN section_class ON section_config.CID = section_class.CID
			WHERE section_config.SID = $sid AND section_config.enable AND section_class.enable";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query data error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if($row = mysqli_fetch_array($rs))
	{
		$class_title = $row["c_title"];
		$class_name = $row["cname"];
		$section_name = $row["sname"];
		$section_title = $row["s_title"];
		$announcement = $row["announcement"];
	}
	else
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "版块不存在";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_free_result($rs);

	$search_topic = mysqli_real_escape_string($db_conn, $search_text);

	$sql = "SELECT count(*) AS article_count FROM bbs WHERE SID = $sid AND visible AND " .
		($reply ? "" : " TID = 0 AND ") .
		($ex ? " excerption AND " : "") .
		" title LIKE '%" . $search_topic . "%'";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query data error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$toa = 0;
	if ($row = mysqli_fetch_array($rs))
	{
		$toa = $row["article_count"];
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

	if ($_SESSION["BBS_uid"] > 0 && time() - $_SESSION["BBS_last_msg_check"] >= $BBS_check_msg_interval)
	{
		$_SESSION["BBS_new_msg"] = check_new_msg($_SESSION["BBS_uid"], $db_conn);
		$_SESSION["BBS_last_msg_check"] = time();
	}

	$section_select_options = section_list_gen($db_conn);

	// Fill up result data
	$result_set["data"] = array(
		"redir" => $redir,
		"sid" => $sid,
		"ex" => $ex,
		"reply" => $reply,
		"use_nick" => $use_nick,
		"sort" => $sort,
		"search_text" => $search_text,
		"page" => $page,
		"rpp" => $rpp,
		"page_total" => $page_total,

		"class_title" => $class_title,
		"class_name" => $class_name,
		"section_name" => $section_name,
		"section_title" => $section_title,
		"announcement" => $announcement,
		"section_masters" => array(),

		"section_select_options" => $section_select_options,

		"articles" => array(),
	);

	// Query section master
	$sql = "SELECT user_list.UID, user_list.username, section_master.major FROM section_master
			INNER JOIN user_list ON section_master.UID = user_list.UID
			WHERE SID = $sid AND section_master.enable AND (NOW() BETWEEN begin_dt AND end_dt)
			ORDER BY major DESC";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query section master error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	while ($row = mysqli_fetch_array($rs))
	{
		array_push($result_set["data"]["section_masters"], array(
			"uid" => $row["UID"],
			"username" => $row["username"],
			"major" => $row["major"],
		));
	}
	mysqli_free_result($rs);

	// Query articles
	$sql = "SELECT * FROM bbs WHERE SID = $sid AND visible AND ".
		($reply ? "" : " TID = 0 AND ") .
		($ex ? " excerption AND " : "") .
		" title LIKE '%" . $search_topic . "%'".
		" ORDER BY ontop DESC, ".
		($reply ? "sub_dt DESC" : $sort_sql).
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
	unset($redir);
	unset($sid);
	unset($ex);
	unset($reply);
	unset($use_nick);
	unset($sort);
	unset($search_text);
	unset($search_topic);
	unset($page);
	unset($rpp);
	unset($page_total);

	unset($class_title);
	unset($class_name);
	unset($section_name);
	unset($section_title);
	unset($announcement);

	unset($section_select_options);

	// Output with theme view
	$theme_view_file = get_theme_file("view/list", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;

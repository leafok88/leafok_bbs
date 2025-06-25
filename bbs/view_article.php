<?php
	require_once "../lib/db_open.inc.php";
	require_once "../lib/ip_mask.inc.php";
	require_once "../bbs/session_init.inc.php";
	require_once "../bbs/user_photo_path.inc.php";
	require_once "../bbs/section_list_dst.inc.php";
	require_once "../bbs/theme.inc.php";

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	if (isset($_SERVER["argc"]) && $_SERVER["argc"] == 2)
	{
		$priv_check = false;

		$id = intval($_SERVER["argv"][1]);
		$ex = 1;
		$trash = 0;
		$page = 1;
		$rpp = PHP_INT_MAX;

		$theme_name = "gen_ex"; // CLI mode for gen_ex only
	}
	else
	{
		$priv_check = true;

		$id = (isset($_GET["id"]) ? intval($_GET["id"]) : 0);
		$ex = (isset($_GET["ex"]) && $_GET["ex"] == "1" ? 1 : 0);
		$trash = (isset($_GET["trash"]) && $_GET["trash"] == "1" ? 1 : 0);
		$page = (isset($_GET["page"]) ? intval($_GET["page"]) : 1);
		$rpp = (isset($_GET["rpp"]) ? intval($_GET["rpp"]) : 5);

		if (!in_array($rpp, $BBS_view_rpp_options))
		{
			$rpp = $BBS_view_rpp_options[0];
		}

		$theme_name = (isset($_GET["tn"]) ? trim($_GET["tn"]) : $_SESSION["BBS_theme_name"]);

		// Special themes
		if ($theme_name == "portal")
		{
			$rpp = 1;
		}
		else if ($theme_name == "xml")
		{
			$rpp = PHP_INT_MAX;
		}
	}

	$ip_mask_level = ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S) ? 1 : 2);

	$sql = "SELECT bbs.SID, TID, UID, bbs.title AS title, visible, excerption, ontop, `lock`,
			gen_ex, view_count, reply_count, section_config.title AS s_title FROM bbs
			INNER JOIN section_config ON bbs.SID = section_config.SID
			WHERE AID = $id" .
			($trash ? "" : " AND visible") .
			($ex ? " AND excerption" : "");

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query article error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$sid = $row["SID"];
		$tid = $row["TID"];
		$uid = $row["UID"];
		$title = $row["title"];
		$visible = $row["visible"];
		$section_title = $row["s_title"];
		$excerption = $row["excerption"];
		$ontop = $row["ontop"];
		$lock = $row["lock"];
		$gen_ex = $row["gen_ex"];
		$view_count = $row["view_count"] + 1;
		$reply_count = $row["reply_count"];
	}
	else
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "文章不存在或不可见";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_free_result($rs);

	$aid = $id;

	// Find head of topic
	if ($tid != 0)
	{
		$id = $tid;

		$sql = "SELECT bbs.SID, UID, bbs.title AS title, visible, excerption, ontop, `lock`,
				gen_ex, view_count, reply_count, section_config.title AS s_title FROM bbs
				INNER JOIN section_config ON bbs.SID = section_config.SID
				WHERE AID = $id AND TID = 0" .
				($trash ? "" : " AND visible") .
				($ex ? " AND excerption" : "");

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query topic error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($row = mysqli_fetch_array($rs))
		{
			$sid = $row["SID"];
			$uid = $row["UID"];
			$title = $row["title"];
			$visible = $row["visible"];
			$section_title = $row["s_title"];
			$excerption = $row["excerption"];
			$ontop = $row["ontop"];
			$lock = $row["lock"];
			$gen_ex = $row["gen_ex"];
			$view_count = $row["view_count"] + 1;
			$reply_count = $row["reply_count"];
		}
		else
		{
			$result_set["return"]["code"] = -1;
			$result_set["return"]["message"] = "主题不存在或不可见";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		mysqli_free_result($rs);
	}

	$master = ($_SESSION["BBS_priv"]->checkpriv($sid, S_LIST) && $_SESSION["BBS_priv"]->checkpriv($sid, S_MAN_S));

	if ($priv_check && $_SESSION["BBS_uid"] != $uid &&
		(!$_SESSION["BBS_priv"]->checkpriv($sid, S_LIST) ||	(!$master && !$visible)))
	{
		if ($_SESSION["BBS_uid"] == 0)
		{
			force_login();
		}

		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "无权访问";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Get ID of next article
	$sql = "SELECT AID FROM bbs WHERE AID > $id AND TID = 0 AND SID = $sid" .
			($trash ? ($master ? "" : " AND (visible OR UID=" . $_SESSION["BBS_uid"] . ")") : " AND visible") .
			($ex ? " AND excerption" : "") .
			" ORDER BY AID LIMIT 1";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query next topic error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$next_id = $row["AID"];
	}
	else
	{
		$next_id = 0;
	}
	mysqli_free_result($rs);

	// Get ID of previous article
	$sql = "SELECT AID FROM bbs WHERE AID < $id AND TID = 0 AND SID = $sid" .
			($trash ? ($master ? "" : " AND (visible OR UID=" . $_SESSION["BBS_uid"] . ")") : " AND visible") .
			($ex ? " AND excerption" : "") .
			" ORDER BY AID DESC LIMIT 1";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query previous topic error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$previous_id = $row["AID"];
	}
	else
	{
		$previous_id = 0;
	}
	mysqli_free_result($rs);

	// Get reply list
	$sql = "SELECT AID FROM bbs WHERE TID = $id" .
			($trash ? ($master ? "" : " AND (visible OR UID=" . $_SESSION["BBS_uid"] . ")") : " AND visible") .
			($ex ? " AND excerption" : "") .
			" ORDER BY AID";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query reply list error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$toa = mysqli_num_rows($rs) + 1; // replies + topic

	// Override $page by offset of $aid
	if ($id != $aid)
	{
		$aid_index = 1;
		while ($row = mysqli_fetch_array($rs))
		{
			if ($row["AID"] == $aid)
			{
				break;
			}
			$aid_index++;
		}
		$page = intdiv($aid_index, $rpp) + 1;
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

	$fid = -1;
	$ex_dir = "";
	$ex_name = "";
	if ($gen_ex)
	{
		$sql = "SELECT ex_file.FID, dir, name FROM ex_file
				INNER JOIN ex_dir ON ex_file.FID = ex_dir.FID WHERE AID = $id";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query ex_file error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($row = mysqli_fetch_array($rs))
		{
			$fid = $row["FID"];
			$ex_dir = $row["dir"];
			$ex_name = $row["name"];
		}
		else
		{
			$fid = 0;
			$ex_dir = "";
			$ex_name = "根目录";
		}
		mysqli_free_result($rs);
	}

	// Fill up result data
	$result_set["data"] = array(
		"id" => $id,
		"ex" => $ex,
		"trash" => $trash,
		"tid" => $tid,
		"uid" => $uid,
		"sid" => $sid,
		"title" => $title,
		"section_title" => $section_title,
		"excerption" => $excerption,
		"ontop" => $ontop,
		"lock" => $lock,
		"gen_ex" => $gen_ex,
		"visible" => $visible,
		"view_count" => $view_count,
		"reply_count" => $reply_count,
		"page" => $page,
		"rpp" => $rpp,
		"page_total" => $page_total,
		"fid" => $fid,
		"ex_dir" => $ex_dir,
		"ex_name" => $ex_name,

		"section_ex_dirs" => array(),
		"section_list_options" => ($excerption ? "" : section_list_dst($db_conn, $sid)),

		"articles" => array(),
	);

	// Only show set_ex_file at page 1 in HTTP server mode
	if (!isset($_SERVER["argc"]) && $excerption && $page == 1 && $_SESSION["BBS_priv"]->checkpriv($sid, S_POST | S_MAN_S))
	{
		$sql = "SELECT FID, dir, name FROM ex_dir WHERE SID = $sid AND enable ORDER BY dir";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query ex_dir error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		while ($row = mysqli_fetch_array($rs))
		{
			array_push($result_set["data"]["section_ex_dirs"], array(
				"fid" => $row["FID"],
				"dir" => $row["dir"],
				"name" => $row["name"],
			));
		}
		mysqli_free_result($rs);
	}

	// Query article list
	$sql = "SELECT * FROM bbs WHERE ((bbs.AID = $id AND TID = 0) OR TID = $id)" .
			($trash ? ($master ? "" : " AND (visible OR UID=" . $_SESSION["BBS_uid"] . ")") : " AND visible") .
			($ex ? " AND excerption" : "") .
			" ORDER BY AID LIMIT " . (($page - 1) * $rpp) . ", $rpp";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Read article error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$aid_list = "-1";
	$cid_list = "-1";
	$aid_array = array();

	while($row = mysqli_fetch_array($rs))
	{
		$aid_list .= (", " . $row["AID"]);
		$cid_list .= (", " . $row["CID"]);

		if ($_SESSION["BBS_uid"] > 0 && (new DateTimeImmutable("-" . $BBS_new_article_period . " day")) < (new DateTimeImmutable($row["sub_dt"])))
		{
			$aid_array[$row["AID"]] = true;
		}
	}

	$sql = "SELECT * FROM bbs_content WHERE CID IN ($cid_list) ORDER BY AID";
	$rs_content = mysqli_query($db_conn, $sql);
	if ($rs_content == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Read content error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	$row_content = mysqli_fetch_array($rs_content);

	$sql = "SELECT * FROM upload_file WHERE ref_AID IN ($aid_list)
			AND deleted = 0 AND deny = 0
			ORDER BY ref_AID, AID";
	$rs_attachment = mysqli_query($db_conn, $sql);
	if ($rs_attachment == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Read attachment error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	$row_attachment = mysqli_fetch_array($rs_attachment);

	mysqli_data_seek($rs, 0);

	$author_list = array();

	while($row = mysqli_fetch_array($rs))
	{
		while ($row_content && $row["AID"] > $row_content["AID"])
		{
			$row_content = mysqli_fetch_array($rs_content);
		}

		$article = array(
			"tid" => $row["TID"],
			"aid" => $row["AID"],
			"uid" => $row["UID"],
			"icon" => $row["icon"],
			"visible" => $row["visible"],
			"exp" => $row["exp"],
			"m_del" => $row["m_del"],
			"excerption" => $row["excerption"],
			"transship" => $row["transship"],
			"title" => $row["title"],
			"content" => $row_content["content"],
			"length" => $row["length"],
			"username" => $row["username"],
			"nickname" => $row["nickname"],
			"photo_path" => photo_path($row["UID"], $db_conn),
			"sub_dt" => (new DateTimeImmutable($row["sub_dt"]))->setTimezone($_SESSION["BBS_user_tz"]),
			"sub_ip" => ip_mask($row["sub_ip"], $ip_mask_level),

			"attachments" => array(),
		);

		while ($row_attachment && $row_attachment["ref_AID"] == $row["AID"])
		{
			if ($ex == 0 || $row_attachment["check"] == 1)
			{
				array_push($article["attachments"], array(
					"aid" => $row_attachment["AID"],
					"filename" => $row_attachment["filename"],
					"size" => $row_attachment["size"],
					"check" => $row_attachment["check"],
				));
			}

			$row_attachment = mysqli_fetch_array($rs_attachment);
		}

		array_push($result_set["data"]["articles"], $article);

		unset($article);

		if (!isset($author_list[$row["UID"]]))
		{
			$author_list[$row["UID"]] = true;
		}
	}

	mysqli_free_result($rs_attachment);
	mysqli_free_result($rs_content);
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

	if ($_SESSION["BBS_uid"] > 0)
	{
		$aid_list = "-1";
		foreach ($aid_array as $k => $v)
		{
			$aid_list .= ", $k";
		}

		$sql = "SELECT AID FROM view_article_log
				WHERE AID IN ($aid_list) AND UID = " . $_SESSION["BBS_uid"];

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query view_article_log error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		while ($row = mysqli_fetch_array($rs))
		{
			unset($aid_array[$row["AID"]]);
		}

		mysqli_free_result($rs);

		if (count($aid_array) > 0)
		{
			$first_aid = true;
			foreach ($aid_array as $k => $v)
			{
				if ($first_aid)
				{
					$sql = "INSERT INTO view_article_log(AID, UID, dt) VALUES ";
					$first_aid = false;
				}
				else
				{
					$sql .= ", ";
				}
				$sql .= "($k, " . $_SESSION["BBS_uid"] . ", NOW())";
			}

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Add view_article_log error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
	}

	// Only update view counter in HTTP server mode
	if (!isset($_SERVER["argc"]))
	{
		$sql = "UPDATE bbs SET view_count = view_count + 1 WHERE AID = $id";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update topic error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	mysqli_close($db_conn);

	// Cleanup
	unset($id);
	unset($ex);
	unset($trash);
	unset($aid);
	unset($tid);
	unset($uid);
	unset($sid);
	unset($title);
	unset($section_title);
	unset($excerption);
	unset($gen_ex);
	unset($view_count);
	unset($reply_count);
	unset($visible);
	unset($page);
	unset($rpp);
	unset($page_total);
	unset($fid);
	unset($ex_dir);
	unset($ex_name);

	// Output with theme view
	$theme_view_file = get_theme_file("view/view_article", $theme_name);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;

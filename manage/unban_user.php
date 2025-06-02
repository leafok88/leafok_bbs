<?php
	if (isset($_SERVER["argv"]))
	{
		chdir(dirname($_SERVER["argv"][0]));
	}

	require_once "../lib/common.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "../lib/db_open.inc.php";

	if (!isset($_SERVER["argc"]))
	{
		require_once "../bbs/session_init.inc.php";

		force_login();

		if (!(isset($_SESSION["BBS_priv"]) && $_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S)))
		{
			echo ("没有权限！");
			exit();
		}
	}

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		"data" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

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

	// Check system user
	$sql = "SELECT username, nickname FROM user_list
			INNER JOIN user_pubinfo ON user_list.UID = user_pubinfo.UID
			WHERE user_list.UID = $BBS_sys_uid";

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
		$sys_user = $row["username"];
		$sys_nick = $row["nickname"];
	}
	else
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "系统账户不存在";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	mysqli_free_result($rs);

	// Check expired ban record
	$sql = "SELECT BID, ban_user_list.UID, ban_user_list.SID, username, title FROM ban_user_list
			LEFT JOIN user_list ON ban_user_list.UID = user_list.UID
			LEFT JOIN section_config ON ban_user_list.SID = section_config.SID
			WHERE ban_user_list.enable AND unban_dt <= NOW() AND ban_user_list.UID <> ban_uid";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query ban record error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	while ($row = mysqli_fetch_array($rs))
	{
		switch ($row["SID"])
		{
			case 0:
				$p_name = "发帖权限";
				break;
			case -1:
				$p_name = "登陆权限";
				break;
			case -2:
				$p_name = "消息权限";
				break;
			default:
				$p_name = "发帖权限";
				break;
		}

		$sql = "UPDATE ban_user_list SET enable = 0, unban_UID = $BBS_sys_uid,
				unban_dt = NOW(), unban_ip = '127.0.0.1' WHERE BID = " . $row["BID"];

		$ret = mysqli_query($db_conn, $sql);
		if ($ret == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update ban record error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

   		// Prepare announcement
		$title = ($row["SID"] > 0 ? "" : "[全站]") . "恢复“" . $row["username"] . "”" .
			($row["SID"] > 0 ? "在“" . $row["title"] . "”版块的" : "全站") . $p_name;
		$content = "已恢复用户“" . $row["username"] . "”" .
			($row["SID"] > 0 ? "在“" . $row["title"] . "”版块的" : "全站") . $p_name .
			"。\n执行人: " . $sys_user . "\n";

		// Set user privilege
		$priv_name = "";
		switch ($row["SID"])
		{
			case 0:
				$priv_name = "p_post";
				break;
			case -1:
				$priv_name = "p_login";
				break;
			case -2:
				$priv_name = "p_msg";
				break;
		}

		if ($priv_name != "")
		{
			$sql = "UPDATE user_list SET $priv_name = 1 WHERE UID = " . $row["UID"];

			$ret = mysqli_query($db_conn, $sql);
			if ($ret == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Update user privilege error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}

		$sql = "UPDATE user_online SET current_action = 'reload' WHERE UID = " . $row["UID"];

		$ret = mysqli_query($db_conn, $sql);
		if ($ret == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update user online error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		// Calculate length of content
		$length = str_length(LML($content, false, false, 1024));

		// Post announcement
		$title = mysqli_real_escape_string($db_conn, $title);
		$content = mysqli_real_escape_string($db_conn, $content);

		$sql = "INSERT INTO bbs_content(AID, content) VALUES(0, '$content')";

		$ret = mysqli_query($db_conn, $sql);
		if ($ret == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Add content error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
		$cid = mysqli_insert_id($db_conn);

		$sql = "INSERT INTO bbs(SID, TID, UID, username, nickname, title, CID, sub_dt,
				sub_ip, last_reply_dt, icon, length, excerption)
				VALUES($BBS_notice_sid, 0, $BBS_sys_uid, '$sys_user', '$sys_nick', '$title',
				$cid, NOW(), '127.0.0.1', NOW(), 11, $length, 0)";

		$ret = mysqli_query($db_conn, $sql);
		if ($ret == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Add article error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
		$aid = mysqli_insert_id($db_conn);

		$sql = "UPDATE bbs_content SET AID = $aid WHERE CID = $cid";

		$ret = mysqli_query($db_conn, $sql);
		if ($ret == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update content error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		// Prepare message
		$msg_content = "[hide]SYS_Unban_User[/hide]您" .
			($row["SID"] > 0 ? "在“" . $row["title"] . "”版块的" : "全站") . $p_name .
			"已被恢复。[align right]执行人：[user " . $BBS_sys_uid . "]" .
			$sys_user . "[/user][/align]";

		// Send message
		$msg_content = mysqli_real_escape_string($db_conn, $msg_content);

		$sql = "INSERT INTO bbs_msg(fromUID, toUID, content, send_dt, send_ip)
				VALUES($BBS_sys_uid, " . $row["UID"] . ", '$msg_content', NOW(), '127.0.0.1')";

		$ret = mysqli_query($db_conn, $sql);
		if ($ret == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Insert msg error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		array_push($result_set["return"]["data"], array(
			"bid" => $row["BID"],
			"uid" => $row["UID"],
			"sid" => $row["SID"],
		));
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

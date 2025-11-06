<?php
	require_once "../lib/str_process.inc.php";
	require_once "../lib/db_open.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./article_op.inc.php";
	require_once "./check_sub.inc.php";
	require_once "./session_init.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$uid = (isset($data["uid"]) ? intval($data["uid"]) : 0);
	$sid = (isset($data["sid"]) ? intval($data["sid"]) : -200);
	$ban = (isset($data["ban"]) && $data["ban"] == "1");
	$day = (isset($data["day"]) ? intval($data["day"]) : 0);
	$reason = (isset($data["reason"]) ? $data["reason"] : "");

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	// Validate input data
	if (!isset($_SESSION["BBS_uid"]) || $_SESSION["BBS_uid"] == 0)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有登录";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($sid < -2)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "错误的封禁类型";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if (($sid < 0 && !$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
		|| ($sid >= 0 && !$_SESSION["BBS_priv"]->checkpriv($sid, S_MAN_M)))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($_SESSION["BBS_uid"] == $uid)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "不能对自己操作";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($ban && ($day <= 0 || $day > 365 || $day != $data["day"]))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "期限不符合要求";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($ban && trim($reason) == "")
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "理由必须填写";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$bw_count = 0;
	$r_reason = check_badwords($reason, "****", $bw_count);
	if ($bw_count > 0)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "理由包含非法内容";

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

	// Check user
	$sql = "SELECT username FROM user_list WHERE UID = $uid AND enable";

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
		$ban_user = $row["username"];
	}
	else
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "用户不存在";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	mysqli_free_result($rs);

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

	// Check section
	$section_title = "";
	if ($sid > 0)
	{
		$sql = "SELECT title FROM section_config WHERE SID = $sid";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query section error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($row = mysqli_fetch_array($rs))
		{
			$section_title=$row["title"];
		}
		else
		{
			$result_set["return"]["code"] = -1;
			$result_set["return"]["message"] = "版块不存在";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
		mysqli_free_result($rs);
	}
	else if ($sid == 0)
	{
		$section_title = "本站所有";
	}

	// Check active ban
	$sql = "SELECT BID FROM ban_user_list WHERE UID = $uid
			AND SID = $sid AND enable FOR UPDATE";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query ban record error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$bid = $row["BID"];
	}
	else
	{
		$bid = 0;
	}
	mysqli_free_result($rs);

	switch ($sid)
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

	if ($ban)
	{
		if ($bid != 0)
		{
			$result_set["return"]["code"] = -1;
			$result_set["return"]["message"] = "已存在该类封禁";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$sql = "INSERT INTO ban_user_list(SID, UID, day, ban_uid, ban_dt,
				ban_ip, unban_dt, reason) VALUES($sid, $uid, $day, " .
				$_SESSION["BBS_uid"] . ", NOW(), '" . client_addr() .
				"', ADDDATE(NOW(), INTERVAL $day DAY), '" .
				mysqli_real_escape_string($db_conn, $reason) . "')";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Add ban record error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		//Subtract exp
		$rs = user_exp_change($uid, ($sid ? -50 : -300), $db_conn);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Subtract exp error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		// Prepare announcement
		$title = ($sid > 0 ? "" : "[全站]") . "封禁“" . $ban_user . "”".
			($sid > 0 ? "在“" . $section_title . "”版块的" : "全站") . $p_name;
		$content = "用户“" . $ban_user . "”因：\n" . $reason . "\n应被封禁" .
			($sid > 0 ? "在“" . $section_title . "”版块的"  : "全站") . $p_name .
			$day . "天。\n如不服本决定, 可在7日内申请复议。\n" .
			"执行人: " . $_SESSION["BBS_username"] . "\n";
	}
	else // if (!ban)
	{
		if ($bid == 0)
		{
			$result_set["return"]["code"] = -1;
			$result_set["return"]["message"] = "不存在该类封禁";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$sql = "UPDATE ban_user_list SET unban_uid = " . $_SESSION["BBS_uid"] .
				", unban_dt = NOW(), unban_ip = '" . client_addr() .
				"', enable = 0 WHERE BID = $bid";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update ban record error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		// Prepare announcement
		$title = ($sid > 0 ? "" : "[全站]") . "恢复“" . $ban_user . "”" .
			($sid > 0 ? "在“" . $section_title . "”版块的" : "全站") . $p_name;
		$content = "已恢复用户“" . $ban_user . "”" .
			($sid > 0 ? "在“" . $section_title . "”版块的" : "全站") . $p_name .
			"。\n执行人: " . $_SESSION["BBS_username"] . "\n";
	}

	// Set user privilege
	$priv_name = "";
	switch($sid)
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
		$sql = "UPDATE user_list SET $priv_name = " . ($ban ? 0 : 1) . " WHERE UID = $uid";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update user privilege error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	$sql = "UPDATE user_online SET current_action = '".
			($ban && $sid == -1 ? "exit" : "reload") ."' WHERE UID = $uid";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update user online error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Calculate length of content
	$length = str_length($content, true);

	// Post announcement
	$title = mysqli_real_escape_string($db_conn, $title);
	$content = mysqli_real_escape_string($db_conn, $content);

	$sql = "INSERT INTO bbs_content(AID, content) VALUES(0, '$content')";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
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
			$cid, NOW(), '" . client_addr() . "', NOW(), " .
			($ban ? 9 : 11) . ", $length, " . ($ban && $sid <= 0 ? 1 : 0) . ")";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add article error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	$aid = mysqli_insert_id($db_conn);

	$sql = "UPDATE bbs_content SET AID = $aid WHERE CID = $cid";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update content error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Prepare message
	if ($ban)
	{
		$msg_content = "您" .
			($sid > 0 ? "在“" . $section_title . "”版块的" : "全站") . $p_name .
			"已被封禁，详见[article " . $aid . "]处罚公告[/article]。" .
			"[align right]执行人：[user " . $_SESSION["BBS_uid"] . "]" .
			$_SESSION["BBS_username"] . "[/user][/align]";
	}
	else
	{
		$msg_content = "您" .
			($sid > 0 ? "在“" . $section_title . "”版块的" : "全站") . $p_name .
			"已被恢复。[align right]执行人：[user " . $_SESSION["BBS_uid"] . "]" .
			$_SESSION["BBS_username"] . "[/user][/align]";
	}

	// Send message
	$msg_content = mysqli_real_escape_string($db_conn, $msg_content);

	$sql = "INSERT INTO bbs_msg(fromUID, toUID, content, send_dt, send_ip)
			VALUES($BBS_sys_uid, $uid, '$msg_content', NOW(), '" .
			client_addr() . "')";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Insert msg error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

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

<?php
	require_once "../lib/db_open.inc.php";
	require_once "./article_op.inc.php";
	require_once "./session_init.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$id = (isset($data["id"]) ? intval($data["id"]) : 0);

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	if (!isset($_SESSION["BBS_uid"]) || $_SESSION["BBS_uid"] == 0)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有登录";

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

	$sql = "SELECT UID, TID, SID, visible, excerption FROM bbs WHERE AID = $id FOR UPDATE";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query article error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if($row = mysqli_fetch_array($rs))
	{
		$uid = $row["UID"];
		$tid = $row["TID"];
		$sid = $row["SID"];
		$visible = $row["visible"];
		$excerption = $row["excerption"];
	}
	else
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "文章不存在";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	mysqli_free_result($rs);

	// Check if already deleted
	if (!$visible)
	{
		$result_set["return"]["code"] = 1;
		$result_set["return"]["message"] = "已设置";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($excerption)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "收录文章不可删除";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if (!($_SESSION["BBS_priv"]->checkpriv($sid, S_POST) &&
		($_SESSION["BBS_priv"]->checkpriv($sid, S_MAN_S) || $_SESSION["BBS_uid"] == $uid)))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "UPDATE bbs SET visible = 0, reply_count = 0" .
			($uid == $_SESSION["BBS_uid"] ? "" : ", m_del = 1") .
			" WHERE (AID = $id OR TID = $id) AND visible";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update article error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Update exp
	$exp_change = ($uid == $_SESSION["BBS_uid"] ? ($tid == 0 ? -20 : -5) : ($tid == 0 ? -50 : -15));

	$rs = user_exp_change($uid, $exp_change, $db_conn);

	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Change exp error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($uid != $_SESSION["BBS_uid"]) // Delete by admin
	{
		//Send alarm message
		$msg_content = "您所发表的[article $id]$id" .
			"[/url]号文章，违反了本论坛的相关规定，现已被移至“回收站”。" .
			"[align right]执行人：[user " .	$_SESSION["BBS_uid"] . "]" .
			$_SESSION["BBS_username"] . "[/user][/align]";

		$sql = "INSERT INTO bbs_msg(fromUID, toUID, content, send_dt, send_ip)
				VALUES($BBS_sys_uid, $uid, '" .
				mysqli_real_escape_string($db_conn, $msg_content) .
				"', NOW(), '" . client_addr() . "')";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Add message error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	//Add log
	$rs = article_op_log($id, $_SESSION["BBS_uid"], ($uid == $_SESSION["BBS_uid"] ? "D" : "X"), client_addr(), $db_conn);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add log error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	//Set reply count
	if ($tid != 0)
	{
		$sql = "UPDATE bbs SET reply_count = reply_count - 1 WHERE AID = $tid";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update article error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
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

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

	$sql = "SELECT UID, SID, transship, excerption FROM bbs
			WHERE AID = $id AND TID = 0 AND visible
			FOR UPDATE";

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
		$sid = $row["SID"];
		$transship = $row["transship"];
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

	if (!($_SESSION["BBS_priv"]->checkpriv($sid, S_POST | S_MAN_S)) || $row["excerption"])
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Check if already set
	if ($transship == 1)
	{
		$result_set["return"]["code"] = 1;
		$result_set["return"]["message"] = "已设置";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "UPDATE bbs SET transship = 1 WHERE AID = $id";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Set transship error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	//Subtract exp
	$rs = user_exp_change($uid, -30, $db_conn);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Subtract exp error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	//Send alarm message
	$msg_content = "[hide]SYS_Transship_Article[/hide]您所发表的[article $id]$id" .
		"[/article]号文章，违反了本论坛的相关规定，现已被设为转载。" .
		"[align right]执行人：[user " . $_SESSION["BBS_uid"] . "]" .
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

	//Add log
	$rs = article_op_log($id, $_SESSION["BBS_uid"], "Z", client_addr(), $db_conn);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add log error: " . mysqli_error($db_conn);

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
?>

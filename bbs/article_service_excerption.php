<?php
	require_once "../lib/db_open.inc.php";
	require_once "./article_op.inc.php";
	require_once "./session_init.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$id = (isset($data["id"]) ? intval($data["id"]) : 0);
	$set = (isset($data["set"]) && $data["set"] == "1" ? 1 : 0);

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

	$sql = "SELECT UID, TID, SID, transship, excerption FROM bbs
			WHERE AID = $id AND visible FOR UPDATE";

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

	if (!($_SESSION["BBS_priv"]->checkpriv($sid, S_POST | S_MAN_S)))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Check if already set
	if ($excerption == $set)
	{
		$result_set["return"]["code"] = 1;
		$result_set["return"]["message"] = "已设置";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "UPDATE bbs SET excerption = $set WHERE AID = $id";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Set excerption error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Clear gen_ex if unset excerption
	if ($set == 0)
	{
		$sql = "UPDATE bbs SET gen_ex = 0, static = 0 WHERE AID = $id OR TID = $id";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Set gen_ex error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		//Delete ex_dir path if head of thread
		if ($tid == 0)
		{
			$sql = "DELETE FROM ex_file WHERE AID = $id";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Delete ex_file error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
	}

	//Change UID of attachments
	$sql = "UPDATE upload_file SET UID = " . ($set ? 0 : $uid) .
			" WHERE ref_AID = $id AND deleted = 0";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Set attachment status error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	//Modify exp
	if ($_SESSION["BBS_priv"]->checkpriv($sid, S_GETEXP)) //Except in test section
	{
		$rs = user_exp_change($uid, ($set ? 1 : -1) * ($tid == 0 ? ($transship ? 20 : 50) : 10), $db_conn);

		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Change exp error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	//Add log
	$rs = article_op_log($id, $_SESSION["BBS_uid"], ($set ? "E" : "O"), client_addr(), $db_conn);
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

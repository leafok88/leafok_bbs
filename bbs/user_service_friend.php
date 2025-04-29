<?php
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$uid = (isset($data["uid"]) ? intval($data["uid"]) : 0);
	$set = (isset($data["set"]) && $data["set"] == "1" ? 1 : 0);

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

	if ($uid == $_SESSION["BBS_uid"])
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "不能对自己操作";

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

	$sql = "SELECT UID FROM user_list WHERE UID = $uid AND enable";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query user error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if(mysqli_num_rows($rs) == 0)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "用户不存在";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	mysqli_free_result($rs);

	$sql = "SELECT ID FROM friend_list WHERE UID = " .
			$_SESSION["BBS_uid"] . " AND fUID = $uid FOR UPDATE";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query friend error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($row = mysqli_fetch_array($rs))
	{
		if ($set == 0)
		{
			$sql = "DELETE FROM friend_list WHERE ID = " . $row["ID"];

			$ret = mysqli_query($db_conn, $sql);
			if ($ret == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Delete friend error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
		else // $set == 1
		{
			$result_set["return"]["code"] = 1;
			$result_set["return"]["message"] = "已添加";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}
	else
	{
		if ($set == 1)
		{
			$sql = "INSERT INTO friend_list(UID, fUID) VALUES(".
					$_SESSION["BBS_uid"].", $uid)";

			$ret = mysqli_query($db_conn, $sql);
			if ($ret == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Add friend error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
		else // $set == 0
		{
			$result_set["return"]["code"] = 1;
			$result_set["return"]["message"] = "已删除";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
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

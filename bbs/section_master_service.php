<?
	require_once "../lib/db_open.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./session_init.inc.php";
	require_once "./check_sub.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$sid = (isset($data["sid"]) ? intval($data["sid"]) : 0);
	$op = (isset($data["op"]) ? intval($data["op"]) : 0);
	$username = (isset($data["username"]) ? trim($data["username"]) : "");
	$type = (isset($data["type"]) && $data["type"] == "1" ? 1 : 0);
	
	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	// Validate input data
	if (!preg_match("/^[A-Za-z][A-Za-z0-9]{2,11}$/", $username))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "username",
			"errMsg" => "用户名不符合格式要求",
		));
	}

	if ($result_set["return"]["code"] != 0)
	{
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if (!$_SESSION["BBS_priv"]->checkpriv($sid, S_MAN_M)
		|| ($type == 1 && (!$_SESSION["BBS_priv"]->checkpriv($sid, S_ADMIN))))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "master",
			"errMsg" => "没有权限",
		));

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

	// Check user status
	$sql = "SELECT UID FROM user_list WHERE username = '$username' AND verified";

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
		array_push($result_set["return"]["errorFields"], array(
			"id" => "username",
			"errMsg" => "用户不存在或尚未验证",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	mysqli_free_result($rs);

	// Check section status
	$sql = "SELECT SID FROM section_config INNER JOIN section_class
			WHERE SID = $sid AND section_config.enable AND section_class.enable";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query section master error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if (mysqli_num_rows($rs) == 0)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "master",
			"errMsg" => "版块不存在",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	mysqli_free_result($rs);

	// Check existing section master
	$has_major = false;
	$user_found = false;
	$user_type = 0;

	$sql = "SELECT UID, major FROM section_master
			WHERE SID = $sid AND enable AND (NOW() BETWEEN begin_dt AND end_dt)";
	
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
		if ($uid == $row["UID"])
		{
			$user_found = true;
			$user_type = $row["major"];
		}
	
		if (!$has_major && $row["major"])
		{
			$has_major = true;
		}
	}
	mysqli_free_result($rs);
	
	if ($user_found && $op == 1)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "master",
			"errMsg" => "用户已经是版主",
		));
	
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($op == 2 || $op == 3)
	{
		if (!$user_found)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "master",
				"errMsg" => "未找到记录",
			));
		
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($user_type == 1 && (!$_SESSION["BBS_priv"]->checkpriv($sid, S_ADMIN)))
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "master",
				"errMsg" => "没有管理员权限",
			));
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	switch($op)
	{
		case 1: // Appoint
			if ($type == 1 && $has_major)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "master",
					"errMsg" => "只能有一位正版主",
				));

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			$sql = "INSERT INTO section_master(UID, SID, begin_dt, end_dt, enable, major)
					VALUES($uid, $sid, NOW(), ADDDATE(NOW(), INTERVAL 6 MONTH), 1, $type)";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Add section master error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			break; // case 1: Appoint
		case 2: // Dismiss
			$sql = "UPDATE section_master SET enable = 0, end_dt = NOW()
					WHERE UID = $uid AND SID = $sid AND enable
					AND (NOW() BETWEEN begin_dt AND end_dt)";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Update section master error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			break; // case 2: Dismiss
		case 3: // Renew
			$sql = "UPDATE section_master SET end_dt = ADDDATE(end_dt, INTERVAL 6 MONTH)
					WHERE UID = $uid AND SID = $sid AND enable
					AND (NOW() BETWEEN begin_dt AND end_dt)";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Update section master error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			break; // case 3 : Renew
		default: // Invalid Op
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "master",
				"errMsg" => "非法操作",
			));

			mysqli_close($db_conn);
			exit(json_encode($result_set));

			break; // default: Invalid Op
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

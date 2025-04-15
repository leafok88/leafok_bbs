<?
	require_once "../lib/db_open.inc.php";
	require_once "./common_lib.inc.php";
	require_once "./session_init.inc.php";

	force_login();

	$data = json_decode(file_get_contents("php://input"), true);

	$confirm = (isset($data["confirm"]) && $data["confirm"] == "1");

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	if (!$confirm)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "confirm",
			"errMsg" => "需要勾选确认",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if (!$_SESSION["BBS_priv"]->checkpriv(0, S_POST) ||
		$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S | P_MAN_M | P_MAN_S))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "confirm",
			"errMsg" => "没有权限",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($_SESSION["BBS_login_tm"] < time() - 60) // login earlier than 1 minute
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "confirm",
			"errMsg" => "需要再次登录验证",
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

	// Commit suicide
	$sql = "UPDATE user_pubinfo SET life = 60 WHERE UID = " . $_SESSION["BBS_uid"];

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update user_pubinfo error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "INSERT INTO user_life_log(UID, set_UID, life, dt, ip)
			VALUES(" . $_SESSION["BBS_uid"] . ", " . $_SESSION["BBS_uid"] . ", 60, NOW(), '".
			client_addr() . "')";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add log error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "INSERT INTO ban_user_list(SID, UID, day, ban_uid, ban_dt, ban_ip, unban_dt, reason)
			VALUES(-1, " . $_SESSION["BBS_uid"] . ", 365, " . $_SESSION["BBS_uid"] .
			", NOW(), '" . client_addr() . "', ADDDATE(NOW(), INTERVAL 1 YEAR), '关闭账户')";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Insert ban error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "UPDATE user_list SET p_login = 0 WHERE UID = " . $_SESSION["BBS_uid"];

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update user privilege error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "UPDATE user_online SET current_action = 'exit' WHERE UID = " . $_SESSION["BBS_uid"];

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update user online error: " . mysqli_error($db_conn);

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

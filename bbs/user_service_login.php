<?php
	require_once "../lib/db_open.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/passwd.inc.php";
	require_once "../lib/vn_gif.inc.php";
	require_once "../lib/client_addr.inc.php";
	require_once "../lib/ip_mask.inc.php";
	require_once "./session_init.inc.php";
	require_once "./user_login.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$username = (isset($data["username"]) ? trim($data["username"]) : "");
	$password = (isset($data["password"]) ? trim($data["password"]) : "");
	$ch_passwd = (isset($data["ch_passwd"]) && $data["ch_passwd"] == "1" ? 1 : 0);
	$password_new = (isset($data["password_new"]) ? trim($data["password_new"]) : "");
	$agreement = (isset($data["agreement"]) && $data["agreement"] == "1");
	$mfa = (isset($data["mfa"]) && $data["mfa"] == "1" ? 1 : 0);
	$vn_str = (isset($data["vn_str"]) ? trim($data["vn_str"]) : "");

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	// Validate input data
	if (!preg_match("/^[A-Za-z][A-Za-z0-9_]{2,11}$/", $username))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "username",
			"errMsg" => "不符合格式要求",
		));
	}

	if (!preg_match("/^[A-Za-z0-9]{5,12}$/", $password))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "password",
			"errMsg" => "不符合格式要求",
		));
	}

	if ($ch_passwd)
	{
		if (!preg_match("/^[A-Za-z0-9]{6,12}$/", $password_new))
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "password_new",
				"errMsg" => "不符合格式要求",
			));
		}

		if (!verify_pass_complexity($password_new, $username, 6))
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "password_new",
				"errMsg" => "不符合复杂性要求",
			));
		}
	}

	if ($mfa)
	{
		if ((!isset($_SESSION["BBS_vn_str"])) || $_SESSION["BBS_vn_str"] == "" || strcasecmp($_SESSION["BBS_vn_str"], $vn_str) != 0)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "vn_str",
				"errMsg" => "验证码错误",
			));
		}
	}

	if ($result_set["return"]["code"] != 0)
	{
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

	if (!$mfa)
	{
		// Failed login attempts from the same source (subnet /24) during certain time period
		$sql = "SELECT COUNT(*) AS err_count FROM user_err_login_log
				WHERE login_dt >= SUBDATE(NOW(), INTERVAL 10 MINUTE)
				AND login_ip LIKE '" . client_addr(1) . "'";
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query login log error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($row = mysqli_fetch_array($rs))
		{
			if ($row["err_count"] >= 10)
			{
				$result_set["return"]["code"] = 1;
				$result_set["return"]["message"] = "来源存在多次失败登陆尝试，请输入验证码";

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
		mysqli_free_result($rs);

		// Failed login attempts against the current username since last successful login
		$sql = "SELECT COUNT(*) AS err_count FROM user_err_login_log
				LEFT JOIN user_list ON user_err_login_log.username = user_list.username
				LEFT JOIN user_pubinfo ON user_list.UID = user_pubinfo.UID
				WHERE user_err_login_log.username = '$username'
				AND (user_err_login_log.login_dt >= user_pubinfo.last_login_dt
				OR user_pubinfo.last_login_dt IS NULL)";
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query login log error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($row = mysqli_fetch_array($rs))
		{
			if ($row["err_count"] >= 3)
			{
				$result_set["return"]["code"] = 1;
				$result_set["return"]["message"] = "账户存在多次失败登陆尝试，请输入验证码";

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
		mysqli_free_result($rs);
	}

	$sql = "SELECT UID, username, p_login, verified, temp_password,
			password = MD5('$password') AS old_pass
			FROM user_list WHERE username = '$username' AND
			(password = MD5('$password') OR password = SHA2('$password', 256) OR
			temp_password = '$password')
			AND enable FOR UPDATE";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query user list error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$uid = 0;

	if ($row = mysqli_fetch_array($rs))
	{
		$uid = intval($row["UID"]);
		$username = $row["username"];

		if ($password == $row["temp_password"] && !$ch_passwd)
		{
			$result_set["return"]["code"] = 2;
			$result_set["return"]["message"] = "使用临时密码登录需设置新密码";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($ch_passwd)
		{
			if ($password == $row["temp_password"]) // New user first time login with temp password
			{
				$verified = 1;

				// Set life = 150 for verified user
				$sql = "UPDATE user_pubinfo SET life = 150 WHERE UID = $uid";
				$rs_life = mysqli_query($db_conn, $sql);
				if ($rs_life == false)
				{
					$result_set["return"]["code"] = -2;
					$result_set["return"]["message"] = "Update user life error: " . mysqli_error($db_conn);

					mysqli_close($db_conn);
					exit(json_encode($result_set));
				}
			}
			else
			{
				$verified = $row["verified"];
			}

			$sql = "UPDATE user_list SET password = SHA2('$password_new', 256),
					temp_password = '', verified = $verified WHERE UID = $uid";
			$rs_p = mysqli_query($db_conn, $sql);
			if ($rs_p == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Update password error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
		else if ($row["old_pass"])
		{
			$sql = "UPDATE user_list SET password = SHA2('$password', 256) WHERE UID = $uid";
			$rs_p = mysqli_query($db_conn, $sql);
			if ($rs_p == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Upgrade password error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}

		mysqli_free_result($rs);

		// Add user login log
		$sql = "INSERT INTO user_login_log(uid, login_dt, login_ip) VALUES($uid, NOW(), '" .
				client_addr() . "')";
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Write log error: " . mysqli_error($db_conn);

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

		// Forbidden user
		if (!$row["p_login"])
		{
			$result_set["return"]["code"] = 3;
			$result_set["return"]["message"] = "您已被封禁全站登陆权限！";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}
	else
	{
		// Log login failure
		$sql = "INSERT INTO user_err_login_log(username, password, login_dt, login_ip)
				VALUES('$username', '$password', NOW(), '" . client_addr() . "')";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Write log error: " . mysqli_error($db_conn);

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

		$_SESSION["BBS_vn_str"] = ""; // Force change vn_str

		$result_set["return"]["code"] = 3;
		$result_set["return"]["message"] = "用户名或密码不正确";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// SET AUTOCOMMIT = 1
	$rs = mysqli_query($db_conn, "SET autocommit=1");
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Mysqli error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	//Load User Information
	$ret = load_user_info($uid, $db_conn);
	switch($ret)
	{
		case -1:
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "User data not found: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		case -2:
			if (!$agreement)
			{
				$buffer = file_get_contents("./doc/license/" . (new DateTime($BBS_license_dt))->format("Ymd") . ".txt");

				$result_set["return"]["code"] = 4;
				$result_set["return"]["message"] = LML($buffer, 1024, false);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
			break;
		case -3:
			$result_set["return"]["code"] = 3;
			$result_set["return"]["message"] = "很遗憾，您已经永远离开了我们的世界……";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
	}

	$sql = "UPDATE user_pubinfo SET visit_count = visit_count + 1,
			last_login_dt = NOW() WHERE UID = $uid";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update login info error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$_SESSION["BBS_uid"] = $uid;
	$_SESSION["BBS_username"] = $username;
	$_SESSION["BBS_login_tm"] = time();
	$_SESSION["BBS_vn_str"] = "";

	if (!keep_alive($db_conn))
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Keep alive error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_close($db_conn);
	exit(json_encode($result_set));

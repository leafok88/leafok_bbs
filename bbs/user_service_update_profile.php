<?php
	require_once "../lib/common.inc.php";
	require_once "../lib/db_open.inc.php";
	require_once "../lib//score_change.inc.php";
	require_once "../lib/send_mail.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "../lib/passwd.inc.php";
	require_once "./session_init.inc.php";
	require_once "./user_reg_check.inc.php";

	force_login();

	$data = json_decode(file_get_contents("php://input"), true);

	$nickname = (isset($data["nickname"]) ? trim($data["nickname"]) : "");
	$realname = (isset($data["realname"]) ? trim($data["realname"]) : "");
	$gender = (isset($data["gender"]) ? $data["gender"] : "");
	$gender_public = (isset($data["gender_public"]) && $data["gender_public"] == "1" ? 1 : 0);
	$email = (isset($data["email"]) ? trim($data["email"]) : "");
	$year = (isset($data["year"]) ? intval($data["year"]) : 0);
	$month = (isset($data["month"]) ? intval($data["month"]) : 0);
	$day = (isset($data["day"]) ? intval($data["day"]) : 0);
	$qq = (isset($data["qq"]) ? trim($data["qq"]) : "");

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	// Validate input data
	if ($nickname == "" || preg_match("/[[:space:]]/", $nickname) || str_length($nickname) > 20)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "nickname",
			"errMsg" => "不符合格式要求",
		));
	}
	else if (!check_str($nickname) && !$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "nickname",
			"errMsg" => "昵称不可用",
		));
	}

	if ($realname == "" || preg_match("/[\t\r\n]/", $realname) || str_length($realname) > 10)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "realname",
			"errMsg" => "不符合格式要求",
		));
	}

	if ($gender != "M" && $gender != "F")
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "gender",
			"errMsg" => "未指定性别",
		));
	}

	if (!preg_match("/^[A-Za-z0-9_.-]+@([A-Za-z0-9-]+[.])+[A-Za-z0-9-]+$/", $email))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "email",
			"errMsg" => "不符合格式要求",
		));
	}

	if (!checkdate($month, $day, $year))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "birthday",
			"errMsg" => "非法日期",
		));
	}
	else if ((new DateTimeImmutable("$year-$month-$day")) > (new DateTimeImmutable("-16 year")))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "birthday",
			"errMsg" => "需年满16周岁才能使用本站服务",
		));
	}

	if ($qq != "" && !preg_match("/^[0-9]{5,11}$/", $qq))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "qq",
			"errMsg" => "不符合格式要求",
		));
	}

	if ($result_set["return"]["code"] != 0)
	{
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Secure SQL statement
	$nickname = mysqli_real_escape_string($db_conn, $nickname);
	$realname = mysqli_real_escape_string($db_conn, $realname);

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

	$sql = "SELECT nickname, email FROM user_pubinfo WHERE UID = " . $_SESSION["BBS_uid"] .
			" FOR UPDATE";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query user info error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$old_nickname = $row["nickname"];
		$old_email = $row["email"];
	}
	else
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "个人资料不存在";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_free_result($rs);

	// Update nickname
	if ($old_nickname != $nickname)
	{
		$sql = "SELECT DISTINCT UID FROM user_nickname WHERE nickname = '$nickname'";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query nickname error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$free_change = false;
		if ($row = mysqli_fetch_array($rs))
		{
			if ($row["UID"] == $_SESSION["BBS_uid"]) // Re-use old nickname
			{
				$free_change = true;
			}
			else // Unavailable nickname
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "nickname",
					"errMsg" => "昵称已存在",
				));

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
		mysqli_free_result($rs);

		if (!$free_change)
		{
			$ret = score_change($_SESSION["BBS_uid"], -abs($BBS_nickname_change_fee), "更改昵称", $db_conn);
			if ($ret < 0)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Query score error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
			else if ($ret > 0)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "nickname",
					"errMsg" => "积分不足",
				));

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}

		$sql = "UPDATE user_nickname SET end_dt = NOW(), end_reason = 'C'
				WHERE UID = " . $_SESSION["BBS_uid"] . " AND end_dt IS NULL";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update old nickname error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$sql = "INSERT INTO user_nickname(UID, nickname, begin_dt, begin_reason)
				VALUES(" . $_SESSION["BBS_uid"] . ", '$nickname', NOW(), 'C')";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Insert new nickname error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$sql = "UPDATE user_pubinfo SET nickname = '$nickname' WHERE UID = " .
				$_SESSION["BBS_uid"];

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update nickname error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	// Update email
	if ($old_email != $email)
	{
		$sql = "SELECT UID FROM user_pubinfo WHERE email = '$email' FOR SHARE";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query user email error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if (mysqli_num_rows($rs) >= $BBS_max_user_per_email)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "email",
				"errMsg" => "该邮箱的使用次数已超过限制",
			));

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
		mysqli_free_result($rs);

		// Generate verify code
		$verify_code = gen_passwd(10);

		$sql = "INSERT INTO user_modify_email_verify (UID, email, verify_code, dt, ip) VALUES(" .
				$_SESSION["BBS_uid"] . ", '$email', '$verify_code', NOW(), '" . client_addr() . "')";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update email error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		//Send mail
		$from = "";
		$fromname = $BBS_name;
		$to = $email;
		$toname = $_SESSION["BBS_username"];
		$subject = $BBS_name . "修改邮件地址确认";
		$body = $_SESSION["BBS_username"] . ":\n    您好！\n" .
				"    请访问以下链接确认更改注册邮件地址：\n" .
				"https://$BBS_host_name/bbs/user_email_verify.php?code=$verify_code\n\n" .
				"    感谢您的大力支持！\n\n" .
				$BBS_name . "\n" . date("Y年m月d日") . "\n";

		$ret = send_mail($from, $fromname, $to, $toname, $subject, $body, $db_conn);
		if ($ret == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Add email error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	$sql = "UPDATE user_reginfo SET name = '$realname',
			birthday = '$year-$month-$day', signup_ip='" . client_addr() .
			"' WHERE UID = " . $_SESSION["BBS_uid"];

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update user reginfo error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "UPDATE user_pubinfo SET gender = '$gender', gender_pub = $gender_public,
			qq = '$qq' WHERE UID =" . $_SESSION["BBS_uid"];

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update user pubinfo error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "INSERT INTO user_modify_log(UID, modify_dt, modify_ip, complete) VALUES(".
			$_SESSION["BBS_uid"] . ", NOW(), '" . client_addr() . "', 1)";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add log error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	//Send mail
	$from = "";
	$fromname = $BBS_name;
	$to = $old_email;
	$toname = $_SESSION["BBS_username"];
	$subject = $BBS_name . "用户资料更改通知";
	$body = $_SESSION["BBS_username"] . ":\n    您好！\n" .
			"    您在本站的注册资料已经于" . date("Y年m月d日 H:i:s") . "更改。\n" .
			"    为了您的个人资料的安全，如果此情况与事实不符，请立即与我们联系。\n\n" .
			$BBS_name . "\n" . date("Y年m月d日") . "\n";

	$ret = send_mail($from, $fromname, $to, $toname, $subject, $body, $db_conn);
	if ($ret == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add email error: " . mysqli_error($db_conn);

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

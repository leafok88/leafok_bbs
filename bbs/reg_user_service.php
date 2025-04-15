<?
	require_once "../lib/common.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "../lib/vn_gif.inc.php";
	require_once "../lib/passwd.inc.php";
	require_once "../lib/db_open.inc.php";
	require_once "../lib/send_mail.inc.php";
	require_once "./session_init.inc.php";
	require_once "./reg_check.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$username = (isset($data["username"]) ? trim($data["username"]) : "");
	$nickname = (isset($data["nickname"]) ? trim($data["nickname"]) : "");
	$realname = (isset($data["realname"]) ? trim($data["realname"]) : "");
	$gender = (isset($data["gender"]) ? $data["gender"] : "");
	$gender_public = (isset($data["gender_public"]) && $data["gender_public"] == "1" ? 1 : 0);
	$email = (isset($data["email"]) ? trim($data["email"]) : "");
	$year = (isset($data["year"]) ? intval($data["year"]) : 0);
	$month = (isset($data["month"]) ? intval($data["month"]) : 0);
	$day = (isset($data["day"]) ? intval($data["day"]) : 0);
	$qq = (isset($data["qq"]) ? trim($data["qq"]) : "");
	$agreement = (isset($data["agreement"]) && $data["agreement"] == "1");
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
	if (!preg_match("/^[A-Za-z][A-Za-z0-9]{4,11}$/", $username))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "username",
			"errMsg" => "不符合格式要求",
		));
	}
	else if (!check_str($username))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "username",
			"errMsg" => "用户名不可用",
		));
	}

	if ($nickname == "" || preg_match("/[[:space:]]/", $nickname) || str_length($nickname) > 20)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "nickname",
			"errMsg" => "不符合格式要求",
		));
	}
	else if (!check_str($nickname))
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

	if (!$agreement)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "agreement",
			"errMsg" => "请仔细阅读并确认同意《用户许可协议》",
		));
	}

	if ((!isset($_SESSION["BBS_vn_str"])) || $_SESSION["BBS_vn_str"] == "" || strcasecmp($_SESSION["BBS_vn_str"], $vn_str) != 0)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "vn_str",
			"errMsg" => "验证码错误",
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

	// Check availability of username and nickname
	$sql = "SELECT UID FROM user_list WHERE username = '$username'";
	
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query user list error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if (mysqli_num_rows($rs) > 0)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "username",
			"errMsg" => "用户名已存在",
		));
	}
	mysqli_free_result($rs);

	$sql = "SELECT UID FROM user_nickname WHERE nickname = '$nickname'";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query user nickname error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if (mysqli_num_rows($rs) > 0)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "nickname",
			"errMsg" => "昵称已存在",
		));
	}
	mysqli_free_result($rs);
	
	$sql = "SELECT UID FROM user_pubinfo WHERE email = '$email'";
	
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
	}
	mysqli_free_result($rs);

	if ($result_set["return"]["code"] != 0)
	{
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Create new user
	$temp_password = gen_passwd(10);

	$sql = "INSERT INTO user_list(username, temp_password) values('$username', '$temp_password')";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add user list error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$uid = mysqli_insert_id($db_conn);

	$sql = "INSERT INTO user_reginfo(UID, name, birthday, signup_dt, signup_ip)
			VALUES($uid, '$realname', '$year-$month-$day', NOW(), '".
			client_addr() . "')";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add user reginfo error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "INSERT INTO user_pubinfo(UID, nickname, email, gender, gender_pub, qq, last_login_dt)
			VALUES($uid, '$nickname', '$email', '$gender', $gender_public, '$qq', NOW())";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add user pubinfo error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "INSERT INTO user_nickname(UID, nickname, begin_dt, begin_reason)
			VALUES($uid, '$nickname', NOW(), 'R')";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add user nickname error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Send initial password via email
	$from = "";
	$fromname = $BBS_name;
	$to = $email;
	$toname = $username;
	$subject = $BBS_name . "注册确认";
	$body = $username . ":\n    您好！\n" .
			"    您的临时密码是：  $temp_password  （区分大小写）\n".
			"    请访问以下链接并在登录时修改密码：\n".
			"https://$BBS_host_name/bbs/\n\n".
			"    感谢您的大力支持！\n\n".
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

	$_SESSION["BBS_vn_str"] = "";

	mysqli_close($db_conn);
	exit(json_encode($result_set));
?>

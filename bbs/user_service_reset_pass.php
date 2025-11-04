<?php
	require_once "../lib/common.inc.php";
	require_once "../lib/db_open.inc.php";
	require_once "../lib/passwd.inc.php";
	require_once "../lib/client_addr.inc.php";
	require_once "../lib/send_mail.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$username = (isset($data["username"]) ? trim($data["username"]) : "");
	$email = (isset($data["email"]) ? trim($data["email"]) : "");

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

	if (!preg_match("/^[A-Za-z0-9_.-]+@([A-Za-z0-9-]+[.])+[A-Za-z0-9-]+$/", $email))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "email",
			"errMsg" => "不符合格式要求",
		));
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

	$sql = "SELECT user_list.UID, username, temp_password, email FROM user_list
			INNER JOIN user_pubinfo ON user_list.UID = user_pubinfo.UID
			WHERE user_list.enable AND username = '$username' and email = '$email'";

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
		$uid = $row["UID"];
		$username = $row["username"];
		$temp_password = $row["temp_password"];
		$email = $row["email"];
	}
	else
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "username",
			"errMsg" => "用户名和邮件地址不匹配",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_free_result($rs);

	if ($temp_password == null || $temp_password == "")
	{
		$temp_password = gen_passwd(10);
	}

	$sql = "UPDATE user_list SET temp_password = '$temp_password'
			WHERE UID = $uid";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update password error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	//Add Log
	$sql = "INSERT INTO send_pass_log(UID, dt, ip) VALUES($uid, NOW(), '" .
			client_addr() . "')";

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
	$to = $email;
	$toname = $username;
	$subject = $BBS_name . "重置密码";
	$body = $username.":\n    您好！\n".
			"    您的临时密码是：  $temp_password  （区分大小写）\n".
			"    请访问以下链接并在登录时修改密码：\n".
			"https://$BBS_host_name/bbs/\n\n".
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

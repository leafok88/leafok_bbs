<?php
	require_once "../lib/common.inc.php";
	require_once "../lib/db_open.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./session_init.inc.php";
	require_once "./check_sub.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$uid = (isset($data["uid"]) ? intval($data["uid"]) : 0);
	$content = (isset($data["content"]) ? $data["content"] : "");

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	// Validate input data
	if ($_SESSION["BBS_uid"] == 0)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有登录";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if (!$_SESSION["BBS_priv"]->checkpriv(0, S_MSG) || $uid == $BBS_sys_uid)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$r_content = check_badwords(split_line($content, "", 256, 10), "****");
	if ($content != $r_content)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "内容不符合要求";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Secure SQL statement
	$content = mysqli_real_escape_string($db_conn, $content);

	$sql = "SELECT UID FROM user_list WHERE UID = $uid";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query user error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if (mysqli_num_rows($rs) == 0)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "用户不存在";
	}
	mysqli_free_result($rs);

	$sql = "INSERT INTO bbs_msg(fromUID, toUID, content, send_dt, send_ip)
			VALUES(" . $_SESSION["BBS_uid"] . ", $uid, '$content', NOW(),'" .
			client_addr() . "')";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Insert msg error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_close($db_conn);
	exit(json_encode($result_set));

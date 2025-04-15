<?
	require_once "../lib/db_open.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./session_init.inc.php";
	require_once "./check_sub.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$sid = (isset($data["sid"]) ? intval($data["sid"]) : 0);
	$comment = (isset($data["comment"]) ? $data["comment"] : "");
	$announcement = (isset($data["announcement"]) ? $data["announcement"] : "");
	$ex_update = (isset($data["ex_update"]) && $data["ex_update"] == "1" ? 1 : 0);
	
	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	// Validate input data
	if (!$_SESSION["BBS_priv"]->checkpriv($sid, S_POST | S_MAN_S))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "prompt",
			"errMsg" => "没有权限",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$r_comment = check_badwords(split_line($comment, "", 80, 3), "****");
	if ($comment != $r_comment)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "comment",
			"errMsg" => "不符合要求",
			"updateValue" => $r_comment,
		));
	}

	$r_announcement = check_badwords(split_line($announcement, "", 150, 3), "****");
	if ($announcement != $r_announcement)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "announcement",
			"errMsg" => "不符合要求",
			"updateValue" => $r_announcement,
		));
	}

	if ($result_set["return"]["code"] != 0)
	{
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Secure SQL statement
	$comment = mysqli_real_escape_string($db_conn, $comment);
	$announcement = mysqli_real_escape_string($db_conn, $announcement);

	$sql = "UPDATE section_config SET comment = '$comment',
			announcement = '$announcement', ex_update = $ex_update,
			set_UID = " . $_SESSION["BBS_uid"] . ", set_dt = NOW(), set_ip='" .
			client_addr() ."' WHERE SID = $sid AND enable";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update section data error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_close($db_conn);
	exit(json_encode($result_set));
?>

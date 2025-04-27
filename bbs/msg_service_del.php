<?php
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$sent = (isset($data["sent"]) && $data["sent"] == "1");
	$msg_id = (isset($data["delete_msg_id"]) ? $data["delete_msg_id"] : array());

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	if ($_SESSION["BBS_uid"] == 0)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有登录";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$msg_id_list = "-1";
	foreach($msg_id as $mid)
	{
		$msg_id_list .= (", " . $mid);
	}

	if ($msg_id_list == "-1")
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有选中消息";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($msg_id_list != "-1")
	{
		$sql = "UPDATE bbs_msg SET " . ($sent ? "s_deleted" : "deleted") .
				" = 1 WHERE MID IN ($msg_id_list) AND " .
				($sent ? "fromUID" : "toUID") . " = " . $_SESSION["BBS_uid"] .
				" AND " . ($sent ? "s_deleted" : "deleted") . " = 0";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Delete message error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	mysqli_close($db_conn);
	exit(json_encode($result_set));
?>

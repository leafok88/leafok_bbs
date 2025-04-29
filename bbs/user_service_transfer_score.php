<?php
	require_once "../lib/db_open.inc.php";
	require_once "../lib/score_change.inc.php";
	require_once "./session_init.inc.php";

	force_login();

	$data = json_decode(file_get_contents("php://input"), true);

	$uid = (isset($data["uid"]) ? intval($data["uid"]) : 0);
	$amount = (isset($data["amount"]) ? intval($data["amount"]) : 0);

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	// Validate input data
	if ($amount <= 0 || $amount > 10000 || $amount % 10 != 0 || $data["amount"] != $amount)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "transfer",
			"errMsg" => "转让额输入错误",
		));
	}

	if ($_SESSION["BBS_uid"] == $uid)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "transfer",
			"errMsg" => "不能转让积分给自己",
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

	// Check recipient
	$sql = "SELECT UID FROM user_list WHERE UID = $uid AND enable AND verified";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query user info error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if (!($row = mysqli_fetch_array($rs)))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "transfer",
			"errMsg" => "接收方不存在",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$ret = score_change($_SESSION["BBS_uid"], round($amount * (-1 - $BBS_score_transfer_fee), 0), "积分转出[$uid]", $db_conn);
	if ($ret < 0)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update score error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	else if ($ret > 0)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "transfer",
			"errMsg" => "积分不足",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$ret = score_change($uid, $amount, "积分转入[" . $_SESSION["BBS_uid"] . "]", $db_conn);
	if ($ret < 0)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update score error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	else if ($ret > 0)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "The balance of recipient's account is negative";

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

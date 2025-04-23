<?php
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";
	require_once "./s_favor.inc.php";

	force_login();

	$data = json_decode(file_get_contents("php://input"), true);

	$sid_list = (isset($data["sid_list"]) ? $data["sid_list"] : array());

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	foreach ($sid_list as $index => $sid)
	{
		if (!$_SESSION["BBS_priv"]->checkpriv(intval($sid), S_LIST))
		{
			unset($sid_list[$index]);
		}
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

	$s_favor = new section_favorite();
	$s_favor->s_list = $sid_list;

	if ($s_favor->save_s_favor($_SESSION["BBS_uid"], $db_conn) != 0)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Mysqli error: " . mysqli_error($db_conn);

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

	$cache_files = array(
		"../bbs/cache/section_list_" . $_SESSION["BBS_uid"],
		"../bbs/cache/www_doc_list_" . $_SESSION["BBS_uid"],
	);

	foreach ($cache_files as $cache_path)
	{
		if (file_exists($cache_path))
		{
			unlink($cache_path);
		}
	}

	mysqli_close($db_conn);
	exit(json_encode($result_set));
?>

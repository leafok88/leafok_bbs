<?php
	require_once "../lib/db_open.inc.php";
	require_once "./section_list.inc.php";
	require_once "./session_init.inc.php";
	require_once "./theme.inc.php";

	force_login();

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	$sid = (isset($_GET["sid"]) ? intval($_GET["sid"]) : $BBS_default_sid);

	if (!$_SESSION["BBS_priv"]->checkpriv($sid, S_POST | S_MAN_S))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Load section setting
	$sql = "SELECT * FROM section_config WHERE SID = $sid AND enable";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query section data error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$result_set["data"] = array(
			"sid" => $sid,
			"cid" => $row["CID"],
			"sname" => $row["sname"],
			"title" => $row["title"],
			"exp_get" => $row["exp_get"],
			"recommend" => $row["recommend"],
			"read_user_level" => $row["read_user_level"],
			"write_user_level" => $row["write_user_level"],
			"announcement" => $row["announcement"],
			"comment" => $row["comment"],
			"ex_update" => $row["ex_update"],
			"sort_order" => $row["sort_order"],
			"class_sections" => array(),
			"section_hierachy" => array(),
			"masters" => array(),
		);
	}
	else
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "Section data not exist";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_free_result($rs);

	// Load section master
	$sql = "SELECT section_master.UID, username, major, begin_dt, end_dt FROM section_master
			INNER JOIN user_list ON section_master.UID = user_list.UID
			WHERE SID = $sid AND section_master.enable AND (NOW() BETWEEN begin_dt AND end_dt)
			ORDER BY major DESC, begin_dt ASC, end_dt ASC";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query section master error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	while ($row = mysqli_fetch_array($rs))
	{
		array_push($result_set["data"]["masters"], array(
			"uid" => $row["UID"],
			"username" => $row["username"],
			"major" => $row["major"],
			"begin_dt" => $row["begin_dt"],
			"end_dt" => $row["end_dt"],
		));
	}
	mysqli_free_result($rs);

	// Load sections in current class
	$sql = "SELECT SID, title, sort_order FROM section_config WHERE CID = " .
			$result_set["data"]["cid"] . " AND enable ORDER BY sort_order";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query section list error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	while ($row = mysqli_fetch_array($rs))
	{
		array_push($result_set["data"]["class_sections"], array(
			"sid" => $row["SID"],
			"title" => $row["title"],
			"sort_order" => $row["sort_order"],
		));
	}
	mysqli_free_result($rs);

	// Load section list
	$ret = load_section_list($result_set["data"]["section_hierachy"],
		function (array $section, array $filter_param) : bool
		{
			return $_SESSION["BBS_priv"]->checkpriv($section["SID"], S_POST | S_MAN_S);
		},
		function (array $section, array $filter_param) : mixed
		{
			return null;
		},
		$db_conn);

	if ($ret == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query section error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_close($db_conn);

	// Output with theme view
	$theme_view_file = get_theme_file("view/section_setting", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;
?>

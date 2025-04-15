<?
	require_once "../lib/db_open.inc.php";
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

	$sql = "SELECT user_timezone, introduction, photo, sign_1, sign_2, sign_3 FROM user_pubinfo WHERE UID = " .
			$_SESSION["BBS_uid"];

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query user data error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if($row = mysqli_fetch_array($rs))
	{
		$result_set["data"] = array(
			"user_tz" => ($row["user_timezone"] != "" ? $row["user_timezone"] : $BBS_timezone),
			"introduction" => $row["introduction"],
			"photo" => $row["photo"],
			"sign_1" => $row["sign_1"],
			"sign_2" => $row["sign_2"],
			"sign_3" => $row["sign_3"],
		);
	}
	else
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "个人数据不存在！";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_free_result($rs);

	mysqli_close($db_conn);

	// Output with theme view
	$theme_view_file = get_theme_file("view/preference", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;
?>

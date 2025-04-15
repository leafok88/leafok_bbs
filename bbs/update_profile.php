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

	$sql = "SELECT nickname, name, email, gender, gender_pub, birthday, qq
			FROM user_reginfo INNER JOIN user_pubinfo ON user_reginfo.UID = user_pubinfo.UID
			WHERE user_reginfo.UID = ". $_SESSION["BBS_uid"];

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query user info error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if($row = mysqli_fetch_array($rs))
	{
		$result_set["data"] = array(
			"nickname" => $row["nickname"],
			"nicknames" => array(),
			"name" => $row["name"],
			"gender" => $row["gender"],
			"gender_pub" => $row["gender_pub"],
			"email" => $row["email"],
			"birthday" => $row["birthday"],
			"qq" => $row["qq"],
		);
	}
	else
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "个人资料不存在！";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_free_result($rs);

	$sql = "SELECT DISTINCT nickname FROM user_nickname WHERE UID = " . $_SESSION["BBS_uid"] .
			" ORDER BY nickname";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query nickname error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	
	while ($row = mysqli_fetch_array($rs))
	{
		array_push($result_set["data"]["nicknames"], $row["nickname"]);
	}
	mysqli_free_result($rs);

	mysqli_close($db_conn);

	// Output with theme view
	$theme_view_file = get_theme_file("view/update_profile", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;
?>

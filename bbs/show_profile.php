<?php
	require_once "../lib/db_open.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/ip_mask.inc.php";
	require_once "./section_list.inc.php";
	require_once "./session_init.inc.php";
	require_once "./user_level.inc.php";
	require_once "./user_photo_path.inc.php";
	require_once "./theme.inc.php";

	force_login();

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	$uid = (isset($_GET["uid"]) ? intval($_GET["uid"]) : 0);
	$ip_mask_level = ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S) ? 1 : 2);

	$sql = "SELECT user_list.*, user_reginfo.*, user_pubinfo.*,
			DATEDIFF(NOW(), last_login_dt) AS day
			FROM user_list INNER JOIN user_reginfo ON user_list.UID = user_reginfo.UID
			INNER JOIN user_pubinfo ON user_list.UID = user_pubinfo.UID
			WHERE user_list.UID = $uid AND enable";

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
		$result_set["return"]["message"] = "用户数据不存在！";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Fill up result data
	$result_set["data"] = array(
		"uid" => $uid,
		"username" => $row["username"],
		"nickname" => $row["nickname"],
		"verified" => $row["verified"],
		"p_login" => $row["p_login"],
		"p_post" => $row["p_post"],
		"p_msg" => $row["p_msg"],
		"p_all" => ($row["p_login"] && $row["p_post"] && $row["p_msg"]),
		"birthday" => (new DateTimeImmutable($row["birthday"])),
		"gender" => $row["gender"],
		"gender_pub" => $row["gender_pub"],
		"signup_dt" => (new DateTimeImmutable($row["signup_dt"]))->setTimezone($_SESSION["BBS_user_tz"]),
		"introduction" => $row["introduction"],
		"exp" => $row["exp"],
		"life" => $row["life"],
		"dead" => false,
		"online" => false,
		"last_tm" => (new DateTimeImmutable($row["last_login_dt"]))->setTimezone($_SESSION["BBS_user_tz"]),
		"ip" => "",
		"is_friend" => false,
		"photo" => "",
		"section_hierachy" => array(),
		);

	if (!in_array($row["life"], $BBS_life_immortal))
	{
		$result_set["data"]["life"] = $row["life"] - $row["day"] - 1;
		if ($result_set["data"]["life"] < 0)
		{
			$result_set["data"]["life"] = 0;
			$result_set["data"]["dead"] = true;
		}
	}

	mysqli_free_result($rs);

	$sql = "SELECT IF(last_tm < SUBDATE(NOW(), INTERVAL $BBS_user_off_line SECOND), 1, 0) AS timeout,
			ip, last_tm FROM user_online WHERE UID = $uid
			ORDER BY last_tm DESC LIMIT 1";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query online user error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$result_set["data"]["online"] = ($row["timeout"] == 0);
		$result_set["data"]["ip"] = ip_mask($row["ip"], $ip_mask_level);
		$result_set["data"]["last_tm"] = (new DateTimeImmutable($row["last_tm"]))->setTimezone($_SESSION["BBS_user_tz"]);
	}
	mysqli_free_result($rs);

	$sql = "SELECT * FROM friend_list WHERE UID = " . $_SESSION["BBS_uid"] .
			" AND fUID = $uid";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query friend error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$result_set["data"]["is_friend"] = (mysqli_num_rows($rs) > 0);
	mysqli_free_result($rs);

	$result_set["data"]["photo"] = photo_path($uid, $db_conn);

	// Load section list
	$ret = load_section_list($result_set["data"]["section_hierachy"],
		function (array $section, array $filter_param) : bool
		{
			return $_SESSION["BBS_priv"]->checkpriv($section["SID"], S_MAN_M);
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
	$theme_view_file = get_theme_file("view/show_profile", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;
?>

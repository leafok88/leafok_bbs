<?
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";
	require_once "./user_level.inc.php";
	require_once "./theme.inc.php";

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	$page = (isset($_GET["page"]) ? intval($_GET["page"]) : 1);
	$rpp = (isset($_GET["rpp"]) ? intval($_GET["rpp"]) : 20);

	$type = (isset($_GET["type"]) ? intval($_GET["type"]) : 0);
	$online = (isset($_GET["online"]) && $_GET["online"] == "1" ? 1 : 0);
	$friend = (isset($_GET["friend"]) && $_GET["friend"] == "1" ? 1 : 0);
	$search_text = (isset($_GET["search_text"]) ? $_GET["search_text"] : "");

	$sql = "SELECT IF(UID = 0, 1, 0) AS is_guest, COUNT(*) AS u_count FROM user_online
			WHERE last_tm >= SUBDATE(NOW(), INTERVAL $BBS_user_off_line SECOND)
			GROUP BY is_guest";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Count online user error" . mysqli_error($db_conn));
		exit();
	}

	$guest_online = 0;
	$user_online = 0;

	while ($row = mysqli_fetch_array($rs))
	{
		if ($row["is_guest"])
		{
			$guest_online = $row["u_count"];
		}
		else
		{
			$user_online = $row["u_count"];
		}
	}
	mysqli_free_result($rs);

	$sql = "SELECT COUNT(user_list.UID) AS rec_count FROM user_list" .
			($online ? " INNER JOIN user_online ON user_list.UID = user_online.UID" : "") .
			($friend ? " INNER JOIN friend_list ON user_list.UID = friend_list.fUID" : "") .
			($type == 1 ? " INNER JOIN user_pubinfo ON user_list.UID = user_pubinfo.UID" : "") .
			" WHERE user_list.enable AND ".
			($type == 1 ? "nickname" : "username") .
			" LIKE '%" . mysqli_real_escape_string($db_conn, $search_text) . "%'" .
			($online ? " AND last_tm >= SUBDATE(NOW(), INTERVAL $BBS_user_off_line SECOND)" : "").
			($friend ? " AND friend_list.UID = " . $_SESSION["BBS_uid"] : "");

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Query user error" . mysqli_error($db_conn));
		exit();
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$toa = $row["rec_count"];
	}

	mysqli_free_result($rs);

	if (!in_array($rpp, $BBS_list_rpp_options))
	{
		$rpp = $BBS_list_rpp_options[0];
	}

	$page_total = ceil($toa / $rpp);
	if ($page > $page_total)
	{
		$page = $page_total;
	}

	if ($page <= 0)
	{
		$page = 1;
	}

	// Fill up result data
	$result_set["data"] = array(
		"type" => $type,
		"online" => $online,
		"friend" => $friend,
		"search_text" => $search_text,
		"page" => $page,
		"rpp" => $rpp,
		"page_total" => $page_total,
		"toa" => $toa,
		"user_online" => $user_online,
		"guest_online" => $guest_online,

		"users" => array(),
	);

	$sql = "SELECT user_list.UID, username, nickname, exp, gender, gender_pub, last_login_dt FROM user_list" .
			($online ? " INNER JOIN user_online ON user_list.UID = user_online.UID" : "") .
			($friend ? " INNER JOIN friend_list ON user_list.UID = friend_list.fUID" : "") .
			" INNER JOIN user_pubinfo ON user_list.UID = user_pubinfo.UID WHERE user_list.enable AND ".
			($type == 1 ? "nickname" : "username") .
			" LIKE '%" . mysqli_real_escape_string($db_conn, $search_text) . "%'" .
			($online ? " AND last_tm >= SUBDATE(NOW(), INTERVAL $BBS_user_off_line SECOND)" : "").
			($friend ? " AND friend_list.UID = " . $_SESSION["BBS_uid"] : "") .
			" ORDER BY " . ($type == 1 ? "nickname" : "username") .
			" LIMIT " . ($page - 1) * $rpp . ", $rpp";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Query user error" . mysqli_error($db_conn));
		exit();
	}

	while ($row = mysqli_fetch_array($rs))
	{
		array_push($result_set["data"]["users"], array(
			"uid" => $row["UID"],
			"username" => $row["username"],
			"nickname" => $row["nickname"],
			"exp" => $row["exp"],
			"gender" => $row["gender"],
			"gender_pub" => $row["gender_pub"],
			"last_login_dt" => (new DateTimeImmutable($row["last_login_dt"]))->setTimezone($_SESSION["BBS_user_tz"]),
		));
	}
	mysqli_free_result($rs);

	// Cleanup
	unset($type);
	unset($online);
	unset($friend);
	unset($search_text);
	unset($page);
	unset($rpp);
	unset($page_total);
	unset($toa);
	unset($user_online);
	unset($guest_online);

	// Output with theme view
	$theme_view_file = get_theme_file("view/search_user", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;
?>

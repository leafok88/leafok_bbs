<?php
	require_once "../lib/db_open.inc.php";
	require_once "../lib/common.inc.php";
	require_once "./session_init.inc.php";
	require_once "./theme.inc.php";

	force_login();

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		),
		"data" => array(
			"score" => 0,
			"transactions" => array(),
		),
	);

	$sql = "SELECT score FROM user_score where UID = " . $_SESSION["BBS_uid"];

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query score error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$result_set["data"]["score"] = $row["score"];
	}
	mysqli_free_result($rs);

	$sql = "SELECT score_change, reason, dt FROM user_score_log
			WHERE UID = " . $_SESSION["BBS_uid"] .
			" AND dt >= SUBDATE(NOW(), INTERVAL 3 YEAR)
			ORDER BY id DESC";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query transactions error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	while($row = mysqli_fetch_array($rs))
	{
		array_push($result_set["data"]["transactions"], array(
			"dt" => (new DateTimeImmutable($row["dt"]))->setTimezone($_SESSION["BBS_user_tz"]),
			"amount" => $row["score_change"],
			"reason" => $row["reason"],
		));
	}
	mysqli_free_result($rs);

	mysqli_close($db_conn);

	// Output with theme view
	$theme_view_file = get_theme_file("view/score_detail", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;
?>

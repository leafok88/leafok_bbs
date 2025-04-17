<?
	require_once "../lib/db_open.inc.php";
	require_once "../bbs/session_init.inc.php";

	force_login();

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		),
	);

	$aid = ($_GET["aid"] ? intval($_GET["aid"]) : 0);
	$enable = (isset($_GET["enable"]) && $_GET["enable"] == "1");

	if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($enable)
	{
		$sql = "UPDATE upload_file SET `check` = 1 WHERE AID = $aid AND `check` = 0";
	}
	else
	{
		$sql = "UPDATE upload_file SET `check` = 1, deny = 1 WHERE AID = $aid AND `check` = 0";
	}

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update file status error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_close($db_conn);

	header("Location: upload_list.php");
?>

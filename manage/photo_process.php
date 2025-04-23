<?php
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

	$p_id = ($_GET["p_id"] ? intval($_GET["p_id"]) : 0);
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
		$sql = "UPDATE user_pubinfo SET photo_enable = 1 WHERE UID = $p_id
				AND photo = 999 AND photo_enable = 0 AND photo_ext <> ''";
	}
	else
	{
		$sql = "UPDATE user_pubinfo SET photo_ext = '' WHERE UID = $p_id
				AND photo = 999 AND photo_enable = 0 AND photo_ext <> ''";
	}

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update photo error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	mysqli_close($db_conn);

	header("Location: photo_list.php");
?>

<?php
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

	if (!$_SESSION["BBS_priv"]->checkpriv(0, S_POST) ||
		$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S | P_MAN_M | P_MAN_S))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($_SESSION["BBS_login_tm"] < time() - 60) // login earlier than 1 minute
	{
		header ("Location: index.php?msg=1&redir=suicide.php");
		exit();
	}

	// Output with theme view
	$theme_view_file = get_theme_file("view/suicide", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;
?>

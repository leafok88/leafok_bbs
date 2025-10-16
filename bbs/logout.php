<?php
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";

	// +1 exp for every 5 minutes online since last logout
	// but at most 1 hour worth of exp can be gained in Web session
	$sql = "UPDATE user_pubinfo SET exp = exp + 
			FLOOR((UNIX_TIMESTAMP() - GREATEST(IF(last_logout_dt IS NULL, 0, UNIX_TIMESTAMP(last_logout_dt)), " . 
			max($_SESSION["BBS_login_tm"], time() - 60 * 60) . ")) / 60 / 5), 
			last_logout_dt = NOW()
			WHERE UID = " . $_SESSION["BBS_uid"];
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Update user_pubinfo error: " . mysqli_error($db_conn));
		exit();
	}

	$sql = "DELETE FROM user_online WHERE SID='" . session_id() . "'";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Delete online user error: " . mysqli_error($db_conn));
		exit();
	}

	mysqli_close($db_conn);

	session_unset();
	session_destroy();

	header("Location: index.php");

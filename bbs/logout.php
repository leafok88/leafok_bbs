<?php
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";

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
?>

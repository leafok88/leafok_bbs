<?
	if (isset($_SERVER["argv"]) && strrpos($_SERVER["argv"][0], "/") !== false)
	{
		chdir(substr($_SERVER["argv"][0], 0, strrpos($_SERVER["argv"][0], "/")));
	}
	
	require_once "../lib/db_open.inc.php";
	require_once "../lib/send_mail.inc.php";

	// Begin transaction
	$rs = mysqli_query($db_conn, "SET autocommit=0");
	if ($rs == false)
	{
		echo ("Mysqli error: " . mysqli_error($db_conn));
		mysqli_close($db_conn);
		exit();
	}
	
	$rs = mysqli_query($db_conn, "BEGIN");
	if ($rs == false)
	{
		echo ("Mysqli error: " . mysqli_error($db_conn));
		mysqli_close($db_conn);
		exit();
	}

	send_mail_do($db_conn);

	// Commit transaction
	$rs = mysqli_query($db_conn, "COMMIT");
	if ($rs == false)
	{
		echo ("Mysqli error: " . mysqli_error($db_conn));
		mysqli_close($db_conn);
		exit();
	}

	mysqli_close($db_conn);
?>

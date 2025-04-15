<?
	require_once "../lib/common.inc.php";
	require_once "../lib/db_open.inc.php";
	require_once "../lib/send_mail.inc.php";
	require_once "./session_init.inc.php";
?>
<?
	force_login();

	$verify_code = (isset($_GET["code"]) ? trim($_GET["code"]) : "");

	if (!preg_match("/^[A-Za-z0-9]{10}$/", $verify_code))
	{
		echo ("确认码格式错误！\n");
		exit();
	}

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

	$sql = "SELECT email FROM user_modify_email_verify WHERE UID = " .
			$_SESSION["BBS_uid"] . " AND complete = 0 AND verify_code = '$verify_code'";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo "Query data error: " . mysqli_error($db_conn);
		mysqli_close($db_conn);
		exit();
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$email = $row["email"];
	}
	else
	{
		echo ("确认码和当前用户不匹配\n");
		mysqli_close($db_conn);
		exit();
	}
	mysqli_free_result($rs);

	$sql = "UPDATE user_pubinfo SET email = '$email' WHERE UID = " . $_SESSION["BBS_uid"];

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo "Update email error: " . mysqli_error($db_conn);
		mysqli_close($db_conn);
		exit();
	}

	$sql = "UPDATE user_modify_email_verify set complete = 1
			WHERE verify_code = '$verify_code'";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo "Update verify code error: " . mysqli_error($db_conn);
		mysqli_close($db_conn);
		exit();
	}

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
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>修改邮件地址确认</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p align="center">
			&nbsp;
		</p>
		<p align="center">
			修改邮件地址成功
		</p>
		<p align="center">
			&nbsp;
		</p>
<?
	include "foot.inc.php";
?>
	</body>
</html>

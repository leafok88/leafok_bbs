<?
	require_once "../lib/db_open.inc.php";
	require_once "./common_lib.inc.php";
	require_once "./session_init.inc.php";
?>
<?
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
?>
<html>
	<head>
		<meta HTTP-EQUIV="Content-Type" Content="text-html; charset=UTF-8">
		<title>用户退出登陆</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p>
			&nbsp;
		</p>
		<p>
			&nbsp;
		</p>
		<p>
			&nbsp;
		</p>
		<p align="center">
			您已经成功退出！
		</p>
		<p align="center">
			<a class="s2" href="index.php">[返回首页]</a>
			<a class="s2" href="javascript:self.close();">[关闭窗口]</a>
		</p>
	</body>
</html>

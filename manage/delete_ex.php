<?php
	require_once "../lib/db_open.inc.php";
	require_once "../lib/dir.inc.php";
	require_once "../bbs/session_init.inc.php";

	force_login();

	if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		echo ("没有权限！");
		exit();
	}

	$sql = "SELECT section_config.SID, section_config.title AS s_title FROM section_config
			INNER JOIN section_class ON section_config.CID = section_class.CID
			WHERE section_config.enable AND section_class.enable
			ORDER BY section_class.sort_order, section_config.sort_order";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query section error: " . mysqli_error($db_conn));
		exit();
	}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>删除精华</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p>
<?php
	$sid_list = "-1";

	while ($row = mysqli_fetch_array($rs))
	{
		echo ("[" . $row["SID"] . "]" . $row["s_title"] . "...");
		if (file_exists("../gen_ex/" . $row["SID"]))
		{
			delTree("../gen_ex/" . $row["SID"]);
		}
		$sid_list .= (", " . $row["SID"]);
		echo ("OK<br />\n");
		flush();
	}

	mysqli_free_result($rs);

	$sql = "UPDATE bbs SET static = 0
			WHERE SID IN ($sid_list) AND TID = 0
			AND visible AND gen_ex";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Update static error: " . mysqli_error($db_conn));
		exit();
	}

	$static_files = glob("../gen_ex/static/*.html");
	foreach ($static_files as $file)
	{
		unlink($file);
	}

	$sql = "UPDATE section_config SET ex_update = 1, ex_menu_update = 1 WHERE SID IN ($sid_list)";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Update section error: " . mysqli_error($db_conn));
		exit();
	}

	mysqli_close($db_conn);

	if (file_exists("../gen_ex/index.html"))
	{
		unlink("../gen_ex/index.html");
	}
?>
		</p>
		<p><a href="gen_ex.php">返回</a></p>
	</body>
</html>

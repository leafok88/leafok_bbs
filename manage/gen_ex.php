<?
	require_once "../lib/db_open.inc.php";
	require_once "../bbs/session_init.inc.php";

	force_login();

	if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		echo ("没有权限！");
		exit();
	}

	set_time_limit(3600);

	$sql = "SELECT section_config.SID, section_config.title AS s_title,
			ex_gen_tm, ex_update, section_class.title AS c_title FROM section_config
			INNER JOIN section_class ON	section_config.CID = section_class.CID
			WHERE section_config.enable AND section_class.enable
			ORDER BY section_class.sort_order, section_config.sort_order";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Update section error: " . mysqli_error($db_conn));
		exit();
	}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>精华生成</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p>
<?
	while ($row = mysqli_fetch_array($rs))
	{
		echo ("[" . $row["SID"] . "]" . $row["s_title"] .
			($row["ex_update"] ? "<font color=red>[需要更新]</font>" : "") .
			"(" . $row["ex_gen_tm"] . ")...");
?>
			<a href="gen_ex_section.php?sid=<? echo $row["SID"]; ?>">生成</a><br />
<?
	}
	mysqli_free_result($rs);

	mysqli_close($db_conn);
?>
			目录...<a href="gen_ex_context.php">生成</a><br>
		</p>
		<p><a href="delete_ex.php" target="mng_body">删除精华</a></p>
	</body>
</html>

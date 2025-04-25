<?php
	if (isset($_SERVER["argv"]))
	{
		chdir(dirname($_SERVER["argv"][0]));
	}

	require_once "../lib/db_open.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "../bbs/session_init.inc.php";

	force_login();

	if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M) && !isset($_SERVER["argc"]))
	{
		echo ("没有权限！");
		exit();
	} 
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>数据修复</title>
<link rel="stylesheet" href="css/default.css" type="text/css">
</head>
<body>
<P>
<?php
	$sql = "SELECT b1.AID, b1.reply_count AS rc1, SUM(b2.visible) AS rc2
			FROM bbs AS b1 LEFT JOIN bbs AS b2 ON b1.AID = b2.TID
			WHERE b1.TID = 0 AND b1.visible
			GROUP BY b1.AID HAVING rc1 <> rc2
			ORDER BY b1.AID";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query reply error: " . mysqli_error($db_conn));
		exit();
	}

	while ($row = mysqli_fetch_array($rs))
	{
		$sql = "UPDATE bbs SET reply_count = " . $row["rc2"] .
				" WHERE AID = " . $row["AID"];

		$ret = mysqli_query($db_conn, $sql);
		if ($ret == false)
		{
			echo ("Update data error: " . mysqli_error($db_conn));
			exit();
		}

		echo ($row["AID"] . "已修复回帖数<br />\n");
	}
	mysqli_free_result($rs);

	$sql = "SELECT AID, SID FROM bbs WHERE TID = 0 AND reply_count > 0";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query data error: " . mysqli_error($db_conn));
		exit();
	}

	while ($row = mysqli_fetch_array($rs))
	{
		$sql = "UPDATE bbs SET SID = " . $row["SID"] . " WHERE TID = " . $row["AID"];

		$ret = mysqli_query($db_conn, $sql);
		if ($ret == false)
		{
			echo ("Update data error: " . mysqli_error($db_conn));
			exit();
		}

		$ar = mysqli_affected_rows($db_conn);
		if ($ar > 0)
		{
			echo ("已修复[" . $row["AID"] . "]主题的" . $ar . "回帖所属版块<br />\n");
		}
	}
	mysqli_free_result($rs);

	$sql = "SELECT bbs.AID, length, content FROM bbs
			INNER JOIN bbs_content ON bbs.CID = bbs_content.CID
			ORDER BY bbs.AID";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Query user_pubinfo error" . mysqli_error($db_conn));
		exit();
	}

	while ($row = mysqli_fetch_array($rs))
	{
		$content_f = LML($row["content"], false, false, 1024);
		$length = str_length($content_f);
		
		if ($row["length"] != $length)
		{
			$sql = "UPDATE bbs SET length = $length WHERE AID = " . $row["AID"];
			$ret = mysqli_query($db_conn, $sql);
			if ($ret == false)
			{
				echo("Update article error" . mysqli_error($db_conn));
				exit();
			}
			
			echo ("[" . $row["AID"] . "] " . $row["length"] . " => $length<br />\n");
		}
	}
	mysqli_free_result($rs);

	mysqli_close($db_conn);
?>
</p>
<P>数据修复完毕！</P>
</body>
</html>

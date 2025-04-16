<?
	if (isset($_SERVER["argv"]) && strrpos($_SERVER["argv"][0], "/") !== false)
	{
		chdir(substr($_SERVER["argv"][0], 0, strrpos($_SERVER["argv"][0], "/")));
	}

	require_once "../lib/db_open.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "../bbs/session_init.inc.php";
?>
<?
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
<P style="color:brown">
<?
$rs=mysql_query("select b1.AID,b1.reply_count as rc1,sum(b2.visible) as rc2".
	" from bbs as b1 left join bbs as b2 on b1.AID=b2.TID where".
	" b1.TID=0 and b1.visible".
	" group by b1.AID having rc1<>rc2 order by b1.AID")
	or die("Query reply error!");

while($row=mysql_fetch_array($rs))
{
	mysql_query("update bbs set reply_count=".$row["rc2"].
		" where AID=".$row["AID"])
		or die("Update data error!");
	echo ($row["AID"]."已修复回帖数"."<br>\n");
}

mysql_free_result($rs);

$rs=mysql_query("select AID,SID from bbs where TID=0 and reply_count>0")
	or die("Query data error!");

while($row=mysql_fetch_array($rs))
{
	mysql_query("update bbs set SID=".$row["SID"]." where TID="
		.$row["AID"])
		or die("Update data error!");
	if (($ar=mysql_affected_rows())>0)
		echo ("已修复[".$row["AID"]."]主题的".$ar."回帖所属版块<br>\n");
}

mysql_free_result($rs);

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
		$rs_update = mysqli_query($db_conn, $sql);
		if ($rs_update == false)
		{
			echo("Update article error" . mysqli_error($db_conn));
			exit();
		}
		
		echo ("[" . $row["AID"] . "] " . $row["length"] . " => $length<br />\n");
	}
}
mysqli_free_result($rs);

mysql_close($db_conn);
?>
</p>
<P style="color:brown">数据库修复完毕！ </P>
</body>
</html>

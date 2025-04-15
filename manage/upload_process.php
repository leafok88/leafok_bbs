<?
	require_once "../lib/db_open.inc.php";
	require_once "../bbs/session_init.inc.php";
?>
<?
force_login();

if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
{
	echo ("没有权限！");
	exit();
}

$p_id=intval($_GET["p_id"]);
$p_enable=trim($_GET["enable"]);
$enable=($p_enable=="no" ? 0:1);

$rs=mysql_query("select ref_AID from upload_file where AID=$p_id and deleted=0")
	or die("Query upload_file error!");
if ($row=mysql_fetch_array($rs))
{
	$ref_aid=$row["ref_AID"];
}
else
{
	echo("附件不存在！");
	exit();
}
mysql_free_result($rs);

$rs=mysql_query("select TID from bbs where AID=$ref_aid")
	or die("Query article status error!");
if ($row=mysql_fetch_array($rs))
{
	$tid=$row["TID"];
}
else
{
	$tid=0;
}
mysql_free_result($rs);

if ($enable)
{
	mysql_query("update upload_file set upload_file.check=1 where AID=$p_id")
		or die("Update file status error!");
}
else
{
	mysql_query("update upload_file set upload_file.check=1,deny=1 where AID=$p_id")
		or die("Update file status error!");
}

mysql_close($db_conn);
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>BBS附件审核处理</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p align="center">
			附件审核处理成功！
		</p>
		<p>
			<a href="upload_list.php">返回</a>
		</p>
	</body>
</html>

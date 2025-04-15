<?
	require_once "../lib/db_open.inc.php";
	require_once "../bbs/session_init.inc.php";
?>
<?
force_login();

if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M))
{
	echo ("没有权限！");
	exit();
} 

if (isset($_GET["sid"]))
	$sid=$_GET["sid"];
else
	$sid=0;

if (isset($_GET["order"]))
	$order=$_GET["order"];
else
	$order=0;

if ($sid==0)
{
	echo ("Invalid SID");
	exit();
}
else
{
	$rs=mysql_query("select SID,CID,sort_order from section_config where".
		" SID=$sid and enable order by sort_order")
		or die("Query class error!");
	if ($row=mysql_fetch_array($rs))
	{
		$cur_order=$row["sort_order"];
		$cid=$row["CID"];
	}
	else
	{
		echo ("Invalid SID");
		exit();
	}
	mysql_free_result($rs);

	mysql_query("update section_config set sort_order=sort_order".
		($order>$cur_order?"-1":"+1")." where CID=$cid and sort_order".
		" between ".($order>$cur_order?$cur_order:$order)." and ".
		($order>$cur_order?$order:$cur_order)." and enable")
		or die("Update order error!");
	mysql_query("update section_config set sort_order=$order where".
		" SID=$sid")
		or die("Update order error!");
}
mysql_close($db_conn);
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>版块排序</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p>
			排序完成<br>
			<a href="section_list.php?cid=<? echo $cid; ?>">返回</a>
		</p>
	</body>
</html>

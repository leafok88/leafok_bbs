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

if (isset($_GET["cid"]))
	$cid=$_GET["cid"];
else
	$cid=0;

if (isset($_GET["order"]))
	$order=$_GET["order"];
else
	$order=0;

if ($cid==0)
{
	$order_c=1;

	$rs_c=mysql_query("select CID,sort_order from section_class where".
		" enable order by sort_order")
		or die("Query class error!");
	while($row_c=mysql_fetch_array($rs_c))
	{
		mysql_query("update section_class set sort_order=$order_c where".
			" CID=".$row_c["CID"])
			or die("Update order_c error!");
		$order_c++;

		$order_s=1;
		$rs_s=mysql_query("select SID,sort_order from section_config where".
			" CID=".$row_c["CID"]." and enable order by sort_order")
			or die("Query section error!");
		while($row_s=mysql_fetch_array($rs_s))
		{
			mysql_query("update section_config set sort_order=$order_s where".
				" SID=".$row_s["SID"])
				or die("Update order_s error!");
			$order_s++;
		}
		mysql_free_result($rs_s);
	}
	mysql_free_result($rs_c);

	mysql_query("update section_class set sort_order=0 where enable=0")
		or die("Update order error!");

	mysql_query("update section_config set sort_order=0 where enable=0")
		or die("Update order error!");
}
else
{
	$rs=mysql_query("select CID,sort_order from section_class where".
		" CID=$cid and enable order by sort_order")
		or die("Query class error!");
	if ($row=mysql_fetch_array($rs))
	{
		$cur_order=$row["sort_order"];
	}
	else
	{
		echo ("Invalid CID");
		exit();
	}
	mysql_free_result($rs);

	mysql_query("update section_class set sort_order=sort_order".
		($order>$cur_order?"-1":"+1")." where sort_order".
		" between ".($order>$cur_order?$cur_order:$order)." and ".
		($order>$cur_order?$order:$cur_order)." and enable")
		or die("Update order error!");
	mysql_query("update section_class set sort_order=$order where".
		" CID=$cid")
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
			<a href="class_list.php">返回</a>
		</p>
	</body>
</html>

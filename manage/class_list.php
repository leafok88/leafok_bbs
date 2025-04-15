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
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>类别设定</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<table width="95%" cellpadding=0 cellspacing=0 border=0>
<?
$rs=mysql_query("select * from section_class where".
	" enable order by sort_order")
	or die("Query class error!");

while($row=mysql_fetch_array($rs))
{
?>
<tr>
<td width="30%">
<form action="class_order.php" method="get">
<? echo $row["sort_order"]; ?>&nbsp;
<a href="section_list.php?cid=<? echo $row["CID"]; ?>"><? echo $row["title"]; ?></a>
<input type="hidden" name="cid" value="<? echo $row["CID"]; ?>">
<input size="2" value="<? echo $row["sort_order"]; ?>" name="order">
<input type="submit" value="排序">
</form>
</td>
<td width="70%">
<form action="class_set.php" method="post">
<input type="hidden" name="cid" value="<? echo $row["CID"]; ?>">
<input name="cname" value="<? echo $row["cname"]; ?>" size=20>
<input name="title" value="<? echo $row["title"]; ?>" size=20>
<input type="checkbox" name="enable" <? echo ($row["enable"]?"checked":""); ?> >
<input type="submit" value="设定">
</form>
</td>
</tr>
<?
}
mysql_free_result($rs);
mysql_close($db_conn);
?>
		</table>
		<p><a href="class_order.php">完全排序</a></p>
	</body>
</html>

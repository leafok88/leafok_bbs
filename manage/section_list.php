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
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>版块设定</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<table width="95%" cellpadding=0 cellspacing=0 border=0>
			<tr>
				<td width="30%">
				</td>
				<td width="70%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;英文版名&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;中文版名&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					奖励
					推荐&nbsp;&nbsp;
					读&nbsp;&nbsp;
					写&nbsp;&nbsp;
					启用
				</td>
			</tr>
<?
$rs=mysql_query("select * from section_config where".
	" CID=$cid and enable order by sort_order")
	or die("Query section error!");

while($row=mysql_fetch_array($rs))
{
?>
<tr>
<td width="30%">
<form action="section_order.php" method="get">
<? echo $row["sort_order"]; ?>&nbsp;
<? echo $row["title"]; ?>
<input type="hidden" name="sid" value="<? echo $row["SID"]; ?>">
<input size="2" value="<? echo $row["sort_order"]; ?>" name="order">
<input type="submit" value="排序">
</form>
</td>
<td width="70%">
<form action="section_set.php" method="post">
<input type="hidden" name="cid" value="<? echo $cid; ?>">
<input type="hidden" name="sid" value="<? echo $row["SID"]; ?>">
<input name="sname" value="<? echo $row["sname"]; ?>" size=15>
<input name="title" value="<? echo $row["title"]; ?>" size=15>
<input type="checkbox" name="exp_get" <? echo ($row["exp_get"]?"checked":""); ?> >
<input type="checkbox" name="recommend" <? echo ($row["recommend"]?"checked":""); ?> >
<input name="read_user_level" value="<? echo $row["read_user_level"]; ?>" size=2>
<input name="write_user_level" value="<? echo $row["write_user_level"]; ?>" size=2>
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
		<P>
			<form action="section_new.php" method="post">
			<input type="hidden" name="cid" value="<? echo $cid; ?>">
			<span style="color:red;">添加版块</span><br>
			版块英文名称：<INPUT name=sname><BR>
			版块中文名称：<INPUT name=title><BR>
			<INPUT type="submit" value=添加>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
			<INPUT type="reset" value=重填>
			</form>
		</P>
		<p><a href="class_list.php">返回</a></p>
	</body>
</html>

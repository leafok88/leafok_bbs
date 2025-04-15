<?
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";
	require_once "../lib/lml.inc.php";
?>
<?
force_login();

$pagen=10;

if (isset($_GET["mid"]))
	$mid=intval($_GET["mid"]);
else
	$mid=1;

$rs=mysql_query("select count(MID) as m_count from bbs_msg where toUID=".
	$_SESSION["BBS_uid"]." and (not deleted)")
	or die("Query msg count error!");
$row=mysql_fetch_array($rs);
$total_msg=$row["m_count"];
mysql_free_result($rs);

$rs=mysql_query("select count(MID) as m_count from bbs_msg where toUID=".
	$_SESSION["BBS_uid"]." and new and (not deleted)")
	or die("Query msg count error!");
$row=mysql_fetch_array($rs);
$new_msg_count=$row["m_count"];
mysql_free_result($rs);

if ($mid<1 || $mid>$total_msg)
	$mid=1;

?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>查看消息——收件箱</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
		<script type="text/javascript" src="../js/nw_open.js"></script>
		<script type="text/javascript" src="../js/bbs_msg_fun.js"></script>
	</head>
	<body>
		<center>
		<form action="delete_msg.php" method="post" name="delete_msg" id="delete_msg">
		<input type="hidden" name="box" value="inbox">
		<table cols="2" border="0" cellpadding="5" cellspacing="0" width="90%">
			<tr bgcolor="#ffdead" height="20">
				<td width="70%" align="left">
					<a class="s2" href="read_send_msg.php">已发送消息</a>&nbsp;
					<? if ($new_msg_count>0)
{
?>您有<span style="color:red;"><? echo $new_msg_count; ?></span>条新消息<? }
  else
{
?>您没有新消息<? } ?>&nbsp;
				</td>
				<td width="30%" align="right">
					共有<span style="color:red;"><? echo $total_msg; ?></span>条消息
				</td>
			</tr>
			<? 
$color[0]="#faf5f5";
$color[1]="#f0f0f0";
$count=0;

$rs=mysql_query("select bbs_msg.*,user_pubinfo.nickname from bbs_msg left join".
	" user_pubinfo on bbs_msg.fromUID=user_pubinfo.UID where toUID=".
	$_SESSION["BBS_uid"]." and (not deleted) order by new desc,mid desc limit ".($mid-1).",$pagen")
	or die("Query msg error!");

while($row=mysql_fetch_array($rs))
{
?>
			<tr bgcolor="<? echo $color[1]; ?>">
				<td align="left">
					发送人：<a class="s2" href="show_profile.php?uid=<? echo $row["fromUID"]; ?>" target=_blank title="查看作者资料"><? echo $row["nickname"]; ?></a>
					&nbsp;&nbsp;发送时间：<? echo $row["send_dt"]; ?>&nbsp;&nbsp;<? if ($row["new"])
{
?><img src="images/new.gif"><?   } ?>
				</td>
				<td align="right">
					<a class="s2" href="" onclick="return NW_open('send_msg.php?uid=<? echo $row["fromUID"]; ?>','bbs_msg',500,300);">回复消息</a>
					<input type="checkbox" name="delete_msg_id[]" value="<? echo $row["MID"]; ?>">
				</td>
			</tr>
			<tr bgcolor="<? echo $color[0]; ?>">
				<td colspan="2" align="left">
					<? echo LML(htmlspecialchars($row["content"], ENT_HTML401, 'UTF-8'),true); ?>
				</td>
			</tr>
<?
	mysql_query("update bbs_msg set new=0 where MID=".$row["MID"])
		or die("Update msg error!");
} 
mysql_free_result($rs);
mysql_close($db_conn);
?>
			<tr bgcolor="#ffdead" height="5">
				<td colspan="2">
				</td>
			</tr>
			<tr><td align="left">
<? 
if ($mid > 1)
{
?>
	<a class="s7" href="read_msg.php?mid=1">&lt;&lt;首页</a>&nbsp;<a class="s7" href="read_msg.php?mid=<? echo $mid-$pagen; ?>">上一页</a>&nbsp;
<? 
}
else
{
?>
	<font color="999999">&lt;&lt;首页&nbsp;上一页&nbsp;</font>
<? 
}

if ($mid+$pagen <= $total_msg)
{
?>
	<a class="s7" href="read_msg.php?mid=<? echo $mid+$pagen; ?>">下一页</a>&nbsp;<a class="s7" href="read_msg.php?mid=<? echo $total_msg-$pagen+1; ?>">尾页&gt;&gt;</a>&nbsp;
<? 
}
else
{
?>
	<font color="999999">下一页&nbsp;尾页&gt;&gt;</font>
<? 
}
?>
			</td>
			<td align="right">
				<a class="s2" onclick="delete_msg.submit();" href="#">删除</a>
				<input type="checkbox" name="check_all" id="check_all" onclick="setCheckboxes('delete_msg',this.checked);">全选
			</td></tr>
			<tr>
			<td align="left">
				<a class="s2" onclick="return window.confirm('真的要删除吗？');" href="delete_sys_msg.php">删除全部系统消息</a>
			</td>
			<td align="right">
				<a class="s2" onclick="return window.confirm('真的要删除吗？');" href="delete_msg.php?all=1&box=inbox">删除全部</a>
			</td></tr>
		</table>
		</form>
	</center>
	<p align="center">
		[<a class="s2" href="javascript:self.close()">关闭窗口</a>]
	</p>
	</body>
</html>

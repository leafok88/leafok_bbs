<?
	require_once "../lib/db_open.inc.php";
	require_once "./common_lib.inc.php";
	require_once "./session_init.inc.php";
?>
<?
if (!$_SESSION["BBS_priv"]->checkpriv(0, S_MSG))
{
	error_msg("您无权发送消息！",true);
	exit();
}

if (isset($_GET["uid"]))
	$uid=intval($_GET["uid"]);
else
	$uid=0;

$rs=mysql_query("select nickname from user_pubinfo where UID=$uid")
	or die("Query user info error!");
if ($row=mysql_fetch_array($rs))
{
	$nickname = $row["nickname"];
}
else
{
	error_msg("用户不存在",true);
	exit();
} 
mysql_free_result($rs);

mysql_close($db_conn);
?>
<html>
	<head>
		<meta HTTP-EQUIV="Content-Type" Content="text-html; charset=UTF-8">
		<title>发送消息</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<form action="send_msg_sub.php" method="post" id=form1 name=form1>
			<input type="hidden" name="uid" value="<? echo $uid; ?>">
			<p>
				发送给：<input value="<? echo $nickname; ?>" disabled><br>
				内容：<br>
				<TEXTAREA id="TEXTAREA1" style="WIDTH: 429px; HEIGHT: 134px" name="content" rows="6" cols="47"></TEXTAREA>
			</p>
			<p align="center">
				<input id="submit_button" type="submit" value="发送" name="submit">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input id="reset_button" type="reset" value="重填" name="reset">
			</p>
		</form>
		<p align="center">
			<a class="s7" href="javascript:self.close();">关闭</a>
		</p>
	</body>
</html>


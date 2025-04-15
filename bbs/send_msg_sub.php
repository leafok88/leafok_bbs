<?
	require_once "../lib/db_open.inc.php";
	require_once "./common_lib.inc.php";
	require_once "./session_init.inc.php";
	require_once "./check_sub.inc.php";
?>
<? 
if (!$_SESSION["BBS_priv"]->checkpriv(0,S_MSG))
{
	error_msg("您无权发送消息！",true);
	exit();
} 

$to_uid=intval($_POST["uid"]);
$to_content=$_POST["content"];

$to_content=check_badwords($to_content, "[bwf]");
$to_content=mysqli_real_escape_string($db_conn, $to_content);

$rs_to=mysql_query("select UID from user_pubinfo where UID=$to_uid")
	or die("Query to info error!");
if (!($row_to=mysql_fetch_array($rs_to)))
{
	error_msg("用户不存在",true);
	exit();
} 
mysql_free_result($rs_to);

mysql_query("insert into bbs_msg(fromUID,toUID,content,send_dt,send_ip)".
	" values(".$_SESSION["BBS_uid"].",$to_uid,'$to_content',now(),'".
	client_addr()."')")
	or die("insert msg error!");

mysql_close($db_conn);
?>
<HTML>
	<HEAD>
		<META HTTP-EQUIV="Content-Type" Content="text-html; charset=UTF-8">
		<TITLE>发送消息完毕</TITLE>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</HEAD>
	<body>
		<P align="center">
			发送成功！
		</P>
		<p align="center">
			<a class="s7" id="close_text" href="javascript:self.close();">[3秒后关闭]</a>
		</p>
	</body>
<script type="text/javascript">
time_out = 3;

function auto_close()
{
	time_out--;
	if (time_out <= 0)
	{
		self.clearInterval(timer);
		self.close();
	}
	self.document.all("close_text").innerText = "[" + time_out + "秒后关闭]";
}

timer = self.setInterval(auto_close, 1000);
</script>
</HTML>

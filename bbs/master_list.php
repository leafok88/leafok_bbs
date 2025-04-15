<?
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";
?>

<html>
<head>
<meta HTTP-EQUIV="Content-Type" Content="text-html; charset=UTF-8">
<title>论坛版主名单</title>
<style>
TR
{
	height: 30px;
}
TD
{
	font-size: 14px;
	text-align: center;
}
</style>
</head>
<body bgcolor="#ccffcc">
<center>
	<p style="FONT-WEIGHT: bold; FONT-SIZE: 14px; COLOR: #ff0000">
		论坛版主名单
	</p>
	<TABLE WIDTH="1050" BORDER="1" CELLSPACING="0" CELLPADDING="10">
		<TR>
			<TD>
				昵称
			</TD>
			<TD>
				版块及职务
			</TD>
			<TD>
				任职时间
			</TD>
			<TD>
				上次上线时间
			</TD>
		</TR>
<?
	$rs=mysql_query("SELECT user_list.username,section_config.title,".
		"section_master.begin_dt,section_master.end_dt,section_master.major,".
		"user_pubinfo.last_login_dt FROM (section_master".
		" left join section_config on section_master.SID=section_config.SID)".
		" left join user_list on section_master.UID=user_list.UID".
		" left join user_pubinfo on section_master.UID=user_pubinfo.UID".
		" where section_master.enable=1 and section_config.enable=1".
		" order by user_list.UID,section_master.begin_dt,section_master.MID")
		or die("Query data error!");
	$last_user = "";
	while($row=mysql_fetch_array($rs))
	{
		$days_left=round((time()-strtotime($row["last_login_dt"]))/60/60/24);
		if ($days_left<=3)
			$status="<font color=green>很勤快</font>";
		if ($days_left>3 && $days_left<=7)
			$status="<font color=orange>比较勤快</font>";
		if ($days_left>7 && $days_left<=15)
			$status="<font color=blue>有点偷懒</font>";
		if ($days_left>15 && $days_left<=30)
			$status="<font color=red>失职";
		if ($days_left>30)
			$status="<font color=white>严重失职</font>";
?>
		<TR>
			<TD>
				<? echo ($last_user == $row["username"] ? "&nbsp;" : ($last_user = $row["username"])); ?>
			</TD>
			<TD>
				<? echo $row["title"] . "/" . ($row["major"] ? "正版主" : "副版主"); ?>
			</TD>
			<TD>
				<? echo (new DateTimeImmutable($row["begin_dt"]))->setTimezone($_SESSION["BBS_user_tz"])->format("Y年m月d日"); ?>--<? echo (new DateTimeImmutable($row["end_dt"]))->setTimezone($_SESSION["BBS_user_tz"])->format("Y年m月d日"); ?>
			</TD>
			<TD>
				<? echo $days_left; ?>天前(<? echo $status; ?>)
			</TD>
		</TR>
<?
	}
	mysql_free_result($rs);
	mysql_close($db_conn);
?>
	</TABLE>
</center>
</body>
</html>

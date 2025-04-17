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
	$sql = "SELECT username, section_config.title, begin_dt, end_dt, major, last_login_dt
			FROM section_master INNER JOIN section_config ON section_master.SID = section_config.SID
			INNER JOIN user_list ON section_master.UID = user_list.UID
			INNER JOIN user_pubinfo ON section_master.UID = user_pubinfo.UID
			WHERE section_master.enable AND section_config.enable
			ORDER BY user_list.UID, begin_dt, MID";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo "Query data error: " . mysqli_error($db_conn);
		exit();
	}

	$last_user = "";
	while ($row = mysqli_fetch_array($rs))
	{
		$days_left = (new DateTimeImmutable($row["last_login_dt"]))->diff(new DateTimeImmutable("now"))->days;

		if ($days_left <= 3)
		{
			$status = "<font color=green>很勤快</font>";
		}
		else if ($days_left<=7)
		{
			$status="<font color=orange>比较勤快</font>";
		}
		else if ($days_left<=15)
		{
			$status="<font color=blue>有点偷懒</font>";
		}
		else if ($days_left<=30)
		{
			$status="<font color=red>失职";
		}
		else
		{
			$status="<font color=white>严重失职</font>";
		}
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
	mysqli_free_result($rs);

	mysql_close($db_conn);
?>
	</TABLE>
</center>
</body>
</html>

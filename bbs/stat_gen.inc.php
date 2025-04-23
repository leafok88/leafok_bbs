<?php
	require_once "../lib/db_open.inc.php";
?>
<html>
	<head>
		<meta HTTP-EQUIV="Content-Type" Content="text-html; charset=UTF-8">
		<title>论坛流量分析</title>
		<style>
TR
{
	height:30px;
}
TD
{
	font-size:12px;
	text-align:center;
}
-->
		</style>
	</head>
	<body bgcolor="#f8f8f0" style="margin:10px;">
		<p align="center" style="FONT-WEIGHT: bold; FONT-SIZE: 14px; COLOR: #ff0000">
			论坛流量分析
		</p>
		<center>
<?php
	//All
	$rs = mysqli_query($db_conn, "SELECT COUNT(UID) AS c_user FROM user_list WHERE enable")
		or die("Query user error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_user_all = $row["c_user"];
	}
	else
	{
		$c_user_all = 0;
	}
	
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT COUNT(AID) AS c_topic FROM bbs WHERE TID = 0 AND visible")
		or die("Query topic error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_topic_all = $row["c_topic"];
	}
	else
	{
		$c_topic_all = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT COUNT(AID) AS c_article FROM bbs WHERE visible")
		or die("Query article error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_article_all = $row["c_article"];
	}
	else
	{
		$c_article_all = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT SUM(view_count) AS s_view FROM bbs WHERE TID = 0")
		or die("Query view error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$s_view_all = intval($row["s_view"]);
	}
	else
	{
		$s_view_all = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT MAX(ID) AS c_login FROM user_login_log")
		or die("Query login error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_login_all = $row["c_login"];
	}
	else
	{
		$c_login_all = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT MAX(VID) AS c_visit FROM visit_log")
		or die("Query login error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_visit_all = $row["c_visit"];
	}
	else
	{
		$c_visit_all = 0;
	}
	mysqli_free_result($rs);

	//Week
	$rs = mysqli_query($db_conn, "SELECT COUNT(user_list.UID) AS c_user FROM user_list
		INNER JOIN user_reginfo ON user_list.UID = user_reginfo.UID
		WHERE enable AND signup_dt > SUBDATE(NOW(), INTERVAL 7 DAY)")
		or die("Query user error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_user_week = $row["c_user"];
	}
	else
	{
		$c_user_week = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT COUNT(AID) AS c_topic FROM bbs
		WHERE TID = 0 AND visible AND sub_dt > SUBDATE(NOW(), INTERVAL 7 DAY)")
		or die("Query topic error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_topic_week = $row["c_topic"];
	}
	else
	{
		$c_topic_week = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT COUNT(AID) AS c_article FROM bbs
		WHERE visible AND sub_dt > SUBDATE(NOW(), INTERVAL 7 DAY)")
		or die("Query article error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_article_week = $row["c_article"];
	}
	else
	{
		$c_article_week = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT SUM(view_count) AS s_view FROM bbs
		WHERE TID = 0 AND sub_dt > SUBDATE(NOW(), INTERVAL 7 DAY)")
		or die("Query view error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$s_view_week = intval($row["s_view"]);
	}
	else
	{
		$s_view_week = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT COUNT(ID) AS c_login FROM user_login_log
		WHERE login_dt > SUBDATE(NOW(), INTERVAL 7 DAY)")
		or die("Query login error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_login_week = $row["c_login"];
	}
	else
	{
		$c_login_week = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT COUNT(VID) AS c_visit FROM visit_log
		WHERE dt > SUBDATE(NOW(), INTERVAL 7 DAY)")
		or die("Query login error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_visit_week = $row["c_visit"];
	}
	else
	{
		$c_visit_week = 0;
	}
	mysqli_free_result($rs);

	//Day
	$rs = mysqli_query($db_conn, "SELECT COUNT(user_list.UID) AS c_user FROM user_list
		INNER JOIN user_reginfo ON user_list.UID = user_reginfo.UID
		WHERE enable AND signup_dt > SUBDATE(NOW(), INTERVAL 1 DAY)")
		or die("Query user error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_user_day = $row["c_user"];
	}
	else
	{
		$c_user_day = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT COUNT(AID) AS c_topic FROM bbs
		WHERE TID = 0 AND visible AND sub_dt > SUBDATE(NOW(), INTERVAL 1 DAY)")
		or die("Query topic error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_topic_day = $row["c_topic"];
	}
	else
	{
		$c_topic_day = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT COUNT(AID) AS c_article FROM bbs
		WHERE visible AND sub_dt > SUBDATE(NOW(), INTERVAL 1 DAY)")
		or die("Query article error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_article_day = $row["c_article"];
	}
	else
	{
		$c_article_day = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT COUNT(ID) AS c_login FROM user_login_log
		WHERE login_dt > SUBDATE(NOW(), INTERVAL 1 DAY)")
		or die("Query login error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_login_day = $row["c_login"];
	}
	else
	{
		$c_login_day = 0;
	}
	mysqli_free_result($rs);

	$rs = mysqli_query($db_conn, "SELECT COUNT(VID) AS c_visit FROM visit_log
		WHERE dt > SUBDATE(NOW(), INTERVAL 1 DAY)")
		or die("Query login error!");
	if ($row = mysqli_fetch_array($rs))
	{
		$c_visit_day = $row["c_visit"];
	}
	else
	{
		$c_visit_day = 0;
	}
	mysqli_free_result($rs);
?>
			<TABLE WIDTH="98%" BORDER="1" CELLSPACING="1" CELLPADDING="1" style="color:green;">
				<TR>
					<TD width="25%">统计类别</TD>
					<TD width="25%">全部</TD>
					<TD width="25%">最近7天</TD>
					<TD width="25%">最近24小时</TD>
				</TR>
				<TR>
					<TD>注册用户数</TD>
					<TD><?= $c_user_all; ?></TD>
					<TD><?= $c_user_week; ?></TD>
					<TD><?= $c_user_day; ?></TD>
				</TR>
				<TR>
					<TD>当前主题数</TD>
					<TD><?= $c_topic_all; ?></TD>
					<TD><?= $c_topic_week; ?></TD>
					<TD><?= $c_topic_day; ?></TD>
				</TR>
				<TR>
					<TD>当前文章数</TD>
					<TD><?= $c_article_all; ?></TD>
					<TD><?= $c_article_week; ?></TD>
					<TD><?= $c_article_day; ?></TD>
				</TR>
				<TR>
					<TD>登陆人次数</TD>
					<TD><?= $c_login_all; ?></TD>
					<TD><?= $c_login_week; ?></TD>
					<TD><?= $c_login_day; ?></TD>
				</TR>
				<TR>
					<TD>访问人次数</TD>
					<TD><?= $c_visit_all; ?><br>（2002年11月4日以来）</TD>
					<TD><?= $c_visit_week; ?></TD>
					<TD><?= $c_visit_day; ?></TD>
				</TR>
			</TABLE>
			<TABLE WIDTH="98%" BORDER="1" CELLSPACING="1" CELLPADDING="1">
				<TR>
					<TD colspan="2" style="text-align:left; color:brown;">
						*登陆人次数按注册用户计算，不包含游客。当前主题/文章数不包含已删除文章。
					</TD>
				</TR>
			</TABLE>
			<TABLE WIDTH="98%" BORDER="1" CELLSPACING="1" CELLPADDING="1" style="color:orange;">
				<TR>
					<TD colspan="8">版块统计结果（全部/最近7天）</TD>
				</TR>
				<TR style="height:0">
					<TD width="20%"></TD>
					<TD width="14%"></TD>
					<TD width="12%"></TD>
					<TD width="10%"></TD>
					<TD width="12%"></TD>
					<TD width="10%"></TD>
					<TD width="12%"></TD>
					<TD width="10%"></TD>
				</TR>
				<TR>
					<TD>版块名称</TD>
					<TD>奖励/推荐</TD>
					<TD colspan="2">当前主题数</TD>
					<TD colspan="2">当前文章数</TD>
					<TD colspan="2">浏览文章数</TD>
				</TR>
<?php
	$rs_section = mysqli_query($db_conn, "SELECT SID, section_class.title AS t_class,
		section_config.title AS t_section, exp_get, recommend FROM section_class
		INNER JOIN section_config ON section_class.CID = section_config.CID
		WHERE section_class.enable AND section_config.enable
		ORDER BY section_class.sort_order, section_config.sort_order")
		or die("Query section error!");

	while ($row_section = mysqli_fetch_array($rs_section))
	{
		$rs = mysqli_query($db_conn, "SELECT COUNT(AID) AS c_topic FROM bbs
			WHERE TID = 0 AND SID = " . $row_section["SID"] . " AND visible")
			or die("Query topic error!");
		if ($row = mysqli_fetch_array($rs))
		{
			$c_topic = $row["c_topic"];
		}
		else
		{
			$c_topic = 0;
		}
		mysqli_free_result($rs);

		$rs = mysqli_query($db_conn, "SELECT COUNT(AID) AS c_article FROM bbs
			WHERE SID = " . $row_section["SID"] . " AND visible")
			or die("Query article error!");
		if ($row = mysqli_fetch_array($rs))
		{
			$c_article = $row["c_article"];
		}
		else
		{
			$c_article = 0;
		}
		mysqli_free_result($rs);

		$rs = mysqli_query($db_conn, "SELECT SUM(view_count) AS s_view FROM bbs
			WHERE TID = 0 AND SID = " . $row_section["SID"])
			or die("Query view error!");
		if ($row = mysqli_fetch_array($rs))
		{
			$s_view = intval($row["s_view"]);
		}
		else
		{
			$s_view = 0;
		}
		mysqli_free_result($rs);

		$rs = mysqli_query($db_conn, "SELECT COUNT(AID) AS c_topic FROM bbs
			WHERE TID = 0 AND SID = " . $row_section["SID"] .
			" AND sub_dt > SUBDATE(NOW(), INTERVAL 7 DAY)")
			or die("Query topic error!");
		if ($row = mysqli_fetch_array($rs))
		{
			$c_topic_p = $row["c_topic"];
		}
		else
		{
			$c_topic_p = 0;
		}
		mysqli_free_result($rs);

		$rs = mysqli_query($db_conn, "SELECT COUNT(AID) AS c_article FROM bbs
			WHERE SID = " . $row_section["SID"] .
			" AND sub_dt > SUBDATE(NOW(), INTERVAL 7 DAY)")
			or die("Query article error!");
		if ($row = mysqli_fetch_array($rs))
		{
			$c_article_p = $row["c_article"];
		}
		else
		{
			$c_article_p = 0;
		}
		mysqli_free_result($rs);

		$rs = mysqli_query($db_conn, "SELECT SUM(view_count) AS s_view FROM bbs
			WHERE TID = 0 AND SID = " . $row_section["SID"] .
			" AND sub_dt > SUBDATE(NOW(), INTERVAL 7 DAY)")
			or die("Query view error!");
		if ($row = mysqli_fetch_array($rs))
		{
			$s_view_p = intval($row["s_view"]);
		}
		else
		{
			$s_view_p = 0;
		}
		mysqli_free_result($rs);
?>
				<TR>
					<TD><?= $row_section["t_class"]." / ".$row_section["t_section"]; ?></TD>
					<TD><img src="images/<?= ($row_section["exp_get"]?"tick":"cross"); ?>.gif">&nbsp;<img src="images/<?= ($row_section["recommend"]?"tick":"cross"); ?>.gif"></TD>
					<TD><?= $c_topic; ?>(<?= ($c_topic_all?round($c_topic/$c_topic_all*100,1)."%":"-"); ?>)</TD>
					<TD style="color:<?= ($c_topic_week?($c_topic_p/$c_topic_week>$c_topic/$c_topic_all?"red":"green"):"orange"); ?>"><?= $c_topic_p; ?>(<?= ($c_topic_week?round($c_topic_p/$c_topic_week*100,1)."%":"-"); ?>)</TD>
					<TD><?= $c_article; ?>(<?= ($c_article_all?round($c_article/$c_article_all*100,1)."%":"-"); ?>)</TD>
					<TD style="color:<?= ($c_topic_week?($c_article_p/$c_article_week>$c_article/$c_article_all?"red":"green"):"orange"); ?>"><?= $c_article_p; ?>(<?= ($c_article_week?round($c_article_p/$c_article_week*100,1)."%":"-"); ?>)</TD>
					<TD><?= $s_view; ?>(<?= ($s_view_all?round($s_view/$s_view_all*100,1)."%":"-"); ?>)</TD>
					<TD style="color:<?= ($c_topic_week?($s_view_p/$s_view_week>$s_view/$s_view_all?"red":"green"):"orange"); ?>"><?= $s_view_p; ?>(<?= ($s_view_week?round($s_view_p/$s_view_week*100,1)."%":"-"); ?>)</TD>
				</TR>
<?php
	}
	mysqli_free_result($rs_section);

	mysqli_close($db_conn);
?>
				<TR>
					<TD colspan=8 style="text-align:left; color:brown;">
						“奖励”指在该版块发帖可以获得经验值<br>
						“推荐”指该版块为被推荐阅读的版块
					</TD>
				<TR>
			</TABLE>
			<TABLE WIDTH="98%" BORDER="1" CELLSPACING="1" CELLPADDING="1">
				<TR>
					<TD colspan="2" style="text-align:left; color:gray;">
						本分析报告于 <?= date("Y-m-d H:i:s"); ?> 更新。
					</TD>
				</TR>
			</TABLE>
		</center>
	</body>
</html>

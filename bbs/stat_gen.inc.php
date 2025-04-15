<?
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
<?
	//All
	$rs=mysql_query("select count(UID) as c_user from user_list where enable")
		or die("Query user error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_user_all=$row["c_user"];
	}
	else
	{
		$c_user_all=0;
	}
	
	mysql_free_result($rs);

	$rs=mysql_query("select count(AID) as c_topic from bbs where TID=0 and visible")
		or die("Query topic error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_topic_all=$row["c_topic"];
	}
	else
	{
		$c_topic_all=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select count(AID) as c_article from bbs where visible")
		or die("Query article error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_article_all=$row["c_article"];
	}
	else
	{
		$c_article_all=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select sum(view_count) as s_view from bbs where TID=0")
		or die("Query view error!");
	if ($row=mysql_fetch_array($rs))
	{
		$s_view_all=intval($row["s_view"]);
	}
	else
	{
		$s_view_all=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select max(ID) as c_login from user_login_log")
		or die("Query login error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_login_all=$row["c_login"];
	}
	else
	{
		$c_login_all=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select max(VID) as c_visit from visit_log")
		or die("Query login error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_visit_all=$row["c_visit"];
	}
	else
	{
		$c_visit_all=0;
	}
	mysql_free_result($rs);

	//Week
	$rs=mysql_query("select count(user_list.UID) as c_user from user_list".
		" left join user_reginfo on user_list.UID=user_reginfo.UID where".
		" user_list.enable and user_reginfo.signup_dt>".
		"subdate(now(),interval 7 day)")
		or die("Query user error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_user_week=$row["c_user"];
	}
	else
	{
		$c_user_week=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select count(AID) as c_topic from bbs where".
		" TID=0 and visible and sub_dt>".
		"subdate(now(),interval 7 day)")
		or die("Query topic error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_topic_week=$row["c_topic"];
	}
	else
	{
		$c_topic_week=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select count(AID) as c_article from bbs where".
		" visible and sub_dt>".
		"subdate(now(),interval 7 day)")
		or die("Query article error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_article_week=$row["c_article"];
	}
	else
	{
		$c_article_week=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select sum(view_count) as s_view from bbs where".
		" TID=0 and sub_dt>".
		"subdate(now(),interval 7 day)")
		or die("Query view error!");
	if ($row=mysql_fetch_array($rs))
	{
		$s_view_week=intval($row["s_view"]);
	}
	else
	{
		$s_view_week=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select count(ID) as c_login from user_login_log".
		" where login_dt>".
		"subdate(now(),interval 7 day)")
		or die("Query login error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_login_week=$row["c_login"];
	}
	else
	{
		$c_login_week=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select count(VID) as c_visit from visit_log".
		" where dt>".
		"subdate(now(),interval 7 day)")
		or die("Query login error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_visit_week=$row["c_visit"];
	}
	else
	{
		$c_visit_week=0;
	}
	mysql_free_result($rs);

	//Day
	$rs=mysql_query("select count(user_list.UID) as c_user from user_list".
		" left join user_reginfo on user_list.UID=user_reginfo.UID where".
		" user_list.enable and user_reginfo.signup_dt>".
		"subdate(now(),interval 1 day)")
		or die("Query user error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_user_day=$row["c_user"];
	}
	else
	{
		$c_user_day=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select count(AID) as c_topic from bbs where".
		" TID=0 and visible and sub_dt>".
		"subdate(now(),interval 1 day)")
		or die("Query topic error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_topic_day=$row["c_topic"];
	}
	else
	{
		$c_topic_day=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select count(AID) as c_article from bbs where".
		" visible and sub_dt>".
		"subdate(now(),interval 1 day)")
		or die("Query article error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_article_day=$row["c_article"];
	}
	else
	{
		$c_article_day=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select count(ID) as c_login from user_login_log".
		" where login_dt>".
		"subdate(now(),interval 1 day)")
		or die("Query login error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_login_day=$row["c_login"];
	}
	else
	{
		$c_login_day=0;
	}
	mysql_free_result($rs);

	$rs=mysql_query("select count(VID) as c_visit from visit_log".
		" where dt>".
		"subdate(now(),interval 1 day)")
		or die("Query login error!");
	if ($row=mysql_fetch_array($rs))
	{
		$c_visit_day=$row["c_visit"];
	}
	else
	{
		$c_visit_day=0;
	}
	mysql_free_result($rs);
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
					<TD><? echo $c_user_all; ?></TD>
					<TD><? echo $c_user_week; ?></TD>
					<TD><? echo $c_user_day; ?></TD>
				</TR>
				<TR>
					<TD>当前主题数</TD>
					<TD><? echo $c_topic_all; ?></TD>
					<TD><? echo $c_topic_week; ?></TD>
					<TD><? echo $c_topic_day; ?></TD>
				</TR>
				<TR>
					<TD>当前文章数</TD>
					<TD><? echo $c_article_all; ?></TD>
					<TD><? echo $c_article_week; ?></TD>
					<TD><? echo $c_article_day; ?></TD>
				</TR>
				<TR>
					<TD>登陆人次数</TD>
					<TD><? echo $c_login_all; ?></TD>
					<TD><? echo $c_login_week; ?></TD>
					<TD><? echo $c_login_day; ?></TD>
				</TR>
				<TR>
					<TD>访问人次数</TD>
					<TD><? echo $c_visit_all; ?><br>（2002年11月4日以来）</TD>
					<TD><? echo $c_visit_week; ?></TD>
					<TD><? echo $c_visit_day; ?></TD>
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
<?
	$rs_section=mysql_query("select SID,section_class.title as t_class,".
		"section_config.title as t_section,section_config.exp_get,".
		"section_config.recommend from section_class left join section_config on".
		" section_class.CID=section_config.CID".
		" where section_class.enable and section_config.enable".
		" order by section_class.sort_order,".
		" section_config.sort_order")
		or die("Query section error!");

	while($row_section=mysql_fetch_array($rs_section))
	{
		$rs=mysql_query("select count(AID) as c_topic from bbs where".
			" TID=0 and SID=".$row_section["SID"]." and visible")
			or die("Query topic error!");
		if ($row=mysql_fetch_array($rs))
		{
			$c_topic=$row["c_topic"];
		}
		else
		{
			$c_topic=0;
		}
		mysql_free_result($rs);

		$rs=mysql_query("select count(AID) as c_article from bbs where".
			" SID=".$row_section["SID"]." and visible")
			or die("Query article error!");
		if ($row=mysql_fetch_array($rs))
		{
			$c_article=$row["c_article"];
		}
		else
		{
			$c_article=0;
		}
		mysql_free_result($rs);

		$rs=mysql_query("select sum(view_count) as s_view from bbs where".
			" TID=0 and SID=".$row_section["SID"])
			or die("Query view error!");
		if ($row=mysql_fetch_array($rs))
		{
			$s_view=intval($row["s_view"]);
		}
		else
		{
			$s_view=0;
		}
		mysql_free_result($rs);

		$rs=mysql_query("select count(AID) as c_topic from bbs where".
			" TID=0 and SID=".$row_section["SID"]." and sub_dt>'".
			date("Y-m-d H:i:s",time()-60*60*24*7)."'")
			or die("Query topic error!");
		if ($row=mysql_fetch_array($rs))
		{
			$c_topic_p=$row["c_topic"];
		}
		else
		{
			$c_topic_p=0;
		}
		mysql_free_result($rs);

		$rs=mysql_query("select count(AID) as c_article from bbs where".
			" SID=".$row_section["SID"]." and sub_dt>'".
			date("Y-m-d H:i:s",time()-60*60*24*7)."'")
			or die("Query article error!");
		if ($row=mysql_fetch_array($rs))
		{
			$c_article_p=$row["c_article"];
		}
		else
		{
			$c_article_p=0;
		}
		mysql_free_result($rs);

		$rs=mysql_query("select sum(view_count) as s_view from bbs where".
			" TID=0 and SID=".$row_section["SID"]." and sub_dt>'".
			date("Y-m-d H:i:s",time()-60*60*24*7)."'")
			or die("Query view error!");
		if ($row=mysql_fetch_array($rs))
		{
			$s_view_p=intval($row["s_view"]);
		}
		else
		{
			$s_view_p=0;
		}
		mysql_free_result($rs);
?>
				<TR>
					<TD><? echo $row_section["t_class"]." / ".$row_section["t_section"]; ?></TD>
					<TD><img src="images/<? echo ($row_section["exp_get"]?"tick":"cross"); ?>.gif">&nbsp;<img src="images/<? echo ($row_section["recommend"]?"tick":"cross"); ?>.gif"></TD>
					<TD><? echo $c_topic; ?>(<? echo ($c_topic_all?round($c_topic/$c_topic_all*100,1)."%":"-"); ?>)</TD>
					<TD style="color:<? echo ($c_topic_week?($c_topic_p/$c_topic_week>$c_topic/$c_topic_all?"red":"green"):"orange"); ?>"><? echo $c_topic_p; ?>(<? echo ($c_topic_week?round($c_topic_p/$c_topic_week*100,1)."%":"-"); ?>)</TD>
					<TD><? echo $c_article; ?>(<? echo ($c_article_all?round($c_article/$c_article_all*100,1)."%":"-"); ?>)</TD>
					<TD style="color:<? echo ($c_topic_week?($c_article_p/$c_article_week>$c_article/$c_article_all?"red":"green"):"orange"); ?>"><? echo $c_article_p; ?>(<? echo ($c_article_week?round($c_article_p/$c_article_week*100,1)."%":"-"); ?>)</TD>
					<TD><? echo $s_view; ?>(<? echo ($s_view_all?round($s_view/$s_view_all*100,1)."%":"-"); ?>)</TD>
					<TD style="color:<? echo ($c_topic_week?($s_view_p/$s_view_week>$s_view/$s_view_all?"red":"green"):"orange"); ?>"><? echo $s_view_p; ?>(<? echo ($s_view_week?round($s_view_p/$s_view_week*100,1)."%":"-"); ?>)</TD>
				</TR>
<?
	}
	mysql_free_result($rs_section);

	mysql_close($db_conn);
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
						本分析报告于 <? echo date("Y-m-d H:i:s"); ?> 更新。
					</TD>
				</TR>
			</TABLE>
		</center>
	</body>
</html>

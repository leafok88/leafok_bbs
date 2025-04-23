<?php
	require_once "../lib/db_open.inc.php";
	require_once "../lib/common.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./session_init.inc.php";
	require_once "./message.inc.php";
	require_once "./section_menu_gen.inc.php";

	$sid = (isset($_GET["sid"]) ? intval($_GET["sid"]) : $BBS_default_sid);

	$sql = "SELECT CID FROM section_config WHERE SID = $sid AND enable";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Query section error: " . mysqli_error($db_conn));
		exit();
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$cid = $row["CID"];
	}
	else
	{
		$cid = $BBS_default_cid;
		$sid = $BBS_default_sid;
	}

	mysqli_free_result($rs);
	
	if ($_SESSION["BBS_uid"] > 0 && time() - $_SESSION["BBS_last_msg_check"] >= $BBS_check_msg_interval)
	{
		$_SESSION["BBS_new_msg"] = check_new_msg($_SESSION["BBS_uid"], $db_conn);
		$_SESSION["BBS_last_msg_check"] = time();
	}
?>
<html>
<head>
<meta HTTP-EQUIV="Content-Type" Content="text-html; charset=UTF-8">
<title>欢迎光临<?= $BBS_name; ?></title>
<link rel="stylesheet" href="css/default.css" type="text/css">
<style type="text/css">
TR.t1
{
	background-color: #f0f5f5;
}
</style>
<script type="text/javascript">
cur_cid=0;

function ch_cid(cid)
{
	if (cur_cid != cid)
	{
		old_cid = cur_cid;
		cur_cid = cid;
		if (old_cid != 0)
			document.all("class_" + old_cid).style.display="none";
		if (cur_cid != 0)
			document.all("class_" + cur_cid).style.display="block";
	}
	return false;
}

window.addEventListener("load", () => {
	ch_cid(<?= $cid; ?>);
});

</script>
</head>
<body>
<center>
<table cols="3" cellSpacing="1" cellPadding="1" width="1050" border="0">
	<tr class="t1" height="25">
		<td colspan="3" align="left" style="color:green;">
<?php
	if ($_SESSION["BBS_uid"] == 0)
	{
?>
		<?= $BBS_name; ?>&gt;&gt;欢迎光临
		[<a class="s2" href="index.php">登录</a>]
<?php
	}
	else
	{
?>
		<?= $BBS_name; ?>&gt;&gt;欢迎回来 <font color=blue><?= ($_SESSION["BBS_username"]); ?></font>
<?php
		if ($_SESSION["BBS_new_msg"] > 0)
		{
?>
		[<a class="s6" href="read_msg.php" target=_blank><?= $_SESSION["BBS_new_msg"]; ?>条新消息</a>]
<?php
		}
?>
    	[<a class="s6" href="logout.php">退出</a>]
<?php
	}
?>
		</td>
	</tr>
	<tr height="1">
    	<td colspan="3">
    	</td>
	</tr>
	<tr class="t1">
    	<td width="15%" align="center" valign="top">
			<table cellSpacing="1" cellPadding="1" width="100%" border="0">
				<tr>
		   			<td align="center" height="10"></td></tr>
				<tr>
					<td align="center" height="2" bgcolor="#bdb76b"></td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
<?php
	echo section_menu_gen($db_conn);
?>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center" height="2" bgcolor="#bdb76b"></td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
			</table>
		</td>
		<td width="70%" align="center" valign="top">
			<table cellSpacing="0" cellPadding="3" width="90%" border="0">
				<tr class="t1">
					<td align="left" style="color: blue;">
						<marquee direction="left" height="25" scrollamount="2" scrolldelay="10" onmouseover="stop()" onmouseout="start()">
<?php
	$sql = "SELECT AID, title, sub_dt FROM bbs
			WHERE TID = 0 AND SID = 4 AND visible AND excerption
			AND (sub_dt >= SUBDATE(NOW(), INTERVAL 28 DAY))
			ORDER BY ontop DESC, sub_dt DESC";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Query data error: " . mysqli_error($db_conn));
		exit();
	}

	while ($row = mysqli_fetch_array($rs))
	{
?>
							[<?= (new DateTime($row["sub_dt"]))->format("Y-m-d"); ?>]
							<a class="s2" href="view_article.php?id=<?= $row["AID"]; ?>" target=_blank><?= htmlspecialchars($row["title"], ENT_HTML401, 'UTF-8'); ?></a>&nbsp;&nbsp;
<?php
	}
	mysqli_free_result($rs);
?>
    					</marquee>
					</td>
				</tr>
				<tr height="10">
					<td></td>
				</tr>
				<tr class="t1">
					<td align="left" style="font-family:楷体; font-size:14px; color:orange;">
						本站热点<img src="images/hotclosed.gif">
					</td></tr>
<?php
	$sql = "SELECT AID, bbs.title AS title, section_config.title as s_title
			FROM bbs INNER JOIN section_config ON bbs.SID = section_config.SID
			WHERE section_config.recommend AND TID = 0 AND visible AND view_count >= 10
			AND (sub_dt >= SUBDATE(NOW(), INTERVAL 7 DAY))
			ORDER BY excerption DESC, (view_count + reply_count) DESC, transship
			LIMIT 10";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Query data error: " . mysqli_error($db_conn));
		exit();
	}

	while ($row = mysqli_fetch_array($rs))
	{
?>
				<tr class="t1">
					<td align="left" style="color:blue;">
						[<?= $row["s_title"]; ?>]
						<a class="s2" href="view_article.php?id=<?= $row["AID"]; ?>" target=_blank>
							<?= split_line(htmlspecialchars($row["title"], ENT_HTML401, 'UTF-8'), "", 70, 2); ?>
						</a>
					</td>
				</tr>
<?php
	}
	mysqli_free_result($rs);
?>
				<tr height="10">
					<td></td>
				</tr>
				 <tr class="t1">
					<td align="left" width="80%" style="font-family:楷体; font-size:14px; color:orange;">
						最新发帖<img src="images/new1.gif">
					</td>
				</tr>
<?php
	$sql = "SELECT AID, bbs.title AS title, section_config.title AS s_title
			FROM bbs INNER JOIN section_config ON bbs.SID = section_config.SID
			WHERE (section_config.recommend OR excerption) AND read_user_level <= " .
			$_SESSION["BBS_priv"]->level .
			" AND TID = 0 AND visible ORDER BY sub_dt DESC
			LIMIT 10";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Query data error: " . mysqli_error($db_conn));
		exit();
	}

	while ($row = mysqli_fetch_array($rs))
	{
?>
				<tr class="t1">
					<td colspan=2 align="left" style="color:blue;">
						[<?= $row["s_title"]; ?>]
						<a class="s2" href="view_article.php?id=<?= $row["AID"]; ?>" target=_blank>
							<?= split_line(htmlspecialchars($row["title"], ENT_HTML401, 'UTF-8'), "", 70, 2); ?>
						</a>
					</td>
				</tr>
<?php
	}
	mysqli_free_result($rs);
?>
				<tr height="5">
					<td></td>
				</tr>
			</table>
		</td>
    	<td width="15%" align="center" valign="top">
			<table cellSpacing="1" cellPadding="1" width="90%" border="0">
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center" height="2" bgcolor="#bdb76b"></td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center" bgcolor="#ececec" style="color:orange;font-size:14px;font-family:楷体;">
						用户状态
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center">
						<font color=blue><?= ($_SESSION["BBS_priv"]->levelname()); ?></font><br>
						<?= ($_SESSION["BBS_priv"]->checkpriv(0, S_POST) ? "" : "仅限浏览"); ?>
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center" bgcolor="#ececec" style="color:orange;font-size:14px;font-family:楷体;">
						管理团队
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center">
						<a class="s2" href="master_list.php" target="_blank">版主</a><br>
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center" bgcolor="#ececec" style="color:orange;font-size:14px;font-family:楷体;">
						本站站规
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center">
						<a class="s2" href="doc/management.xml" target="_blank">论坛管理章程</a><br>
						<a class="s2" href="doc/bbs_master.xml" target="_blank">版主管理条例</a><br>
						<a class="s2" href="doc/board_manage.xml" target="_blank">版面管理条例</a><br>
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center" bgcolor="#ececec" style="color:orange;font-size:14px;font-family:楷体;">
		    			站内搜索
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center">
						<a class="s2" href="search_user.php" target=_blank>用户</a> <a class="s2" href="search_user.php?friend=1" target=_blank>好友</a> <a class="s2" href="search_form.php" target="_blank">文章</a>
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center" bgcolor="#ececec" style="color:orange;font-size:14px;font-family:楷体;">
		    			精华区
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center">
						<a class="s2" href="../gen_ex/" target=_blank>浏览</a> <a class="s2" href="../gen_ex/pack/bbs_ex.chm">下载</a>
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center" bgcolor="#ececec" style="color:orange;font-size:14px;font-family:楷体;">
		    			访问统计
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center">
						<a class="s2" href="statistics.php" target="_blank">综合分析</a><br>
					</td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
				<tr>
					<td align="center" height="2" bgcolor="#bdb76b"></td></tr>
				<tr>
					<td align="center" height="10"></td></tr>
			</table>
		</td>
	</tr>
	<tr colspan="3" height="10">
    	<td></td>
	</tr>
</table>
</center>
<?php
	mysqli_close($db_conn);

	include "foot.inc.php";
?>
</body>
</html>

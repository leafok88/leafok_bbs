<?
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}

	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";

	function pic_file(string $status) : string
	{
		switch(strtoupper($status))
		{
			case "H":
				$file = "hotclosed.gif";
				break;
			case "M":
				$file = "hotfolder.gif";
				break;
			case "G":
				$file = "star.gif";
				break;
			case "B":
				$file = "settop.gif";
				break;
			default:
				$file = "closed.gif";
		}
			
		return($file);
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><? echo $result_set["data"]["section_title"] . "——" . ($result_set["data"]["ex"] ? "文摘区" : "讨论区"); ?></title>
<link rel="stylesheet" href="<? echo get_theme_file('css/default'); ?>" type="text/css">
<style type="text/css">
TD.head,TD.favor,TD.reply,TD.dark,TD.topic
{
	border-right: #d0d3F0 1px solid;
	border-left: #d0d3F0 1px solid;
	border-bottom: #d0d3F0 1px solid;
}
TD.head
{
	font-family: 楷体;
	color: blue;
}
TD.favor,TD.dark
{
	text-align: center;
}
TD.reply,TD.dark
{
	color: #909090;
	background-color: #eaf0Fa;
}
TD.head,TD.favor
{
	background-color: #fafbfc;
}
TD.favor
{
	color: #c0c3f0;
	font-weight: bold;
}
TD.topic
{
	background-color: #fafbfc;
}
TD.topic:hover
{
	background-color: #eaf0Fa;
}
</style>

<script type="text/javascript" src="../js/nw_open.js"></script>
<script type="text/javascript">
function ch_page(page)
{
	document.change_page.page.value = page;
	document.change_page.submit();
	return false;
}

function ch_rpp()
{
	document.change_page.page.value = Math.floor((document.change_page.page.value - 1) * <? echo $result_set["data"]["rpp"]; ?> / document.change_page.rpp.value) + 1;
	document.change_page.submit();
	return false;
}

function ch_sect(sid)
{
	if (sid > 0) 
	{
		document.change_page.sid.value = sid;
		document.change_page.page.value = 1;
		document.change_page.submit();
	}
	return false;
}

function ch_ex(ex)
{
	document.change_page.ex.value = ex;
	document.change_page.submit();
	return false;
}

function ch_reply(reply)
{
	document.change_page.reply.value = reply;
	document.change_page.submit();
	return false;
}

function use_nick(use)
{
	document.change_page.use_nick.value = use;
	document.change_page.submit();
	return false;
}

function ch_sort(type)
{
	document.change_page.sort.value = type;
	document.change_page.submit();
	return false;
}

window.addEventListener("load", () => {
	var s = document.change_section.sid;
	for (i = 0; i < s.options.length; i++)
	{
		if (s.options[i].value == <? echo $result_set["data"]["sid"]; ?>)
		{
			s.selectedIndex = i;
			break;
		}
	}
});

</script>
</head>
<body>
<center>
<table cols="2" border="0" cellpadding="0" cellspacing="0" width="1050">
	<tr>
		<td width="60%" style="color: green;">
			<a class="s2" href="main.php?sid=<? echo $result_set["data"]["sid"]; ?>"><? echo $BBS_name; ?></a>&gt;&gt;<? echo ($result_set["data"]["class_title"] . "[" . $result_set["data"]["class_name"] . "]"); ?>&gt;&gt;<? echo ($result_set["data"]["section_title"] . "[" . $result_set["data"]["section_name"] . "]"); ?>&gt;&gt;<? echo ($result_set["data"]["ex"] ? "文摘区" : "讨论区"); ?>
		</td>
		<td width="40%" align="right" style="color: gray;">
<?
	if ($_SESSION["BBS_uid"] == 0)
	{
?>
		[<a class="s2" href="index.php?redir=<? echo $result_set["data"]["redir"]; ?>">登录</a>]
<?
	}
	else
	{
		if ($_SESSION["BBS_new_msg"] > 0)
		{
?>
		[<a class="s6" href="read_msg.php" target=_blank><? echo $_SESSION["BBS_new_msg"]; ?>条新消息</a>]
<?
		}
?>
		欢迎回来&nbsp;<font color=blue><? echo ($_SESSION["BBS_username"]); ?></font>
		[<a class="s6" href="logout.php">退出</a>]
<?
	}
?>
		</td>
	</tr>
</table>
<table cols="2" border="0" cellpadding="0" cellspacing="0" width="1050">
	<tr bgcolor="#d0d3F0" height="2">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td class="dark" width="3%"><img src="images/master.gif" width="16" height="16" alt="本版版主"></td>
		<td class="head" width="97%">
<?
	foreach ($result_set["data"]["section_masters"] as $master)
	{
?>
			<img src="images/<? echo ($master["major"] ? "master_major.gif" : "master_minor.gif"); ?>" width="12" height="11" alt="<? echo ($master["major"] ? "正版主" : "副版主"); ?>"><a class="s3" href="show_profile.php?uid=<? echo $master['uid']; ?>" target=_blank title="查看版主资料"><? echo $master["username"]; ?></a>&nbsp;&nbsp;
<? 
	} 
?>
		</td>
	</tr>
<?
	if ($result_set["data"]["announcement"] != "")
	{
?>
	<tr>
		<td class="dark"><img src="images/announce.gif" width="18" height="18" alt="本版公告"></td>
		<td class="head">
<? 
		echo (LML(htmlspecialchars($result_set["data"]["announcement"], ENT_HTML401, 'UTF-8'), true));
?>
		</td>
	</tr>
<?
	}
?>
	<tr>
		<td class="dark"></td>
		<td class="head" align="right">
<?
	if ($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST))
	{
?>
			<a class="s4" href="post.php?sid=<? echo $result_set["data"]["sid"]; ?>" title="发表新文章">发帖</a>&nbsp;
<?
	}
?>
			<a class="s4" href="" onclick='return ch_ex(<? echo ($result_set["data"]["ex"] ? 0 : 1); ?>);' title="切换"><? echo ($result_set["data"]["ex"] ? "讨论区" : "文摘区"); ?></a>&nbsp;
			<a class="s4" href="/gen_ex/<? echo $result_set["data"]["sid"]; ?>/" title="浏览本版块精华区" target=_blank>精华区</a>&nbsp;
			<a class="s4" href="" onclick='return ch_reply(<? echo ($result_set["data"]["reply"] ? 0 : 1); ?>);' title="切换文章显示模式"><? echo ($result_set["data"]["reply"] ? "主题" : "普通"); ?></a>&nbsp;
		</td>
	</tr>
	<tr height="10">
		<td colspan="2"></td>
	</tr>
</table>
<table cols="5" border="0" cellpadding="0" cellspacing="0" width="1050">
	<tr bgcolor="#d0d3F0" height="25">
		<td width="4%" class="title">状态</td>
		<td width="39%" class="title"><a class="s10" href="" onclick="return ch_sort('topic');" title="按主题发表时间排序">标题</a></td>
		<td width="16%" class="title"><a class="s10" href="" onclick='return use_nick(<? echo ($result_set["data"]["use_nick"] ? 0 : 1); ?>);' title="切换用户显示模式">作者</a></td>
		<td width="10%" class="title"><a class="s10" href="" onclick="return ch_sort('hot');" title="按人气回复排序">回复/人气</a></td>
		<td width="31%" class="title"><a class="s10" href="" onclick="return ch_sort('reply');" title="按最后回复时间排序">最后更新 | 回复人</a></td>
	</tr>
</table>
<table cols="5" border="0" cellpadding="0" cellspacing="0" width="1050">
<?
	foreach ($result_set["data"]["articles"] as $article)
	{
		$status = ($article["ontop"] ? "B" : ($article["gen_ex"] ? "G" : ($article["excerption"] ? "M" : ($article["reply_count"] < 10 ? "N" : "H"))));
		$status = ($article["visited"] ? strtolower($status) : strtoupper($status));

		if ($status == "n" || $status == "h")
		{
			$ss = "";
		}
		else if ($status == "H")
		{
			$ss = "N";
		}
		else
		{
			$ss = $status;
		}

		$status_str = "<font color=#b0b0b0>$ss</font>";

		if ($article["lock"])
		{
			$status_str .= "<font color=red>x</font>";
		}
		
		$title = split_line(htmlspecialchars($article["title"], ENT_QUOTES | ENT_HTML401, 'UTF-8'), "", 50, 2, "<br />");
		$username = htmlspecialchars($article["username"], ENT_QUOTES | ENT_HTML401, 'UTF-8');
		$nickname = htmlspecialchars($article["nickname"], ENT_QUOTES | ENT_HTML401, 'UTF-8');
		$user_viewable = (isset($result_set["data"]["author_list"][$article["uid"]]));
		$last_reply_username = htmlspecialchars($article["last_reply_username"], ENT_QUOTES | ENT_HTML401, 'UTF-8');
		$last_reply_nickname = htmlspecialchars($article["last_reply_nickname"], ENT_QUOTES | ENT_HTML401, 'UTF-8');
		$last_reply_user_viewable = (isset($result_set["data"]["author_list"][$article["last_reply_uid"]]));
?>
	<tr height="30">
		<td width="4%" class="dark">
			<a class="s0" href="view_article.php?tn=xml&rpp=20&id=<? echo $article["aid"]; ?>&ex=<? echo $result_set["data"]["ex"]; ?>#<? echo $article["aid"]; ?>" target=_blank>
				<img src="images/<? echo pic_file($status); ?>" border="0">
			</a>
		</td>
		<td width="39%" class="topic">
			<a class="s0" href="view_article.php?id=<? echo $article["aid"]; ?>&ex=<? echo $result_set["data"]["ex"]; ?>#<? echo $article["aid"]; ?>" target=_blank title="发表时间：<? echo $article["sub_dt"]->format("Y-m-d H:i:s") . "\n"; ?>文章长度：<? echo $article["length"]; ?>字">
				<img src="images/expression/<? echo $article["icon"]; ?>.gif" border="0">
<?
		if ($article["transship"])
		{
?>
				<font color=#b0b0b0>[转]</font>
<?	
		}
?>
				<? echo $title; ?>
			</a>
			<? echo $status_str; ?>
		</td>
		<td width="16%" class="dark">
			<a class="s2" href="show_profile.php?uid=<? echo $article["uid"]; ?>" onclick='return <? echo ($user_viewable ? "true" : "false"); ?>' title="<? echo ($result_set["data"]["use_nick"] ? $username : $nickname); ?>" target=_blank>
				<? echo ($result_set["data"]["use_nick"] ? $nickname : $username); ?>
			</a>
		</td>
		<td width="10%" class="favor">
			<? echo $article["reply_count"]; ?>/<? echo $article["view_count"]; ?>
		</td>
		<td width="31%" class="reply"><? echo $article["last_reply_dt"]->format("Y-m-d H:i:s"); ?> | 
<?
		if ($article["reply_count"] > 0)
		{
?>
			<a class="s2" href="show_profile.php?uid=<? echo $article["last_reply_uid"]; ?>" onclick='return <? echo ($last_reply_user_viewable ? "true" : "false"); ?>' title="<? echo ($result_set["data"]["use_nick"] ? $last_reply_username : $last_reply_nickname); ?>" target=_blank>
				<? echo ($result_set["data"]["use_nick"] ? $last_reply_nickname : $last_reply_username); ?>
			</a>
<?
		}
		else
		{
?>
			------
<?	
		}
?>
		</td>
	</tr>
<?
	}
?>
</table>
<table cols="3" border="0" cellpadding="0" cellspacing="0" width="1050">
	<tr bgcolor="#d0d3F0" height="5">
		<td colspan="3"></td></tr>
	<tr height="10">
		<td colspan="3"></td></tr>
	<tr valign="top">
		<form action="list.php" method="get" id="change_page" name="change_page">
		<td width="40%" style="color: #909090">
			<input type="hidden" id="sid" name="sid" value="<? echo $result_set["data"]["sid"]; ?>">
			<input type="hidden" id="reply" name="reply" value="<? echo $result_set["data"]["reply"]; ?>">
			<input type="hidden" id="ex" name="ex" value="<? echo $result_set["data"]["ex"]; ?>">
			<input type="hidden" id="use_nick" name="use_nick" value="<? echo $result_set["data"]["use_nick"]; ?>">
			<input type="hidden" id="sort" name="sort" value="<? echo $result_set["data"]["sort"]; ?>">
			每页<select size="1" id="rpp" name="rpp" onchange="ch_rpp();">
<?
	foreach ($BBS_list_rpp_options as $v)
	{
		echo ("<option value=\"$v\"" . ($v == $result_set["data"]["rpp"] ? " selected" : "") . ">$v</option>");
	}
?>
			</select>篇
<?
	if ($result_set["data"]["page"] > 1)
	{
?>
			<a class="s8" title="首页" href="" onclick="return ch_page(1);">|◀</a>
			<a class="s8" title="上一页" href="" onclick='return ch_page(<? echo ($result_set["data"]["page"] - 1); ?>);'>◀</a>
<?
	}
	else
	{
?>
			|◀ ◀
<?
	}
?>
			第<input id="page" name="page" value="<? echo ($result_set["data"]["page"]) ; ?>" style="width: 30px;">/<? echo $result_set["data"]["page_total"]; ?>页
<?
	if ($result_set["data"]["page"] < $result_set["data"]["page_total"])
	{
?>
			<a class="s8" title="下一页" href="" onclick="return ch_page(<? echo ($result_set["data"]["page"] + 1); ?>);">▶</a>
			<a class="s8" title="尾页" href="" onclick="return ch_page(<? echo ($result_set["data"]["page_total"]); ?>);">▶|</a>
<?
	}
	else
	{
?>
			▶ ▶|
<?
	}
?>
		</td>
		<td width="35%">
			<font color=#909090>查找文章</font>
			<input type="text" id="search_text" name="search_text" value='<? echo $result_set["data"]["search_text"];?>' size="15"> <input type=image src="images/search.gif" alt="按主题内容查找文章" border="0"></a>
			<a class="s8" href="search_form.php?sid=<? echo $result_set["data"]["sid"]; ?>" target=_blank title="全功能检索">高级</a>&nbsp;
		</td>
		</form>
		<td width="25%" align="right">
			<form action="" method="get" id="change_section" name="change_section">
				<select size="1" id="sid" name="sid" onchange="ch_sect(this.value);">
<?
	echo $result_set["data"]["section_select_options"];
?>
				</select>
			</form>
		</td>
	</tr>
</table>  
</center>
<?
	include "./foot.inc.php";
?>
</body>
</html>

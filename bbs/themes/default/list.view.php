<?php
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

	$section_scope = ($result_set["data"]["ex"] ? "文摘区" : "讨论区");

	$css_file = get_theme_file('css/default');

	$top_right_info = "";
	if ($_SESSION["BBS_uid"] == 0)
	{
		$top_right_info .= <<<HTML
			[<a class="s2" href="index.php?redir={$result_set['data']['redir']}">登录</a>]
		HTML;
	}
	else
	{
		if ($_SESSION["BBS_new_msg"] > 0)
		{
			$top_right_info .= <<<HTML
				[<a class="s6" href="msg_read.php" target="_blank">{$_SESSION["BBS_new_msg"]}条新消息</a>]
			HTML;
		}

		$top_right_info .= <<<HTML
			欢迎回来&nbsp;<font color="blue">{$_SESSION["BBS_username"]}</font>
			[<a class="s6" href="logout.php">退出</a>]
		HTML;
	}

	$section_master_info = "";
	foreach ($result_set["data"]["section_masters"] as $master)
	{
		$img_src = ($master["major"] ? "master_major.gif" : "master_minor.gif");
		$img_alt = ($master["major"] ? "正版主" : "副版主");

		$section_master_info .= <<<HTML
			<img src="images/{$img_src}" width="12" height="11" alt="{$img_alt}"><a class="s3" href="view_user.php?uid={$master['uid']}" target="_blank" title="查看版主资料">{$master["username"]}</a>&nbsp;&nbsp;
		HTML;
	}

	$announcement_info = "";
	if ($result_set["data"]["announcement"] != "")
	{
		$annoucement = LML(htmlspecialchars($result_set["data"]["announcement"], ENT_HTML401, 'UTF-8'), true, true, 100);

		$announcement_info .= <<<HTML
			<tr>
				<td class="dark"><img src="images/announce.gif" width="18" height="18" alt="本版公告"></td>
				<td class="head">
					<pre class="announcement">{$annoucement}</pre>
				</td>
			</tr>
		HTML;
	}

	$article_op_bar = "";
	if ($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST))
	{
		$article_op_bar .= <<<HTML
			<a class="s4" href="article_post.php?sid={$result_set['data']['sid']}" title="发表新文章">发帖</a>&nbsp;
		HTML;
	}

	$scope_switch = ($result_set["data"]["ex"] ? "讨论区" : "文摘区");
	$reply_switch = ($result_set["data"]["reply"] ? "主题" : "普通");

	$article_op_bar .= <<<HTML
		<a class="s4" href="" onclick="return ch_ex({$result_set['data']['ex']} ? 0 : 1)" title="切换">{$scope_switch}</a>&nbsp;
		<a class="s4" href="/gen_ex/{$result_set['data']['sid']}/" title="浏览本版块精华区" target=_blank>精华区</a>&nbsp;
		<a class="s4" href="" onclick="return ch_reply({$result_set['data']['reply']} ? 0 : 1)" title="切换文章显示模式">{$reply_switch}</a>&nbsp;
	HTML;

	echo <<<HTML
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>{$result_set["data"]["section_title"]}——{$section_scope}</title>
	<link rel="stylesheet" href="{$css_file}" type="text/css">
	<style type="text/css">
	TD.head,TD.favor,TD.reply,TD.dark,TD.topic
	{
		border-right: #d0d3F0 1px solid;
		border-left: #d0d3F0 1px solid;
		border-bottom: #d0d3F0 1px solid;
	}
	TD.head,PRE.announcement
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

	<script type="text/javascript">
	function ch_page(page)
	{
		document.change_page.page.value = page;
		document.change_page.submit();
		return false;
	}

	function ch_rpp()
	{
		document.change_page.page.value = Math.floor((document.change_page.page.value - 1) * {$result_set["data"]["rpp"]} / document.change_page.rpp.value) + 1;
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
			if (s.options[i].value == {$result_set["data"]["sid"]})
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
			<td width="60%" style="color: green">
				<a class="s2" href="main.php?sid={$result_set['data']['sid']}">{$BBS_name}</a>&gt;&gt;{$result_set["data"]["class_title"]}[{$result_set["data"]["class_name"]}]&gt;&gt;{$result_set["data"]["section_title"]}[{$result_set["data"]["section_name"]}]&gt;&gt;{$section_scope}
			</td>
			<td width="40%" align="right" style="color: gray">
				{$top_right_info}
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
				{$section_master_info}
			</td>
		</tr>
		{$announcement_info}
		<tr>
			<td class="dark"></td>
			<td class="head" align="right">
				{$article_op_bar}
			</td>
		</tr>
		<tr height="10">
			<td colspan="2"></td>
		</tr>
	</table>
	<table cols="5" border="0" cellpadding="0" cellspacing="0" width="1050">
		<tr bgcolor="#d0d3F0" height="25">
			<td width="4%" class="title">状态</td>
			<td width="39%" class="title"><a class="s10" href="" onclick="return ch_sort('topic')" title="按主题发表时间排序">标题</a></td>
			<td width="16%" class="title"><a class="s10" href="" onclick="return use_nick({$result_set['data']['use_nick']} ? 0 : 1)" title="切换用户显示模式">作者</a></td>
			<td width="10%" class="title"><a class="s10" href="" onclick="return ch_sort('hot')" title="按人气回复排序">回复/人气</a></td>
			<td width="31%" class="title"><a class="s10" href="" onclick="return ch_sort('reply')" title="按最后回复时间排序">最后更新 | 回复人</a></td>
		</tr>
	</table>
	<table cols="5" border="0" cellpadding="0" cellspacing="0" width="1050">
	HTML;

	foreach ($result_set["data"]["articles"] as $article)
	{
		$status = ($article["ontop"] ? "B" : ($article["gen_ex"] ? "G" : ($article["excerption"] ? "M" : ($article["reply_count"] < 10 ? "N" : "H"))));
		$status = ($article["visited"] ? strtolower($status) : strtoupper($status));

		$status_str = "";
		if ($status == "H")
		{
			$status_str = <<<HTML
				<font color="#b0b0b0">N</font>
			HTML;
		}
		else if ($status != "n" && $status != "h")
		{
			$status_str = <<<HTML
				<font color="#b0b0b0">{$status}</font>
			HTML;
		}

		if ($article["lock"])
		{
			$status_str .= <<<HTML
				<font color="red">x</font>
			HTML;
		}
		
		$status_pic = pic_file($status);
		$title = split_line(htmlspecialchars($article["title"], ENT_HTML401, 'UTF-8'), "", 50, 2, "<br />");
		$user_viewable = (isset($result_set["data"]["author_list"][$article["uid"]]) ? "true" : "false");
		$name = htmlspecialchars(($result_set["data"]["use_nick"] ? $article["nickname"] : $article["username"]), ENT_HTML401, 'UTF-8');
		$name_alt = htmlspecialchars(($result_set["data"]["use_nick"] ? $article["username"] : $article["nickname"]), ENT_QUOTES, 'UTF-8');
		$last_reply_user_viewable = (isset($result_set["data"]["author_list"][$article["last_reply_uid"]]) ? "true" : "false");
		$last_reply_name = htmlspecialchars(($result_set["data"]["use_nick"] ? $article["last_reply_nickname"] : $article["last_reply_username"]), ENT_HTML401, 'UTF-8');
		$last_reply_name_alt = htmlspecialchars(($result_set["data"]["use_nick"] ? $article["last_reply_username"] : $article["last_reply_nickname"]), ENT_QUOTES, 'UTF-8');

		$transship_info = "";
		if ($article["transship"])
		{
			$transship_info = <<<HTML
				<font color="#b0b0b0">[转]</font>
			HTML;
		}

		if ($article["reply_count"] > 0)
		{
			$last_reply_info = <<<HTML
				<a class="s2" href="view_user.php?uid={$article['last_reply_uid']}" onclick="return {$last_reply_user_viewable}" title="{$last_reply_name_alt}" target="_blank">
					{$last_reply_name}
				</a>
			HTML;
		}
		else
		{
			$last_reply_info = <<<HTML
				------
			HTML;
		}

		echo <<<HTML
			<tr height="30">
				<td width="4%" class="dark">
					<a class="s0" href="view_article.php?tn=xml&rpp=20&id={$article['aid']}&ex={$result_set['data']['ex']}#{$article['aid']}" target="_blank">
						<img src="images/{$status_pic}" border="0">
					</a>
				</td>
				<td width="39%" class="topic">
					<a class="s0" href="view_article.php?id={$article['aid']}&ex={$result_set['data']['ex']}#{$article['aid']}" target="_blank" title="发表时间：{$article['sub_dt']->format('Y-m-d H:i:s')}\n文章长度：{$article['length']}字">
						<img src="images/expression/{$article['icon']}.gif" border="0">
						{$transship_info}
						{$title}
					</a>
					{$status_str}
				</td>
				<td width="16%" class="dark">
					<a class="s2" href="view_user.php?uid={$article['uid']}" onclick="return {$user_viewable}" title="{$name_alt}" target="_blank">
						{$name}
					</a>
				</td>
				<td width="10%" class="favor">
					{$article["reply_count"]}/{$article["view_count"]}
				</td>
				<td width="31%" class="reply">
					{$article["last_reply_dt"]->format("Y-m-d H:i:s")} | {$last_reply_info}
				</td>
			</tr>

		HTML;
	}

	$rpp_options = "";
	foreach ($BBS_list_rpp_options as $v)
	{
		$selected = ($v == $result_set["data"]["rpp"] ? "selected" : "");

		$rpp_options .= <<<HTML
			<option value="{$v}" {$selected}>{$v}</option>
		HTML;
	}

	echo <<<HTML
	</table>
	<table cols="3" border="0" cellpadding="0" cellspacing="0" width="1050">
		<tr bgcolor="#d0d3F0" height="5">
			<td colspan="3"></td></tr>
		<tr height="10">
			<td colspan="3"></td></tr>
		<tr valign="top">
			<form action="list.php" method="get" id="change_page" name="change_page">
			<td width="40%" style="color: #909090">
				<input type="hidden" id="sid" name="sid" value="{$result_set['data']['sid']}">
				<input type="hidden" id="reply" name="reply" value="{$result_set['data']['reply']}">
				<input type="hidden" id="ex" name="ex" value="{$result_set['data']['ex']}">
				<input type="hidden" id="use_nick" name="use_nick" value="{$result_set['data']['use_nick']}">
				<input type="hidden" id="sort" name="sort" value="{$result_set['data']['sort']}">
				每页<select size="1" id="rpp" name="rpp" onchange="ch_rpp()">
				{$rpp_options}
				</select>篇
	HTML;

	if ($result_set["data"]["page"] > 1)
	{
		echo <<<HTML
			<a class="s8" title="首页" href="" onclick="return ch_page(1)">|◀</a>
			<a class="s8" title="上一页" href="" onclick='return ch_page({$result_set["data"]["page"]} - 1)'>◀</a>
		HTML;
	}
	else
	{
		echo <<<HTML
			|◀ ◀
		HTML;
	}

	echo <<<HTML
		第<input id="page" name="page" value="{$result_set['data']['page']}" style="width: 30px;">/{$result_set['data']['page_total']}页
	HTML;

	if ($result_set["data"]["page"] < $result_set["data"]["page_total"])
	{
		echo <<<HTML
			<a class="s8" title="下一页" href="" onclick="return ch_page({$result_set['data']['page']} + 1)">▶</a>
			<a class="s8" title="尾页" href="" onclick="return ch_page({$result_set['data']['page_total']})">▶|</a>
		HTML;
	}
	else
	{
		echo <<<HTML
			▶ ▶|
		HTML;
	}

	echo <<<HTML
			</td>
			<td width="35%">
				<font color="#909090">查找文章</font>
				<input type="text" id="search_text" name="search_text" value="{$result_set['data']['search_text']}" size="15"> <input type="image" src="images/search.gif" alt="按主题内容查找文章" border="0"></a>
				<a class="s8" href="search_form.php?sid={$result_set['data']['sid']}" target="_blank" title="全功能检索">高级</a>&nbsp;
			</td>
			</form>
			<td width="25%" align="right">
				<form action="" method="get" id="change_section" name="change_section">
					<select size="1" id="sid" name="sid" onchange="ch_sect(this.value);">
						{$result_set["data"]["section_select_options"]}
					</select>
				</form>
			</td>
		</tr>
	</table>  
	</center>
	HTML;

	include "./foot.inc.php";

	echo <<<HTML
	</body>
	</html>
	HTML;
?>

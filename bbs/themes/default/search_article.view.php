<?php
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}

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

	$css_file = get_theme_file('css/default');

	echo <<<HTML
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>文章查找结果</title>
	<link rel="stylesheet" href="{$css_file}" type="text/css">
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
		color: #c0c3F0;
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
		document.search_form.page.value = page;
		document.search_form.submit();
		return false;
	}

	function ch_rpp()
	{
		document.search_form.page.value = Math.floor((document.search_form.page.value - 1) * {$result_set["data"]["rpp"]} / document.search_form.rpp.value) + 1;
		document.search_form.submit();
		return false;
	}
	</script>
	</head>
	<body>
	<center>
	<table cols="5" border="0" cellpadding="0" cellspacing="0" width="1050" >
		<tr bgcolor="#d0d3F0" height="25">
			<td width="4%" class="title">状态</td>
			<td width="39%" class="title">标题（共{$result_set["data"]["toa"]}篇）</td>
			<td width="16%" class="title">作者</td>
			<td width="10%" class="title">回复/人气</td>
			<td width="31%" class="title">最后更新 | 回复人</td>
		</tr>
	</table>
	<table cols="5" border="0" cellpadding="0" cellspacing="0" width="1050">
	HTML;

	$ex = ($result_set["data"]["ex"] > 0 ? 1 : 0);

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
		$class_title = htmlspecialchars($article["class_title"], ENT_HTML401, 'UTF-8');
		$section_title = htmlspecialchars($article["section_title"], ENT_HTML401, 'UTF-8');

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
					<a class="s0" href="view_article.php?tn=xml&rpp=20&id={$article['aid']}&ex={$ex}&trash={$result_set["data"]["trash"]}#{$article['aid']}" target="_blank">
						<img src="images/{$status_pic}" border="0">
					</a>
				</td>
				<td width="39%" class="topic">
					<font color="green">{$class_title}&gt;&gt;{$section_title}&gt;&gt;</font><br />
					<a class="s0" href="view_article.php?id={$article['aid']}&ex={$result_set['data']['ex']}&trash={$result_set["data"]["trash"]}#{$article['aid']}" target="_blank" title="发表时间：{$article['sub_dt']->format('Y-m-d H:i:s')}\n文章长度：{$article['length']}字">
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
			<td colspan="3"></td>
		</tr>
		<tr height="10">
			<td colspan="3"></td>
		</tr>
		<tr valign="top">
			<td width="40%" align="left" style="color: #909090">
			<form action="search_article.php" method="GET" id="search_form" name="search_form">
				<a name="cp"></a>
				<input type="hidden" id="uid" name="uid" value="{$result_set['data']['uid']}">
				<input type="hidden" id="nickname" name="nickname" value="{$result_set['data']['nickname']}">
				<input type="hidden" id="title" name="title" value="{$result_set['data']['title']}">
				<input type="hidden" id="content" name="content" value="{$result_set['data']['content']}">
				<input type="hidden" id="sid" name="sid" value="{$result_set['data']['sid']}">
				<input type="hidden" id="begin_dt" name="begin_dt" value="{$result_set['data']['begin_dt']->format('Y-m-d')}">
				<input type="hidden" id="end_dt" name="end_dt" value="{$result_set['data']['end_dt']->format('Y-m-d')}">
				<input type="hidden" id="reply" name="reply" value="{$result_set['data']['reply']}">
				<input type="hidden" id="ex" name="ex" value="{$result_set['data']['ex']}">
				<input type="hidden" id="use_nick" name="use_nick" value="{$result_set['data']['use_nick']}">
				<input type="hidden" id="original" name="original" value="{$result_set['data']['original']}">
				<input type="hidden" id="trash" name="trash" value="{$result_set['data']['trash']}">
				每页<select size="1" id="rpp" name="rpp" onchange="ch_rpp();">
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
			</form>
			</td>
			<td width="35%">
			</td>
			<td width="25%">
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

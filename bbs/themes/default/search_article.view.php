<?
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
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>文章查找结果</title>
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
	document.search_form.page.value = Math.floor((document.search_form.page.value - 1) * <? echo $result_set["data"]["rpp"]; ?> / document.search_form.rpp.value) + 1;
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
		<td width="39%" class="title">标题（共<? echo $result_set["data"]["toa"]; ?>篇）</td>
		<td width="16%" class="title">作者</td>
		<td width="10%" class="title">回复/人气</td>
		<td width="31%" class="title">最后更新 | 回复人</td>
	</tr>
</table>
<table cols="5" border="0" cellpadding="0" cellspacing="0" width="1050">
<?
	$ex = ($result_set["data"]["ex"] > 0 ? 1 : 0);

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
		$class_title = htmlspecialchars($article["class_title"], ENT_QUOTES | ENT_HTML401, 'UTF-8');
		$section_title = htmlspecialchars($article["section_title"], ENT_QUOTES | ENT_HTML401, 'UTF-8');
?>
	<tr height="30">
		<td width="4%" class="dark">
			<a class="s0" href="view_article.php?tn=xml&rpp=20&id=<? echo $article["aid"]; ?>&ex=<? echo $ex; ?>#<? echo $article["aid"]; ?>" target=_blank>
				<img src="images/<? echo pic_file($status); ?>" border="0">
			</a>
		</td>
		<td width="39%" class="topic">
		<font color="green"><? echo $class_title; ?>&gt;&gt;</font>
		<font color="green"><? echo $section_title; ?>&gt;&gt;</font><br />

			<a class="s0" href="view_article.php?id=<? echo $article["aid"]; ?>&ex=<? echo $ex; ?>&trash=<? echo ($result_set["data"]["trash"] ? 1 : 0); ?>#<? echo $article["aid"]; ?>" target=_blank title="发表时间：<? echo $article["sub_dt"]->format("Y-m-d H:i:s") . "\n"; ?>文章长度：<? echo $article["length"]; ?>字">
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
			<a class="s2" href="show_profile.php?uid=<? echo $article["uid"]; ?>" onclick='return <? echo ($user_viewable ? "true" : "false"); ?>' title="<? echo $article["username"]; ?>" target=_blank>
				<? echo $article["nickname"]; ?>
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
			<a class="s2" href="show_profile.php?uid=<? echo $article["last_reply_uid"]; ?>" onclick='return <? echo ($last_reply_user_viewable ? "true" : "false"); ?>' title="<? echo $article["last_reply_username"]; ?>" target=_blank>
				<? echo $article["last_reply_nickname"]; ?>
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
		<td colspan="3"></td>
	</tr>
	<tr height="10">
		<td colspan="3"></td>
	</tr>
	<tr valign="top">
		<td width="40%" align="left" style="color:#909090">
		<form action="search_article.php" method="GET" id="search_form" name="search_form">
			<a name="cp"></a>
			<input type="hidden" id="uid" name="uid" value="<? echo $result_set["data"]["uid"];?>">
			<input type="hidden" id="nickname" name="nickname" value="<? echo $result_set["data"]["nickname"];?>">
			<input type="hidden" id="title" name="title" value="<? echo $result_set["data"]["title"];?>">
			<input type="hidden" id="content" name="content" value="<? echo $result_set["data"]["content"];?>">
			<input type="hidden" id="sid" name="sid" value="<? echo $result_set["data"]["sid"];?>">
			<input type="hidden" id="begin_dt" name="begin_dt" value="<? echo $result_set["data"]["begin_dt"]->format("Y-m-d");?>">
			<input type="hidden" id="end_dt" name="end_dt" value="<? echo $result_set["data"]["end_dt"]->format("Y-m-d");?>">
			<input type="hidden" id="reply" name="reply" value="<? echo $result_set["data"]["reply"]; ?>">
			<input type="hidden" id="ex" name="ex" value="<? echo $result_set["data"]["ex"]; ?>">
			<input type="hidden" id="original" name="original" value="<? echo $result_set["data"]["original"]; ?>">
			<input type="hidden" id="trash" name="trash" value="<? echo $result_set["data"]["trash"]; ?>">
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
<a class="s8" title="上一页" href="" onclick="return ch_page(<? echo ($result_set["data"]["page"] - 1); ?>);">◀</a>
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
			</form>
		</td>
		<td width="35%" align="left">
		</td>
		<td width="25%" align="right">
		</td>
	</tr>
</table>  
</center>
<?
	include "./foot.inc.php";
?>
</body>
</html>

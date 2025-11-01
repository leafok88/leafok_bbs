<?php
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}

	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./user_level.inc.php";

	$article = $result_set["data"]["articles"][0];

	$title = htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8');

	$css_file = get_theme_file('css/default');

	echo <<<HTML
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<base href="/www/" />
	<title>{$BBS_name} - {$title}</title>
	<link rel="stylesheet" href="{$css_file}" type="text/css">
	<style type="text/css">
	TD.content
	{
		font-size: 16px;
		line-height: 26px;
	}
	IMG.auto_adjust
	{
		display: none;
	}
	</style>
	<script src="../js/jquery.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function() {
		$("img[class=auto_adjust]").on("load", function() {
			if ($(this).width() > {$BBS_img_max_width})
			{
				$(this).width({$BBS_img_max_width});
			}
			$(this).show();
		})
		.on("mousewheel", function(e) {
			var zoom = parseFloat($(this).css("zoom"));
			zoom *= (1 + e.originalEvent.wheelDelta / 1000);
			if (zoom > 0)
			{
				$(this).css("zoom", zoom);
			}
		});
	});
	</script>
	</head>
	<body>
	<center>
	HTML;

	include "../www/head.inc.php";

	$title_f = split_line(htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'), "", 70, 2, "<br />");
	$transship_info = ($article["transship"] ? "转载" : "原创");
	$author_type = ($article["transship"] ? "转载" : "作者");
	$nickname = htmlspecialchars($article["nickname"], ENT_HTML401, 'UTF-8');
	$content_f = LML($article["content"], 110);

	$atta_list = "";
	foreach ($article["attachments"] as $attachment)
	{
		if (!$attachment["check"])
		{
			continue;
		}

		$filename = $attachment["filename"];

		$atta_list .= <<<HTML
			<img src="../www/images/dl.gif"><a class="s2" href="../bbs/dl_file.php?aid={$attachment['aid']}" target="_target">{$filename}</a> ({$attachment["size"]}字节)
		HTML;

		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		switch ($ext)
		{
			case "bmp":
			case "gif":
			case "jpg":
			case "jpeg":
			case "png":
			case "tif":
			case "tiff":
				$atta_list .= <<<HTML
					<br />
					<img class="auto_adjust" src="../bbs/dl_file.php?aid={$attachment['aid']}">
				HTML;
				break;
		}

		$atta_list .= <<<HTML
			<br />
		HTML;
	}

	echo <<<HTML
	<table width="1050" border="0" cellpadding="0" cellspacing="0">
		<tr height=20 bgcolor=#F3F9FC>
			<td width="20">&nbsp;</td>
			<td>{$BBS_name} &gt;&gt; {$title}</td>
			<td width="200" align="right">本文已被浏览<font color=red>{$result_set["data"]["view_count"]}</font>次</td>
			<td width="20">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="4" height="1" bgcolor="gray"></td>
		</tr>
		<tr height="5">
			<td>&nbsp;</td>
		</tr>
	</table>
	<table width="1050" border="0" cellpadding="0" cellspacing="0">
		<tr height="25">
			<td width="5%"></td>
			<td width="90%"></td>
			<td width="5%"></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr height="30">
						<td align="center"><font size="4"><b>{$title_f}</b></font> <font color="gray">[{$transship_info}]</font></td>
					</tr>
					<tr height="25">
						<td align="center">({$article["sub_dt"]->format("Y-m-d H:i:s")})   {$author_type}：{$nickname}</td>
					</tr>
					<tr height="10">
						<td></td>
					</tr>
					<tr height="1">
						<td align="center" bgcolor="gray"></td>
					</tr>
					<tr height="25">
						<td></td>
					</tr>
					<tr>
						<td class="content">
							<pre>{$content_f}</pre>
						</td>
					</tr>
					<tr>
						<td>
							<br />
							{$atta_list}
						</td>
					</tr>
					<tr height="25">
						<td>
						</td>
					</tr>
					<tr height="25">
						<td align="right">
							已有<font color="red">{$result_set["data"]["reply_count"]}</font>人发表评论
						</td>
					</tr>
					<tr height="10">
						<td>
						</td>
					</tr>
					<tr>
						<td align="right">
							【<a href="../bbs/view_article.php?id={$result_set['data']['id']}">查看回复</a>】
						</td>
					</tr>
				</table>
			</td>
			<td>
			</td>
		</tr>
		<tr height="25">
			<td></td>
		</tr>
	</table>
	HTML;

	include "../www/foot.inc.php";

	echo <<<HTML
	</center>
	</body>
	</html>
	HTML;

<?php
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}

	require_once "../lib/common.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";

	// Pre-defined color setting of article display
	$color = array(
		"#FAFBFC",
		"#f0F3Fa"
	);
	$color_index = 0;

	$section_path = ($result_set["data"]["ex_dir"] != null ? str_repeat("../", substr_count($result_set["data"]["ex_dir"], "/")) : "");

	$title = htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8');

	$ex_dir_link = "";
	if ($result_set["data"]["ex_dir"] != null)
	{
		$ex_dir_link = <<<HTML
		&gt;&gt;<a href="index.html">{$result_set["data"]["ex_name"]}</a>
		HTML;
	}

	echo <<<HTML
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<base href="/gen_ex/{$result_set['data']['sid']}/{$result_set['data']['ex_dir']}{$result_set['data']['id']}.html" />
	<title>{$title}</title>
	<link rel="stylesheet" href="{$section_path}../article.css" type="text/css">
	<style type="text/css">
	SPAN.title_normal
	{
		color: #909090;
	}
	TD.content_normal
	{
		font-size: 16px;
	}
	</style>
	<script type="text/javascript" src="{$section_path}../img_adjust.js"></script>
	</head>
	<body>
		<a name="top"></a>
		<center>
			<table border="0" cellpadding="0" cellspacing="0" width="1050">
				<tr>
					<td>
						<a href="{$section_path}../index.html"><?= $BBS_name; ?>精华区</a>&gt;&gt;<a href="{$section_path}index.html">{$result_set["data"]["section_title"]}</a>{$ex_dir_link}
					</td>
				</tr>
				<tr bgcolor="#d0d3F0" height="25">
					<td align="center" class="title">
						[{$result_set["data"]["id"]}]&nbsp;主题：&nbsp;{$title}
					</td>
				</tr>
			</table>
	HTML;

	foreach ($result_set["data"]["articles"] as $article)
	{
		$color_index = ($color_index + 1) % count($color);

		$username = htmlspecialchars($article["username"], ENT_HTML401, 'UTF-8');
		$nickname = htmlspecialchars($article["nickname"], ENT_HTML401, 'UTF-8');
		$title_f = split_line(htmlspecialchars($article["title"], ENT_HTML401, 'UTF-8'), "", 65, 2, "<br />");
		$content_f = LML(htmlspecialchars($article["content"], ENT_HTML401, 'UTF-8'), true, true, 80);

		$transship_info = "";
		if ($article["transship"])
		{
			$transship_info = <<<HTML
				<font color="red">[转载]</font>
			HTML;
		}

		if ($article["tid"] != 0)
		{
			echo <<<HTML
				<a name="{$article['aid']}"></a>
				<table border="0" cellpadding="0" cellspacing="0" width="1050">
					<tr height="1" bgcolor="#202020">
						<td>
						</td>
					</tr>
				</table>
			HTML;
		}

		$atta_list = "";
		foreach ($article["attachments"] as $attachment)
		{
			if (!$attachment["check"])
			{
				continue;
			}

			$filename = $attachment["filename"];
			$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

			if (!copy("../bbs/upload/" . $attachment["aid"], "../gen_ex/attachment/" . $attachment["aid"] . ".$ext"))
			{
				continue;
			}

			$atta_list .= <<<HTML
				<a class="s2" href="{$section_path}../attachment/{$attachment['aid']}.$ext" target="_blank">{$filename}</a> ({$attachment["size"]}字节)
			HTML;

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
						<img onmousewheel="return bbs_img_zoom(event, this)" src="{$section_path}../attachment/{$attachment['aid']}.$ext">
					HTML;
					break;
			}

			$atta_list .= <<<HTML
				<br />
			HTML;
		}

		echo <<<HTML
		<table bgcolor="{$color[$color_index]}" border="0" cellpadding="0" cellspacing="10" width="1050">
			<tr>
				<td width="5%">
				</td>
				<td width="90%" class="body">
					<span style="color: #606060">作者：</span>&nbsp;<span style="color: #909090">{$username} ({$nickname})</span>
				</td>
				<td width="5%">
				</td>
			</tr>
			<tr>
				<td>
				</td>
				<td class="body">
					<span style="color: #606060">标题：</span>
					<img src="{$section_path}../../bbs/images/expression/{$article['icon']}.gif">
					<span class="title_normal">
						{$title_f}
					</span>
					{$transship_info}
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td>
				</td>
				<td class="body">
					<span style="color: #606060">来自：</span>&nbsp;<span style="color: #909090">{$article["sub_ip"]}</span>
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td>
				</td>
				<td class="body">
					<span style="color: #606060">发贴时间：</span>&nbsp;<span style="color: #909090">{$article["sub_dt"]->format("Y年m月d日 H:i:s (\U\T\C P)")}</span>
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td>
				</td>
				<td class="body">
					<span style="color: #606060">长度：</span>&nbsp;<span style="color: #909090">{$article["length"]}字</span>
				</td>
				<td>
				</td>
			</tr>
			<tr height="2">
				<td>
				</td>
				<td style="background-color: #909090">
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td>
				</td>
				<td class="content_normal">
					<pre>{$content_f}</pre>
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td>
				</td>
				<td style="color: #000000">
					========== * * * * * ==========
					<br>
					{$atta_list}
					</td>
				<td>
				</td>
			</tr>
		</table>
		HTML;
	}

	echo <<<HTML
			<table border="0" cellpadding="5" cellspacing="0" width="1050">
				<tr bgcolor="#d0d3F0" height="10">
					<td>
					</td>
				</tr>
				<tr>
					<td>
						<a href="index.html">上级目录</a>
					</td>
				</tr>
				<tr height="10">
					<td>
					</td>
				</tr>
				<tr>
					<td align="center">
						Copyright &copy; {$BBS_copyright_duration} {$BBS_name}({$BBS_host_name})<br />
						All Rights Reserved
					</td>
				</tr>
			</table>
		</center>
	</body>
	</html>
	HTML;
?>

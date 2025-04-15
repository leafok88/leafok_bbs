<?
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}

	require_once "../lib/common.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";

	$section_path = ($result_set["data"]["ex_dir"] != null ? str_repeat("../", substr_count($result_set["data"]["ex_dir"], "/")) : "");

	// Pre-defined color setting of article display
	$color = array(
		"#FAFBFC",
		"#f0F3Fa"
	);
	$color_index = 0;
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title><? echo htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'); ?></title>
		<link rel="stylesheet" href="<? echo $section_path; ?>../article.css" type="text/css">
		<script type="text/javascript" src="<? echo $section_path; ?>../img_adjust.js"></script>
	</head>
	<body>
		<a name="top"></a>
		<center>
			<table border="0" cellpadding="0" cellspacing="0" width="1050">
				<tr>
					<td>
						<a href="<? echo $section_path; ?>../index.html"><? echo $BBS_name; ?>精华区</a>&gt;&gt;<a href="<? echo $section_path; ?>index.html"><? echo $result_set["data"]["section_title"]; ?></a><?
						if ($result_set["data"]["ex_dir"] != null) {
						?>&gt;&gt;<a href="index.html"><? echo $result_set["data"]["ex_name"]; ?></a><?
						}
						?>
					</td>
				</tr>
				<tr bgcolor="#d0d3F0" height="25">
					<td align="center" class="title">
						[<? echo $result_set["data"]["id"]; ?>]&nbsp;主题：&nbsp;<? echo htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'); ?>
					</td>
				</tr>
			</table>
<?
	foreach ($result_set["data"]["articles"] as $article)
	{
		$color_index = ($color_index + 1) % count($color);

		if ($article["tid"] != 0)
		{
?>
			<a name="<? echo $article["aid"]; ?>"></a>
			<table bgcolor="<? echo $color[$color_index]; ?>" border="0" cellpadding="0" cellspacing="0" width="1050">
				<tr height="1" bgcolor="#202020">
					<td colspan="3">
					</td>
				</tr>
			</table>
<?
		}
?>
			<table bgcolor="<? echo $color[$color_index]; ?>" border="0" cellpadding="0" cellspacing="10" width="1050">
				<tr>
					<td width="5%">
					</td>
					<td width="90%" class="body">
						<span style="color:#606060; ">作者：</span>&nbsp;<span style="color:#909090; "><? echo htmlspecialchars($article["username"], ENT_HTML401, 'UTF-8'); ?> (<? echo htmlspecialchars($article["nickname"], ENT_HTML401, 'UTF-8'); ?>)</span>
					</td>
					<td width="5%">
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td class="body">
						<span style="color:#606060; ">标题：</span>&nbsp;<span style="color:#909090; "><? echo split_line(htmlspecialchars($article["title"], ENT_HTML401, 'UTF-8'), "", 65, 2, "<br />"); ?></span><? if ($article["transship"]) { ?><font color="red">[转载]</font><? } ?>
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td class="body">
						<span style="color:#606060; ">来自：</span>&nbsp;<span style="color:#909090; "><? echo $article["sub_ip"]; ?></span>
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td class="body">
						<span style="color:#606060; ">发贴时间：</span>&nbsp;<span style="color:#909090; "><? echo $article["sub_dt"]->format("Y年m月d日 H:i:s (\U\T\C P)"); ?></span>
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td class="body">
						<span style="color:#606060; ">长度：</span>&nbsp;<span style="color:#909090; "><? echo $article["length"]; ?>字</span>
					</td>
					<td>
					</td>
				</tr>
				<tr height="2">
					<td>
					</td>
					<td style="background-color:#909090; ">
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td style="font-size: 16px; ">
						<? echo LML(htmlspecialchars($article["content"], ENT_HTML401, 'UTF-8'), true, true, 80); ?>
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td style="color:#000000; ">
						========== * * * * * ==========
						<br>
<?
		foreach ($article["attachments"] as $attachment)
		{
			$filename = $attachment["filename"];
			$ext = strtolower(substr($filename, (strrpos($filename, ".") ? strrpos($filename, ".") + 1 : 0)));

			if (!copy("../bbs/upload/" . $attachment["aid"], "../gen_ex/attachment/" . $attachment["aid"] . ".$ext"))
			{
				echo ("Copy file error!");
				exit();
			}
?>
						<a class="s2" href="<? echo $section_path; ?>../attachment/<? echo $attachment["aid"] . ".$ext"; ?>" target="_target"><? echo $filename; ?></a> (<? echo $attachment["size"]; ?>字节)<br>
<?
			switch ($ext)
			{
				case "bmp":
				case "gif":
				case "jpg":
				case "jpeg":
				case "png":
				case "tif":
				case "tiff":
?>
						<img onmousewheel="return bbs_img_zoom(event, this)" src="<? echo $section_path; ?>../attachment/<? echo $attachment["aid"] . ".$ext"; ?>">
<?
					break;
			}
?><br /><?
		}
?>
					</td>
					<td>
					</td>
				</tr>
			</table>
<?
	} 
?>
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
						Copyright &copy; <? echo $BBS_copyright_duration; ?> <? echo $BBS_name . "(" . $BBS_host_name . ")"; ?><br /> 
						All Rights Reserved
					</td>
				</tr>
			</table>
		</center>
	</body>
</html>

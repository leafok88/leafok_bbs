<?php
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
		<title><?= htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'); ?></title>
		<link rel="stylesheet" href="<?= $section_path; ?>../article.css" type="text/css">
		<script type="text/javascript" src="<?= $section_path; ?>../img_adjust.js"></script>
	</head>
	<body>
		<a name="top"></a>
		<center>
			<table border="0" cellpadding="0" cellspacing="0" width="1050">
				<tr>
					<td>
						<a href="<?= $section_path; ?>../index.html"><?= $BBS_name; ?>精华区</a>&gt;&gt;<a href="<?= $section_path; ?>index.html"><?= $result_set["data"]["section_title"]; ?></a><?php
						if ($result_set["data"]["ex_dir"] != null) {
						?>&gt;&gt;<a href="index.html"><?= $result_set["data"]["ex_name"]; ?></a><?php
						}
						?>
					</td>
				</tr>
				<tr bgcolor="#d0d3F0" height="25">
					<td align="center" class="title">
						[<?= $result_set["data"]["id"]; ?>]&nbsp;主题：&nbsp;<?= htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'); ?>
					</td>
				</tr>
			</table>
<?php
	foreach ($result_set["data"]["articles"] as $article)
	{
		$color_index = ($color_index + 1) % count($color);

		if ($article["tid"] != 0)
		{
?>
			<a name="<?= $article["aid"]; ?>"></a>
			<table bgcolor="<?= $color[$color_index]; ?>" border="0" cellpadding="0" cellspacing="0" width="1050">
				<tr height="1" bgcolor="#202020">
					<td colspan="3">
					</td>
				</tr>
			</table>
<?php
		}
?>
			<table bgcolor="<?= $color[$color_index]; ?>" border="0" cellpadding="0" cellspacing="10" width="1050">
				<tr>
					<td width="5%">
					</td>
					<td width="90%" class="body">
						<span style="color:#606060; ">作者：</span>&nbsp;<span style="color: #909090; "><?= htmlspecialchars($article["username"], ENT_HTML401, 'UTF-8'); ?> (<?= htmlspecialchars($article["nickname"], ENT_HTML401, 'UTF-8'); ?>)</span>
					</td>
					<td width="5%">
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td class="body">
						<span style="color:#606060; ">标题：</span>&nbsp;<span style="color: #909090; "><?= split_line(htmlspecialchars($article["title"], ENT_HTML401, 'UTF-8'), "", 65, 2, "<br />"); ?></span><?php if ($article["transship"]) { ?><font color="red">[转载]</font><?php } ?>
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td class="body">
						<span style="color:#606060; ">来自：</span>&nbsp;<span style="color: #909090; "><?= $article["sub_ip"]; ?></span>
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td class="body">
						<span style="color:#606060; ">发贴时间：</span>&nbsp;<span style="color: #909090; "><?= $article["sub_dt"]->format("Y年m月d日 H:i:s (\U\T\C P)"); ?></span>
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td class="body">
						<span style="color:#606060; ">长度：</span>&nbsp;<span style="color: #909090; "><?= $article["length"]; ?>字</span>
					</td>
					<td>
					</td>
				</tr>
				<tr height="2">
					<td>
					</td>
					<td style="background-color: #909090; ">
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td style="font-size: 16px; ">
						<pre><?= LML(htmlspecialchars($article["content"], ENT_HTML401, 'UTF-8'), true, true, 80); ?></pre>
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
<?php
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
						<a class="s2" href="<?= $section_path; ?>../attachment/<?= $attachment["aid"] . ".$ext"; ?>" target="_target"><?= $filename; ?></a> (<?= $attachment["size"]; ?>字节)<br>
<?php
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
						<br /><img onmousewheel="return bbs_img_zoom(event, this)" src="<?= $section_path; ?>../attachment/<?= $attachment["aid"] . ".$ext"; ?>">
<?php
					break;
			}
?><br /><?php
		}
?>
					</td>
					<td>
					</td>
				</tr>
			</table>
<?php
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
						Copyright &copy; <?= $BBS_copyright_duration; ?> <?= $BBS_name . "(" . $BBS_host_name . ")"; ?><br /> 
						All Rights Reserved
					</td>
				</tr>
			</table>
		</center>
	</body>
</html>

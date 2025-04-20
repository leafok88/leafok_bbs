<?
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}

	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./user_level.inc.php";

	$article = $result_set["data"]["articles"][0];
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><? echo $BBS_name; ?> - <? echo htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'); ?> </title>
<link rel="stylesheet" href="<? echo get_theme_file('css/default'); ?>" type="text/css">
<style type="text/css">
TD.content
{
	font-size: 16px;
	line-height: 26px;
}
</style>
<script type="text/javascript" src="../js/img_adjust.js"></script>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3013347141025996" crossorigin="anonymous">
</script>
</head>
<body>
<center>
<?
	include "../www/head.inc.php";
?>
<table width="1050" border="0" cellpadding="0" cellspacing="0">
	<tr height=20 bgcolor=#F3F9FC> 
		<td width="20">&nbsp;</td>
		<td><? echo $BBS_name; ?> &gt;&gt; <? echo split_line(htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'), "", 65, 2); ?></td>
		<td width="200" align="right">本文已被浏览<font color=red><? echo $result_set["data"]["view_count"]; ?></font>次</td>
		<td width="20">&nbsp;</td>
	</tr>
	<tr>
		<td colspan=4 height=1 bgcolor=gray></td>
	</tr>
	<tr height=5>
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
					<td align="center"><font size=4><b><? echo split_line(htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'), "", 70, 2); ?></b></font> <font color=gray>[<? echo ($article["transship"]?"转载":"原创"); ?>]</font></td>
				</tr>
				<tr height="25">
					<td align="center">(<? echo $article["sub_dt"]->format("Y-m-d H:i:s"); ?>)   <? echo ($article["transship"]?"转载":"作者"); ?>：<? echo htmlspecialchars($article["nickname"], ENT_HTML401, 'UTF-8'); ?></td>
				</tr>
				<tr height="10">
					<td></td>
				</tr>
				<tr height="1">
					<td align="center" bgcolor=gray></td>
				</tr>
				<tr height="25">
					<td></td>
				</tr>
				<tr>
					<td class="content">
						<pre><? echo LML(htmlspecialchars($article["content"], ENT_HTML401, 'UTF-8'), true, true, 110); ?></pre>
					</td>
				</tr>
				<tr>
					<td>
						<br />
<?
		foreach ($article["attachments"] as $attachment)
		{
			$filename = $attachment["filename"];
			$ext = strtolower(substr($filename, (strrpos($filename, ".") ? strrpos($filename, ".") + 1 : 0)));
?>
						<img src="../www/images/dl.gif"><a class="s2" href="../bbs/dl_file.php?aid=<? echo $attachment["aid"]; ?>" target="_target"><? echo $filename; ?></a> (<? echo $attachment["size"]; ?>字节)
<?
			if ($attachment["check"] == 0)
			{
?><font color="red">未审核</font><?
			}
			else
			{
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
						<br /><img onmousewheel="return bbs_img_zoom(event, this)" src="../bbs/dl_file.php?aid=<? echo $attachment["aid"]; ?>">
<?
						break;
				}
			}
?>
			<br />
<?
		}
?>
					</td>
				</tr>
				<tr height="25">
					<td>
					</td>
				</tr>
				<tr height="25">
					<td align="right">
						已有<font color=red><? echo $result_set["data"]["reply_count"]; ?></font>人发表评论
					</td>
				</tr>
				<tr height="10">
					<td>
					</td>
				</tr>
				<tr>
					<td align="right">
						【<a href="/bbs/view_article.php?id=<? echo $aid; ?>" >相关评论</a>】
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
<?
	include "../www/foot.inc.php";
?>
</center>
</body>
</html>

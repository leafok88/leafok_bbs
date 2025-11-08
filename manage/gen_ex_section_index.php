<?php
	if (!isset($_SERVER["argc"]) || $_SERVER["argc"] != 2)
	{
		echo "Invalid usage";
		exit();
	}

	chdir(dirname($_SERVER["argv"][0]));

	require_once "../lib/db_open.inc.php";
	require_once "../lib/common.inc.php";

	$sid = intval($_SERVER["argv"][1]);

	$sql = "SELECT SID, title FROM section_config WHERE
			SID = $sid AND enable LIMIT 1";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query section error: " . mysqli_error($db_conn));
		exit();
	}
	if ($row = mysqli_fetch_array($rs))
	{
		$sid = $row["SID"];
		$section_title = $row["title"];
	}
	else
	{
		echo ("版块不存在！");
		exit();
	}
	mysqli_free_result($rs);

	$sql = "SELECT dir, name, SUM(cc) AS cc FROM (
			SELECT dir, name, COUNT(bbs.AID) AS cc FROM bbs
			LEFT JOIN ex_file ON bbs.AID = ex_file.AID
			LEFT JOIN ex_dir ON ex_file.FID = ex_dir.FID
			WHERE bbs.SID = $sid AND TID = 0 AND visible AND gen_ex
			AND ex_dir.SID = $sid AND ex_dir.enable
			GROUP BY dir, name
			UNION SELECT dir, name, 0 AS cc FROM ex_dir
			LEFT JOIN ex_file ON ex_dir.FID = ex_file.FID
			LEFT JOIN bbs ON ex_file.AID = bbs.AID
			WHERE ex_dir.SID = $sid AND ex_dir.enable
			AND TID IS NULL
			UNION SELECT NULL, NULL, 0 AS cc
			ORDER BY dir ) AS r1
			GROUP BY dir, name ORDER BY dir";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query index error: " . mysqli_error($db_conn));
		exit();
	}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title><?= $section_title; ?></title>
		<link rel="stylesheet" href="../list.css" type="text/css">
		</style>
	</head>
	<body>
		<a name="top"></a>
		<center>
			<table cols="3" border="0" cellpadding="5" cellspacing="0" width="1050">
				<tr>
					<td width="10%">
					</td>
					<td width="80%" align="center" style="font-size:16px;">
						---====== ※<?= $section_title; ?>版块索引※ [<?= "更新时间：" . date("Y年m月d日"); ?>] ======---
					</td>
					<td width="10%">
					</td>
				</tr>
				<tr height="10">
					<td colspan="3" align="center">
						<hr>
					</td>
				</tr>
<?php
	while ($row = mysqli_fetch_array($rs))
	{
		$level = substr_count(($row["dir"] ? $row["dir"] : ""), "/");
		$prefix = str_repeat("|<span style=\"visibility: hidden;\">---</span>", ($level ? $level - 1 : 0)) . ($level ? "|---" : "");
?>
				<tr height="10">
					<td>
					</td>
					<td>
						<?= $prefix; ?>-&nbsp;<a href="<?= ($row["dir"] ? $row["dir"] : ""); ?>index.html"><?= ($row["name"] ? $row["name"] : "(根目录)"); ?></a>&nbsp;[<?= $row["cc"]; ?>]
					</td>
					<td>
					</td>
				</tr>
<?php
	}
	mysqli_free_result($rs);

	mysqli_close($db_conn);

	$current_tm = (new DateTimeImmutable())->format("Y-m-d H:i:s (\U\T\C P)");

	// Calculate executing durations
	$page_exec_duration = round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000, 2);
?>
				<tr height="10">
					<td colspan="3" align="center">
						<hr>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						<a href="index.html">上级目录</a>
					</td>
					<td>
					</td>
				</tr>
				<tr height="10">
					<td colspan="3">
					</td>
				</tr>
				<tr>
					<td colspan="3" align="center">
						Copyright &copy; <?= $BBS_copyright_duration; ?> <?= $BBS_name . "(" . $BBS_host_name . ")"; ?>	All Rights Reserved<br />
						页面更新于<?=$current_tm?>，使用<?=$page_exec_duration?>毫秒
					</td>
				</tr>
			</table>
		</center>
	</body>
</html>

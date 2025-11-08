<?php
	require_once "../lib/db_open.inc.php";
	require_once "../lib/common.inc.php";

	$sql = "SELECT section_config.SID, section_config.CID, section_config.sname,
			section_config.title AS s_title, section_class.title AS c_title FROM section_config
			INNER JOIN section_class ON section_config.CID=section_class.CID
			WHERE section_config.enable AND section_class.enable
			ORDER BY section_class.sort_order, section_config.sort_order";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query section error: " . mysqli_error($db_conn));
		exit();
	}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title><?= $BBS_name; ?>精华区</title>
		<link rel="stylesheet" href="list.css" type="text/css">
	</head>
	<body>
		<a name="top"></a>
		<center>
			<table cols="3" border="0" cellpadding="5" cellspacing="0" width="1050">
				<tr>
					<td colspan="3" align="center" style="font-size: 18px;">
						--===== ※<?= $BBS_name; ?>精华区※ =====--
					</td>
				</tr>
				<tr height="10">
					<td colspan="3">
						<hr>
					</td>
				</tr>
				<tr>
					<td width="30%" align="center">
						所属类别
					</td>
					<td width="30%" align="left">
						版块标识
					</td>
					<td width="40%" align="left">
						版块名称
					</td>
				</tr>
<?php
	$last_cid = -1;

	while ($row = mysqli_fetch_array($rs))
	{
?>
				<tr>
					<td align="center">
						<?= ($row["CID"] == $last_cid ? "" : $row["c_title"]); ?>
					</td>
					<td align="left">
						<?= $row["sname"]; ?>
					</td>
					<td align="left">
						<a href="<?= $row["SID"]; ?>/index.html"><?= $row["s_title"]; ?></a>
					</td>
				</tr>
<?php
		$last_cid = $row["CID"];
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
					<td colspan="3" align="center">
						Copyright &copy; <?= $BBS_copyright_duration; ?> <?= $BBS_name . "(" . $BBS_host_name . ")"; ?>	All Rights Reserved<br />
						页面更新于<?=$current_tm?>，使用<?=$page_exec_duration?>毫秒
					</td>
				</tr>
			</table>
		</center>
	</body>
</html>

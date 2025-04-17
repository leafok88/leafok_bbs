<?
	require_once "../lib/db_open.inc.php";
	require_once "../bbs/session_init.inc.php";

	force_login();

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		),
		"data" => array(
			"upload_files" => array(),
		),
	);

	if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "SELECT upload_file.*, username FROM upload_file
			INNER JOIN user_list ON upload_file.UID = user_list.UID
			WHERE `check` = 0 AND deleted = 0";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query data error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	while ($row = mysqli_fetch_array($rs))
	{
		array_push($result_set["data"]["upload_files"], array(
			"aid" => $row["AID"],
			"ref_aid" => $row["ref_AID"],
			"username" => $row["username"],
			"filename" => $row["filename"],
			"size" => $row["size"],
		));
	}
	mysqli_free_result($rs);

	mysqli_close($db_conn);
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>BBS附件审核</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p align="center" style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">
			BBS附件审核
		</p>
		<center>
			<table cols="4" border="1" width="95%" cellpadding="5">
				<tr style="font-weight:bold;" height=20>
					<td width="30%" align="center">
						用户
					</td>
					<td width="20%" align="middle">
						关联文章
					</td>
					<td width="30%" align="middle">
						附件
					</td>
					<td width="20%" align="center">
						处理
					</td>
				</tr>
<?
	foreach ($result_set["data"]["upload_files"] as $upload_file)
	{
?>
				<tr height=20>
					<td align="middle">
						<? echo $upload_file["username"]; ?>
					</td>
					<td align="middle">
						<a href="../bbs/view_article.php?trash=1&id=<? echo $upload_file["ref_aid"] . "#" . $upload_file["ref_aid"]; ?>" target=_blank>
							<? echo $upload_file["ref_aid"]; ?>
						</a>
					</td>
					<td align="middle">
						<a href="/bbs/dl_file.php?aid=<? echo $upload_file["aid"]; ?>&force=1" target=_blank><? echo $upload_file["filename"]; ?></a> (<? echo $upload_file["size"]; ?>字节)
					</td>
					<td align="middle">
						<a href="upload_process.php?enable=1&aid=<? echo $upload_file["aid"]; ?>">通过</a>&nbsp;&nbsp;&nbsp;&nbsp;
						<a href="upload_process.php?enable=0&aid=<? echo $upload_file["aid"]; ?>" onclick="return window.confirm('真的要删除吗？');">删除</a>
					</td>
				</tr>
<?
	}
?>
			</table>
		</center>
	</body>
</html>

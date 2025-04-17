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
			"article_ops" => array(),
		),
	);

	$page = isset($_POST["page"]) ? intval($_POST["page"]) : 1;
	if ($page <= 0)
	{
		$page = 1;
	}

	$line = 20;

	if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "SELECT MID, bbs.AID, bbs.TID, username, type, op_dt, op_ip
			FROM bbs_article_op INNER JOIN bbs ON bbs_article_op.AID = bbs.AID
			WHERE type IN ('A','M','X')
			ORDER BY MID DESC LIMIT " . ($page - 1) * $line . ", " . $line;

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
		array_push($result_set["data"]["article_ops"], array(
			"mid" => $row["MID"],
			"aid" => $row["AID"],
			"tid" => $row["TID"],
			"username" => $row["username"],
			"op_dt" => $row["op_dt"],
			"op_ip" => $row["op_ip"],
			"type" => $row["type"],
		));
	}
	mysqli_free_result($rs);

	mysqli_close($db_conn);
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>文章审核</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p align="center" style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">
			文章审核
		</p>
		<form action="article_audit.php" method="post" name="scope" id="scope">
			请输入页号：<input name="page" id="page" value="<? echo $page; ?>" size="3">
			<input type="submit" name="submit" value="显示">
			<input type="submit" name="decrease" value="<" onclick="scope.page.value--;">
			<input type="submit" name="increase" value=">" onclick="scope.page.value++;">
		</form>
		<center>
			<table cols=4 border="1" width="95%">
				<tr style="font-weight:bold;" height=20>
					<td width="10%" align="middle">
						操作编号
					</td>
					<td width="25%" align="middle">
						时间/地址
					</td>
					<td width="25%" align="center">
						用户
					</td>
					<td width="25%" align="middle">
						主题/文章编号
					</td>
					<td width="10%" align="center">
						用户操作
					</td>
				</tr>
<?
	foreach ($result_set["data"]["article_ops"] as $article_op)
	{
?>
				<tr height=20>
					<td align="middle">
						<? echo $article_op["mid"]; ?>
					</td>
					<td align="middle">
						<? echo $article_op["op_dt"] . "<br>" . $article_op["op_ip"]; ?>
					</td>
					<td align="middle">
						<? echo $article_op["username"]; ?>
					</td>
					<td align="middle">
						<a href="../bbs/view_article.php?trash=1&id=<? echo $article_op["aid"] . "#" . $article_op["aid"]; ?>" target=_blank>
							<? echo $article_op["tid"] . "/" . $article_op["aid"]; ?>
						</a>
					</td>
					<td align="middle">
<?
		switch($article_op["type"])
		{
			case "A":
				echo ("发表");
				break;
			case "M":
				echo ("修改");
				break;
			case "X":
				echo ("版主删除");
				break;
			default:
				echo ("未知");
				break;
		}
?>
					</td>
				</tr>
<?
	}
?>
			</table>
		</center>
	</body>
</html>

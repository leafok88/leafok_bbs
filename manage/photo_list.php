<?php
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
			"user_photos" => array(),
		),
	);

	if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "SELECT user_pubinfo.UID, username, photo_ext FROM user_pubinfo
			INNER JOIN user_list ON user_pubinfo.UID = user_list.UID
			WHERE photo = 999 AND photo_enable = 0 AND photo_ext <> ''";

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
		array_push($result_set["data"]["user_photos"], array(
			"uid" => $row["UID"],
			"username" => $row["username"],
			"photo_ext" => $row["photo_ext"],
		));
	}
	mysqli_free_result($rs);

	mysqli_close($db_conn);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>BBS头像审核</title>
<link rel="stylesheet" href="css/default.css" type="text/css">
</head>
<body>
<center>
	<p style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">
		BBS头像审核
	</p>
	<table cols=4 border="1" width="95%">
		<tr style="font-weight:bold;" height=20>
			<td width="15%" align="center">
				用户
			</td>
			<td width="40%" align="middle">
				头像
			</td>
			<td width="10%" align="center">
				处理
			</td>
		</tr>
<?php
	foreach ($result_set["data"]["user_photos"] as $user_photo)
	{
?>
		<tr height=20>
			<td align="middle">
				<?= $user_photo["username"]; ?>
			</td>
			<td align="middle">
				<img src="../bbs/images/face/upload_photo/face_<?= $user_photo["uid"] . "." . $user_photo["photo_ext"]; ?>">
			</td>
			<td align="middle">
				<a href="photo_process.php?enable=1&p_id=<?= $user_photo["uid"]; ?>">通过</a><br>
				<a href="photo_process.php?enable=0&p_id=<?= $user_photo["uid"]; ?>" onclick="return window.confirm('真的要拒绝吗？');">拒绝</a>
			</td>
		</tr>
<?php
	}
?>
	</table>
</center>
</body>
</html>

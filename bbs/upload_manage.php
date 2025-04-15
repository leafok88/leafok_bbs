<?
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";
?>
<?
	force_login();

	$upload_limit = 0;
	$upload_used = 0;

	$sql = "SELECT upload_limit FROM user_pubinfo WHERE UID = " . $_SESSION["BBS_uid"];
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query upload limit error: " . mysqli_error($db_conn));
		exit();
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$upload_limit = $row["upload_limit"];
	}
	mysqli_free_result($rs);

	$sql = "SELECT * FROM upload_file WHERE UID = " . $_SESSION["BBS_uid"] .
			" AND deleted = 0 ORDER BY AID DESC";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query upload file error: " . mysqli_error($db_conn));
		exit();
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>上传管理</title>
<link rel="stylesheet" href="css/default.css" type="text/css">
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
function refresh_err_msg(errorFieldMap)
{
	document.getElementsByName("err_msg").forEach(element => {
		element.innerHTML = (errorFieldMap.has(element.id) ? errorFieldMap.get(element.id) : "");
	});
}

function upload_del(id)
{
	if (window.confirm('真的要删除吗？') == false)
	{
		return false;
	}

	instance.post('upload_del.php', {
        aid: id
    })
    .then(function (response) {
        var ret = response.data;
		var errorFieldMap = new Map();
        switch (ret.return.code)
        {
			case 0: // OK
			case 1: // Already deleted
				document.getElementById("attachment_" + id).style.display = "none";
				refresh_err_msg(errorFieldMap);
				break;
			case -1: // Input validation failed
				errorFieldMap.set("err_msg_attachment_" + id, ret.return.message);
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_attachment_" + id, "内部错误");
				refresh_err_msg(errorFieldMap);
				break;
			default:
				console.log(ret.return.code);
				break;
        }
    })
    .catch(function (error) {
        console.log(error);
    });

	return false;
}

function upload_limit_add()
{
	if (window.confirm('真的要付费扩容吗？') == false)
	{
		return false;
	}

	instance.post('upload_limit_add.php', {
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
				refresh_err_msg(errorFieldMap);
				document.location = "upload_manage.php?ts=" + Date.now();
				break;
			case -1: // Input validation failed
				ret.return.errorFields.forEach(field => {
					errorFieldMap.set("err_msg_" + field.id, field.errMsg);
				});
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_limit", "内部错误");
				refresh_err_msg(errorFieldMap);
				break;
			default:
				console.log(ret.return.code);
				break;
		}
	})
	.catch(function (error) {
		console.log(error);
	});

	return false;
}

const instance = axios.create({
    withCredentials: true,
    timeout: 3000,
    baseURL: document.location.protocol + '//' + document.location.hostname + (document.location.port=='' ? '' : (':' + document.location.port)) + '/bbs/',
});

</script>
</head>
<body>
	<center>
		<table border="0" cellpadding="1" cellspacing="0" width="1050">
			<tr bgcolor="#d0d3F0" height="25">
				<td width="40%" align="center" class="title">
					附件
				</td>
				<td width="20%" align="middle" class="title">
					状态
				</td>
				<td width="30%" align="center" class="title">
					处理
				</td>
			</tr>
<? 
	while ($row = mysqli_fetch_array($rs))
	{
		$upload_limit -= $row["size"];
		$upload_used += $row["size"];
?>
			<tr bgcolor="#f0F3Fa" height="30" id="attachment_<? echo $row["AID"]; ?>">
				<td align="middle">
					<a class="s2" href="dl_file.php?aid=<? echo $row["AID"]; ?>"><? echo $row["filename"]; ?></a> (<? echo $row["size"]; ?>字节)
				</td>
				<td align="middle">
					<? echo ($row["check"]?($row["deny"]?"<font color=red>未通过</font>":"<font color=green>已通过</font>"):"<font color=blue>未审核</font>"); ?>
				</td>
				<td align="middle">
					<a class="s2" href="#" onclick="return upload_del(<? echo $row["AID"]; ?>);">删除</a>
					<span id="err_msg_attachment_<? echo $row["AID"]; ?>" name="err_msg" style="color: red;"></span>
				</td>
			</tr>
<? 
	}
	mysqli_free_result($rs);

	mysqli_close($db_conn);
?>
			<tr bgcolor="#f0F3Fa" height="5">
				<td colspan="3">
				</td>
			</tr>
			<tr height="25">
				<td align="middle">
					<font color=blue>剩余/总空间</font> <? echo round(($upload_limit - $upload_used) / 1024 / 1024, 1); ?>MB / <? echo round($upload_limit / 1024 / 1024, 1); ?>MB
				</td>
				<td align="middle">
					<a class="s2" href="#" onclick="return upload_limit_add();">增加10MB空间</a>
					(每次收取10积分)
				</td>
				<td align="middle">
					<span id="err_msg_limit" name="err_msg" style="color: red;"></span>
				</td>
			</tr>
			<tr bgcolor="#d0d3F0" height="5">
				<td colspan="3" class="title">
				</td>
			</tr>
		</table>
	</center>
</body>
</html>

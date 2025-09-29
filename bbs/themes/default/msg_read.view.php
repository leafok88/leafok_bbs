<?php
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>查看消息——<?= ($result_set["data"]["sent"] ? "发件箱" : "收件箱"); ?></title>
<link rel="stylesheet" href="css/default.css" type="text/css">
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
function ch_page(page)
{
	rpp = document.getElementById("rpp").value;
	document.location = "msg_read.php?sent=<?= ($result_set["data"]["sent"] ? "1" : "0"); ?>&page=" + page + "&rpp=" + rpp + "&ts=" + Date.now();
	return false;
}

function ch_rpp()
{
	page = document.getElementById("page").value;
	rpp = document.getElementById("rpp").value;
	page = Math.floor((page - 1) * <?= $result_set["data"]["rpp"]; ?> / rpp) + 1;
	document.location = "msg_read.php?sent=<?= ($result_set["data"]["sent"] ? "1" : "0"); ?>&page=" + page + "&rpp=" + rpp + "&ts=" + Date.now();
	return false;
}

function refresh_page()
{
	page = document.getElementById("page").value;
	rpp = document.getElementById("rpp").value;
	document.location = "msg_read.php?sent=<?= ($result_set["data"]["sent"] ? "1" : "0"); ?>&page=" + page + "&rpp=" + rpp + "&ts=" + Date.now();
	return false;
}

function set_checkboxes(do_check)
{
	document.getElementsByName("delete_msg_id").forEach(element => {
		element.checked = do_check;
	});
	return true;
}

function show_send_msg(uid, nickname)
{
	document.getElementById("uid").value = uid;
	document.getElementById("nickname").value = nickname;
	document.getElementById("content").value = "";
	document.getElementById("tr_send_msg").style.visibility = "visible";

	return false;
}

function hide_send_msg()
{
	document.getElementById("uid").value = 0;
	document.getElementById("nickname").value = "";
	document.getElementById("content").value = "";
	document.getElementById("tr_send_msg").style.visibility = "collapse";

	return false;
}

function refresh_err_msg(errorFieldMap)
{
	document.getElementsByName("err_msg").forEach(element => {
		element.innerHTML = (errorFieldMap.has(element.id) ? errorFieldMap.get(element.id) : "");
	});
}

function send_msg()
{
	instance.post('msg_service_send.php', {
		uid: document.getElementById("uid").value,
		content: document.getElementById("content").value,
    }, {
		headers: {
			'Content-Type': 'multipart/form-data',
		}
	})
    .then(function (response) {
        var ret = response.data;
		var errorFieldMap = new Map();
        switch (ret.return.code)
        {
			case 0: // OK
				errorFieldMap.set("err_msg_delete", "发送成功");
				refresh_err_msg(errorFieldMap);
				hide_send_msg();
				break;
			case -1: // Input validation failed
				errorFieldMap.set("err_msg_send", ret.return.message + "<br />");
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_send", "内部错误<br />");
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

function delete_msg()
{
	let delete_msg_id = [];
	document.getElementsByName("delete_msg_id").forEach(element => {
		if (element.checked)
		{
			delete_msg_id.push(element.value);
		}
	});

	instance.post('msg_service_del.php', {
		sent: <?= ($result_set["data"]["sent"] ? "1" : "0"); ?>,
		delete_msg_id: delete_msg_id,
    })
    .then(function (response) {
        var ret = response.data;
		var errorFieldMap = new Map();
        switch (ret.return.code)
        {
			case 0: // OK
				refresh_err_msg(errorFieldMap);
				refresh_page();
				break;
			case -1: // Input validation failed
				errorFieldMap.set("err_msg_delete", ret.return.message);
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_delete", "内部错误");
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
		<table cols="2" border="0" cellpadding="5" cellspacing="0" width="1050">
			<tr id="tr_send_msg" style="visibility: <?= ($result_set["data"]["uid"] > 0 ? "visible" : "collapse"); ?>">
				<td width="70%">
					<input type="hidden" id="uid" name="uid" value="<?= $result_set["data"]["uid"]; ?>">
					<p>
						<span id="err_msg_send" name="err_msg" style="color: red"></span>
						发送给：<input id="nickname" name="nickname" value="<?= $result_set["data"]["nickname"]; ?>" readonly><br />
						内容：<br />
						<textarea id="content" name="content" rows="10" cols="90"></textarea><br />
						不能超过10行，每行256字符以内
					</p>
					<p align="center">
						<a class="s2" href="#" onclick="return send_msg();">发送</a>&nbsp;&nbsp;
						<a class="s2" href="#" onclick="return hide_send_msg();">取消</a>
					</p>
				</td>
				<td width="30%">
				</td>
			</tr>
			<tr bgcolor="#ffdead" height="25">
				<td>
<?php
	if (!$result_set["data"]["sent"])
	{
?>
					<a class="s2" href="msg_read.php?sent=1">切换至发件箱</a>&nbsp;
<?php
		if ($result_set["data"]["unread_msg_count"] > 0)
		{
?>您有<span style="color:red;"><?= $result_set["data"]["unread_msg_count"]; ?></span>条未读消息<?php
		}
	}
	else
	{
?>
					<a class="s2" href="msg_read.php?sent=0">切换至收件箱</a>&nbsp;
<?php
	}
?>
					&nbsp;&nbsp;&nbsp;<span id="err_msg_delete" name="err_msg" style="color: red"></span>
				</td>
				<td align="right">
					共<?= ($result_set["data"]["sent"] ? "发送" : "有"); ?><span style="color:red;"><?= $result_set["data"]["msg_count"]; ?></span>条消息
				</td>
			</tr>
<?php
$color[0]="#faf5f5";
$color[1]="#f0f0f0";
$count=0;

foreach ($result_set["data"]["messages"] as $message)
{
?>
			<tr bgcolor="<?= $color[1]; ?>">
				<td>
					<?= ($result_set["data"]["sent"] ? "收件人" : "发送人"); ?>：<a class="s2" href="view_user.php?uid=<?= $message["uid"]; ?>" target=_blank title="查看用户资料"><?= $message["nickname"]; ?></a>
					&nbsp;&nbsp;发送时间：<?= $message["send_dt"]->format("Y-m-d H:i:s"); ?>
<?php
	if ($message["new"])
	{
?>
					<img src="images/new.gif">
<?php
	}
	if ($message["uid"] != $BBS_sys_uid)
	{
?>
					&nbsp;
					<a class="s2" href="" onclick="return show_send_msg(<?= $message["uid"]; ?>, '<?= $message["nickname"]; ?>');">
						<?= ($result_set["data"]["sent"] ? "发送消息" : "回复消息"); ?>
					</a>
<?php
	}
?>
				</td>
				<td align="right">
					<input type="checkbox" id="delete_msg_<?= $message["mid"]; ?>" name="delete_msg_id" value="<?= $message["mid"]; ?>">选中
				</td>
			</tr>
			<tr bgcolor="<?= $color[0]; ?>">
				<td colspan="2">
					<pre><?= LML(htmlspecialchars($message["content"], ENT_HTML401, 'UTF-8'), true, true, 100); ?></pre>
				</td>
			</tr>
<?php
}
?>
			<tr bgcolor="#ffdead" height="5">
				<td colspan="2">
				</td>
			</tr>
			<tr>
				<td style="color: #909090">
					每页<select size="1" id="rpp" name="rpp" onchange="ch_rpp();">
<?php
	foreach ($BBS_msg_rpp_options as $v)
	{
		echo ("<option value=\"$v\"" . ($v == $result_set["data"]["rpp"] ? " selected" : "") . ">$v</option>");
	}
?>
					</select>条
<?php
	if ($result_set["data"]["page"] > 1)
	{
?>
					<a class="s8" title="首页" href="" onclick="return ch_page(1);">|◀</a>
					<a class="s8" title="上一页" href="" onclick='return ch_page(<?= ($result_set["data"]["page"] - 1); ?>);'>◀</a>
<?php
	}
	else
	{
?>
					|◀ ◀
<?php
	}
?>
					第<input id="page" name="page" value="<?= ($result_set["data"]["page"]) ; ?>" style="width: 30px;">/<?= $result_set["data"]["page_total"]; ?>页
<?php
	if ($result_set["data"]["page"] < $result_set["data"]["page_total"])
	{
?>
					<a class="s8" title="下一页" href="" onclick="return ch_page(<?= ($result_set["data"]["page"] + 1); ?>);">▶</a>
					<a class="s8" title="尾页" href="" onclick="return ch_page(<?= ($result_set["data"]["page_total"]); ?>);">▶|</a>
<?php
	}
	else
	{
?>
					▶ ▶|
<?php
	}
?>
				</td>
				<td align="right">
					<a class="s2" onclick="delete_msg();" href="#">删除</a>
					<input type="checkbox" name="check_all" id="check_all" onclick="set_checkboxes(this.checked);">全选
				</td>
			</tr>
		</table>
	</center>
</body>
</html>

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
<title>更改个人设定</title>
<link rel="stylesheet" href="<?= get_theme_file('css/default'); ?>" type="text/css">
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
function NW_open(url, name, w, h)
{
	hwnd = window.open(url, name, "width=" + w + ", height=" + h + ", top=0, left=0, toolbar=no, scrollbars=yes, menubar=no, statusbar=0, location=no");
	hwnd.focus();
	return false;
}

function tz_select(s, value)
{
	for (i = 0; i < s.options.length; i++)
	{
		if (s.options[i].value == value)
		{
			s.selectedIndex = i;
			break;
		}
	}

	return false;
}

function refresh_err_msg(errorFieldMap)
{
	document.getElementsByName("err_msg").forEach(element => {
		element.innerHTML = (errorFieldMap.has(element.id) ? errorFieldMap.get(element.id) : "");
	});
}

function refresh_textarea(updateFieldMap)
{
	document.getElementsByName("textarea").forEach(element => {
		if (updateFieldMap.has(element.id))
		{
			element.value = updateFieldMap.get(element.id);
		}
	});
}

function update_preference(f)
{
	instance.post('user_service_update_pref.php', {
		user_tz: f.user_tz.value,
		photo: f.photo.value,
		photo_file: f.photo_file.files,
		introduction: f.textarea_introduction.value,
		sign_1: f.textarea_sign_1.value,
		sign_2: f.textarea_sign_2.value,
		sign_3: f.textarea_sign_3.value,
	}, {
		headers: {
			'Content-Type': 'multipart/form-data',
		}
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		var updateFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
				errorFieldMap.set("err_msg_prompt", "更新成功");
				refresh_err_msg(errorFieldMap);
				break;
			case -1: // Input validation failed
				ret.return.errorFields.forEach(field => {
					errorFieldMap.set("err_msg_" + field.id, field.errMsg + "<br />");
					updateFieldMap.set("textarea_" + field.id, field.updateValue);
				});
				refresh_err_msg(errorFieldMap);
				refresh_textarea(updateFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_prompt", "内部错误");
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

window.addEventListener("load", () => {
	var f = document.getElementById("preference_form");
	f.addEventListener("submit", (e) => {
		e.preventDefault();
		update_preference(f);
	});
});

</script>
</head>
<body>
<?php
	include get_theme_file("view/user_center_header");
?>
	<center>
		<p style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">更改个人设定</p>
		<p><span id="err_msg_prompt" name="err_msg" style="color: red"></span></p>
		<form method="post" action="#" id="preference_form" name="preference_form">
			<table border="1" cellpadding="10" cellspacing="0" width="1050" bgcolor="#ffdead">
				<tr>
					<td align="right">时区设置</td>
					<td>
						<span id="err_msg_user_tz" name="err_msg" style="color: red;"></span>
						<select id="user_tz" name="user_tz" size="1">
<?php
	$timezone_identifiers = DateTimeZone::listIdentifiers();
	foreach ($timezone_identifiers as $tz)
	{
?>
							<option value="<?= $tz; ?>" <?= ($tz == $result_set["data"]["user_tz"] ? "selected" : ""); ?>><?= $tz; ?></option>
<?php
	}
?>
						</select>
						<a class="s2" href="#" onclick="return tz_select(user_tz, '<?= $BBS_timezone; ?>');">恢复默认</a>
					</td>
				</tr>
				<tr>
					<td width="25%" align="right">个人头像</td>
					<td width="75%">
						<a class="s2" href="#" onclick="return NW_open('facelist.php', 'bbs_face', 250, 400);">选择系统头像</a>
						<input type="text" maxlength="3" id="photo" name="photo" size="3" value="<?= $result_set["data"]["photo"]; ?>"><br />
						上传头像：<input type="file" size="20" name="photo_file" id="photo_file"><span id="err_msg_photo_file" name="err_msg" style="color: red;"></span>
					</td>
				</tr>
				<tr>
					<td align="right">个人介绍</td>
					<td>
						<span id="err_msg_introduction" name="err_msg" style="color: red;"></span>
						<textarea id="textarea_introduction" name="textarea" cols="80" rows="7"><?= htmlspecialchars($result_set["data"]["introduction"], ENT_HTML401, 'UTF-8'); ?></textarea>
						限10行以内
					</td>
				</tr>
				<tr>
					<td align="right">签名1</td>
					<td>
						<span id="err_msg_sign_1" name="err_msg" style="color: red;"></span>
						<textarea id="textarea_sign_1" name="textarea" cols="80" rows="7"><?= htmlspecialchars($result_set["data"]["sign_1"], ENT_HTML401, 'UTF-8'); ?></textarea>
						限10行以内
					</td>
				</tr>
				<tr>
					<td align="right">签名2</td>
					<td>
						<span id="err_msg_sign_2" name="err_msg" style="color: red;"></span>
						<textarea id="textarea_sign_2" name="textarea" cols="80" rows="7"><?= htmlspecialchars($result_set["data"]["sign_2"], ENT_HTML401, 'UTF-8'); ?></textarea>
						限10行以内
					</td>
				</tr>
				<tr>
					<td align="right">签名3</td>
					<td>
						<span id="err_msg_sign_3" name="err_msg" style="color: red;"></span>
						<textarea id="textarea_sign_3" name="textarea" cols="80" rows="7"><?= htmlspecialchars($result_set["data"]["sign_3"], ENT_HTML401, 'UTF-8'); ?></textarea>
						限10行以内
					</td>
				</tr>
			</table>
			<p>
				<input type="submit" value="提交" name="Submit">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="reset" value="重填" name="Reset">
			</p>
		</form>
	</center>
</body>
</html>

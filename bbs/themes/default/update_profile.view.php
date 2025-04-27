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
<title>更改用户资料</title>
<link rel="stylesheet" href="<?= get_theme_file('css/default'); ?>" type="text/css">
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
function refresh_err_msg(errorFieldMap)
{
	document.getElementsByName("err_msg").forEach(element => {
		element.innerHTML = (errorFieldMap.has(element.id) ? errorFieldMap.get(element.id) : "");
	});
}

function update_profile(f)
{
	instance.post('user_service_update_profile.php', {
		nickname: f.nickname.value,
		realname: f.realname.value,
		gender: f.gender.value,
		gender_public: (f.gender_public.checked ? "1" : "0"),
		email: f.email.value,
		year: f.year.value,
		month: f.month.value,
		day: f.day.value,
		qq: f.qq.value,
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
				errorFieldMap.set("err_msg_prompt", "更新成功");
				refresh_err_msg(errorFieldMap);
				break;
			case -1: // Input validation failed
				ret.return.errorFields.forEach(field => {
					errorFieldMap.set("err_msg_" + field.id, field.errMsg + "<br />");
				});
				refresh_err_msg(errorFieldMap);
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
	var f = document.getElementById("profile_form");
	f.addEventListener("submit", (e) => {
		e.preventDefault();
		update_profile(f);
	});

	var s = document.getElementById("select_nick");
	s.addEventListener("change", (e) => {
		n = document.getElementById("nickname");
		n.value = s.options[s.selectedIndex].value;
	});

});

</script>
</head>
<body>
<?php
	include get_theme_file("view/user_center_header");
?>
	<center>
		<p style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">更改用户资料</p>
		<p><span id="err_msg_prompt" name="err_msg" style="color: red"></span></p>
		<form method="post" id="profile_form" name="profile_form" action="#">
			<table border="1" cellpadding="10" cellspacing="0" width="1050" bgcolor="#ffdead" id=TABLE1>
				<tr>
					<td width="25%" align="right">
						密码
					</td>
					<td width="75%">
						密码修改请<a href="user_reset_pass.php" target=_blank>点击此处</a>通过邮箱重置。
					</td>
				</tr>
				<tr>
					<td align="right">
						昵称
					</td>
					<td>
						<span id="err_msg_nickname" name="err_msg" style="color: red;"></span><input id="nickname" name="nickname" value="<?= htmlspecialchars($result_set["data"]["nickname"], ENT_HTML401, 'UTF-8'); ?>">
						<span style="color: red">*</span>
						<select name="select_nick" id="select_nick">
						<option value="">----曾用昵称免费----</option>
<?php
	foreach ($result_set["data"]["nicknames"] as $nickname)
	{
		echo <<<HTML
						<option value="{$nickname}">{$nickname}</option>
		HTML;
	}
?>
						</select>
						使用新昵称每次收取2个积分
					</td>
				</tr>
				<tr>
					<td align="right">
						姓名
					</td>
					<td>
						<span id="err_msg_realname" name="err_msg" style="color: red;"></span><input id="realname" name="realname" value="<?= htmlspecialchars($result_set["data"]["name"], ENT_HTML401, 'UTF-8'); ?>">
						<span style="color: red">*</span>
						长度不超过5个全角字符
					</td>
				</tr>
				<tr>
					<td align="right">
						性别
					</td>
					<td>
						<span id="err_msg_gender" name="err_msg" style="color: red;"></span><input type="radio" id="gender_male" name="gender" value="M" <?= ($result_set["data"]["gender"] == "M" ? "checked" : ""); ?>>男
						<input type="radio" id="gender_female" name="gender" value="F" <?= ($result_set["data"]["gender"] == "F" ? "checked" : ""); ?>>女
						<span style="color: red">*</span>
						<input type="checkbox" id="gender_public" name="gender_public" value="1" <?= ($result_set["data"]["gender_pub"] ? "checked" : ""); ?>>公开
					</td>
				</tr>
				<tr>
					<td align="right">
						邮件地址
					</td>
					<td>
						<span id="err_msg_email" name="err_msg" style="color: red;"></span><input id="email" name="email" value="<?= $result_set["data"]["email"]; ?>">
						<span style="color: red">*</span>
						修改邮箱后，请按照确认邮件提示操作<br>
					</td>
				</tr>
				<tr>
					<td align="right">
						出生日期
					</td>
					<td>
						<span id="err_msg_birthday" name="err_msg" style="color: red;"></span><select id="year" name="year" size="1">
<?php
	$birthday = (new DateTimeImmutable($result_set["data"]["birthday"]));

	$year_current = intval(date("Y", time()));
	$year_max = $year_current - 16; // Accept registrant of 16+ only
	$year_min = $year_current - 80;
	$year_selected = intval($birthday->format("Y"));
	for ($year = $year_min; $year <= $year_max; $year++)
	{
		$option_year_selected = ($year == $year_selected ? "selected" : "");

		echo <<<HTML
							<option value="{$year}" {$option_year_selected}>{$year}</option>
		HTML;
	}
?>

						</select>年
						<select id="month" name="month" size="1">
<?php
	$month_selected = intval($birthday->format("m"));
	for ($month = 1; $month <= 12; $month++)
	{
		$option_month_selected = ($month == $month_selected ? "selected" : "");

		echo <<<HTML
							<option value="{$month}" {$option_month_selected}>{$month}</option>
		HTML;
	}
?>
						</select>月
						<select id="day" name="day" size="1">
<?php
	$day_selected = intval($birthday->format("d"));
	for ($day = 1; $day <= 31; $day++)
	{
		$option_day_selected = ($day == $day_selected ? "selected" : "");
		
		echo <<<HTML
							<option value="{$day}" {$option_day_selected}>{$day}</option>
		HTML;
	}
?>
						</select>日
						<span style="color: red">*</span>
					</td>
				</tr>
				<tr>
					<td align="right">
						QQ号码
					</td>
					<td>
						<span id="err_msg_qq" name="err_msg" style="color: red;"></span><input id="qq" name="qq" size="20" value="<?= $result_set["data"]["qq"]; ?>">
					</td>
				</tr>
  			</table>
			<p>
				<input type="submit" value="提交">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="reset" value="重填">
			</p>
		</form>
	</center>
</body>
</html>

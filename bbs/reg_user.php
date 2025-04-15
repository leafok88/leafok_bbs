<?
	require_once "../lib/common.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "./session_init.inc.php";
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>新用户注册</title>
<link rel="stylesheet" href="css/default.css" type="text/css">
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
function vn_refresh(img)
{
	img.src = img.src;
	return false;
}

function refresh_err_msg(errorFieldMap)
{
	document.getElementsByName("err_msg").forEach(element => {
		element.innerHTML = (errorFieldMap.has(element.id) ? errorFieldMap.get(element.id) : "");
	});
}

function reg_sub(f)
{
	instance.post('reg_user_service.php', {
        username: f.username.value,
		nickname: f.nickname.value,
		realname: f.realname.value,
		gender: f.gender.value,
		gender_public: (f.gender_public.checked ? "1" : "0"),
		email: f.email.value,
		year: f.year.value,
		month: f.month.value,
		day: f.day.value,
		qq: f.qq.value,
		agreement: (f.agreement.checked ? "1" : "0"),
		vn_str: f.vn_str.value,
    })
    .then(function (response) {
        var ret = response.data;
		var errorFieldMap = new Map();
        switch (ret.return.code)
        {
			case 0: // OK
				errorFieldMap.set("err_msg_prompt", "注册成功，请查收邮件");
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
	var f = document.getElementById("reg_form");
	f.addEventListener("submit", (e) => {
		e.preventDefault();
		reg_sub(f);
	});
});

</script>
</head>
<body>
	<center>
		<form method="post" id="reg_form" name="reg_form" action="#">
			<p style="font-weight: bold; font-size: 16px; color: red; font-family: 楷体">新用户注册</p>
			<p><span id="err_msg_prompt" name="err_msg" style="color: red"></span></p>
			<table border="1" cellpadding="10" cellspacing="0" width="700" bgcolor="#ffdead">
				<tr>
					<td width="26%" align="right">
						用户名
					</td>
					<td width="74%">
						<span id="err_msg_username" name="err_msg" style="color: red;"></span><input id="username" name="username" value="">
						<font color="red">*</font>
						5-12位英文子母、数字的组合，必须以字母开头，不可更改
					</td>
				</tr>
				<tr>
					<td align="right">
						昵称
					</td>
					<td>
						<span id="err_msg_nickname" name="err_msg" style="color: red;"></span><input id="nickname" name="nickname" value="">
						<span style="color: red">*</span>
						长度不超过10个全角字符，不能包含空格
					</td>
				</tr>
				<tr>
					<td align="right">
						姓名
					</td>
					<td>
						<span id="err_msg_realname" name="err_msg" style="color: red;"></span><input id="realname" name="realname" value="">
						<span style="color: red">*</span>
						长度不超过5个全角字符
					</td>
				</tr>
				<tr>
					<td align="right">
						性别
					</td>
					<td>
						<span id="err_msg_gender" name="err_msg" style="color: red;"></span><input type="radio" id="gender_male" name="gender" value="M">男
						<input type="radio" id="gender_female" name="gender" value="F">女
						<span style="color: red">*</span>
						<input type="checkbox" id="gender_public" name="gender_public" value="1" checked>公开
					</td>
				</tr>
				<tr>
					<td align="right">
						邮件地址
					</td>
					<td>
						<span id="err_msg_email" name="err_msg" style="color: red;"></span><input id="email" name="email" value="">
						<span style="color: red">*</span>
						请务必准确填写，否则无法激活账户<br>
					</td>
				</tr>
				<tr>
					<td align="right">
						出生日期
					</td>
					<td>
						<span id="err_msg_birthday" name="err_msg" style="color: red;"></span><select id="year" name="year" size="1">
<?
	$year_current = intval(date("Y", time()));
	$year_max = $year_current - 16; // Accept registrant of 16+ only
	$year_min = $year_current - 80;
	$year_selected = $year_current - 25;
	for ($year = $year_min; $year <= $year_max; $year++)
	{
?>
							<option value="<? echo $year; ?>" <? echo ($year == $year_selected ? "selected" : ""); ?>><? echo $year; ?></option>
<?
	}
?>

						</select>年
						<select id="month" name="month" size="1">
<?
	$month_selected = 1;
	for ($month = 1; $month <= 12; $month++)
	{
?>
							<option value="<? echo $month; ?>" <? echo ($month == $month_selected ? "selected" : ""); ?>><? echo $month; ?></option>
<?
	}
?>
						</select>月
						<select id="day" name="day" size="1">
<?
	$day_selected = 1;
	for ($day = 1; $day <= 31; $day++)
	{
?>
							<option value="<? echo $day; ?>" <? echo ($day == $day_selected ? "selected" : ""); ?>><? echo $day; ?></option>
<?
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
						<span id="err_msg_qq" name="err_msg" style="color: red;"></span><input id="qq" name="qq" size="20" value="">
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
					<span id="err_msg_agreement" name="err_msg" style="color: red;"></span><textarea rows="15" cols="80"><?
	$buffer = file_get_contents("./doc/license/" . (new DateTime($BBS_license_dt))->format("Ymd") . ".txt");
	echo (LML(htmlspecialchars($buffer, ENT_HTML401, 'UTF-8'), false, false, 1024));
						?></textarea>
						<p>
							<input type="checkbox" id="agreement" name="agreement" value="1">
							我已仔细阅读并完全同意以上《用户许可协议》
						</p>
					</td>
				</tr>
				<tr>
					<td align="right">
						验证码
					</td>
					<td>
						<span id="err_msg_vn_str" name="err_msg" style="color: red;"></span><input size=4 name="vn_str" value="">
						<img id="vn_img" src="vn_display.php" onclick="vn_refresh(this);">
						<span style="color: red">*</span>
						按图片内容填写，单击图片刷新
					</td>
				</tr>
			</table>
			<p>
				<input type="submit" value="提交">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="reset" value="重填">
			</p>
		</form>
	</center>
<?
	include "./foot.inc.php";
?>
</body>
</html>

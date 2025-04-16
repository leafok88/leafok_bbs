<?
	require_once "./session_init.inc.php";
	require_once "../lib/vn_gif.inc.php";
?>
<?
	$msg_list = array(
		0	=> "",
		1	=> "你需要登录才能访问该页面",
		2	=> "您已被强制退出，请重新登陆",
		3	=> "会话已过期，请重新登陆",
	);

	$mfa = (isset($_GET["mfa"]) && $_GET["mfa"] == "1" ? 1 : 0);
	$ch_passwd = (isset($_GET["ch_passwd"]) && $_GET["ch_passwd"] == "1" ? 1 : 0);
	$redir = (isset($_GET["redir"]) ? $_GET["redir"] : "main.php");
	$msg = (isset($_GET["msg"]) ? intval($_GET["msg"]) : 0);

	if ($_SESSION["BBS_uid"] > 0 && $ch_passwd == 0 && $msg == 0)
	{
		header ("Location: $redir");
		exit();
	}
?>
<html>
<head>
<meta HTTP-EQUIV="Content-Type" Content="text-html; charset=UTF-8">
<title>欢迎光临<? echo $BBS_name; ?></title>
<link rel="stylesheet" href="css/default.css" type="text/css">
<style type="text/css">
TD.t1
{
	background-color: #f0f5f5;
	color: green;
	font-size: 14px;
}
</style>
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

function check_user(f)
{
	if (f.ch_passwd.value == "0" && f.username.value == "" && f.password.value == "")
	{
		document.location = "<? echo $redir; ?>";
		return false;
	}

	if (f.ch_passwd.value == "1" && f.password_1.value != f.password_2.value)
	{
		document.getElementById("err_msg_password_confirm").innerHTML = "密码重复输入不一致<br />";
		return false;
	}

	instance.post('user_login_service.php', {
        username: f.username.value,
        password: f.password.value,
		ch_passwd: f.ch_passwd.value,
        password_new: f.password_1.value,
		agreement: (f.agreement.checked ? "1" : "0"),
		mfa: f.mfa.value,
		vn_str: f.vn_str.value,
    })
    .then(function (response) {
        var ret = response.data;
		var errorFieldMap = new Map();
        switch (ret.return.code)
        {
			case 0:
				refresh_err_msg(errorFieldMap);
				document.location = "<? echo $redir; ?>";
				break;
			case 1:
				f.mfa.value = "1";
				errorFieldMap.set("err_msg_prompt", ret.return.message);
				refresh_err_msg(errorFieldMap);
				document.getElementById("tr_vn_str").style.visibility = "visible";
				break;
			case 2:
				f.ch_passwd.value = "1";
				errorFieldMap.set("err_msg_prompt", ret.return.message);
				refresh_err_msg(errorFieldMap);
				document.getElementById("tr_ch_passwd").style.visibility = "visible";
				document.getElementById("tr_password_1").style.visibility = "visible";
				document.getElementById("tr_password_2").style.visibility = "visible";
			case 3: // Login forbidden
				errorFieldMap.set("err_msg_prompt", ret.return.message);
				refresh_err_msg(errorFieldMap);
				break;
			case 4:
				errorFieldMap.set("err_msg_prompt", "本站《用户许可协议》已更新，需要您的确认");
				refresh_err_msg(errorFieldMap);
				document.getElementById("text_agreement").value = ret.return.message;
				document.getElementById("tr_agreement_text").style.visibility = "visible";
				document.getElementById("tr_agreement_check").style.visibility = "visible";
				break;
			case -1:
				ret.return.errorFields.forEach(field => {
					errorFieldMap.set("err_msg_" + field.id, field.errMsg + "<br />");
				});
				refresh_err_msg(errorFieldMap);
				break;
			case -2:
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
	var f = document.getElementById("login_form");
	f.addEventListener("submit", (e) => {
		e.preventDefault();
		check_user(f);
	});
});

</script>
</head>
<body>
<center>
<table cellSpacing="0" cellPadding="0" width="1050" border="0">
	<tr height=70>
		<td></td>
	</tr>
	<tr height=120>
		<td align=center>
			<a href="/"><img src="/images/logo/fenglinonline_1.gif" border="0"></a>
		</td>
	</tr>
	<tr height=30>
		<td></td>
	</tr>
</table>
<form method="post" id="login_form" name="login_form" action="#">
<input type="hidden" name="mfa" value="<? echo $mfa; ?>">
<input type="hidden" name="ch_passwd" value="<? echo $ch_passwd; ?>">
<table cols="4" cellSpacing="0" cellPadding="10" width="1050" border="0">
	<tr height=20>
		<td></td>
    	<td colspan="2" width="40" class="t1" align="center">
			<span id="err_msg_prompt" name="err_msg" style="color: red"><? echo htmlspecialchars($msg_list[$msg], ENT_HTML401, 'UTF-8'); ?><br /></span>
		</td>
		<td></td>
	</tr>
	<tr>
		<td width="5%"></td>
    	<td width="40%" class="t1" align="right">
    		用户名：
		</td>
    	<td width="50%" class="t1" align="left">
			<span id="err_msg_username" name="err_msg" style="color: red;"></span><input size="14" id="username" name="username" value="<? echo $_SESSION["BBS_username"]; ?>" onfocus="this.select();">
		</td>
		<td width="5%"></td>
	</tr>
	<tr>
		<td></td>
    	<td class="t1" align="right">
			密码：
		</td>
    	<td class="t1" align="left">
			<span id="err_msg_password" name="err_msg" style="color: red;"></span><input type="password" id="password" name="password" size="14" value="" onfocus="this.select();">
		</td>
		<td></td>
	</tr>
	<tr id="tr_ch_passwd" style="visibility: <? echo ($ch_passwd ? "visible" : "collapse"); ?>;">
		<td></td>
    	<td colspan="2" class="t1" align="center">
			<font color=blue>密码为6-12个英文字母和数字的组合，必须同时包含大写、小写字母和数字，不能包含用户名</font>
		</td>
		<td></td>
	</tr>
	<tr id="tr_password_1" style="visibility: <? echo ($ch_passwd ? "visible" : "collapse"); ?>;">
		<td></td>
    	<td class="t1" align="right">
			新密码：
		</td>
    	<td class="t1" align="left">
			<span id="err_msg_password_new" name="err_msg" style="color: red;"></span><input type="password" id="password_1" name="password_1" size="14" value="" onfocus="this.select();">
		</td>
		<td></td>
	</tr>
	<tr id="tr_password_2" style="visibility: <? echo ($ch_passwd ? "visible" : "collapse"); ?>;">
		<td></td>
    	<td class="t1" align="right">
			重复新密码：
		</td>
    	<td class="t1" align="left">
			<span id="err_msg_password_confirm" name="err_msg" style="color: red;"></span><input type="password" id="password_2" name="password_2" size="14" value="" onfocus="this.select();">
		</td>
		<td></td>
	</tr>
	<tr id="tr_agreement_text" style="visibility: collapse;">
		<td></td>
		<td colspan="2" class="t1" align="center">
			<span id="err_msg_agreement" name="err_msg" style="color: red;"></span><textarea id="text_agreement" name="text_agreement" rows="15" cols="80"></textarea>
		</td>
		<td></td>
	</tr>
	<tr id="tr_agreement_check" style="visibility: collapse;">
		<td></td>
		<td colspan="2" class="t1" align="center">
			<input type="checkbox" id="agreement" name="agreement" value="1">
			我已仔细阅读并完全同意以上《用户许可协议》
		</td>
		<td></td>
	</tr>
	<tr id="tr_vn_str" style="visibility: <? echo ($mfa ? "visible" : "collapse"); ?>;">
		<td></td>
    	<td class="t1" align="right">
			验证码：
		</td>
    	<td class="t1" align="left">
			<span id="err_msg_vn_str" name="err_msg" style="color: red;"></span><input id="vn_str" name="vn_str" size=4>
			<img id="vn_img" src="vn_display.php" onclick="return vn_refresh(this);">
			<a class="s2" href="" onclick="return vn_refresh(vn_img);">刷新</a>
		</td>
		<td></td>
	</tr>
	<tr>
		<td></td>
    	<td colspan="2" class="t1" align="center">
   			<input type=image id="submit" name="submit" src="images/login.gif" alt="登录" border="0">
    	</td>
		<td></td>
	</tr>
	<tr height=30>
		<td colspan="4"></td>
	</tr>
</table>
</form>
</center>
<?
	include "./foot.inc.php";
?>
</body>
</html>

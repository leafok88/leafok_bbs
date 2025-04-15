<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>关闭账户</title>
<link rel="stylesheet" href="<? echo get_theme_file('css/default'); ?>" type="text/css">
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
function refresh_err_msg(errorFieldMap)
{
	document.getElementsByName("err_msg").forEach(element => {
		element.innerHTML = (errorFieldMap.has(element.id) ? errorFieldMap.get(element.id) : "");
	});
}

function commit_suicide(f)
{
	instance.post('suicide_do.php', {
		confirm: f.confirm.checked ? 1 : 0,
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
				errorFieldMap.set("err_msg_prompt", "操作成功");
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
	var f = document.getElementById("suicide_form");
	f.addEventListener("submit", (e) => {
		e.preventDefault();
		commit_suicide(f);
	});
});

</script>
</head>
<body>
<?
	include get_theme_file("view/member_service_guide");
?>
<center>
	<p style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">关闭账户</p>
	<p><span id="err_msg_prompt" name="err_msg" style="color: red"></span></p>
	<table border="1" cellpadding="10" cellspacing="0" width="1050" bgcolor="#ffdead">
		<tr>
			<td width="100%" align="middle" style="color:red">
				警告：关闭账户不能恢复，您将失去一切！
			</td>
		</tr>
		<tr>
			<td width="100%" align="middle">
				申请关闭账户后您的生命值将减为60，且失去全站登陆权限，60天后您就……
			</td>
		</tr>
		<tr>
			<td width="100%" align="middle">
				<form action="#" id="suicide_form" name="suicide_form">
					<p><span id="err_msg_confirm" name="err_msg" style="color: red;"></span>
					<input type="checkbox" id="confirm" name="confirm">我理解并确认要永久关闭当前账户</p>
					<p><input type="submit" id="submit" name="submit" value="提交"></p>
				</form>
			</td>
		</tr>
	</table>
</center>
</body>
</html>

<?
	require_once "./session_init.inc.php";
?>
<html>
	<head>
		<meta HTTP-EQUIV="Content-Type" Content="text-html; charset=UTF-8">
		<title>重置密码</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
function reset_flag()
{
	var s = document.getElementsByName("err_msg");

	s.forEach(element => {
		element.innerHTML = "";
	});
}

function reset_pass(f)
{
	reset_flag();
	instance.post('reset_pass_service.php', {
        username: f.username.value,
		email: f.email.value,
    })
    .then(function (response) {
        var ret = response.data;
        switch (ret.return.code)
        {
			case 0: // OK
				window.alert("密码重置成功，请查收邮件");
				document.location = "index.php";
				break;
			case -1: // Input validation failed
				ret.return.errorFields.forEach(field => {
					document.getElementById("err_msg_" + field.id).innerHTML = field.errMsg + "<br />";
				});
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				document.getElementById("err_msg_username").innerHTML = "内部错误<br />";
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
	<body>
		<p align="center" style="font-size:14px; color:red;">
			请填写您的用户名及关联的电子邮件地址
		</p>
		<center>
			<form method="post" id="reset_form" name="reset_form" action="javascript: reset_pass(reset_form);">
				<table cellSpacing="0" cellPadding="10" width="300" border="0">
					<tr>
						<td>
							<p align="center">
								用户名：
							</p>
						</td>
						<td>
							<p align="center">
								<span id="err_msg_username" name="err_msg" style="color: red;"></span><input id="username" name="username" value="">
							</p>
						</td>
					</tr>
					<tr>
						<td>
							<p align="center">
								邮件地址：
							</p>
						</td>
						<td>
							<p align="center">
								<span id="err_msg_email" name="err_msg" style="color: red;"></span><input id="email" name="email" value="">
							</p>
						</td>
					</tr>
					<tr>
						<td>
							<p align="right">
								<input type="submit" value="重置密码">
							</p>
						</td>
						<td>
							<p align="center">
								<input type="reset" value="清空">
							</p>
						</td>
					</tr>
				</table>
			</form>
		</center>
<?
	include "./foot.inc.php";
?>
	</body>
</html>

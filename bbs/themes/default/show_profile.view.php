<?
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}

	require_once "../lib/astro.inc.php";
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>查看用户资料</title>
<link rel="stylesheet" href="<? echo get_theme_file('css/default'); ?>" type="text/css">
<script type="text/javascript" src="../js/img_adjust.js"></script>
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
function refresh_err_msg(errorFieldMap)
{
	document.getElementsByName("err_msg").forEach(element => {
		element.innerHTML = (errorFieldMap.has(element.id) ? errorFieldMap.get(element.id) : "");
	});
}

function transfer_score(f)
{
	if (window.confirm('真的要转让吗？') == false)
	{
		return false;
	}

	instance.post('transfer_score_sub.php', {
		uid: <? echo $result_set["data"]["uid"]; ?>,
		amount: f.amount.value,
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
				errorFieldMap.set("err_msg_transfer", "积分转让成功<br />");
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
				errorFieldMap.set("err_msg_transfer", "内部错误<br />");
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

function set_life(f)
{
	instance.post('set_life.php', {
		uid: <? echo $result_set["data"]["uid"]; ?>,
		life: f.life.value,
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
				refresh_err_msg(errorFieldMap);
				document.location = "show_profile.php?uid=<? echo $result_set["data"]["uid"]; ?>&ts=" + Date.now();
				break;
			case -1: // Input validation failed
				errorFieldMap.set("err_msg_life", ret.return.message + "<br />");
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_life", "内部错误<br />");
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

function ban_user(f)
{
	instance.post('ban_user.php', {
		uid: <? echo $result_set["data"]["uid"]; ?>,
		ban: f.ban.value,
		sid: f.sid.value,
		day: f.day.value,
		reason: f.reason.value,
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
				errorFieldMap.set("err_msg_ban", "操作成功<br />");
				refresh_err_msg(errorFieldMap);
				break;
			case -1: // Input validation failed
				errorFieldMap.set("err_msg_ban", ret.return.message + "<br />");
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_ban", "内部错误<br />");
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

function set_friend(set)
{
	instance.post('set_friend.php', {
        uid: <? echo $result_set["data"]["uid"]; ?>,
		set: set,
    })
    .then(function (response) {
        var ret = response.data;
		var errorFieldMap = new Map();
        switch (ret.return.code)
        {
			case 0: // OK
			case 1: // Already set
				refresh_err_msg(errorFieldMap);
				document.location = "show_profile.php?uid=<? echo $result_set["data"]["uid"]; ?>&ts=" + Date.now();
				break;
			case -1: // Input validation failed
				errorFieldMap.set("err_msg_friend", ret.return.message);
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_friend", "内部错误");
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
	var f1 = document.getElementById("transfer_score");
	if (f1)
	{
		f1.addEventListener("submit", (e) => {
			e.preventDefault();
			transfer_score(f1);
		});
	}

	var f2 = document.getElementById("set_life");
	if (f2)
	{
		f2.addEventListener("submit", (e) => {
			e.preventDefault();
			set_life(f2);
		});
	}

	var f3 = document.getElementById("ban_user");
	if (f3)
	{
		f3.addEventListener("submit", (e) => {
			e.preventDefault();
			ban_user(f3);
		});
	}

});

</script>
</head>
<body>
	<center>
		<table border="0" cellpadding="0" cellspacing="10" width="1050">
			<tr>
				<td colspan="2" align="center" style="font-size: 16px; font-family: 楷体; font-weight: bold; color: red">
					<? echo $result_set["data"]["username"]; ?>的个人资料
				</td>
			</tr>
			<tr height="1" bgcolor="gray">
				<td colspan="2">
				</td>
			</tr>
			<tr>
				<td width="35%" align="right">
					头像：
				</td>
				<td width="65%">
					<img src="<? echo $result_set["data"]["photo"]; ?>" border="0">
				</td>
			</tr>
			<tr height="1" bgcolor="gray">
				<td colspan="2">
				</td>
			</tr>
			<tr>
				<td align="right">
					昵称：
				</td>
				<td>
					<span style="color: #909090; ">
						<? echo $result_set["data"]["nickname"]; ?>
					</span>
				</td>
			</tr>
			<tr>
				<td align="right">
					星座：
				</td>
				<td>
<?
	$astro = Date2Astro(intval($result_set["data"]["birthday"]->format("m")), intval($result_set["data"]["birthday"]->format("d"))) . "座";

	if ($result_set["data"]["gender_pub"])
	{
		if ($result_set["data"]["gender"] == "M")
		{
?><span style="color:blue;"><? echo $astro; ?></span><?
		}
		else if ($result_set["data"]["gender"] == "F")
		{
?><span style="color:red;"><? echo $astro; ?></span><?
		}
	}
	else
	{
?><span style="color:green;"><? echo $astro; ?></span><?
	}
?>
				</td>
			</tr>
			<tr>
				<td align="right">
					注册时间：
				</td>
				<td>
					<? echo $result_set["data"]["signup_dt"]->format("Y年m月d日 H:i:s"); ?>
				</td>
			</tr>
			<tr>
				<td align="right">
					最后活动：
				</td>
				<td>
					<? echo $result_set["data"]["last_tm"]->format("Y年m月d日 H:i:s"); ?>
				</td>
			</tr>
			<tr>
				<td align="right">
					源IP地址：
				</td>
				<td>
					<? echo $result_set["data"]["ip"]; ?>
				</td>
			</tr>
			<tr>
				<td align="right">
					经验值&nbsp;/&nbsp;生命值：
				</td>
				<td>
					<span style="color: blue"><? echo $result_set["data"]["exp"] . "&nbsp;/&nbsp;" . $result_set["data"]["life"]; ?></span>
				</td>
			</tr>
<?
	if ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
?>
			<tr>
				<td>
				</td>
				<td>
					<form method="POST" action="#" id="set_life" name="set_life">
						<span id="err_msg_life" name="err_msg" style="color: red;"></span>
						<input name="life" value="<? echo $result_set["data"]["life"]; ?>" size="3">&nbsp;&nbsp;
						<input type="submit" value="授予">
					</form>
<?
	}
?>
				</td>
			</tr>
			<tr>
				<td align="right">
					等级：
				</td>
				<td>
					<span style="color:orange; ">
						<?   echo user_level($result_set["data"]["exp"]); ?>
					</span>
				</td>
			</tr>
			<tr>
				<td align="right">
					状态：
				</td>
				<td>
<?
	if (!$result_set["data"]["dead"] && $result_set["data"]["verified"])
	{
		if ($result_set["data"]["p_all"])
		{
?><span style="color: green">正常</span><?
		}
		if (!$result_set["data"]["p_login"])
		{
?><span style="color: red">限制登陆</span><?
		}
		if (!$result_set["data"]["p_post"])
		{
?><span style="color: red">限制发帖</span><?
		}
		if (!$result_set["data"]["p_msg"])
		{
?><span style="color: red">限制消息</span><?
		}
	}
	else
	{
		if (!$result_set["data"]["verified"])
		{
?><span style="color: red">尚未确认</span><?
		}
		if ($result_set["data"]["dead"])
		{
?><span style="color: red">已升天</span><?
		}
	}

	if ($result_set["data"]["online"])
	{
?>&nbsp;&nbsp;<span style="color: blue">在线</span><?
	}
	else
	{
?>&nbsp;&nbsp;<span style="color: gray">离线</span><?
 	}
?>
				</td>
			</tr>
			<tr>
				<td align="right">
					最近发表的主题：
				</td>
				<td>
					<a class="s7" href="search_article.php?uid=<? echo $result_set["data"]["uid"]; ?>" target=_blank>查看&gt;&gt;</a>
				</td>
			</tr>
			<tr height="1" bgcolor="gray">
				<td colspan="2">
				</td>
			</tr>
			<tr>
				<td align="right">
					个人介绍：
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<table border="0" cellspacing="0" cellpadding="0" width="80%">
						<tr>
							<td style="color:gray; font-size:14px;">
								<? echo LML(htmlspecialchars($result_set["data"]["introduction"], ENT_HTML401, 'UTF-8'), true, true, 80); ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr height="1" bgcolor="gray">
				<td colspan="2">
				</td>
			</tr>
			<tr>
				<td align="right">
					用户操作：
				</td>
				<td>
<?
	if ($_SESSION["BBS_priv"]->checkpriv(0, S_MSG) && $result_set["data"]["uid"] != $BBS_sys_uid)
	{
?>
					<a class="s2" href="read_msg.php?sent=1&uid=<? echo $result_set["data"]["uid"]; ?>" target=_blank>发送消息</a>
<?
	}

	if ($_SESSION["BBS_uid"] > 0 && $_SESSION["BBS_uid"] != $result_set["data"]["uid"])
	{
?>
					<a class="s2" href="#" onclick="return set_friend(<? echo ($result_set["data"]["is_friend"] ? 0 : 1); ?>);"><? echo ($result_set["data"]["is_friend"]? "删除好友" : "加为好友"); ?></a>
					<span id="err_msg_friend" name="err_msg" style="color: red;"></span>
<?
	}
?>
				</td>
			</tr>
<?
	if ($_SESSION["BBS_uid"] > 0 && $_SESSION["BBS_uid"] != $result_set["data"]["uid"])
	{
?>
			<tr>
				<td align="right">
					积分转让：
				</td>
				<td>
					<span id="err_msg_transfer" name="err_msg" style="color: red;"></span>
					<form method="post" action="#" id="transfer_score" name="transfer_score">
						<input id="amount" name="amount" value="0" size="3">&nbsp;&nbsp;
						<input type="submit" value="转让">
					</form>
					转让额必须是10的倍数，单次限额10000。<br />
					<span style="color:red; ">服务费率为转让额的10%</span>
				</td>
			</tr>
<?
	}

	if ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S | P_MAN_M))
	{
?>
			<tr height="1" bgcolor="gray">
				<td colspan="2">
				</td>
			</tr>
			<tr>
				<td align="right">
					封禁权限：
				</td>
				<td>
					<form method="POST" action="#" id="ban_user" name="ban_user">
						<p>
							<span id="err_msg_ban" name="err_msg" style="color: red;"></span>
							<input type="radio" name="ban" value="1" checked>封
							<input type="radio" name="ban" value="0">解封
							<select id="sid" name="sid" size="1">
								<option value="0">全站发帖</option>
								<option value="-1">用户登录</option>
								<option value="-2">站内消息</option>
<?
		foreach ($result_set["data"]["section_hierachy"] as $c_index => $section_class)
		{
?>
								<option value="-100">==<? echo $section_class["title"]; ?>==</option>
<?
			foreach ($section_class["sections"] as $s_index => $section)
			{
?>
								<option value="<? echo $section["sid"]; ?>">&nbsp;&nbsp;├<? echo $section["title"]; ?></option>
<?
			}
		}
?>
							</select>
							权限
							<input name="day" size="3">天（最多365）<br />
							理由：<br />
							<textarea name="reason" cols="40" rows="5"></textarea>
						</p>
						<input type="submit" value="提交">
					</form>
				</td>
			</tr>
<?
	}
?>
			<tr height="1" bgcolor="gray">
				<td colspan="2">
				</td>
			</tr>
		</table>
	</center>
</body>
</html>

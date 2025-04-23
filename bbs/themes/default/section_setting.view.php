<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>版块设定</title>
<link rel="stylesheet" href="<?= get_theme_file('css/default'); ?>" type="text/css">
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
function refresh_ex_dir(s, dirList)
{
	for (i = dirList.length - s.options.length; i > 0 ; i--)
	{
		s.options.add(new Option());
	}

	i = 0;
	for (var dirItem of dirList)
	{
		s.options[i].value = dirItem.dir;
		s.options[i].text = dirItem.dir + '(' + dirItem.name + ')';
		i++;
	}

	for (j = s.options.length; j >= i ; j--)
	{
		s.options.remove(j);
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

function section_setting(f)
{
	instance.post('section_service_setting.php', {
		sid: <?= $result_set["data"]["sid"]; ?>,
		sname: f.sname.value,
		title: f.title.value,
		comment: f.textarea_comment.value,
		announcement: f.textarea_announcement.value,
		exp_get: (f.exp_get.checked ? "1" : "0"),
		recommend: (f.recommend.checked ? "1" : "0"),
		read_user_level: f.read_user_level.value,
		write_user_level: f.write_user_level.value,
		sort_order: f.sort_order.value,
		ex_update: (f.ex_update.checked ? "1" : "0"),
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

function ex_dir_op(f, op)
{
	instance.post('section_service_dir.php', {
		sid: <?= $result_set["data"]["sid"]; ?>,
		current_dir: f.current_dir.value,
		dir: f.dir.value,
		dir_name: f.dir_name.value,
		dir_op: op,
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
				if (op != 0)
				{
					errorFieldMap.set("err_msg_current_dir", "操作成功");
				}
				refresh_err_msg(errorFieldMap);
				refresh_ex_dir(f.current_dir, ret.return.data.ex_dir);
				break;
			case -1: // Input validation failed
				ret.return.errorFields.forEach(field => {
					errorFieldMap.set("err_msg_" + field.id, field.errMsg + "<br />");
				});
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_current_dir", "内部错误");
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

function master_op(f, op, username)
{
	instance.post('section_service_master.php', {
		sid: <?= $result_set["data"]["sid"]; ?>,
		op: op,
		username: (username != null ? username : f.master_username.value),
		type: f.master_type.value,
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
				refresh_err_msg(errorFieldMap);
				document.location = "section_setting.php?sid=<?= $result_set["data"]["sid"]; ?>&ts=" + Date.now();
				break;
			case -1: // Input validation failed
				ret.return.errorFields.forEach(field => {
					errorFieldMap.set("err_msg_" + field.id, field.errMsg + "<br />");
				});
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_master", "内部错误");
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
	var f = document.getElementById("section_setting");
	ex_dir_op(f, 0); // Load dir list
	f.addEventListener("submit", (e) => {
		e.preventDefault();
		switch(e.submitter.name)
		{
			case "submit":
				section_setting(f);
				break;
			case "master_appoint":
				master_op(f, 1, null);
				break;
			case "dir_create":
				ex_dir_op(f, 1);
				break;
			case "dir_update":
				ex_dir_op(f, 2);
				break;
			case "dir_delete":
				ex_dir_op(f, 3);
				break;
			default:
				console.log(e.submitter.name);
				break;
		}
	});

	var s = document.getElementById("section_switch");
	s.addEventListener("change", (e) => {
		if (s.value != 0)
		{
			document.location = "section_setting.php?sid=" + s.value + "&ts=" + Date.now();
		}
	});

<?php
	$user_level_types = array("read_user_level", "write_user_level");

	foreach ($user_level_types as $t)
	{
?>
	var l = document.getElementById("<?= $t; ?>");
	var found = false;
	for (i = 0; !found && i < l.options.length; i++)
	{
		if (l.options[i].value == <?= $result_set["data"][$t]; ?>)
		{
			l.selectedIndex = i;
			found = true;
		}
	}

	for (i = 0; !found && i < l.options.length; i++)
	{
		if (l.options[i].value >= <?= $result_set["data"][$t]; ?>)
		{
			l.selectedIndex = i;
			found = true;
		}
	}
<?php
	}
?>

	var s_sort = document.getElementById("sort_order");
	for (i = 0; i < s_sort.options.length; i++)
	{
		if (s_sort.options[i].value == <?= $result_set["data"]["sort_order"]; ?>)
		{
			s_sort.selectedIndex = i;
			break;
		}
	}
});

</script>
</head>
<body>
	<center>
	<p style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">版块设定</p>
	<p><span id="err_msg_prompt" name="err_msg" style="color: red"></span></p>
	<form method="post" action="#" id="section_setting" name="section_setting" >
		<table border="1" cellpadding="10" cellspacing="0" width="1050" bgcolor="#ffdead">
			<tr>
				<td width="25%" align="right">
					操作版块
				</td>
				<td width="75%">
					<select id="section_switch" name="section_switch" size="1">
<?php
	foreach ($result_set["data"]["section_hierachy"] as $c_index => $section_class)
	{
?>
						<option value="0">==<?= $section_class["title"]; ?>==</option>
<?php
		foreach ($section_class["sections"] as $s_index => $section)
		{
?>
						<option value="<?= $section["sid"]; ?>" <?= ($section["sid"] == $result_set["data"]["sid"] ? "selected" : ""); ?>>&nbsp;&nbsp;├<?= $section["title"]; ?></option>
<?php
		}
	}
?>
					</select>
				</td>
			</tr>
			<tr>
				<td width="25%" align="right">
					版块名称
				</td>
				<td width="75%">
					<span id="err_msg_sname" name="err_msg" style="color: red;"></span>
					<input id="sname" name="sname" size="20" value="<?= htmlspecialchars($result_set["data"]["sname"], ENT_QUOTES | ENT_HTML401, 'UTF-8'); ?>"
						<?= ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M) ? "" : "disabled"); ?>
					<br />1-20位大小写字母、数字、下划线的组合，必须以字母开头
				</td>
			</tr>
			<tr>
				<td width="25%" align="right">
					版块标题
				</td>
				<td width="75%">
					<span id="err_msg_title" name="err_msg" style="color: red;"></span>
					<input id="title" name="title" size="20" value="<?= htmlspecialchars($result_set["data"]["title"], ENT_QUOTES | ENT_HTML401, 'UTF-8'); ?>"
						<?= ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M) ? "" : "disabled"); ?>
					<br />长度不超过10个全角字符，不能包含空格和HTML特殊语义字符
				</td>
			</tr>
			<tr>
				<td align="right">
					版块介绍
				</td>
				<td>
					<span id="err_msg_comment" name="err_msg" style="color: red;"></span>
					<textarea id="textarea_comment" name="textarea" cols="80" rows="5"><?= htmlspecialchars($result_set["data"]["comment"], ENT_HTML401, 'UTF-8'); ?></textarea>
					<br />限3行80列以内
				</td>
			</tr>
			<tr>
				<td align="right">
					版块公告
				</td>
				<td>
					<span id="err_msg_announcement" name="err_msg" style="color: red;"></span>
					<textarea id="textarea_announcement" name="textarea" cols="80" rows="5"><?= htmlspecialchars($result_set["data"]["announcement"], ENT_HTML401, 'UTF-8'); ?></textarea>
					<br />限3行以内，每行不超过150字符
				</td>
			</tr>
			<tr>
				<td width="25%" align="right">
					版块属性
				</td>
				<td width="75%">
					<span id="err_msg_flag" name="err_msg" style="color: red;"></span>
					<input type="checkbox" id="exp_get" name="exp_get" <?= ($result_set["data"]["exp_get"] ? "checked" : ""); ?>
						<?= ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M) ? "" : "disabled"); ?>>经验&nbsp;&nbsp;&nbsp;
					<input type="checkbox" id="recommend" name="recommend" <?= ($result_set["data"]["recommend"] ? "checked" : ""); ?>
						<?= ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M) ? "" : "disabled"); ?>>推荐&nbsp;&nbsp;&nbsp;
					<select id="read_user_level" name="read_user_level" size="1"
						<?= ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M) ? "" : "disabled"); ?>>
<?php
	foreach (user_priv::$user_level_list as $level)
	{
?>
						<option value="<?= $level; ?>"><?= user_priv::s_levelname($level); ?></option>
<?php
	}
?>
					</select>可读&nbsp;&nbsp;&nbsp;
					<select id="write_user_level" name="write_user_level" size="1"
						<?= ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M) ? "" : "disabled"); ?>>
<?php
	foreach (user_priv::$user_level_list as $level)
	{
		if ($level < P_USER) // Guests are not allowed to write
		{
			continue;
		}
?>
						<option value="<?= $level; ?>"><?= user_priv::s_levelname($level); ?></option>
<?php
	}
?>
					</select>可写&nbsp;&nbsp;&nbsp;
					位于
					<select id="sort_order" name="sort_order" size="1"
						<?= ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M) ? "" : "disabled"); ?>>
<?php
	$class_sections = $result_set["data"]["class_sections"];
	for ($i = 0; $i < count($class_sections); $i++)
	{
?>
						<option value="<?= ($i + 1); ?>">
							<?= $class_sections[$i]["title"]; ?>
							<?= ($i + 1 < $result_set["data"]["sort_order"] ? "之前" : ($i + 1 > $result_set["data"]["sort_order"] ? "之后" : "")); ?>

						</option>
<?php
	}
?>
					</select><br />
				</td>
			</tr>
<?php
	if ($_SESSION["BBS_priv"]->checkpriv($sid, S_MAN_M))
	{
?>
			<tr>
				<td align="right">
					版主任命
				</td>
				<td>
					<p><span id="err_msg_master" name="err_msg" style="color: red;"></span></p>
<?php
		$has_major = false;
		foreach ($result_set["data"]["masters"] as $m_index => $section_master)
		{
			if (!$has_major && $section_master["major"])
			{
				$has_major = true;
			}
?>
					<p>
						<?= ($section_master["major"] ? "正版主" : "副版主"); ?>&nbsp;&nbsp;
						<a class="s3" href="show_profile.php?uid=<?= $section_master['uid']; ?>" target=_blank><?= $section_master["username"]; ?></a>&nbsp;&nbsp;
						<?= (new DateTimeImmutable($section_master["begin_dt"]))->setTimezone($_SESSION["BBS_user_tz"])->format("y年m月d日"); ?>--<?= (new DateTimeImmutable($section_master["end_dt"]))->setTimezone($_SESSION["BBS_user_tz"])->format("y年m月d日"); ?>&nbsp;&nbsp;
<?php
			if ($_SESSION["BBS_priv"]->checkpriv($sid, S_ADMIN) || $section_master["major"] == 0)
			{
?>
						<a class="s2" href="#" onclick="return master_op(section_setting, 2, '<?= $section_master["username"]; ?>');">撤销</a>&nbsp;&nbsp;
						<a class="s2" href="#" onclick="return master_op(section_setting, 3, '<?= $section_master["username"]; ?>');">延期</a>&nbsp;&nbsp;
<?php
			}
?>
					</p>
<?php
		}
?>
					<p>
						用户名：
						<input type="text" id="master_username" name="master_username" size="20">
						<input type="radio" id="master_major" name="master_type" value="1" <?= ($has_major ? "disabled" : "checked"); ?>>正版主
						<input type="radio" id="master_minor" name="master_type" value="0" <?= ($has_major ? "checked" : ""); ?>>副版主&nbsp;&nbsp;
						<input type="submit" name="master_appoint" value="任命">&nbsp;&nbsp;
						<span id="err_msg_username" name="err_msg" style="color: red;"></span>
					</p>
				</td>
			</tr>
<?php
	}
?>
			<tr>
				<td align="right">
					精华更新
				</td>
				<td>
					<span id="err_msg_ex_update" name="err_msg" style="color: red;"></span>
					<input type="checkbox" name="ex_update" <?= ($result_set["data"]["ex_update"] ? "checked" : ""); ?>>申请更新
				</td>
			</tr>
			<tr>
				<td align="right">
					目录管理
				</td>
				<td>
					<p><span id="err_msg_current_dir" name="err_msg" style="color: red;"></span></p>
					<p>位置：<select id="current_dir" name="current_dir" size="1">
					</select></p>
					<p><span id="err_msg_dir" name="err_msg" style="color: red;"></span>
					目录：<input id="dir" name="dir" value="" size="50">
					大小写英文字母、数字和下划线的组合，长度不超过20</p>
					<p><span id="err_msg_dir_name" name="err_msg" style="color: red;"></span>
					名称：<input id="dir_name" name="dir_name" value="" size="50">
					长度不超过15个全角字符</p>
					<p><span id="err_msg_dir_op" name="err_msg" style="color: red;"></span>&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="submit" name="dir_create" value="新增">&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="submit" name="dir_update" value="改名">&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="submit" name="dir_delete" value="删除">
					</p>
				</td>
			</tr>
		</table>
		<p align="center">
			<input type="submit" value="提交" name="submit">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="reset" value="重填" name="reset">
		</p>
	</form>
	</center>
</body>
</html>

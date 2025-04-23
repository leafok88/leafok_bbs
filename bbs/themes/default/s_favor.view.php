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
<title>设定版块收藏</title>
<link rel="stylesheet" href="<?= get_theme_file('css/default'); ?>" type="text/css">
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
function select_class(cid, flag)
{
	for (var element of document.getElementById("class_" + cid).getElementsByTagName("input"))
	{
		element.checked = flag;
	};
}

function refresh_err_msg(errorFieldMap)
{
	document.getElementsByName("err_msg").forEach(element => {
		element.innerHTML = (errorFieldMap.has(element.id) ? errorFieldMap.get(element.id) : "");
	});
}

function s_favor_sub(f)
{
	let sidList = [];

	for (const sid of f.sid_list)
	{
		if (sid.checked)
		{
			sidList.push(sid.value);
		}
	}

	instance.post('s_favor_sub.php', {
		sid_list: sidList,
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
	var f = document.getElementById("s_favor_form");
	f.addEventListener("submit", (e) => {
		e.preventDefault();
		s_favor_sub(f);
	});
});

</script>
</head>
<body>
<?php
	include get_theme_file("view/member_service_guide");
?>
	<center>
		<p align="center" style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">
			设定版块收藏
		</p>
		<p>
			<span id="err_msg_prompt" name="err_msg" style="color: red"></span>
		</p>
		<form method="post" action="#" id="s_favor_form" name="s_favor_form">
		<table border="1" cellpadding="5" cellspacing="0" width="1050" bgcolor="#ffdead">
<?php
	foreach ($result_set["data"]["section_hierachy"] as $c_index => $section_class)
	{
?>
			<tr>
				<td align="left">
					<?= $section_class["title"]; ?>
					<a class="s2" href="#" onclick="return select_class(<?= $section_class['cid']; ?>, true);">全选</a>
					<a class="s2" href="#" onclick="return select_class(<?= $section_class['cid']; ?>, false);">不选</a>
				</td>
			</tr>
			<tr>
				<td id="class_<?= $section_class["cid"]; ?>" align="left">
<?php
		foreach ($section_class["sections"] as $s_index => $section)
		{
?>
					<input type="checkbox" id="sid_list" name="sid_list[]" value="<?= $section["sid"]; ?>" <?= ($section["udf_values"] ? "checked" : ""); ?>><a class="s2" href="list.php?sid=<?= $section["sid"]; ?>" target=_blank><?= $section["title"]; ?></a>
<?php
		}
?>
				</td>
			</tr>
<?php
	}
?>
		</table>
		<p>
			<input type="submit" value="提交" name="Submit">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="reset" value="重填" name="Reset">
		</p>
		</form>
	</center>
</body>
</html>

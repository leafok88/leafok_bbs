<?
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>发表文章</title>
<link rel="stylesheet" href="<? echo get_theme_file('css/default'); ?>" type="text/css">
<script type="text/javascript" src="<? echo get_theme_file('js/lml_assistant'); ?>"></script>
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
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

function post_article(f)
{
	instance.post('post_service.php', {
        id: <? echo $result_set["data"]["id"]; ?>,
        reply_id: <? echo $result_set["data"]["reply_id"]; ?>,
        sid: <? echo $result_set["data"]["sid"]; ?>,
        title: f.textarea_title.value,
		transship: (f.transship != null && f.transship.checked ? "1" : "0"),
		content: f.textarea_content.value,
		emoji: f.emoji.value,
		sign_id: f.sign_id.value,
		reply_note: (f.reply_note.checked ? "1" : "0"),
		attachment: f.attachment.files,
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
				var returnPath = "view_article.php?id=" + ret.return.aid + "#" + ret.return.aid;
				document.location = returnPath;
				refresh_err_msg(errorFieldMap);
				break;
			case -1: // Input validation failed
				ret.return.errorFields.forEach(field => {
					errorFieldMap.set("err_msg_" + field.id, "<br />" + field.errMsg);
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

function upload_del(id)
{
	if (window.confirm('真的要删除吗？') == false)
	{
		return false;
	}

	instance.post('upload_del.php', {
        aid: id
    })
    .then(function (response) {
        var ret = response.data;
		var errorFieldMap = new Map();
        switch (ret.return.code)
        {
			case 0: // OK
			case 1: // Already deleted
				document.getElementById("attachment_" + id).style.display = "none";
				refresh_err_msg(errorFieldMap);
				break;
			case -1: // Input validation failed
				errorFieldMap.set("err_msg_attachment", "<br />" + ret.return.message);
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
	var f = document.getElementById("post_form");
	f.addEventListener("submit", (e) => {
		e.preventDefault();
		post_article(f);
	});
});

</script>
</head>
<body>
<center>
<table border="0" cellpadding="1" cellspacing="0" width="1050">
	<tr>
		<td>
			<a class="s2" href="main.php?sid=<? echo $result_set["data"]["sid"]; ?>"><? echo $BBS_name; ?></a>&gt;&gt;<a class="s2" href="list.php?sid=<? echo $result_set["data"]["sid"]; ?>"><? echo $result_set["data"]["section_title"]; ?></a>&gt;&gt;<?
	if ($result_set["data"]["id"] == 0)
	{
		if ($result_set["data"]["reply_id"] > 0)
		{
?><a class="s2" href="view_article.php?id=<? echo $result_set["data"]["reply_id"] . "#" . $result_set["data"]["reply_id"]; ?>"><? echo split_line(htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'), "", 65, 2, "<br />"); ?></a>&gt;&gt;<a class="s2" href="#" onclick="return false;">回复文章</a><?
		}
		else
		{
?><a class="s2" href="#" onclick="return false;">发表新文章</a><?
		}
	}
	else
	{
?><a class="s2" href="view_article.php?id=<? echo $result_set["data"]["id"] . "#" . $result_set["data"]["id"]; ?>"><? echo split_line(htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'), "", 65, 2, "<br />"); ?></a>&gt;&gt;<a class="s2" href="#" onclick="return false;">修改文章</a><?
	}
?>
		</td>
	</tr>
</table>
<form method="POST" ENCTYPE="multipart/form-data" id="post_form" name="post_form" action="#">
<table border="0" cellpadding="5" cellspacing="0" width="1050">
	<tr>
		<td colspan="2" align="center" style="color:red;">别忙着发贴，请先看一下<a class="s0" href="doc/management.xml" target=_blank>《论坛管理章程》</a>吧！<br>
		（请对您的言论负责，遵守有关法律、法规，尊重网络道德）</td>
	</tr>
	<tr height="10">
		<td colspan="2" align="center"><span id="err_msg_prompt" name="err_msg" style="color: red;"></span></td>
	</tr>
	<tr>
		<td width="20%" align="right">标题<span id="err_msg_title" name="err_msg" style="color: red;"></span></td>
		<td width="80%">
			<input type="text" name="textarea" id="textarea_title" size="90" <? echo ($result_set["data"]["id"] != 0 ? "readonly" : ""); ?> value="<? echo ($result_set["data"]["reply_id"] > 0 ? split_line(htmlspecialchars($result_set["data"]["title"], ENT_QUOTES | ENT_HTML401, 'UTF-8'), "Re: ", 80, 1) : htmlspecialchars($result_set["data"]["title"], ENT_QUOTES | ENT_HTML401, 'UTF-8')); ?>">
<?
	if ($result_set["data"]["id"] == 0 && $result_set["data"]["reply_id"] == 0)
	{
?>
			<input type="checkbox" name="transship">转载
<?
	}
?></td>
	</tr>
	<tr>
		<td align="right">正文<span id="err_msg_content" name="err_msg" style="color: red;"></span></td>
		<td>
			<textarea name="textarea" id="textarea_content" cols="90" rows="25"><?
if ($result_set["data"]["reply_id"] == 0)
{
	echo htmlspecialchars($result_set["data"]["content"], ENT_HTML401, 'UTF-8');
}
else if ($quote)
{
?>



【 在 <? echo htmlspecialchars($result_set["data"]["r_username"], ENT_HTML401, 'UTF-8'); ?> (<? echo htmlspecialchars($result_set["data"]["r_nickname"], ENT_HTML401, 'UTF-8'); ?>) 的大作中提到: 】
<?
	echo htmlspecialchars(LMLtagFilter(LML(split_line($result_set["data"]["content"], ": ", 76, 20), false, false, 1024)), ENT_HTML401, 'UTF-8');
}
?></textarea>
		</td>
	</tr>
	<tr>
		<td align="right"><a class="s0" href="doc/lml.htm" target=_blank>LML</a>助手</td>
		<td>
			<INPUT type="button" value="B" onclick="b_bold(textarea_content)" style="font-weight:bold; width:25px;">
			<INPUT type="button" value="I" onclick="b_italic(textarea_content)" style="font-style:italic; width:25px;">
			<INPUT type="button" value="U" onclick="b_underline(textarea_content)" style="text-decoration:underline; width:25px;">
			<INPUT type="button" value="[" onclick="b_left(textarea_content)" style="width:20px;">
			<INPUT type="button" value="]" onclick="b_right(textarea_content)" style="width:20px;">
			<INPUT type="button" value="Aa" onclick="b_size(textarea_content)" style="width:30px;">
			<INPUT type="button" value="A" onclick="b_color(textarea_content)" style="font-weight:bold; color:red; width:25px;">
			<INPUT type="button" value="@" onclick="b_email(textarea_content)" style="width:25px;">
			<INPUT type="button" value="Link" onclick="b_link(textarea_content)" style="text-decoration:underline; color:blue; width:40px;">
			<INPUT type="button" value="主题" onclick="b_article(textarea_content)" style="text-decoration:underline; color:green; width:40px;">
			<INPUT type="button" value="图片" onclick="b_image(textarea_content)" style="width:40px;">
			<INPUT type="button" value="字幕" onclick="b_marquee(textarea_content)" style="width:40px;">
		</td>
	</tr>
	<tr>
		<td align="right">上传附件<span id="err_msg_attachment" name="err_msg" style="color: red;"></span></td>
		<td>
			单个文件大小不能超过<? echo $BBS_upload_size_limit; ?>M，
			单次上传不超过<? echo $BBS_upload_count_limit; ?>个文件<br />
			文件类型限于BMP，GIF，JPEG，PNG，TIFF，TXT，ZIP，RAR<br />
	  		<INPUT TYPE="file" size="40" name="attachment[]" id="attachment" multiple>
<?
	if ($result_set["data"]["id"] != 0) // Modify article
	{
		if (count($result_set["data"]["attachments"]) > 0)
		{
?>
			<hr width="80%" align="left" />已上传附件<br />
<?
		}

		foreach ($result_set["data"]["attachments"] as $aid => $attachment)
		{
			$filename = $attachment["filename"];
			$ext = strtolower(substr($filename, (strrpos($filename, ".") ? strrpos($filename, ".") + 1 : 0)));
?>
			<span id="attachment_<? echo $aid; ?>"><img src="images/closed.gif"><a class="s2" href="dl_file.php?aid=<? echo $aid; ?>" target="_target"><? echo $filename; ?></a> (<? echo $attachment["size"]; ?>字节)
<?
			if ($attachment["check"] == 0)
			{
?><font color="red">未审核</font><?
			}
?>
			<a class="s2" href="#" onclick="return upload_del(<? echo $aid; ?>);">删除</a>
			<br /></span>
<?
		}
	}
?>
		</td>
	</tr>
	<tr>
		<td align="right">表情<span id="err_msg_emoji" name="err_msg" style="color: red;"></span></td>
		<td><?
	for ($i = 1; $i <= $BBS_emoji_count; $i++)
	{
?><input type="radio" name="emoji" value="<? echo $i; ?>" <? echo ($i == $result_set["data"]["emoji"] ? "checked" : ""); ?>><img src="images/expression/<? echo $i; ?>.gif" width="15" height="15" alt="<? echo $i; ?>.gif"><?
		if (($i % 12)==0)
		{
?><br><? 
		}
	}
?></td>
	</tr>
<?
	if ($result_set["data"]["id"] == 0)
	{
?>
	<tr>
		<td align="right">签名<span id="err_msg_sign" name="err_msg" style="color: red;"></span></td>
		<td>
			<input type="radio" id="sign_id_0" name="sign_id" value="0" checked>不使用&nbsp;
			<input type="radio" id="sign_id_1" name="sign_id" value="1">1&nbsp;
			<input type="radio" id="sign_id_2" name="sign_id" value="2">2&nbsp;
			<input type="radio" id="sign_id_3" name="sign_id" value="3">3&nbsp;
			<a class="s0" href="preference.php" target=_blank>设置个人签名</a>
		</td>
	</tr>
<?
	}
	else
	{
?>
	<input type="hidden" id="sign_id_0" name="sign_id" value="0">
<?
	}
?>
	<tr>
		<td align="right"></td>
		<td><input type="checkbox" name="reply_note" id="reply_note" <? echo ($result_set["data"]["reply_note"] ? "checked":""); ?>>有人回复该主题时通知我</td>
	</tr>
</table>
<p><input type="submit" value="提交" name="submit">&nbsp;&nbsp;&nbsp;<input type="reset" value="重填" name="reset"></p>
</center>
</form>
<?
	include "./foot.inc.php";
?>
</body>
</html>

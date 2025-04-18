<?
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}

	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./user_level.inc.php";

	// Pre-defined color setting of article display
	$color = array(
		"#FAFBFC",
		"#f0F3Fa"
	);
	$color_index = 0;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><? echo htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'); ?></title>
<link rel="stylesheet" href="<? echo get_theme_file('css/default'); ?>" type="text/css">
<style type="text/css">
SPAN.title_normal
{
	color: #909090;
}
SPAN.title_deleted
{
	color: red;
	text-decoration: line-through;
}
TD.content_normal
{
	font-size: 16px;
}
TD.content_deleted
{
	font-size: 16px;
	text-decoration: line-through;
}
</style>
<script type="text/javascript" src="../js/img_adjust.js"></script>
<script src="../js/polyfill.min.js"></script>
<script src="../js/axios.min.js"></script>
<script type="text/javascript">
function ch_page(page)
{
	document.change_page.page.value = page;
	document.change_page.submit();
	return false;
}

function ch_rpp()
{
	document.change_page.page.value = Math.floor((document.change_page.page.value - 1) * <? echo $result_set["data"]["rpp"]; ?> / document.change_page.rpp.value) + 1;
	document.change_page.submit();
	return false;
}

function refresh_err_msg(errorFieldMap)
{
	document.getElementsByName("err_msg").forEach(element => {
		element.innerHTML = (errorFieldMap.has(element.id) ? errorFieldMap.get(element.id) : "");
	});
}

function upload_del(id)
{
	if (window.confirm('真的要删除吗？') == false)
	{
		return false;
	}

	instance.post('upload_del.php', {
		aid: id,
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
				errorFieldMap.set("err_msg_attachment_" + id, ret.return.message);
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_attachment_" + id, "内部错误<br />");
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

function article_op(op_type, id, set, confirm = false)
{
	var opService = new Map([
		["delete", "delete.php"],
		["restore", "restore.php"],
		["excerption", "set_excerption.php"],
		["ontop", "set_ontop.php"],
		["lock", "lock.php"],
		["transship", "set_transship.php"],
	]);

	var opNeedRefresh = new Set([
		"delete",
		"restore",
	]);

	if (confirm && window.confirm('真的要操作吗？') == false)
	{
		return false;
	}

	instance.post(opService.get(op_type), {
		id: id,
		set: set,
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
			case 1: // Already set
				if (opNeedRefresh.has(op_type))
				{
					// Refresh with additional parameters
					document.location = "view_article.php?trash=1&rpp=<? echo $result_set["data"]["rpp"]; ?>&ts=" + Date.now() + "&id=" + id + "#" + id;
					break;
				}
				document.getElementById("set_" + op_type + "_" + id).style.display = (set ? "none" : "inline");
				document.getElementById("unset_" + op_type + "_" + id).style.display = (set ? "inline" : "none");
				refresh_err_msg(errorFieldMap);
				break;
			case -1: // Input validation failed
				errorFieldMap.set("err_msg_ctrl_" + id, ret.return.message);
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_ctrl_" + id, "内部错误");
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

function move_article(sid)
{
	instance.post('move_article.php', {
		id: <? echo $result_set["data"]["id"]; ?>,
		sid: sid,
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
				refresh_err_msg(errorFieldMap);
				document.location = "view_article.php?id=<? echo $result_set["data"]["id"]; ?>&trash=<? echo $result_set["data"]["trash"]; ?>&rpp=<? echo $result_set["data"]["rpp"]; ?>&ts=" + Date.now();
				break;
			case -1: // Input validation failed
				errorFieldMap.set("err_msg_move", ret.return.message);
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_move", "内部错误");
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

function set_ex_dir(fid)
{
	instance.post('set_ex_file_sub.php', {
		id: <? echo $result_set["data"]["id"]; ?>,
		fid: fid,
	})
	.then(function (response) {
		var ret = response.data;
		var errorFieldMap = new Map();
		switch (ret.return.code)
		{
			case 0: // OK
				refresh_err_msg(errorFieldMap);
				document.location = "view_article.php?id=<? echo $result_set["data"]["id"]; ?>&trash=<? echo $result_set["data"]["trash"]; ?>&rpp=<? echo $result_set["data"]["rpp"]; ?>&ts=" + Date.now();
				break;
			case -1: // Input validation failed
				errorFieldMap.set("err_msg_ex_dir", ret.return.message);
				refresh_err_msg(errorFieldMap);
				break;
			case -2: // Internal error
				console.log(ret.return.message);
				errorFieldMap.set("err_msg_ex_dir", "内部错误");
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
	var s = document.getElementById("ex_dir");
	if (s)
	{
		s.addEventListener("change", (e) => {
			set_ex_dir(s.value);
		});
	}

	var f = document.getElementById("move_article");
	if (f)
	{
		f.addEventListener("submit", (e) => {
			e.preventDefault();
			move_article(f.sid.value);
		});
	}
});

</script>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3013347141025996" crossorigin="anonymous">
</script>
</head>
<body>
	<a name="top"></a>
	<center>
		<span id="err_msg_prompt" name="err_msg" style="color: red;"></span>
		<table cols="2" border="0" cellpadding="0" cellspacing="0" width="1050">
			<tr>
				<td width="50%">
					<a class="s2" href="main.php?sid=<? echo $result_set["data"]["sid"]; ?>"><? echo $BBS_name; ?></a>&gt;&gt;<a class="s2" href="list.php?sid=<? echo $result_set["data"]["sid"]; ?>"><? echo $result_set["data"]["section_title"]; ?></a>&gt;&gt;<a class="s2" href="list.php?sid=<? echo $result_set["data"]["sid"]; ?>&ex=<? echo ($result_set["data"]["ex"]); ?>"><? echo ($result_set["data"]["ex"] ? "文摘区" : "讨论区"); ?></a>
					<a class="s2" href="post.php?reply_id=<? echo $result_set["data"]["id"]; ?>&quote=0" title="直接回复该文章">[快速回复]</a>
<?
	if ($previous_id > 0)
	{
?>
					<a class="s2" href="view_article.php?id=<? echo $previous_id; ?>&ex=<? echo $result_set["data"]["ex"]; ?>&trash=<? echo $result_set["data"]["trash"]; ?>">[上一主题]</a>
<?
	}
	if ($next_id > 0)
	{
?>
					<a class="s2" href="view_article.php?id=<? echo $next_id; ?>&ex=<? echo $result_set["data"]["ex"]; ?>&trash=<? echo $result_set["data"]["trash"]; ?>">[下一主题]</a>
<?
	}
?>
				</td>
				<td width="50%" align="right">
<?
	// Only show set_ex_file at page 1
	if ($result_set["data"]["excerption"] && $result_set["data"]["page"] == 1 && $_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST | S_MAN_S))
	{
?>
					<span id="err_msg_ex_dir" name="err_msg" style="color: red;"></span>
					<select id="ex_dir" name="ex_dir" size="1">
						<option value="-1" <? echo ($result_set["data"]["fid"] == -1 ? "selected" : ""); ?>>[不属于精华区]</option>
						<option value="0" <? echo ($result_set["data"]["fid"] == 0 ? "selected" : ""); ?>>(根目录)</option>
<?
		foreach ($result_set["data"]["section_ex_dirs"] as $section_ex_dir)
		{
?>
						<option value="<? echo $section_ex_dir["fid"]; ?>" <? echo ($result_set["data"]["fid"] == $section_ex_dir["fid"] ? "selected" : ""); ?>><? echo $section_ex_dir["dir"]; ?>(<? echo $section_ex_dir["name"]; ?>)</option>
<?
		}
?>
					</select>
<?
	}
	else if ($result_set["data"]["fid"] >= 0)
	{
?>
					<a class="s2" href="/gen_ex/<? echo $result_set["data"]["sid"] . "/" . $result_set["data"]["ex_dir"]; ?>" target=_blank title="精华区目录"><? echo $result_set["data"]["ex_dir"] . "(" . $result_set["data"]["ex_name"] . ")"; ?></a>
<?
	}
?>
				</td>
			</tr>
			<tr bgcolor="#d0d3F0" height="25">
				<td colspan="2" align="center" class="title">
					[<? echo $result_set["data"]["id"]; ?>]&nbsp;主题：&nbsp;<? echo htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'); ?>
				</td>
			</tr>
		</table>
<?
	foreach ($result_set["data"]["articles"] as $article)
	{
		$color_index = ($color_index + 1) % count($color);

		$user_viewable = (isset($result_set["data"]["author_list"][$article["uid"]]));

		if ($article["tid"] != 0)
		{
?>
		<a name="<? echo $article["aid"]; ?>"></a>
		<table bgcolor="<? echo $color[$color_index]; ?>" border="0" cellpadding="0" cellspacing="0" width="1050">
			<tr height="1" bgcolor="#202020">
				<td colspan="3">
				</td>
			</tr>
		</table>
<?
		}
?>
		<table bgcolor="<? echo $color[$color_index]; ?>" border="0" cellpadding="0" cellspacing="10" width="1050">
			<tr>
				<td width="20%">
				</td>
				<td width="75%">
<?
		if ($_SESSION["BBS_priv"]->checkpriv(0, S_MSG) && $_SESSION["BBS_uid"] != $article["uid"])
		{
?>
					<img src="images/mail.gif" width="16" height="16"><a class="s4" href="read_msg.php?sent=1&uid=<? echo $article["uid"]; ?>" target=_blank title="给作者发消息">消息</a>
<?
		}
		if ($article["visible"])
		{
			if ($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST) && $_SESSION["BBS_uid"] == $article["uid"] && (!$article["excerption"]))
			{
?>
					<a class="s4" href="post.php?id=<? echo $article["aid"]; ?>" title="修改该文章">修改</a>
<?
			}
			if ($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST) &&
				($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_MAN_S) || $_SESSION["BBS_uid"] == $article["uid"]) && (!$article["excerption"]))
			{
?>
					<span id="set_delete_<? echo $article["aid"]; ?>"><img src="images/del.gif" width="16" height="16"><a class="s4" href="" onclick="return article_op('delete', <? echo $article["aid"]; ?>, 1, true);" title="删除该文章">删除</a></span>
<?
			}
			if ($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST))
			{
?>
					<img src="images/edit.gif" width="16" height="16"><a class="s4" href="post.php?reply_id=<? echo $article["aid"]; ?>" title="引用回复该文章">回复</a>
<?
			}
			if ($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST | S_MAN_S))
			{
?>
					<a class="s4" id="set_excerption_<? echo $article["aid"]; ?>" style="display: <? echo ($article["excerption"] ? "none" : "inline"); ?>" href="" onclick="return article_op('excerption', <? echo $article["aid"]; ?>, 1);" title="加入文摘区">收录</a>
					<a class="s4" id="unset_excerption_<? echo $article["aid"]; ?>" style="display: <? echo ($article["excerption"] ? "inline" : "none"); ?>" href="" onclick="return article_op('excerption', <? echo $article["aid"]; ?>, 0, true);" title="移出文摘区">移出</a>
<?
			}
			if ($article["tid"] == 0 && $_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST | S_MAN_S))
			{
?>
					<a class="s4" id="set_ontop_<? echo $article["aid"]; ?>" style="display: <? echo ($article["ontop"] ? "none" : "inline"); ?>" href="" onclick="return article_op('ontop', <? echo $article["aid"]; ?>, 1, true);" title="置顶">置顶</a>
					<a class="s4" id="unset_ontop_<? echo $article["aid"]; ?>" style="display: <? echo ($article["ontop"] ? "inline" : "none"); ?>" href="" onclick="return article_op('ontop', <? echo $article["aid"]; ?>, 0);" title="取消置顶">取消置顶</a>
<?
			}
			if ($article["tid"] == 0 && $_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST) && 
				($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_MAN_S) || $_SESSION["BBS_uid"] == $article["uid"]))
			{
?>
					<a class="s4" id="set_lock_<? echo $article["aid"]; ?>" style="display: <? echo ($article["lock"] ? "none" : "inline"); ?>" href="" onclick="return article_op('lock', <? echo $article["aid"]; ?>, 1);" title="禁止回复">静默</a>
					<a class="s4" id="unset_lock_<? echo $article["aid"]; ?>" style="display: <? echo ($article["lock"] ? "inline" : "none"); ?>" href="" onclick="return article_op('lock', <? echo $article["aid"]; ?>, 0);" title="取消禁止回复">取消静默</a>
<?
			}
			if ($article["tid"] == 0 && $_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST | S_MAN_S) && (!$article["transship"]) && (!$article["excerption"]))
			{
?>
					<a class="s4" id="set_transship_<? echo $article["aid"]; ?>" style="display: <? echo ($article["transship"] ? "none" : "inline"); ?>" href="" onclick="return article_op('transship', <? echo $article["aid"]; ?>, 1, true);" title="设为转载">设为转载</a>
					<a class="s4" id="unset_transship_<? echo $article["aid"]; ?>" style="display: none" href=""></a>
<?
			}
		}
		else
		{
			if ($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST | S_MAN_S) && $article["m_del"])
			{
?>
					<a class="s4" id="set_restore_<? echo $article["aid"]; ?>" href="" onclick="return article_op('restore', <? echo $article["aid"]; ?>, 1, true);" title="恢复删除">恢复</a>
<?
			}
		}
?>
					<span id="err_msg_ctrl_<? echo $article["aid"]; ?>" name="err_msg" style="color: red;"></span>
				</td>
				<td width="5%">
				</td>
			</tr>
			<tr>
				<td width="20%" align="center">
					作者：&nbsp;<a class="s2" href="show_profile.php?uid=<? echo $article["uid"]; ?>" onclick='return <? echo ($user_viewable ? "true" : "false"); ?>' target=_blank title="查看用户资料"><? echo htmlspecialchars($article["username"], ENT_HTML401, 'UTF-8'); ?></a>
				</td>
				<td width="75%" class="body">
					<span style="color:#606060;">标题：</span>
					<img src="images/expression/<? echo $article["icon"]; ?>.gif">
					<span id="title_<? echo $article["aid"]; ?>" class="<? echo ($article["visible"] ? "title_normal" : "title_deleted"); ?>">
						<? echo split_line(htmlspecialchars($article["title"], ENT_HTML401, 'UTF-8'), "", 65, 2, "<br />"); ?>
					</span>
					<? if ($article["transship"]) { ?><font color="red">[转载]</font><? } ?>
				</td>
				<td width="5%">
				</td>
			</tr>
			<tr>
				<td align="center">
					昵称：&nbsp;<span style="color: #909090;"><? echo htmlspecialchars($article["nickname"], ENT_HTML401, 'UTF-8'); ?></span>
				</td>
				<td class="body">
					<span style="color:#606060;">来自：</span>&nbsp;<span style="color: #909090; "><? echo $article["sub_ip"]; ?></span>
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td align="center">
					经验值：&nbsp;<span style="color:red;"><? echo $article["exp"]; ?></span>
				</td>
				<td class="body">
					<span style="color:#606060;">发贴时间：</span>&nbsp;<span style="color: #909090; "><? echo $article["sub_dt"]->format("Y年m月d日 H:i:s (\U\T\C P)"); ?></span>
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td align="center">
					等级：&nbsp;<span style="color: #909090;"><? echo user_level($article["exp"]); ?></span>
				</td>
				<td class="body">
					<span style="color:#606060;">长度：</span>&nbsp;<span style="color: #909090; "><? echo $article["length"]; ?>字</span>
				</td>
				<td>
				</td>
			</tr>
			<tr height="2">
				<td>
				</td>
				<td style="background-color: #909090;">
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td align="center" valign="top">
					<img src="<? echo $article["photo_path"]; ?>" border="0">
				</td>
				<td id="content_<? echo $article["aid"]; ?>" class="<? echo ($article["visible"] ? "content_normal" : "content_deleted"); ?>">
					<? echo LML(htmlspecialchars((isset($article["content"]) ? $article["content"] : ""), ENT_HTML401, 'UTF-8'), true, true, 80); ?>
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td>
				</td>
				<td style="color:#000000; ">
					========== * * * * * ==========
					<br />
<?
		foreach ($article["attachments"] as $attachment)
		{
			$filename = $attachment["filename"];
			$ext = strtolower(substr($filename, (strrpos($filename, ".") ? strrpos($filename, ".") + 1 : 0)));
?>
					<span id="attachment_<? echo $attachment["aid"]; ?>"><img src="images/closed.gif"><a class="s2" href="dl_file.php?aid=<? echo $attachment["aid"]; ?>" target="_target"><? echo $filename; ?></a> (<? echo $attachment["size"]; ?>字节)
<?
			if ($attachment["check"] == 0)
			{
?><font color="red">未审核</font><?
			}
			else
			{
				switch ($ext)
				{
					case "bmp":
					case "gif":
					case "jpg":
					case "jpeg":
					case "png":
					case "tif":
					case "tiff":
?>
					<br /><img onmousewheel="return bbs_img_zoom(event, this)" src="dl_file.php?aid=<? echo $attachment["aid"]; ?>">
<?
						break;
				}
			}

			if ($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST) &&
				($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_MAN_S) || $_SESSION["BBS_uid"] == $article["uid"]) && (!$article["excerption"]))
			{
?>
					<a class="s2" href="#" onclick="return upload_del(<? echo $attachment["aid"]; ?>);">删除</a>
					<span id="err_msg_attachment_<? echo $attachment["aid"]; ?>" name="err_msg" style="color: red;"></span>
<?
			}
?>
					<br /></span>
<?
		}
?>
				</td>
				<td>
				</td>
			</tr>
		</table>
<?
	}
?>
		<table cols="3" border="0" cellpadding="5" cellspacing="0" width="1050">
			<tr bgcolor="#d0d3F0" height="10">
				<td colspan="3">
				</td>
			</tr>
			<tr>
				<td width="40%" style="color: #909090">
				<form action="view_article.php" method="get" id="change_page" name="change_page">
					<input type="hidden" id="id" name="id" value="<? echo $result_set["data"]["id"]; ?>">
					<input type="hidden" id="ex" name="ex" value="<? echo $result_set["data"]["ex"]; ?>">
					<input type="hidden" id="trash" name="trash" value="<? echo $result_set["data"]["trash"]; ?>">
					每页<select size="1" id="rpp" name="rpp" onchange="ch_rpp();">
<?
	foreach ($BBS_view_rpp_options as $v)
	{
		echo ("<option value=\"$v\"" . ($v == $result_set["data"]["rpp"] ? " selected" : "") . ">$v</option>");
	}
?>
					</select>条
<?
	if ($result_set["data"]["page"] > 1)
	{
?>
					<a class="s8" title="首页" href="" onclick="return ch_page(1);">|◀</a>
					<a class="s8" title="上一页" href="" onclick="return ch_page(<? echo ($result_set["data"]["page"] - 1); ?>);">◀</a>
<?
	}
	else
	{
?>
					|◀ ◀
<?
	}
?>
					第<input id="page" name="page" value="<? echo ($result_set["data"]["page"]) ; ?>" style="width: 30px;">/<? echo $result_set["data"]["page_total"]; ?>页
<?
	if ($result_set["data"]["page"] < $result_set["data"]["page_total"])
	{
?>
					<a class="s8" title="下一页" href="" onclick="return ch_page(<? echo ($result_set["data"]["page"] + 1); ?>);">▶</a>
					<a class="s8" title="尾页" href="" onclick="return ch_page(<? echo ($result_set["data"]["page_total"]); ?>);">▶|</a>
<?
	}
	else
	{
?>
					▶ ▶|
<?
	}
?>
				</form>
				</td>
				<td width="35%" align="center">
<?
	if ($_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_POST | S_MAN_S) && (!$result_set["data"]["excerption"]))
	{
?>
				<form method="post" id="move_article" name="move_article" action="#">
					<select id="sid" name="sid" size="1">
<?
		echo $result_set["data"]["section_list_options"];
?>
					</select>
					<input type="submit" value="移动">
					<span id="err_msg_move" name="err_msg" style="color: red;"></span>
				</form>
<?
	}
?>				</td>
				<td width="25%" align="right">
					<a class="s2" href="#top" title="返回页首"><img src="images/gotop.gif" border="0">Top<img src="images/gotop.gif" border="0"></a>
				</td>
			</tr>
		</table>
	</center>
<?
	include "./foot.inc.php";
?>
</body>
</html>

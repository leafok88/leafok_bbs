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
<title>用户查找结果</title>
<link rel="stylesheet" href="<? echo get_theme_file('css/default'); ?>" type="text/css">
<style type="text/css">
TD.head,TD.level,TD.login_dt,TD.dark,TD.username
{
	border-right: #d0d3F0 1px solid;
	border-left: #d0d3F0 1px solid;
	border-bottom: #d0d3F0 1px solid;
}
TD.head
{
	font-family: 楷体;
	color: #909090;
}
TD.login_dt,TD.level,TD.dark,TD.username
{
	text-align: center;
}
TD.login_dt,TD.dark
{
	color: #909090;
	background-color: #eaf0Fa;
}
TD.head,TD.level
{
	background-color: #fafbfc;
}
TD.level
{
	color: orange;
}
TD.username
{
	background-color: #fafbfc;
}
TD.username:hover
{
	background-color: #eaf0Fa;
}
</style>

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
</script>
</head>
<body>
		<center>
			<table cols="2" border="0" cellpadding="0" cellspacing="0" width="1050">
				<tr>
					<td colspan="2" style="color:green;">
						<a class="s2" href="main.php"><? echo $BBS_name; ?></a>&gt;&gt;查找<? echo ($result_set["data"]["online"] ? "在线" : ""); ?><? echo ($result_set["data"]["friend"] ? "好友" : "用户"); ?>
					</td>
				</tr>
				<tr bgcolor="#d0d3F0" height="2">
					<td colspan="2"></td></tr>
				<tr>
					<td class="dark" width="3%"></td>
					<td class="head" width="97%">
<?
	if ($result_set["data"]["toa"] == 0)
	{
?>未找到指定用户<?
	}
	else
	{
?>用户查找结果（共<? echo $result_set["data"]["toa"]; ?>位）
<?
	}
?>（当前在线注册用户<? echo $result_set["data"]["user_online"]; ?>位，游客<? echo $result_set["data"]["guest_online"]; ?>位）
					</td>
				</tr>
			</table>
			<table border="0" cellpadding="1" cellspacing="0" width="1050">
				<tr height="10">
					<td>
					</td>
				</tr>
			</table>
			<table bgcolor="#f0F3Fa" border="0" cellpadding="0" cellspacing="0" width="1050">
				<tr bgcolor="#d0d3F0" height="20">
					<td class="title" width="4%"></td>
					<td class="title" width="15%">用户ID</td>
					<td class="title" width="20%">昵称</td>
					<td class="title" width="12%">等级</td>
					<td class="title" width="18%">最后登陆时间</td>
					<td class="title" width="27%"></td>
					<td class="title" width="4%"></td>
				</tr>
<?
	foreach ($result_set["data"]["users"] as $user)
	{
?>
				<tr height="25">
					<td class="dark">
<?
		if ($user["gender_pub"])
		{
			if ($user["gender"] == 'M')
			{
				echo ("<font color=blue>♂</font>");
			}
			else
			{
				echo ("<font color=red>♀</font>");
			}
		}
		else
		{
			echo ("<font color=green>?</font>");
		}
?>
					</td>
					<td class="username">
						<a class="s2" href="show_profile.php?uid=<? echo $user["uid"]; ?>" target=_blank><? echo $user["username"]; ?></a>
					</td>
					<td class="dark">
						<? echo $user["nickname"]; ?>
					</td>
					<td class="level">
						<? echo user_level($user["exp"]); ?>
					</td>
					<td class="login_dt">
						<? echo $user["last_login_dt"]->format("Y-m-d H:i:s"); ?>
					</td>
					<td class="level">
<?
		if ($_SESSION["BBS_priv"]->checkpriv(0, S_MSG))
		{
?>
						<a class="s2" href="read_msg.php?sent=1&uid=<? echo $user["uid"]; ?>" target=_blank>发送消息</a>
<?
		}
?>
					</td>
					<td align="center">
					</td>
				</tr>
<?
	}
?>
			</table>
			<table cols="3" border="0" cellpadding="5" cellspacing="0" width="1050">
				<tr bgcolor="#d0d3F0" height="10">
					<td colspan="3" >
					</td>
				</tr>
				<tr height="10">
					<td colspan="3" >
					</td>
				</tr>
				<tr>
				<form action="search_user.php" method="get" id="change_page" name="change_page">
					<td width="30%" style="color: #909090">
					每页<select size="1" id="rpp" name="rpp" onchange="ch_rpp();">
<?
	foreach ($BBS_list_rpp_options as $v)
	{
		echo ("<option value=\"$v\"" . ($v == $result_set["data"]["rpp"] ? " selected" : "") . ">$v</option>");
	}
?>
			</select>人
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
					</td>
					<td width="50%">
						<select name="type">
							<option value="0" <? if ($result_set["data"]["type"] == 0) echo "selected"; ?> >按用户名</option>
							<option value="1" <? if ($result_set["data"]["type"] == 1) echo "selected"; ?> >按昵称</option>
						</select>
						<input type="text" id="search_text" name="search_text" size="15" value="<? echo $result_set["data"]["search_text"];?>">
						<input type="checkbox" id="online" name="online" value="1" <? echo ($result_set["data"]["online"] ? "checked" : "");?>><font color=#909090>在线</font>
						<input type="checkbox" id="friend" name="friend" value="1" <? echo ($result_set["data"]["friend"] ? "checked" : "");?>><font color=#909090>好友</font>
						<input type=image src="images/search.gif" alt="查找用户" border="0" name="image"></a>
					</td>
					<td width="10%">
					</td>
					</form>
				</tr>
			</table>
		</center>
<?
include "./foot.inc.php";
?>
</body>
</html>

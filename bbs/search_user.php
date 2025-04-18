<?
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";
	require_once "./user_level.inc.php";
?>
<?
$page = (isset($_GET["page"]) ? intval($_GET["page"]) : 1);
$rpp = (isset($_GET["rpp"]) ? intval($_GET["rpp"]) : 20);

$type = (isset($_GET["type"]) ? intval($_GET["type"]) : 0);
$online = (isset($_GET["online"]) && $_GET["online"] == "1" ? 1 : 0);
$friend = (isset($_GET["friend"]) && $_GET["friend"] == "1" ? 1 : 0);
$search_text = (isset($_GET["search_text"]) ? $_GET["search_text"] : "");

$sql = "SELECT IF(UID = 0, 1, 0) AS is_guest, COUNT(*) AS u_count FROM user_online
		WHERE last_tm >= SUBDATE(NOW(), INTERVAL $BBS_user_off_line SECOND)
		GROUP BY is_guest";

$rs = mysqli_query($db_conn, $sql);
if ($rs == false)
{
	echo("Count online user error" . mysqli_error($db_conn));
	exit();
}

$guest_online = 0;
$user_online = 0;

while ($row = mysqli_fetch_array($rs))
{
	if ($row["is_guest"])
	{
		$guest_online = $row["u_count"];
	}
	else
	{
		$user_online = $row["u_count"];
	}
}
mysqli_free_result($rs);

$sql = "SELECT COUNT(user_list.UID) AS rec_count FROM user_list" .
		($online ? " INNER JOIN user_online ON user_list.UID = user_online.UID" : "") .
		($friend ? " INNER JOIN friend_list ON user_list.UID = friend_list.fUID" : "") .
		($type == 1 ? " INNER JOIN user_pubinfo ON user_list.UID = user_pubinfo.UID" : "") .
		" WHERE user_list.enable AND ".
		($type == 1 ? "nickname" : "username") .
		" LIKE '%" . mysqli_real_escape_string($db_conn, $search_text) . "%'" .
		($online ? " AND last_tm >= SUBDATE(NOW(), INTERVAL $BBS_user_off_line SECOND)" : "").
		($friend ? " AND friend_list.UID = " . $_SESSION["BBS_uid"] : "");

$rs = mysqli_query($db_conn, $sql);
if ($rs == false)
{
	echo("Query user error" . mysqli_error($db_conn));
	exit();
}

if ($row = mysqli_fetch_array($rs))
{
	$toa = $row["rec_count"];
}

mysqli_free_result($rs);

if (!in_array($rpp, $BBS_list_rpp_options))
{
	$rpp = $BBS_list_rpp_options[0];
}

$page_total = ceil($toa / $rpp);
if ($page > $page_total)
{
	$page = $page_total;
}

if ($page <= 0)
{
	$page = 1;
}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>用户查找结果</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
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

<script type="text/javascript" src="../js/nw_open.js"></script>
<script type="text/javascript">
function ch_page(page)
{
	document.change_page.page.value = page;
	document.change_page.submit();
	return false;
}

function ch_rpp()
{
	document.change_page.page.value = Math.floor((document.change_page.page.value - 1) * <? echo $rpp; ?> / document.change_page.rpp.value) + 1;
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
						<a class="s2" href="main.php"><? echo $BBS_name; ?></a>&gt;&gt;查找<? echo ($online?"在线":""); ?><? echo ($friend?"好友":"用户"); ?>
					</td>
				</tr>
				<tr bgcolor="#d0d3F0" height="2">
					<td colspan="2"></td></tr>
				<tr>
					<td class="dark" width="3%"></td>
					<td class="head" width="97%">
<?
if ($toa==0)
{
?>未找到指定用户<?
}
else
{
?>用户查找结果（共<? echo $toa; ?>位）
<?
}
?>（当前在线注册用户<? echo $user_online; ?>位，游客<? echo $guest_online; ?>位）
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
$sql = "SELECT user_list.UID, username, nickname, exp, gender, gender_pub, last_login_dt FROM user_list" .
		($online ? " INNER JOIN user_online ON user_list.UID = user_online.UID" : "") .
		($friend ? " INNER JOIN friend_list ON user_list.UID = friend_list.fUID" : "") .
		" INNER JOIN user_pubinfo ON user_list.UID = user_pubinfo.UID WHERE user_list.enable AND ".
		($type == 1 ? "nickname" : "username") .
		" LIKE '%" . mysqli_real_escape_string($db_conn, $search_text) . "%'" .
		($online ? " AND last_tm >= SUBDATE(NOW(), INTERVAL $BBS_user_off_line SECOND)" : "").
		($friend ? " AND friend_list.UID = " . $_SESSION["BBS_uid"] : "") .
		" ORDER BY " . ($type == 1 ? "nickname" : "username") .
		" LIMIT " . ($page-1) * $rpp . ", $rpp";

$rs = mysqli_query($db_conn, $sql);
if ($rs == false)
{
	echo("Query user error" . mysqli_error($db_conn));
	exit();
}

while ($row = mysqli_fetch_array($rs))
{
?>
				<tr height="25">
					<td class="dark">
<?
	if ($row["gender_pub"])
	{
		if ($row["gender"] == 'M')
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
						<a class="s2" href="show_profile.php?uid=<? echo $row["UID"]; ?>" target=_blank><? echo $row["username"]; ?></a>
					</td>
					<td class="dark">
						<? echo $row["nickname"]; ?>
					</td>
					<td class="level">
						<? echo user_level($row["exp"]); ?>
					</td>
					<td class="login_dt">
						<? echo (new DateTimeImmutable($row["last_login_dt"]))->setTimezone($_SESSION["BBS_user_tz"])->format("Y-m-d H:i:s"); ?>
					</td>
					<td class="level">
<?
	if ($_SESSION["BBS_priv"]->checkpriv(0, S_MSG))
	{
?>
						<a class="s2" href="read_msg.php?sent=1&uid=<? echo $row["UID"]; ?>" target=_blank>发送消息</a>
<?
	}
?>
					</td>
					<td align="center">
					</td>
				</tr>
<? 
}

mysqli_free_result($rs);
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
		echo ("<option value=\"$v\"" . ($v == $rpp ? " selected" : "") . ">$v</option>");
	}
?>
			</select>人
			<?
if ($page > 1)
{
?>
			<a class="s8" title="首页" href="" onclick="return ch_page(1);">|◀</a>
			<a class="s8" title="上一页" href="" onclick="return ch_page(<? echo ($page - 1); ?>);">◀</a>
<?
}
else
{
?>
|◀ ◀
<?
}
?>
    		第<input id="page" name="page" value="<? echo ($page) ; ?>" style="width: 30px;">/<? echo $page_total; ?>页
<?
if ($page < $page_total)
{
?>
			<a class="s8" title="下一页" href="" onclick="return ch_page(<? echo ($page + 1); ?>);">▶</a>
			<a class="s8" title="尾页" href="" onclick="return ch_page(<? echo ($page_total); ?>);">▶|</a>
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
							<option value="0" <? if ($type==0) echo "selected"; ?> >按用户名</option>
							<option value="1" <? if ($type==1) echo "selected"; ?> >按昵称</option>
						</select>
						<input type="text" id="search_text" name="search_text" size="15" value="<? echo $search_text;?>">
						<input type="checkbox" id="online" name="online" value="1" <? echo ($online ? "checked" : "");?>><font color=#909090>在线</font>
						<input type="checkbox" id="friend" name="friend" value="1" <? echo ($friend ? "checked" : "");?>><font color=#909090>好友</font>
						<input type=image src="images/search.gif" alt="查找用户" border="0" name="image"></a>
					</td>
					<td width="10%">
					</td>
					</form>
				</tr>
			</table>
		</center>
<?
mysqli_close($db_conn);

include "./foot.inc.php";
?>
	</body>
</html>

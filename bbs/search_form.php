<?php
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";
	require_once "./section_list_gen.inc.php";

	force_login();

	$sid = (isset($_GET["sid"]) ? intval($_GET["sid"]) : 0);

	$section_select_options = section_list_gen($db_conn);

	mysqli_close($db_conn);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>文章查找</title>
<link rel="stylesheet" href="css/default.css" type="text/css">
<script type="text/javascript">
window.addEventListener("load", () => {
	var s = document.search_form.sid;
	for (i = 0; i < s.options.length; i++)
	{
		if (s.options[i].value == <?= $sid; ?>)
		{
			s.selectedIndex = i;
			break;
		}
	}
});
</script>
</head>
<body>
	<center>
	<table border="0" cellpadding="1" cellspacing="0" width="1050">
		<tr bgcolor="#d0d3F0" height="20">
			<td align="center" class="title">
				文章查找
			</td>
		</tr>
	</table>
	<table bgcolor="#f0F3Fa" border="0" cellpadding="10" cellspacing="0" width="1050">
		<form action="search_article.php" method="GET" id="search_form" name="search_form" target="_blank">
		<tr>
			<td width="15%">
			</td>
			<td width="10%">
			</td>
			<td width="65%">
			</td>
			<td width="10%">
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				用户名
			</td>
			<td>
				<input name="username" id="username" size="20">
			</td>
			<td>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				标题包含
			</td>
			<td>
				<input name="title" id="title" size="50">
			</td>
			<td>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				正文包含
			</td>
			<td>
				<input name="content" id="content" size="50">
			</td>
			<td>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				昵称包含
			</td>
			<td>
				<input name="nickname" id="nickname" size="20">
			</td>
			<td>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				所属版块
			</td>
			<td>
			    <select size="1" name="sid">
					<option value="0">----所有版块----</option>
<?php
	echo $section_select_options;
?>
				</select>
			</td>
			<td>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				发帖时间
			</td>
			<td>
				从
				<input name="begin_dt" id="begin_dt" value="<?= (new DateTimeImmutable("-1 month", $_SESSION["BBS_user_tz"]))->format("Y-m-d"); ?>" size="10">
				至
				<input name="end_dt" id="end_dt" value="<?= (new DateTimeImmutable("1 day", $_SESSION["BBS_user_tz"]))->format("Y-m-d"); ?>" size="10">
			</td>
			<td>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
			</td>
			<td colspan="2">
				<input type="radio" name="ex" id="ex" value="0" checked>讨论区
				<input type="radio" name="ex" id="ex" value="1">文摘区
				<input type="radio" name="ex" id="ex" value="2">精华区
				<input type="checkbox" name="reply" id="reply" value="1">含回复
				<input type="checkbox" name="original" id="original" value="1">仅原创
<?php
	if ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S | P_MAN_M | P_MAN_S))
	{
?>
				<input type="checkbox" name="trash" id="trash" value="1">回收站
<?php
	}
?>
				<br />
				<input type="radio" name="use_nick" id="use_nick" value="1" checked>显示昵称
				<input type="radio" name="use_nick" id="use_nick" value="0">显示用户名
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td colspan="2" align="center">
				<input type="submit" value="查找">&nbsp;&nbsp;
				<input type="reset" value="清空">
			</td>
			<td>
			</td>
		</tr>
		</form>
	</table>
	</center>
<?php
	include "./foot.inc.php";
?>
</body>
</html>

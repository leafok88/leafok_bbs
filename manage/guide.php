<?php
	require_once "../bbs/session_init.inc.php";

	force_login();

	if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		echo ("没有权限！");
		exit();
	}
?>
<html>
<head>
<meta HTTP-EQUIV="Content-Type" Content="text-html; charset=UTF-8">
<title>管理中心</title>
<link rel="stylesheet" href="css/default.css" type="text/css">
<style>
P
{
	text-align:center;
}
</style>
</head>
<body>
	<p>&nbsp;</p>
<?php
	if ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
?>
	<p><a href="article_audit.php" target="mng_body">文章审核</a></p>
	<p><a href="photo_list.php" target="mng_body">头像审核</a></p>
	<p><a href="upload_list.php" target="mng_body">附件审核</a></p>
	<p><a href="score_calculate.php" target="mng_body">计算积分</a></p>
	<p><a href="db_cleanup.php" target="mng_body">数据清理</a></p>
	<p><a href="unban_user.php" target="mng_body">自动解封</a></p>
	<p><a href="article_stat.php" target="mng_body">发帖统计</a></p>
	<p><a href="gen_ex.php" target="mng_body">精华生成</a></p>
<?php
	}
	if ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M))
	{
?>
	<p><a href="db_repair.php" target="mng_body">数据修复</a></p>
<?php
	}
?>
</body>
</html>

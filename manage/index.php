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
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>
管理中心
</title>
</head>
<frameset cols='15%,85%' frameborder=1 framespacing=0 bannercolor=white>
	<frame src="guide.php" name=mng_guide scrolling=auto>
	<frame src="article_audit.php" name=mng_body scrolling=auto>
</frameset>
</html>

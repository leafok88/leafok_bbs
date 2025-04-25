<?php
	echo <<<HTML
	<p align="center">
	HTML;

	if ($_SESSION["BBS_uid"]>0)
	{
		echo <<<HTML
			| <a class="s4" href="update_pref.php" target=_blank>个人设定</a>
		HTML;
	}
	else
	{
		echo <<<HTML
			| <a class="s4" href="user_reg.php">用户注册</a>
			| <a class="s4" href="user_reset_pass.php">密码重置</a>
		HTML;
	}

	echo <<<HTML
		| <a class="s4" href="search_user.php?online=1" target=_blank>在线用户</a>
	HTML;

	if ($_SESSION["BBS_uid"] > 0)
	{
		echo <<<HTML
			| <a class="s4" href="msg_read.php" target=_blank>查看消息</a>
			| <a class="s4" href="upload_manage.php" target=_blank>上传管理</a>
		HTML;
	}

	if (isset($result_set) && isset($result_set["data"]["sid"]) && $_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_MAN_S))
	{
		echo <<<HTML
			| <a class="s4" href="section_setting.php?sid={$result_set["data"]["sid"]}" target=_blank>版块设定</a>
		HTML;
	}

	if ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		echo <<<HTML
			| <a class="s4" href="/manage/" target=_blank>系统管理</a>
		HTML;
	}

	$user_tz = (new DateTimeImmutable("", $_SESSION["BBS_user_tz"]))->format("e (\U\T\C P)");

	// Log end time
	$page_exec_duration = round((microtime(true) - $time_start) * 1000, 2);

	echo <<<HTML
		| <a class="s4" href="get_help.htm" target=_blank>常见问题</a> |
	</p>
	<p align="center" style="color:gray;">
		Copyright &copy; {$BBS_copyright_duration} <a class="s8" href="/" target=_blank>{$BBS_name}({$BBS_host_name})</a> All Rights Reserved<br />
		时间显示基于用户时区设置：<a class="s8" href="update_pref.php" target=_blank>{$user_tz}</a><br />
		页面运行使用{$page_exec_duration}毫秒
	</p>
	HTML;
?>

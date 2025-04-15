<p align="center">
<?
if ($_SESSION["BBS_uid"]>0)
{
?>
	| <a class="s4" href="preference.php" target=_blank>个人设定</a>
<?
}
else
{
?>
	| <a class="s4" href="reg_user.php">用户注册</a>
	| <a class="s4" href="reset_pass.php">密码重置</a>
<?
}
?>
	| <a class="s4" href="search_user.php?online=1" target=_blank>在线用户</a>
<?
if ($_SESSION["BBS_uid"] > 0)
{
?>
	| <a class="s4" href="read_msg.php" target=_blank>查看消息</a>
<?
}
if ($_SESSION["BBS_uid"]>0)
{
?>
	| <a class="s4" href="upload_manage.php" target=_blank>上传管理</a>
<?
}
if (isset($result_set) && $_SESSION["BBS_priv"]->checkpriv($result_set["data"]["sid"], S_MAN_S))
{
?>
	| <a class="s4" href="section_setting.php?sid=<? echo $result_set["data"]["sid"]; ?>" target=_blank>版块设定</a>
<?
}
if ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
{
?>
	| <a class="s4" href="/manage/" target=_blank>系统管理</a>
<?
}
?>
	| <a class="s4" href="get_help.php" target=_blank>常见问题</a> |
</p>
<p align="center" style="color:gray;">
	Copyright &copy; <? echo $BBS_copyright_duration; ?> <a class="s8" href="/" target=_blank><? echo $BBS_name . "(" . $BBS_host_name . ")"; ?></a> All Rights Reserved<br />
	时间显示基于用户时区设置：<a class="s8" href="preference.php" target=_blank><? echo (new DateTimeImmutable("", $_SESSION["BBS_user_tz"]))->format("e (\U\T\C P)"); ?></a><br />
<?
	// Log end time
	echo "页面运行使用" . round((microtime(true) - $time_start) * 1000, 2) . "毫秒\n";
?>
</p>

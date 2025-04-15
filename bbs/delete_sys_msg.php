<?
	require_once "../lib/db_open.inc.php";
	require_once "./common_lib.inc.php";
	require_once "./session_init.inc.php";
?>
<? 
if ($_SESSION["BBS_uid"]==0)
{
	error_msg("您无权删除消息！",true);
	exit();
}

mysql_query("update bbs_msg set deleted=1 where fromUID=".$BBS_sys_uid.
	" and toUID=".$_SESSION["BBS_uid"]." and (not deleted)")
	or die("delete msg error!");

mysql_close($db_conn);

header("Location: read_msg.php");
?>

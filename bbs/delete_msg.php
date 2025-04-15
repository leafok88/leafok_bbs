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

if (isset($_POST["box"]))
{
	$box=trim($_POST["box"]);
}
else
{
	if (isset($_GET["box"]))
		$box=trim($_GET["box"]);
	else
		$box="";
}

switch($box)
{
	case "inbox":
		$box_type=1;
		break;
	case "sent":
		$box_type=2;
		break;
	default:
		echo ("未指定信箱类型！");
		exit();
}

if (isset($_POST["delete_msg_id"]))
	$msg_id=$_POST["delete_msg_id"];
else
	$msg_id=array();

if (isset($_GET["all"]))
	$del_all=1;
else
	$del_all=0;

foreach($msg_id as $mid)
{
	switch($box_type)
	{
		case 1:
			mysql_query("update bbs_msg set deleted=1 where MID=$mid".
				" and toUID=".$_SESSION["BBS_uid"]." and (not deleted)")
				or die("delete msg error!");
			break;
		case 2:
			mysql_query("update bbs_msg set s_deleted=1 where MID=$mid".
				" and fromUID=".$_SESSION["BBS_uid"]." and (not s_deleted)")
				or die("delete msg error!");
			break;
	}
}

if($del_all)
{
	switch($box_type)
	{
		case 1:
			mysql_query("update bbs_msg set deleted=1 where".
				" toUID=".$_SESSION["BBS_uid"]." and (not deleted)")
				or die("delete msg error!");
			break;
		case 2:
			mysql_query("update bbs_msg set s_deleted=1 where".
				" fromUID=".$_SESSION["BBS_uid"]." and (not s_deleted)")
				or die("delete msg error!");
			break;
	}
}

mysql_close($db_conn);

switch($box_type)
{
	case 1:
		$redir="read_msg.php";
		break;
	case 2:
		$redir="read_send_msg.php";
		break;
}

header ("Location: $redir");
?>

<?
	require_once "../lib/db_open.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "../bbs/session_init.inc.php";
?>
<?
force_login();

if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M))
{
	echo ("没有权限！");
	exit();
} 

if (isset($_POST["cid"]))
	$cid=$_POST["cid"];
else
	$cid=0;

if (isset($_POST["sid"]))
	$sid=$_POST["sid"];
else
	$sid=0;

if (isset($_POST["sname"]))
	$sname=trim($_POST["sname"]);
else
	$sname="";
if (!preg_match("/^[A-Za-z0-9_]{1,20}$/",$sname))
{
	echo ("版块填写不正确！");
	exit();
}

if (isset($_POST["title"]))
	$title=trim($_POST["title"]);
else
	$title="";
$r_title = htmlspecialchars(split_line($title, "", 20, 1), ENT_QUOTES | ENT_HTML401, 'UTF-8');
if ($title != $r_title)
{
	echo ("版块名称格式不正确！");
	exit();
}

$title=mysqli_real_escape_string($db_conn, $title);

if (isset($_POST["exp_get"]))
	$exp_get=($_POST["exp_get"]=="on"?1:0);
else
	$exp_get=0;

if (isset($_POST["recommend"]))
	$recommend=($_POST["recommend"]=="on"?1:0);
else
	$recommend=0;

if (isset($_POST["read_user_level"]))
	$read_user_level=intval(trim($_POST["read_user_level"]));
else
	$read_user_level=0;

if (isset($_POST["write_user_level"]))
	$write_user_level=intval(trim($_POST["write_user_level"]));
else
	$write_user_level=0;

if (isset($_POST["enable"]))
	$enable=($_POST["enable"]=="on"?1:0);
else
	$enable=0;

mysql_query("update section_config set sname='$sname',title='$title',".
	"exp_get=$exp_get,recommend=$recommend,read_user_level=$read_user_level,".
	"write_user_level=$write_user_level,enable=$enable where SID=$sid")
	or die("update section_config error!");

mysql_close($db_conn);
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>版块设定</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p>
			设定完成<br>
			<a href="section_list.php?cid=<? echo $cid; ?>">返回</a>
		</p>
	</body>
</html>

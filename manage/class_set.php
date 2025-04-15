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

if (isset($_POST["cname"]))
	$cname=trim($_POST["cname"]);
else
	$cname="";
if (!preg_match("/^[A-Za-z0-9_]{1,20}$/",$cname))
{
	echo ("类别填写不正确！");
	exit();
}

if (isset($_POST["title"]))
	$title=trim($_POST["title"]);
else
	$title="";
$r_title = htmlspecialchars(split_line($title, "", 20, 1), ENT_QUOTES | ENT_HTML401, 'UTF-8');
if ($title != $r_title)
{
	echo ("类别名称格式不正确！");
	exit();
}
	
$title=mysqli_real_escape_string($db_conn, $title);

if (isset($_POST["enable"]))
	$enable=($_POST["enable"]=="on"?1:0);
else
	$enable=0;

mysql_query("update section_class set cname='$cname',title='$title',".
	"enable=$enable where CID=$cid")
	or die("update section_class error!");

mysql_close($db_conn);
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>类别设定</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p>
			设定完成<br>
			<a href="class_list.php">返回</a>
		</p>
	</body>
</html>

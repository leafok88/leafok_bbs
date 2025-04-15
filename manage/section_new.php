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

$cid=trim($_POST["cid"]);

$sname=trim($_POST["sname"]);
if (!preg_match("/^[A-Za-z0-9_]{1,20}$/",$sname))
{
	echo ("版块填写不正确！");
	exit();
}

$cid=trim($_POST["cid"]);

$title=trim($_POST["title"]);
$r_title = htmlspecialchars(split_line($title, "", 20, 1), ENT_QUOTES | ENT_HTML401, 'UTF-8');
if ($title != $r_title)
{
	echo ("版块名称格式不正确！");
	exit();
}

$title=mysqli_real_escape_string($db_conn, $title);

$rs=mysql_query("select CID from section_class where CID=$cid and enable")
	or die("Query section classification error!");
if ($row=mysql_fetch_array($rs))
	$cid=$row["CID"];
else
{
	echo ("类别不存在！");
	exit();
}
mysql_free_result($rs);

mysql_query("insert into section_config (CID,sname,title) values('$cid','$sname','$title')")
	or die("Add section error!");
$sid=mysql_insert_id();

mysql_close($db_conn);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>添加版块</title>
<link rel="stylesheet" href="css/default.css" type="text/css">
</head>
<body>
	<P style="color:brown">
		版块添加完成！
	</P>
	<p>
		<a href="section_list.php?cid=<? echo $cid; ?>">返回</a>
	</p>
</body>
</html>

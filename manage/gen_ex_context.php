<?
	require_once "../bbs/session_init.inc.php";

	force_login();
?>
<?
if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S) && !isset($_SERVER["argc"]))
{
	echo ("没有权限！");
	exit();
}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>精华生成</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
<?
echo("Index...");
$buffer = shell_exec($PHP_bin . " gen_ex_index.php");
if (!$buffer || file_put_contents("../gen_ex/index.html", $buffer) == false)
{
	echo ("Open output error #2!");
	exit();
}
echo("OK<br>\n");
?>
		<p><a href="gen_ex.php">返回</a></p>
	</body>
</html>

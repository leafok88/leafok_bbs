<?
if (isset($_SERVER["argc"]))
{
	if ($_SERVER["argc"] != 2)
	{
		echo ("Invalid usage");
		exit();
	}

	if (strrpos($_SERVER["argv"][0], "/") !== false)
	{
		chdir(substr($_SERVER["argv"][0], 0, strrpos($_SERVER["argv"][0], "/")));
	}

	$sid = intval($_SERVER["argv"][1]);
}
else
{
	require_once "../bbs/session_init.inc.php";

	force_login();

	if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		echo ("没有权限！");
		exit();
	} 
	
	if (!isset($_GET["sid"]))
	{
		echo ("Invalid SID!");
		exit();
	}

	$sid = intval($_GET["sid"]);
}

require_once "../lib/db_open.inc.php";
require_once "../lib/common.inc.php";

set_time_limit(3600);

if (!file_exists("../gen_ex/$sid/"))
{
	mkdir("../gen_ex/$sid/", 0755)
		or die("Create dir error!");
}

$buffer = shell_exec($PHP_bin . " ./gen_ex_list.php $sid");
if (!$buffer || file_put_contents("../gen_ex/$sid/index.html", $buffer) == false)
{
	echo ("Write ex_list error!");
	exit();
}

$buffer = shell_exec($PHP_bin . " ./gen_ex_section_index.php $sid");
if (!$buffer || file_put_contents("../gen_ex/$sid/s_index.html", $buffer) == false)
{
	echo ("Write ex_section_index error!");
	exit();
}

$sql = "UPDATE section_config SET ex_gen_tm = NOW(), ex_update = 0 WHERE SID = $sid";
$rs = mysqli_query($db_conn, $sql);
if ($rs == false)
{
	echo ("Update tm error: " . mysqli_error($db_conn));
	exit();
}

mysqli_close($db_conn);
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>精华生成</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p>[<? echo $sid; ?>]OK</p>
		<p><a href="gen_ex.php">返回</a></p>
	</body>
</html>

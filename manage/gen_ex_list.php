<?
if (!isset($_SERVER["argc"]))
{
	echo "Invalid usage";
	exit();
}

switch($_SERVER["argc"])
{
	case 2:
		$sid = intval($_SERVER["argv"][1]);
		$dir = "";
		break;
	case 3:
		$sid = intval($_SERVER["argv"][1]);
		$dir = trim($_SERVER["argv"][2]);
		break;
	default:
        echo "Invalid usage";
        exit();
}

if (strrpos($_SERVER["argv"][0], "/") !== false)
{
	chdir(substr($_SERVER["argv"][0], 0, strrpos($_SERVER["argv"][0], "/")));
}

require_once "../lib/db_open.inc.php";
require_once "../lib/common.inc.php";

$sql = "SELECT SID, title FROM section_config WHERE
		SID = $sid AND enable LIMIT 1";
$rs = mysqli_query($db_conn, $sql);
if ($rs == false)
{
	echo ("Query section error: " . mysqli_error($db_conn));
	exit();
}
if ($row = mysqli_fetch_array($rs))
{
	$sid = $row["SID"];
	$section_title = $row["title"];
}
else
{
	echo ("版块不存在！");
	exit();
}
mysqli_free_result($rs);

$dir_name = "";
if ($dir != "")
{
	$sql = "SELECT name FROM ex_dir WHERE dir = '" .
		mysqli_real_escape_string($db_conn, $dir) .
		"' AND SID = $sid AND enable LIMIT 1";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query dir error: " . mysqli_error($db_conn));
		exit();
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$dir_name = $row["name"];
	}
	else
	{
		echo ("目录不存在！");
		exit();
	}
	mysqli_free_result($rs);
}

$section_path = ($dir != "" ? str_repeat("../", substr_count($dir, "/")) : "");

if (!file_exists("../gen_ex/$sid/$dir"))
{
	mkdir("../gen_ex/$sid/$dir", 0755)
		or die("Create dir error!");
}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title><? echo $section_title; ?></title>
		<link rel="stylesheet" href="<? echo $section_path; ?>../list.css" type="text/css">
		</style>
	</head>
	<body>
		<a name="top"></a>
		<center>
			<table cols="3" border="0" cellpadding="5" cellspacing="0" width="1050">
				<tr>
					<td colspan="3" align="center" style="font-size: 18px;">
						---====== ※<? echo $section_title; ?>※ [<? if ($dir == ""){ echo "更新时间：" . date("Y年m月d日"); } else { echo $dir_name; }?>] ======---
					</td>
				</tr>
				<tr height="10">
					<td colspan="3" align="center">
						<hr>
					</td>
				</tr>
				<tr>
					<td width="15%" align="right">
						编号
					</td>
					<td width="60%" align="center">
						标题
					</td>
					<td width="25%" align="center">
						发表时间
					</td>
				</tr>
<?
if ($dir == "")
{
?>
				<tr>
					<td align="right">
						[索引]
					</td>
					<td>
						<a href="s_index.html">【本版精华区索引】</a>
					</td>
					<td align="center">
						[<? echo date("Y年m月d日"); ?>]
					</td>
				</tr>
<?
}

$sql = "SELECT dir, name, dt FROM ex_dir WHERE SID = $sid AND dir REGEXP '^" .
			mysqli_real_escape_string($db_conn, $dir) .
			"[^/]+/$' AND enable ORDER BY dir";
$rs = mysqli_query($db_conn, $sql);
if ($rs == false)
{
	echo ("Query dir_list error: " . mysqli_error($db_conn));
	exit();
}

while ($row = mysqli_fetch_array($rs))
{
    $buffer = shell_exec($PHP_bin . " ./gen_ex_list.php $sid " . escapeshellcmd($row["dir"]));
	if (!$buffer || file_put_contents("../gen_ex/$sid/" . $row["dir"] . "index.html", $buffer) == false)
	{
		echo ("Write ex_list error!");
		exit();
	}

	$slash_pos = strrpos($row["dir"], "/", -2);
	if ($slash_pos == false)
	{
		$slash_pos = -1;
	}
	$item_dir = substr($row["dir"], $slash_pos + 1);
?>
				<tr>
					<td align="right">
						[目录]
					</td>
					<td>
						<a href="<? echo $item_dir; ?>index.html"><? echo $row["name"]; ?></a>
					</td>
					<td align="center">
						[<? echo date("Y年m月d日",strtotime($row["dt"])); ?>]
					</td>
				</tr>
<?
}
mysqli_free_result($rs);

$sql = "SELECT bbs.AID, title, sub_dt, static FROM bbs
		LEFT JOIN ex_file ON bbs.AID = ex_file.AID
		LEFT JOIN ex_dir ON ex_file.FID = ex_dir.FID
		WHERE bbs.SID = $sid AND TID = 0 AND visible AND gen_ex
		AND IF(dir IS NULL, '', dir) = '" .
		mysqli_real_escape_string($db_conn, $dir) .
		"' ORDER BY bbs.AID";
$rs = mysqli_query($db_conn, $sql);
if ($rs == false)
{
	echo ("Query article error: " . mysqli_error($db_conn));
	exit();
}

$aid_list = "-1";

while ($row = mysqli_fetch_array($rs))
{
?>
				<tr>
					<td align="right">
						<? echo $row["AID"]; ?>
					</td>
					<td>
						<a href="<? echo $row["AID"].".html"; ?>"><? echo htmlspecialchars($row["title"], ENT_HTML401, 'UTF-8'); ?></a>
					</td>
					<td align="center">
						[<? echo date("Y年m月d日",strtotime($row["sub_dt"])); ?>]
					</td>
				</tr>
<?
	if ((!$row["static"]) || (!file_exists("../gen_ex/static/".$row["AID"].".html")))
	{
        $buffer = shell_exec($PHP_bin . " ../bbs/view_article.php " . $row["AID"]);
		if (!$buffer || file_put_contents("../gen_ex/static/" . $row["AID"] . ".html", $buffer) == false)
		{
			echo ("Open output error!");
			exit();
		}

		$aid_list .= (", " . $row["AID"]);
	}
	if (!copy("../gen_ex/static/" . $row["AID"] . ".html", "../gen_ex/$sid/$dir" . $row["AID"] . ".html"))
	{
		echo ("Copy file error!");
		exit();
	}
}
?>
				<tr height="10">
					<td colspan="3" align="center">
						<hr>
					</td>
				</tr>
				<tr>
					<td align="right">
						<a href="../index.html">上级目录</a>
					</td>
					<td>
					</td>
					<td>
					</td>
				</tr>
				<tr height="10">
					<td colspan="3">
					</td>
				</tr>
				<tr>
					<td colspan="3" align="center">
						Copyright &copy; <? echo $BBS_copyright_duration; ?> <? echo $BBS_name . "(" . $BBS_host_name . ")"; ?><br /> 
						All Rights Reserved
					</td>
				</tr>
			</table>
		</center>
	</body>
</html>
<?
mysqli_free_result($rs);

$sql = "UPDATE bbs SET static = 1 WHERE AID IN ($aid_list)";
$rs = mysqli_query($db_conn, $sql);
if ($rs == false)
{
	echo ("Update status error: " . mysqli_error($db_conn));
	exit();
}

mysqli_close($db_conn);
?>

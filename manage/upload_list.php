<?
	require_once "../lib/db_open.inc.php";
	require_once "../bbs/session_init.inc.php";
?>
<?
force_login();

if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
{
	echo ("没有权限！");
	exit();
}

$rs=mysql_query("select upload_file.*,username from upload_file left join".
	" user_list on upload_file.UID=user_list.UID where upload_file.check=0".
	" and upload_file.deleted=0")
	or die("Query data error!");
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>BBS附件审核</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p align="center" style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">
			BBS附件审核
		</p>
		<center>
			<table cols=4 border="1" width="95%">
				<tr style="font-weight:bold;" height=20>
					<td width="40%" align="center">
						用户
					</td>
					<td width="40%" align="middle">
						附件
					</td>
					<td width="10%" align="center">
						处理
					</td>
				</tr>
<? 
while($row=mysql_fetch_array($rs))
{
?>
				<tr height=20>
					<td align="middle">
						<? echo $row["username"]; ?>
					</td>
					<td align="middle">
						<a href="/bbs/dl_file.php?aid=<? echo $row["AID"]; ?>&force=1" target=_blank><? echo $row["filename"]; ?></a> (<? echo $row["size"]; ?>)
					</td>
					<td align="middle">
						<a href="upload_process.php?enable=yes&p_id=<? echo $row["AID"]; ?>">通过</a><br>
						<a href="upload_process.php?enable=no&p_id=<? echo $row["AID"]; ?>" onclick="return window.confirm('真的要删除吗？');">删除</a>
					</td>
				</tr>
<? 
} 
mysql_free_result($rs);

mysql_close($db_conn);
?>
			</table>
		</center>
	</body>
</html>

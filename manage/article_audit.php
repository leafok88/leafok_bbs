<?
	require_once "../lib/db_open.inc.php";
	require_once "../bbs/session_init.inc.php";
?>
<?
if (isset($_POST["page"]))
	$page=intval($_POST["page"]);
else
	$page=1;

$line=20;

force_login();

if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
{
	echo ("没有权限！");
	exit();
} 

$rs=mysql_query("select bbs_article_op.MID,bbs.AID,bbs.TID,".
	"bbs.username,bbs_article_op.type,bbs_article_op.op_dt,".
	"bbs_article_op.op_ip from (bbs_article_op left join bbs on ".
	"bbs_article_op.AID=bbs.AID) where bbs_article_op.type in ".
	"('A','M','X') order by bbs_article_op.MID desc limit ".($page-1)*$line.
	",".$line)
	or die("Query data error!");
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>文章审核</title>
		<link rel="stylesheet" href="css/default.css" type="text/css">
	</head>
	<body>
		<p align="center" style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">
			文章审核
		</p>
		<form action="article_audit.php" method="post" name="scope" id="scope">
			请输入页号：<input name="page" id="page" value="<? echo $page; ?>" size="3">
			<input type="submit" name="submit" value="显示">
			<input type="submit" name="decrease" value="<" onclick="(scope.page.value)--;">
			<input type="submit" name="increase" value=">" onclick="(scope.page.value)++;">
		</form>
		<center>
			<table cols=4 border="1" width="95%">
				<tr style="font-weight:bold;" height=20>
					<td width="10%" align="middle">
						操作编号
					</td>
					<td width="25%" align="middle">
						时间/地址
					</td>
					<td width="25%" align="center">
						用户
					</td>
					<td width="25%" align="middle">
						主题/文章编号
					</td>
					<td width="10%" align="center">
						用户操作
					</td>
				</tr>
<? 
while($row=mysql_fetch_array($rs))
{
?>
				<tr height=20>
					<td align="middle">
						<? echo $row["MID"]; ?>
					</td>
					<td align="middle">
						<? echo $row["op_dt"]."<br>".$row["op_ip"]; ?>
					</td>
					<td align="middle">
						<? echo $row["username"]; ?>
					</td>
					<td align="middle">
						<a href="/bbs/view_article.php?trash=1&id=<? echo $row["AID"]; ?>#<? echo $row["AID"]; ?>" target=_blank><? echo $row["TID"]."/".$row["AID"]; ?></a>
					</td>
					<td align="middle">
<?
	switch($row["type"])
	{
		case "A":
			echo ("发表");
			break;
		case "M":
			echo ("修改");
			break;
		case "X":
			echo ("版主删除");
			break;
		default:
			echo ("未知");
			break;
	}
?>
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

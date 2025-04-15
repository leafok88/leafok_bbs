<?
	$pagen = 20;
	$total_face = 318;

	$first = (isset($_GET["first"]) ? intval($_GET["first"]) : 1);

	if ($first < 1 || $first > $total_face)
	{
		$first = 1;
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>头像代号列表</title>
<link rel="stylesheet" href="css/default.css" type="text/css">
<script>
function setTextOfTextfield(objName, newText)
{
	opener.document.getElementById(objName).value=newText;
	return false;
}
</script>
<body>
<center>
<table>
	<tr><td colspan="4" align="center"><font color=red>单击头像选择</font></td></tr>
	<tr><td colspan="4"><hr size="1"></td></tr>
	<tr>
		<td colspan="4" align="center">
<? 
if ($first > $pagen)
{
?>
			<a class="s7" href="facelist.php?first=1">&lt;&lt;首页</a>&nbsp;
			<a class="s7" href="facelist.php?first=<? echo ($first - $pagen); ?>">上一页</a>&nbsp;
<? 
}
else
{
?>
			<font color="999999">&lt;&lt;首页&nbsp;上一页&nbsp;</font>
<? 
} 

if ($first + $pagen < $total_face)
{
?>
			<a class="s7" href="facelist.php?first=<? echo ($first + $pagen); ?>">下一页</a>&nbsp;
			<a class="s7" href="facelist.php?first=<? echo ($total_face - $pagen + 1); ?>">尾页&gt;&gt;</a>&nbsp;
<? 
}
else
{
?>
			<font color="999999">下一页&nbsp;尾页&gt;&gt;</font>
<? 
} 
?>
		</td>
	</tr>
	<tr>
		<td width="40" align="center">代号</td><td width="40" align="center">图片</td><td width="40" align="center">代号</td><td align="center" width="40">图片</td>
	</tr>
<? 
for ($n = $first; $n < $first + $pagen && $n <= $total_face; )
{
?>
	<tr align="center">
<? 
	for ($i = 0; $i < 2 && $n <= $total_face; $i++)
	{
		$face_id = str_repeat("0", 3 - strlen($n)) . $n;
?>
		<td><? echo $face_id; ?></td><td><a class="s7" href="#" onclick="return setTextOfTextfield('photo', '<? echo $face_id; ?>');"><img src="images/face/<? echo $face_id; ?>.gif" border="0"></td>
<?
		$n++;
	}
?>
	</tr>
<? 
} 
?>
	<tr>
		<td colspan="4" align="center">
			<hr size="1">
		</td>
	</tr>
	<tr>
		<td colspan="4" align="center">
			<a class="s7" href="javascript:window.close();">关闭</a>
		</td>
	</tr>
</table>
</center>
</body>
</html>

<?
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>账户积分</title>
<link rel="stylesheet" href="<? echo get_theme_file('css/default'); ?>" type="text/css">
</head>
<body>
<?
	include get_theme_file("view/member_service_guide");
?>
<center>
	<p style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">
		积分余额
	</p>
	<table cols="3" border="1" cellpadding="8" cellspacing="0" width="1050" bgcolor="#ffdead">
		<tr>
			<td colspan="3">
				目前剩余积分：<span style="color:red;font-style:italic;"><? echo $result_set["data"]["score"]; ?></span>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<p style="FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: red; FONT-FAMILY: 楷体">
				积分变动明细（最近3年）
				</p>
			</td>
		</tr>
		<tr style="font-weight:bold;">
			<td width="30%" align="middle">
				时间
			</td>
			<td width="10%" align="middle">
				数量
			</td>
			<td width="60%" align="center">
				原因
			</td>
		</tr>
<?
	foreach ($result_set["data"]["transactions"] as $transaction)
	{
?>
		<tr>
			<td align="middle">
				<? echo $transaction["dt"]->format("Y-m-d H:i:s"); ?>
			</td>
			<td align="middle" style="color: <? echo ($transaction["amount"] < 0 ? "red" : "green"); ?>;">
				<? echo $transaction["amount"]; ?>
			</td>
			<td>
				<? echo $transaction["reason"]; ?>
			</td>
		</tr>
<? 
	}
?>
	</table>
</center>
</body>
</html>

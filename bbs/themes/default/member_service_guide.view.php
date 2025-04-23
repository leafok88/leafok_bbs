	<center>
		<table cols="6" border="1" cellpadding="3" cellspacing="0" width="1050" bgcolor="#ffdead" id="Guide">
			<tr>
				<td width="16%" align="middle">
					<a class="s7" href="update_profile.php">个人资料</a>
				</td>
				<td width="16%" align="middle">
					<a class="s7" href="update_pref.php">个人设定</a>
				</td>
				<td width="16%" align="middle">
					<a class="s7" href="s_favor.php">版块收藏</a>
				</td>
				<td width="16%" align="middle">
					<a class="s7" href="score_detail.php">账户积分</a>
				</td>
				<td width="16%" align="middle">
<?php
	if ($_SESSION["BBS_priv"]->checkpriv(0, S_POST) &&
		!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S | P_MAN_M | P_MAN_S))
	{
?>
					<a class="s7" href="suicide.php">关闭账户</a>
<?php
	}
	else
	{
?>
					<a class="s7" href="#">关闭账户</a>
<?php
	}
?>
				</td>
			</tr>
		</table>
	</center>

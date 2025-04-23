<?php
require_once "../lib/common.inc.php";
require_once "../bbs/user_priv.inc.php";

function load_user_info($uid, $db_conn)
{
	global $BBS_license_dt;
	global $BBS_life_immortal;
	global $BBS_timezone;

	$sql = "SELECT life, upload_limit, last_login_dt, user_timezone FROM user_pubinfo WHERE UID = $uid";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Read data error: " . mysqli_error($db_conn));
		exit();
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$life = $row["life"];
		$last_login_dt = $row["last_login_dt"];
		$_SESSION["BBS_user_tz"] = new DateTimeZone($row["user_timezone"] != "" ? $row["user_timezone"] : $BBS_timezone);

		if (!in_array($life, $BBS_life_immortal) &&
			(new DateTimeImmutable("-" . $life . " day")) > (new DateTimeImmutable($last_login_dt)))
		{
			return (-3); //Dead
		}
	}
	else
	{
		return (-1); //Data not found
	}

	mysqli_free_result($rs);

	$_SESSION["BBS_priv"]->loadpriv($uid, $db_conn);

	if ((new DateTimeImmutable($last_login_dt)) < (new DateTimeImmutable($BBS_license_dt)))
	{
		return (-2); //require update agreement first
	} 

	return 0;
}
?>

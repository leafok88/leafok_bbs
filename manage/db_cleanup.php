<?php
	if (isset($_SERVER["argv"]))
	{
		chdir(dirname($_SERVER["argv"][0]));
	}

	require_once "../lib/common.inc.php";
	require_once "../lib/db_open.inc.php";

	if (!isset($_SERVER["argc"]))
	{
		require_once "../bbs/session_init.inc.php";

		force_login();

		if (!$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
		{
			echo ("没有权限！");
			exit();
		}
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>数据清理</title>
<link rel="stylesheet" href="css/default.css" type="text/css">
</head>
<body>
<?php
	$sql = "DELETE FROM email WHERE complete AND set_dt < SUBDATE(NOW(), INTERVAL 180 DAY)";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Delete mail error: " . mysqli_error($db_conn));
		exit();
	}

	$sql = "UPDATE email SET complete = 0, error = 0
			WHERE error = 1 AND set_dt > SUBDATE(NOW(), INTERVAL 7 DAY)";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Update email status error: " . mysqli_error($db_conn));
		exit();
	}

	$sql = "DELETE FROM visit_log WHERE dt < SUBDATE(NOW(), INTERVAL $BBS_normal_log_retention DAY)";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Delete visit_log error: " . mysqli_error($db_conn));
		exit();
	}

	$sql = "DELETE FROM user_online WHERE last_tm < SUBDATE(NOW(), INTERVAL $BBS_session_lifetime SECOND)";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Delete online user error: " . mysqli_error($db_conn));
		exit();
	}

	$sql = "DELETE FROM user_login_log WHERE login_dt < SUBDATE(NOW(), INTERVAL $BBS_critical_log_retention DAY)";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Delete user_login_log error: " . mysqli_error($db_conn));
		exit();
	}

	$sql = "DELETE FROM user_err_login_log WHERE login_dt < SUBDATE(NOW(), INTERVAL $BBS_normal_log_retention DAY)";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Delete user_err_login_log error: " . mysqli_error($db_conn));
		exit();
	}

	$sql = "DELETE FROM bbs_article_op WHERE op_dt < SUBDATE(NOW(), INTERVAL $BBS_critical_log_retention DAY)";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Delete bbs_article_op error: " . mysqli_error($db_conn));
		exit();
	}

	$sql = "DELETE FROM bbs_msg WHERE send_dt < SUBDATE(NOW(), INTERVAL $BBS_user_msg_retention DAY) AND (NOT new)";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Delete bbs_msg error: " . mysqli_error($db_conn));
		exit();
	}

	$sql = "DELETE FROM view_article_log WHERE dt < SUBDATE(NOW(), INTERVAL $BBS_new_article_period DAY)";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Delete view_article_log error: " . mysqli_error($db_conn));
		exit();
	}

	//Cleanup out-of-date non-excerptional topic
	$sql = "SELECT SID, topic_retention FROM section_config
			WHERE enable AND topic_retention > 0";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query section info error: " . mysqli_error($db_conn));
		exit();
	}

	while ($row = mysqli_fetch_array($rs))
	{
		$sql = "SELECT AID FROM bbs WHERE TID = 0 AND SID = " . $row["SID"] .
				" AND visible = 1 AND excerption = 0 AND sub_dt < SUBDATE(NOW(), INTERVAL " .
				$row["topic_retention"] . " DAY)";

		$rs_topic = mysqli_query($db_conn, $sql);
		if ($rs_topic == false)
		{
			echo ("Query expired topic error: " . mysqli_error($db_conn));
			exit();
		}

		$aid_list = "-1";
		while ($row_topic = mysqli_fetch_array($rs_topic))
		{
			$aid_list .= (", " . $row_topic["AID"]);
		}
		mysqli_free_result($rs_topic);

		if ($aid_list != "-1")
		{
			// Reserve excerption reply as topic
			$sql = "UPDATE bbs SET TID = 0 WHERE TID IN ($aid_list) AND excerption = 1";
			$ret = mysqli_query($db_conn, $sql);
			if ($ret == false)
			{
				echo ("Update reply error: " . mysqli_error($db_conn));
				exit();
			}

			echo ("Convert " . mysqli_affected_rows($db_conn) . " replies to topics in section [" .
					$row["SID"] . "]<br />\n");

			$sql = "UPDATE bbs SET visible = 0, reply_count = 0 WHERE AID IN ($aid_list) OR TID IN ($aid_list)";
			$ret = mysqli_query($db_conn, $sql);
			if ($ret == false)
			{
				echo ("Update expired article error: " . mysqli_error($db_conn));
				exit();
			}

			echo ("Set " . mysqli_affected_rows($db_conn) . " articles in section [" .
				$row["SID"] . "] as invisible<br />\n");
		}
	}
	mysqli_free_result($rs);

	//Purge out-of-date deleted article
	$sql = "SELECT bbs.AID FROM bbs LEFT JOIN bbs_article_op
			ON bbs.AID = bbs_article_op.AID
			WHERE visible = 0 AND bbs_article_op.AID IS NULL
			AND sub_dt < SUBDATE(NOW(), INTERVAL $BBS_article_purge_duration DAY)";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query article to be purged error: " . mysqli_error($db_conn));
		exit();
	}

	$aid_list = "-1";
	while ($row = mysqli_fetch_array($rs))
	{
		$aid_list .= (", " . $row["AID"]);
		$aid_count++;
	}
	mysqli_free_result($rs);

	if ($aid_list != "-1")
	{
		$sql = "DELETE FROM bbs_content WHERE AID IN ($aid_list)";
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			echo ("Delete content error: " . mysqli_error($db_conn));
			exit();
		}

		$sql = "DELETE FROM bbs WHERE AID IN ($aid_list)";
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			echo ("Delete article error: " . mysqli_error($db_conn));
			exit();
		}

		echo ("Purged " . mysqli_affected_rows($db_conn) . " articles<br />\n");
	}

	//Delete expired upload file
	$sql = "SELECT upload_file.AID FROM upload_file
			INNER JOIN bbs ON upload_file.ref_AID = bbs.AID
			WHERE upload_file.deleted = 0 AND upload_file.deny = 0 AND bbs.visible = 1";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Query upload_file error" . mysqli_error($db_conn));
		exit();
	}

	$upload_reserve_list="-1";
	$file_reserved = array();
	while ($row = mysqli_fetch_array($rs))
	{
		$upload_reserve_list .= (", " . $row["AID"]);
		array_push($file_reserved, $row["AID"]);
	}
	mysqli_free_result($rs);

	$sql = "UPDATE upload_file SET deleted = 1 WHERE AID NOT IN ($upload_reserve_list)";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Update upload_file error" . mysqli_error($db_conn));
		exit();
	}

	if (file_exists("../bbs/upload"))
	{
		$handle = opendir("../bbs/upload");
		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != ".." && !in_array($file, $file_reserved))
			{
				unlink("../bbs/upload/$file");
			}
		}
		closedir($handle);
	}

	//Delete expired upload photo
	$sql = "SELECT UID, photo_ext FROM user_pubinfo
			WHERE photo = 999 AND photo_enable = 1";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Query user photo error" . mysqli_error($db_conn));
		exit();
	}

	$file_reserved = array();
	while ($row = mysqli_fetch_array($rs))
	{
		array_push($file_reserved, "face_" . $row["UID"] . "." . $row["photo_ext"]);
	}
	mysqli_free_result($rs);

	if (file_exists("../bbs/images/face/upload_photo"))
	{
		$handle = opendir("../bbs/images/face/upload_photo");
		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != ".." && !in_array($file, $file_reserved))
			{
				unlink("../bbs/images/face/upload_photo/$file");
			}
		}
		closedir($handle);
	}

	//Purge dead ID
	$life_list = "-1";
	foreach ($BBS_life_immortal as $life)
	{
		$life_list .= (", " . $life);
	}

	$sql = "SELECT UID FROM user_pubinfo WHERE life NOT IN ($life_list)
			AND last_login_dt < SUBDATE(NOW(), INTERVAL (life + $BBS_user_purge_duration) DAY)
			ORDER BY UID";
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Query user_pubinfo error" . mysqli_error($db_conn));
		exit();
	}

	$uid_list = "-1";
	while ($row = mysqli_fetch_array($rs))
	{
		$uid_list .= (", " . $row["UID"]);
	}
	mysqli_free_result($rs);

	if ($uid_list != "-1")
	{
		echo ("Purge UID in list ($uid_list)<br />\n");

		$user_db = array(
			"bbs_msg" => "toUID",
			"section_favorite" => "UID",
			"user_pubinfo" => "UID",
			"user_list" => "UID",
			"user_nickname" => "UID",
			"user_modify_log" => "UID",
			"user_reginfo" => "UID",
			"user_score" => "UID",
			"user_score_log" => "UID",
			"user_modify_email_verify" => "UID",
		);

		foreach($user_db as $table => $column)
		{
			$sql = "DELETE from $table WHERE $column IN ($uid_list)";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				echo("Delete $table error: " . mysqli_error($db_conn));
				exit();
			}
		}
	}

	mysqli_close($db_conn);
?>
<P>数据库整理完毕！</P>
</body>
</html>

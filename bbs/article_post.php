<?php
	require_once "../lib/common.inc.php";
	require_once "../lib/db_open.inc.php";
	require_once "./session_init.inc.php";
	require_once "./check_sub.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./theme.inc.php";

	force_login();

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	$id = (isset($_GET["id"]) ? intval($_GET["id"]) : 0);
	$reply_id = (isset($_GET["reply_id"]) ? intval($_GET["reply_id"]) : 0);
	$sid = (isset($_GET["sid"]) ? intval($_GET["sid"]) : $BBS_default_sid);
	$quote = (isset($_GET["quote"]) && $_GET["quote"] == "0" ? false : true);

	$uid = 0;
	$tid = 0;
	$title = "";
	$r_username = "";
	$r_nickname = "";
	$content = "";
	$emoji = 1;
	$reply_note = ($reply_id == 0 ? 1 : 0);
	$excerption = 0;
	$attachments = array();

	if($id == 0) // Post article
	{
		if ($reply_id == 0) // Post new thread
		{
			$sql = "SELECT title FROM section_config WHERE SID = $sid AND enable";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Query section error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			if ($row = mysqli_fetch_array($rs))
			{
				$section_title = $row["title"];
			}
			else
			{
				$result_set["return"]["code"] = -1;
				$result_set["return"]["message"] = "版块不存在！";

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
			mysqli_free_result($rs);

			if (!$_SESSION["BBS_priv"]->checkpriv($sid, S_POST))
			{
				$result_set["return"]["code"] = -1;
				$result_set["return"]["message"] = "您无权发表文章！";

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
		else // Reply article
		{
			$sql = "SELECT TID, bbs.SID, bbs.title, `lock`, username, nickname, content,
					section_config.title AS s_title FROM bbs
					INNER JOIN bbs_content ON bbs.CID = bbs_content.CID
					INNER JOIN section_config ON bbs.SID = section_config.SID
					WHERE bbs.AID = $reply_id AND visible";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Query article error: " . mysqli_error($db_conn);

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			if ($row = mysqli_fetch_array($rs))
			{
				$tid = $row["TID"];
				$sid = $row["SID"];
				$title = $row["title"];
				$lock = $row["lock"];
				$r_username = $row["username"];
				$r_nickname = $row["nickname"];
				$content = $row["content"];
				$section_title = $row["s_title"];
			}
			else
			{
				$result_set["return"]["code"] = -1;
				$result_set["return"]["message"] = "回复的文章不存在！";

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
			mysqli_free_result($rs);

			if ($tid != 0) // Article to be replied is not the head of topic thread
			{
				$sql = "SELECT SID, `lock` FROM bbs WHERE AID = $tid AND visible";

				$rs = mysqli_query($db_conn, $sql);
				if ($rs == false)
				{
					$result_set["return"]["code"] = -2;
					$result_set["return"]["message"] = "Query article error: " . mysqli_error($db_conn);

					mysqli_close($db_conn);
					exit(json_encode($result_set));
				}

				if ($row = mysqli_fetch_array($rs))
				{
					$sid = $row["SID"]; // In case of inconsistent SID data
					$lock = $row["lock"];
				}
				else
				{
					$result_set["return"]["code"] = -1;
					$result_set["return"]["message"] = "回复的主题不存在！";

					mysqli_close($db_conn);
					exit(json_encode($result_set));
				}
				mysqli_free_result($rs);
			}

			if (!$_SESSION["BBS_priv"]->checkpriv($sid, S_POST))
			{
				$result_set["return"]["code"] = -1;
				$result_set["return"]["message"] = "您无权发表文章！";

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			if ($lock)
			{
				$result_set["return"]["code"] = -1;
				$result_set["return"]["message"] = "该主题谢绝回复！";

				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
	}
	else // Modify article
	{
		$sql = "select UID, bbs.SID, TID, bbs.title, content, icon, reply_note, excerption,
				section_config.title AS s_title FROM bbs
				INNER JOIN bbs_content ON bbs.CID = bbs_content.CID
				INNER JOIN section_config ON bbs.SID = section_config.SID
				WHERE bbs.AID = $id AND visible";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query article error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($row = mysqli_fetch_array($rs))
		{
			$uid = $row["UID"];
			$sid = $row["SID"];
			$tid = $row["TID"];
			$title = $row["title"];
			$content = $row["content"];
			$emoji = $row["icon"];
			$reply_note = $row["reply_note"];
			$excerption = $row["excerption"];
			$section_title = $row["s_title"];
		}
		else
		{
			$result_set["return"]["code"] = -1;
			$result_set["return"]["message"] = "修改的文章不存在！";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
		mysqli_free_result($rs);

		if (!($_SESSION["BBS_priv"]->checkpriv($sid, S_POST) && $_SESSION["BBS_uid"] == $uid && (!$excerption)))
		{
			$result_set["return"]["code"] = -1;
			$result_set["return"]["message"] = "您无权修改此文章！";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$sql = "SELECT * FROM upload_file WHERE ref_AID = $id
				AND deleted = 0 AND deny = 0
				ORDER BY AID";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Read attachment error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		while ($row = mysqli_fetch_array($rs))
		{
			$attachments[$row["AID"]] = array(
				"filename" => $row["filename"],
				"size" => $row["size"],
				"check" => $row["check"],
			);
		}
		mysqli_free_result($rs);
	}

	mysqli_close($db_conn);

	// Fill up result data
	$result_set["data"] = array(
		"id" => $id,
		"reply_id" => $reply_id,
		"uid" => $uid,
		"sid" => $sid,
		"tid" => $tid,
		"title" => $title,
		"r_username" => $r_username,
		"r_nickname" => $r_nickname,
		"content" => $content,
		"quote" => $quote,
		"emoji" => $emoji,
		"reply_note" => $reply_note,
		"excerption" => $excerption,
		"section_title" => $section_title,
		"attachments" => $attachments,
	);

	// Cleanup
	unset($id);
	unset($reply_id);
	unset($uid);
	unset($sid);
	unset($tid);
	unset($title);
	unset($r_username);
	unset($r_nickname);
	unset($content);
	unset($emoji);
	unset($reply_note);
	unset($excerption);
	unset($section_title);
	unset($attachments);

	// Output with theme view
	$theme_view_file = get_theme_file("view/post", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;

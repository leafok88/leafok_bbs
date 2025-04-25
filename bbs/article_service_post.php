<?php
	require_once "../lib/common.inc.php";
	require_once "../lib/db_open.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./article_op.inc.php";
	require_once "./session_init.inc.php";
	require_once "./check_sub.inc.php";

	force_login();

	$id = (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
	$reply_id = (isset($_POST["reply_id"]) ? intval($_POST["reply_id"]) : 0);
	$sid = (isset($_POST["sid"]) ? intval($_POST["sid"]) : $BBS_default_sid);
	$title = (isset($_POST["title"]) ? trim($_POST["title"]) : "");
	$transship = (isset($_POST["transship"]) && $_POST["transship"] == "1" ? 1 : 0);
	$content = (isset($_POST["content"]) ? $_POST["content"] : "");
	$emoji = (isset($_POST["emoji"]) ? intval($_POST["emoji"]) : 1);
	$reply_note = (isset($_POST["reply_note"]) && $_POST["reply_note"] == "1" ? 1 : 0);
	$sign_id = (isset($_POST["sign_id"]) ? intval($_POST["sign_id"]) : 0);

	$result_set = array(
		"return" => array(
			"code" => 0,
			"tid" => 0,
			"aid" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	// Validate input
	if ($title == "")
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "title",
			"errMsg" => "不能为空",
		));
	}

	$r_title = split_line($title, "", 80, 1);
	if ($title != $r_title)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "title",
			"errMsg" => "超长已截断",
			"updateValue" => $r_title,
		));
	}

	$r_title = check_badwords($title, "****");
	if ($title != $r_title)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "title",
			"errMsg" => "已过滤",
			"updateValue" => $r_title,
		));
	}

	$r_content = check_badwords($content, "****");
	if ($content != $r_content)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "content",
			"errMsg" => "已过滤",
			"updateValue" => $r_content,
		));
	}

	if ($emoji <= 0 || $emoji > $BBS_emoji_count)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "emoji",
			"errMsg" => "选择有误",
		));
	}

	if ($sign_id < 0 || $sign_id > 3)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "sign",
			"errMsg" => "选择有误",
		));
	}

	if ($result_set["return"]["code"] != 0)
	{
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Append sign for new post
	if ($id == 0 && $sign_id > 0)
	{
		$sql = "SELECT sign_" . $sign_id . " AS sign FROM user_pubinfo WHERE UID = " . $_SESSION["BBS_uid"];
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query sign error: " . mysqli_error($db_conn);
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if($row = mysqli_fetch_array($rs))
		{
			$content .= ("\n\n--\n" . split_line($row["sign"], "", 80, 10) . "\n");
		}
		mysqli_free_result($rs);
	}

	// Append indication of article update
	if ($id != 0)
	{
		$content .= ("\n--\n※作者已于 " . date("Y-m-d H:i:s") . " 修改本文※\n");
	}

	// Calculate length of content
	$length = str_length(LML($content, false, false, 1024));

	// Initial variables
	$tid = 0;
	$nickname = "";
	$exp = 0;

	// Begin transaction
	$rs = mysqli_query($db_conn, "SET autocommit=0");
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Mysqli error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	
	$rs = mysqli_query($db_conn, "BEGIN");
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Mysqli error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if($id == 0) // Post article
	{
		if (time() - $_SESSION["BBS_last_sub_tm"] < 5)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "prompt",
				"errMsg" => "发帖过于频繁，请稍等",
			));

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	
		if ($reply_id == 0) // Post new thread
		{
			$sql = "SELECT SID FROM section_config WHERE SID = $sid AND enable";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Query section error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		
			if (mysqli_num_rows($rs) == 0)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "prompt",
					"errMsg" => "版块不存在！",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
			mysqli_free_result($rs);

			if (!$_SESSION["BBS_priv"]->checkpriv($sid, S_POST))
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "prompt",
					"errMsg" => "您无权发表文章！",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			if (check_post_count(5, $sid, true, $db_conn) != true)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "prompt",
					"errMsg" => "本版连续发表主题数量达到上限",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
		else // Reply article
		{
			$sql = "SELECT TID, SID, title, `lock` FROM bbs WHERE AID = $reply_id AND visible FOR UPDATE";
			
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
				$r_title = $row["title"];
				$lock = $row["lock"];
			}
			else
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "prompt",
					"errMsg" => "回复的文章不存在！",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			} 
			mysqli_free_result($rs);

			if ($tid != 0) // Article to be replied is not the head of topic thread
			{
				$sql = "SELECT SID, title, `lock` FROM bbs WHERE AID = $tid AND visible FOR UPDATE";

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
					$r_title = $row["title"];
					$lock = $row["lock"];
				}
				else
				{
					$result_set["return"]["code"] = -1;
					array_push($result_set["return"]["errorFields"], array(
						"id" => "prompt",
						"errMsg" => "回复的主题不存在！",
					));
			
					mysqli_close($db_conn);
					exit(json_encode($result_set));
				}
				mysqli_free_result($rs);				
			}
			else
			{
				$tid = $reply_id; // Set tid to the head of the replied thread
			}

			if (!$_SESSION["BBS_priv"]->checkpriv($sid, S_POST))
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "prompt",
					"errMsg" => "您无权发表文章！",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			if ($lock)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "prompt",
					"errMsg" => "该主题谢绝回复！",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			if (check_post_count(10, $sid, false, $db_conn) != true)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "prompt",
					"errMsg" => "本版连续发表文章数量达到上限",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}

		$sql = "SELECT nickname, exp FROM user_pubinfo WHERE UID = " . $_SESSION["BBS_uid"];
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query user pubinfo error: " . mysqli_error($db_conn);
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	
		if($row = mysqli_fetch_array($rs))
		{
			$nickname = $row["nickname"];
			$exp = $row["exp"];
		}
		mysqli_free_result($rs);
	}
	else // Modify article
	{
		$sql = "SELECT TID, UID, SID, excerption FROM bbs WHERE AID = $id AND visible FOR UPDATE";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query article error: " . mysqli_error($db_conn);
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	
		if($row = mysqli_fetch_array($rs))
		{
			$tid = ($row["TID"] > 0 ? $row["TID"] : $id);
			$uid = $row["UID"];
			$sid = $row["SID"];
			$excerption = $row["excerption"];
		}
		else
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "prompt",
				"errMsg" => "修改的文章不存在！",
			));
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
		mysqli_free_result($rs);
		
		if (!($_SESSION["BBS_priv"]->checkpriv($sid, S_POST) && $_SESSION["BBS_uid"] == $uid && (!$excerption)))
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "prompt",
				"errMsg" => "您无权修改此文章！",
			));
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		} 
	}

	// Get upload quota and used space
	$atta_id_list = "-1";
	$attachment_count = (isset($_FILES['attachment']['error']) ? count($_FILES['attachment']['error']) : 0);
	if ($attachment_count > $BBS_upload_count_limit)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "attachment",
			"errMsg" => "文件数量超过限制",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$upload_limit = 0;
	$upload_used = 0;
	$upload_size = 0;

	if ($attachment_count > 0)
	{
		$sql = "SELECT upload_limit FROM user_pubinfo WHERE UID = " . $_SESSION["BBS_uid"];
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query upload limit error: " . mysqli_error($db_conn);
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	
		if ($row = mysqli_fetch_array($rs))
		{
			$upload_limit = $row["upload_limit"];
		}
		mysqli_free_result($rs);
	
		$sql = "SELECT COUNT(size) AS upload_used FROM upload_file WHERE UID = " . $_SESSION["BBS_uid"] .
				" AND deleted = 0";
	
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query upload file error: " . mysqli_error($db_conn);
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	
		if ($row = mysqli_fetch_array($rs))
		{
			$upload_used = $row["upload_used"];
		}
		mysqli_free_result($rs);
	}

	// Validate attachments
	for ($i = 0; $i < $attachment_count; $i++)
	{
		if (!isset($_FILES['attachment']['error'][$i]) || $_FILES['attachment']['error'][$i] != UPLOAD_ERR_OK)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "attachment",
				"errMsg" => "上传文件错误",
			));
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$filesize = $_FILES['attachment']['size'][$i];
		$filename = $_FILES['attachment']['name'][$i];

		if ($filesize > 0)
		{
			if ($filesize > 1024 * 1024 * 2)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "attachment",
					"errMsg" => "文件大小超过限制",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			$upload_used += $filesize;
			if ($upload_used > $upload_limit)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "attachment",
					"errMsg" => "用户上传空间配额不足",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			$ext = strtolower(substr($filename, (strrpos($filename, ".") ? strrpos($filename, ".") + 1 : 0)));
			switch ($ext)
			{
				case "bmp":
				case "gif":
				case "jpg":
				case "jpeg":
				case "png":
				case "tif":
				case "tiff":
				case "txt":
				case "zip":
				case "rar":
					break;
				default:
					$result_set["return"]["code"] = -1;
					array_push($result_set["return"]["errorFields"], array(
						"id" => "attachment",
						"errMsg" => "不支持的文件扩展名",
					));
					
					mysqli_close($db_conn);
					exit(json_encode($result_set));
			}
	
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mime_type = $finfo->file($_FILES['attachment']['tmp_name'][$i]);
			$real_ext = array_search($mime_type, array(
					'txt' => 'text/plain',
					'bmp' => 'image/x-ms-bmp',
					'jpg' => 'image/jpeg',
					'png' => 'image/png',
					'gif' => 'image/gif',
					'tif' => 'image/tiff',
					'rar' => 'application/x-rar',
					'zip' => 'application/zip',
					), true);
			
			if ($real_ext === false)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "attachment",
					"errMsg" => "不支持的文件格式",
				));
				
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
	}

	// Store attachments
	for ($i = 0; $i < $attachment_count; $i++)
	{
		$filesize = $_FILES['attachment']['size'][$i];
		$filename = $_FILES['attachment']['name'][$i];

		$sql = "INSERT INTO upload_file(UID, size, filename, `check`) VALUES(" .
				$_SESSION["BBS_uid"] . ", $filesize, '$filename', 0)";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Add upload file error: " . mysqli_error($db_conn);
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	
		$attachment_id = mysqli_insert_id($db_conn);
		$atta_id_list .= ("," . $attachment_id);

		$file_path = "upload/" . $attachment_id;
		if(!move_uploaded_file($_FILES['attachment']['tmp_name'][$i], $file_path))
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Copy file error";
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	// Add content
	$sql = "INSERT INTO bbs_content(AID, content) values(0, '" .
			mysqli_real_escape_string($db_conn, $content) . "')";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add content error: " . mysqli_error($db_conn);
	
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	$cid = mysqli_insert_id($db_conn);

	if($id == 0) // Post article
	{
		$sql = "INSERT INTO bbs(SID, TID, UID, username, nickname, title, CID, transship,
				sub_dt, sub_ip, reply_note, exp, last_reply_dt, icon, length)
				VALUES($sid, $tid, " . $_SESSION["BBS_uid"] . ", '" .
				$_SESSION["BBS_username"] . "', '" .
				mysqli_real_escape_string($db_conn, $nickname) . "', '" .
				mysqli_real_escape_string($db_conn, $title) . "', " .
				"$cid, $transship, NOW(), '" . client_addr() .
				"', $reply_note, $exp, NOW(), $emoji, $length)";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Add article error: " . mysqli_error($db_conn);
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
		$aid = mysqli_insert_id($db_conn);

		//Set last reply info
		if ($reply_id > 0)
		{
			$sql = "UPDATE bbs SET reply_count = reply_count + 1,
					last_reply_dt = NOW(), last_reply_UID=" . $_SESSION["BBS_uid"] .
					", last_reply_username = '" . $_SESSION["BBS_username"] .
					"', last_reply_nickname = '" . mysqli_real_escape_string($db_conn, $nickname) .
					"' WHERE Aid = $tid";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Update replied article error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			//Notify the authors of the topic which is replyed.
			$sql = "SELECT DISTINCT UID FROM bbs WHERE (AID = $tid OR TID = $tid)
					AND visible AND reply_note AND UID <> " . $_SESSION["BBS_uid"];

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Read reply info error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			while ($row = mysqli_fetch_array($rs))
			{
				//Send notification message
				$msg_content = "[hide]SYS_Reply_Article[/hide]有人回复了您所发表/回复的主题文章，快来".
					"[article $aid]看看[/article]《" . $r_title . "》吧！\n";

				$sql = "INSERT INTO bbs_msg(fromUID, toUID, content, send_dt, send_ip)
						VALUES($BBS_sys_uid, " . $row["UID"] . ", '" . 
						mysqli_real_escape_string($db_conn, $msg_content) .
						"', NOW(), '" . client_addr() . "')";

				$rs_msg = mysqli_query($db_conn, $sql);
				if ($rs_msg == false)
				{
					$result_set["return"]["code"] = -2;
					$result_set["return"]["message"] = "Insert msg error: " . mysqli_error($db_conn);
			
					mysqli_close($db_conn);
					exit(json_encode($result_set));
				}
			} 

			mysqli_free_result($rs);
		}
		else // Post new article
		{
			$tid = $aid;
		}

		//Add exp
		if ($_SESSION["BBS_priv"]->checkpriv($sid, S_GETEXP)) //Except in test section
		{
			$rs = user_exp_change($_SESSION["BBS_uid"], ($reply_id > 0 ? 3 : ($transship ? 5 : 15)), $db_conn);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Add exp error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
	}
	else // Modify article
	{
		$aid = $id;

		$sql = "UPDATE bbs SET CID = $cid, reply_note = $reply_note,
				icon = $emoji, length = $length WHERE AID = $aid";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Update article error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	// Link content to article
	$sql = "UPDATE bbs_content SET AID = $aid WHERE CID = $cid";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update content error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Link attachments to article
	$sql = "UPDATE upload_file SET ref_AID = $aid WHERE AID IN ($atta_id_list)";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update upload file error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Add log
	$rs = article_op_log($aid, $_SESSION["BBS_uid"], ($id == 0 ? "A" : "M"), client_addr(), $db_conn);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Add log error: " . mysqli_error($db_conn);
	
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Commit transaction
	$rs = mysqli_query($db_conn, "COMMIT");
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Mysqli error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Set return path
	$result_set["return"]["aid"] = $aid;

	$_SESSION["BBS_last_sub_tm"] = time();

	mysqli_close($db_conn);
	exit(json_encode($result_set));
?>

<?
	require_once "../lib/db_open.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./session_init.inc.php";
	require_once "./check_sub.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$sid = (isset($data["sid"]) ? intval($data["sid"]) : 0);
	$sname = (isset($data["sname"]) ? trim($data["sname"]) : "");
	$title = (isset($data["title"]) ? trim($data["title"]) : "");
	$exp_get = (isset($data["exp_get"]) && $data["exp_get"] == "1" ? 1 : 0);
	$recommend = (isset($data["recommend"]) && $data["recommend"] == "1" ? 1 : 0);
	$read_user_level = (isset($data["read_user_level"]) ? intval($data["read_user_level"]) : P_GUEST);
	$write_user_level = (isset($data["write_user_level"]) ? intval($data["write_user_level"]) : P_USER);
	$comment = (isset($data["comment"]) ? $data["comment"] : "");
	$announcement = (isset($data["announcement"]) ? $data["announcement"] : "");
	$sort_order = (isset($data["sort_order"]) ? intval($data["sort_order"]) : 0);
	$ex_update = (isset($data["ex_update"]) && $data["ex_update"] == "1" ? 1 : 0);
	
	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	// Validate input data
	if (!$_SESSION["BBS_priv"]->checkpriv($sid, S_POST | S_MAN_S))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "prompt",
			"errMsg" => "没有权限",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if (!preg_match("/^[A-Za-z][A-Za-z0-9_]{0,19}$/", $sname))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "sname",
			"errMsg" => "不符合格式要求",
		));
	}

	if ($title == "" || preg_match("/[[:space:]]/", $title) ||
		htmlspecialchars(split_line($title, "", 20, 1), ENT_QUOTES | ENT_HTML401, 'UTF-8') != $title)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "title",
			"errMsg" => "不符合格式要求",
		));
	}

	$r_comment = check_badwords(split_line($comment, "", 80, 3), "****");
	if ($comment != $r_comment)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "comment",
			"errMsg" => "不符合要求",
			"updateValue" => $r_comment,
		));
	}

	$r_announcement = check_badwords(split_line($announcement, "", 150, 3), "****");
	if ($announcement != $r_announcement)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "announcement",
			"errMsg" => "不符合要求",
			"updateValue" => $r_announcement,
		));
	}

	if ($result_set["return"]["code"] != 0)
	{
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Secure SQL statement
	$title = mysqli_real_escape_string($db_conn, $title);
	$comment = mysqli_real_escape_string($db_conn, $comment);
	$announcement = mysqli_real_escape_string($db_conn, $announcement);

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

	// Query section
	$sql = "SELECT CID FROM section_config WHERE SID = $sid FOR UPDATE";

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
		$cid = $row["CID"];
	}
	else
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "prompt",
			"errMsg" => "版块不存在",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	mysqli_free_result($rs);
	
	if ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M))
	{
		// Set sort order of sections in the same section class
		$sql = "SELECT SID, enable, sort_order FROM section_config WHERE CID = $cid
				ORDER BY sort_order FOR UPDATE";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query section list error: " . mysqli_error($db_conn);

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$i = 1;
		$sid_disabled_list = "-1";
		$real_sort_order = 0;
		while ($row = mysqli_fetch_array($rs))
		{
			if ($sort_order == $i)
			{
				$real_sort_order = $i;
				$i++;

				if ($row["SID"] == $sid)
				{
					if ($row["sort_order"] == $sort_order)
					{
						$real_sort_order = -1;
					}
					continue;
				}
			}

			if (!$row["enable"])
			{
				if ($row["sort_order"] != 0)
				{
					$sid_disabled_list .= (", " . $row["SID"]);
				}
				continue;
			}

			if ($row["SID"] != $sid)
			{
				if ($row["sort_order"] != $i)
				{
					// Set sort_order for section with updated value
					$sql = "UPDATE section_config SET sort_order = $i WHERE SID = " . $row["SID"];

					$ret = mysqli_query($db_conn, $sql);
					if ($ret == false)
					{
						$result_set["return"]["code"] = -2;
						$result_set["return"]["message"] = "Update section error: " . mysqli_error($db_conn);
				
						mysqli_close($db_conn);
						exit(json_encode($result_set));
					}
				}
				$i++;
			}
		}
		mysqli_free_result($rs);

		if ($real_sort_order == 0)
		{
			$real_sort_order = $i;
		}

		if ($real_sort_order > 0)
		{
			$sql = "UPDATE section_config SET sort_order = $real_sort_order WHERE SID = $sid";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Update section error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}

		// Enforce sort_order of disabled sections to 0
		if ($sid_disabled_list != "-1")
		{
			$sql = "UPDATE section_config SET sort_order = 0 WHERE SID IN ($sid_disabled_list)";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Update section error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
	}

	if ($_SESSION["BBS_priv"]->checklevel(P_ADMIN_M))
	{
		$sql = "UPDATE section_config SET sname = '$sname', title = '$title',
				exp_get = $exp_get, recommend = $recommend, read_user_level = $read_user_level,
				write_user_level = $write_user_level, comment = '$comment',
				announcement = '$announcement', ex_update = $ex_update,
				set_UID = " . $_SESSION["BBS_uid"] . ", set_dt = NOW(), set_ip='" .
				client_addr() ."' WHERE SID = $sid";
	}
	else
	{
		$sql = "UPDATE section_config SET comment = '$comment',
				announcement = '$announcement', ex_update = $ex_update,
				set_UID = " . $_SESSION["BBS_uid"] . ", set_dt = NOW(), set_ip='" .
				client_addr() ."' WHERE SID = $sid";
	}

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update section data error: " . mysqli_error($db_conn);

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

	mysqli_close($db_conn);
	exit(json_encode($result_set));
?>

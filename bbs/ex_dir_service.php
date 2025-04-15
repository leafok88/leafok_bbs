<?
	require_once "../lib/db_open.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./check_sub.inc.php";
	require_once "./session_init.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$sid = (isset($data["sid"]) ? intval($data["sid"]) : 0);
	$current_dir = (isset($data["current_dir"]) ? trim($data["current_dir"]) : "");
	$dir = (isset($data["dir"]) ? trim($data["dir"]) : "");
	$dir_name = (isset($data["dir_name"]) ? trim($data["dir_name"]) : "");
	$dir_op = (isset($data["dir_op"]) ? intval($data["dir_op"]) : 0);
	
	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	// Validate input data
	if (!preg_match("/^[A-Za-z0-9_\/]{0,50}$/", $current_dir))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "current_dir",
			"errMsg" => "格式不正确",
		));
	}

	if (!preg_match("/^[A-Za-z0-9_]{0,20}$/", $dir))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "dir",
			"errMsg" => "格式不正确",
		));
	}

	$r_dir_name = check_badwords(split_line(htmlspecialchars($dir_name, ENT_HTML401, 'UTF-8'), "", 30, 1), "****");
	if ($dir_name != $r_dir_name)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "dir_name",
			"errMsg" => "格式不正确",
		));
	}

	if ($result_set["return"]["code"] != 0)
	{
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

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

	// Secure SQL statement
	$dir_name = mysqli_real_escape_string($db_conn, $dir_name);

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

	if ($current_dir != "") // Not root
	{
		$sql = "SELECT FID, name FROM ex_dir WHERE dir = '$current_dir' AND enable";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Query ex_dir error: " . mysqli_error($db_conn);
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	
		if ($row = mysqli_fetch_array($rs))
		{
			$fid = $row["FID"];
			$old_name = $row["name"];
		}
		else // Not exist
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "current_dir",
				"errMsg" => "目录不存在",
			));
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
		mysqli_free_result($rs);
	}
	else
	{
		$fid = 0; // Root
	}

	// Operation of ex_dir
	switch($dir_op)
	{
		case 0: // List
			break;
		case 1: // Create
			if ($dir == "" || $dir_name == "")
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "dir",
					"errMsg" => "目录和名称都不能为空",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			$sql = "SELECT FID FROM ex_dir WHERE SID = $sid AND dir = '$current_dir$dir/'";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Query ex_dir error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		
			if (mysqli_num_rows($rs) > 0)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "dir",
					"errMsg" => "目录已存在",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
			mysqli_free_result($rs);

			$sql = "INSERT INTO ex_dir(dir, name, SID, enable, dt) VALUES
					('$current_dir$dir/', '$dir_name', $sid, 1, now())";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Create ex_dir error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			break; // case 1: Create
		case 2: // Update
			if ($fid == 0) // if ($current_dir == "")
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "current_dir",
					"errMsg" => "根目录不能改名",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			if ($dir == "" && $dir_name == "")
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "dir",
					"errMsg" => "目录和名称不能同时为空",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			$old_dir = substr($current_dir, strrpos("/" . $current_dir, "/", -2));
			$old_dir = substr($old_dir, 0, strlen($old_dir) - 1);
			if ($dir == $old_dir && $dir_name == $old_name)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "dir",
					"errMsg" => "没有更改",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			if ($dir != "" && $dir != $current_dir)
			{
				$parent_dir = substr($current_dir, 0, strrpos("/" . $current_dir, "/", -2));
				$current_dir_len = strlen($current_dir);

				$sql = "SELECT FID, dir FROM ex_dir WHERE SID = $sid AND dir LIKE '$current_dir%'";

				$rs = mysqli_query($db_conn, $sql);
				if ($rs == false)
				{
					$result_set["return"]["code"] = -2;
					$result_set["return"]["message"] = "Query ex_dir error: " . mysqli_error($db_conn);
			
					mysqli_close($db_conn);
					exit(json_encode($result_set));
				}

				while ($row = mysqli_fetch_array($rs))
				{
					$child_dir = substr($row["dir"], $current_dir_len);

					$sql = "UPDATE ex_dir SET dir = '$parent_dir$dir/$child_dir' WHERE FID = " . $row["FID"];

					$rs_update = mysqli_query($db_conn, $sql);
					if ($rs_update == false)
					{
						$result_set["return"]["code"] = -2;
						$result_set["return"]["message"] = "Update ex_dir error: " . mysqli_error($db_conn);
				
						mysqli_close($db_conn);
						exit(json_encode($result_set));
					}
				}
				mysqli_free_result($rs);
			}

			if ($dir_name != "" && $dir_name != $old_name)
			{
				$sql = "UPDATE ex_dir SET name = '$dir_name' WHERE FID = $fid";

				$rs_update = mysqli_query($db_conn, $sql);
				if ($rs_update == false)
				{
					$result_set["return"]["code"] = -2;
					$result_set["return"]["message"] = "Update ex_dir error: " . mysqli_error($db_conn);
			
					mysqli_close($db_conn);
					exit(json_encode($result_set));
				}
			}

			break; // case 2: Update
		case 3: // Delete
			if ($fid == 0) // if ($current_dir == "")
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "current_dir",
					"errMsg" => "根目录不能删除",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			if ($dir != "" || $dir_name != "")
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "dir",
					"errMsg" => "目录和名称必须都为空",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			$sql = "SELECT FID FROM ex_dir WHERE SID = $sid AND dir LIKE '$current_dir%'";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Query ex_dir error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			if (mysqli_num_rows($rs) > 1)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "current_dir",
					"errMsg" => "目录中有子目录存在",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
			mysqli_free_result($rs);

			$sql = "SELECT AID FROM ex_file WHERE FID = $fid";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Query ex_file error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			if (mysqli_num_rows($rs) > 0)
			{
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "current_dir",
					"errMsg" => "目录中有文章存在",
				));
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
			mysqli_free_result($rs);

			$sql = "DELETE FROM ex_dir WHERE FID = $fid";

			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Delete ex_dir error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}

			break; // case 3: Delete
		default: // Invalid Op
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "dir_op",
				"errMsg" => "非法操作",
			));

			mysqli_close($db_conn);
			exit(json_encode($result_set));

			break; // default: Invalid Op
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

	$sql = "SELECT * FROM ex_dir WHERE SID = $sid AND enable ORDER BY dir";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query ex_dir error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Fill up ex_dir data
	$result_set["return"]["data"] = array(
		"ex_dir" => array(),
	);

	array_push($result_set["return"]["data"]["ex_dir"], array(
		"dir" => "",
		"name" => "根目录",
	));

	while($row = mysqli_fetch_array($rs))
	{
		array_push($result_set["return"]["data"]["ex_dir"], array(
			"dir" => $row["dir"],
			"name" => $row["name"],
		));
	}
	mysqli_free_result($rs);

	mysqli_close($db_conn);
	exit(json_encode($result_set));
?>

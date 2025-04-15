<?
	require_once "../lib/db_open.inc.php";
	require_once "./common_lib.inc.php";
	require_once "./session_init.inc.php";

	$data = json_decode(file_get_contents("php://input"), true);

	$id = (isset($data["id"]) ? intval($data["id"]) : 0);
	$fid_set = (isset($data["fid"]) ? intval($data["fid"]) : -2);

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	if (!isset($_SESSION["BBS_uid"]) || $_SESSION["BBS_uid"] == 0)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有登录";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($fid_set < -1)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "参数错误";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

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

	$sql = "SELECT SID, gen_ex, FID FROM bbs
			LEFT JOIN ex_file ON bbs.AID = ex_file.AID
			WHERE bbs.AID = $id AND TID = 0
			AND visible AND excerption";

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
		$sid = $row["SID"];
		$fid = ($row["gen_ex"] ? $row["FID"] : -1);
	}
	else
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "文章不存在";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}
	mysqli_free_result($rs);

	if (!($_SESSION["BBS_priv"]->checkpriv($sid, S_POST | S_MAN_S)))
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有权限";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($fid == $fid_set)
	{
		$result_set["return"]["code"] = -1;
		$result_set["return"]["message"] = "没有改动";

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	$sql = "DELETE FROM ex_file WHERE AID = $id";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Delete ex_file error: " . mysqli_error($db_conn);
	
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	if ($fid_set > 0)
	{
		$sql = "SELECT FID FROM ex_dir WHERE FID = $fid_set AND SID = $sid AND enable";

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
			$sql = "INSERT INTO ex_file(AID, FID) VALUES($id, $fid_set)";

			$ret = mysqli_query($db_conn, $sql);
			if ($ret == false)
			{
				$result_set["return"]["code"] = -2;
				$result_set["return"]["message"] = "Add ex_dir error: " . mysqli_error($db_conn);
		
				mysqli_close($db_conn);
				exit(json_encode($result_set));
			}
		}
		else
		{
			$result_set["return"]["code"] = -1;
			$result_set["return"]["message"] = "目录选择有误";
	
			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		mysqli_free_result($rs);
	}

	$sql = "UPDATE bbs SET gen_ex = " . ($fid_set >= 0 ? 1 : 0) .
			", static = 0 WHERE (AID = $id OR TID = $id) AND excerption";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update article error: " . mysqli_error($db_conn);

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

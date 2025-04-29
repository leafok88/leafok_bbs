<?php
	function check_new_msg(int $uid, mysqli $db_conn) : int
	{
		$new_msg = 0;

		$sql = "SELECT COUNT(*) AS msg_count FROM bbs_msg
				WHERE toUID = $uid AND new = 1 AND deleted = 0";

		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			echo("Query message error: " . mysqli_error($db_conn));
			return -1;
		}

		if ($row = mysqli_fetch_array($rs))
		{
			$new_msg = $row["msg_count"];
		}

		mysqli_free_result($rs);

		return $new_msg;
	}

<?php
function score_change(int $uid, int $score_change, string $reason, mysqli $db_conn) : int
{
	$score_change = intval($score_change);

	if ($score_change == 0)
	{
		return 0; // OK if no change
	}

	$sql = "SELECT score FROM user_score WHERE UID = $uid FOR UPDATE";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		return -1; // Query error
	}

	if ($row = mysqli_fetch_array($rs))
	{
		if ($score_change < 0 && $row["score"] + $score_change < 0)
		{
			return 1; // No enough balance
		}

		$sql = "UPDATE user_score SET score = score + ($score_change)
				WHERE UID = $uid";

		$ret = mysqli_query($db_conn, $sql);
		if ($ret == false)
		{
			return -2; // Update error
		}
	}
	else
	{
		$sql = "INSERT INTO user_score(UID, score) VALUES($uid, $score_change)";

		$ret = mysqli_query($db_conn, $sql);
		if ($ret == false)
		{
			return -3; // Insert error
		}
	}

	mysqli_free_result($rs);

	$sql = "INSERT INTO user_score_log(UID, score_change, reason, dt)
			VALUES($uid, $score_change, '" .
			mysqli_real_escape_string($db_conn, $reason) . "', NOW())";

	$ret = mysqli_query($db_conn, $sql);
	if ($ret == false)
	{
		return -4; // Add log error
	}

	return 0;
}

?>

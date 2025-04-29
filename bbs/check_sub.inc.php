<?php
function check_badwords(string $str_check, string $bw_replace = "") : string | null
{
	$badwords_dict = "../conf/badwords_strict.conf";

	$contents = file_get_contents($badwords_dict);
	if ($contents == false)
	{
		// echo ("Load bad words dict failed!\n");
		return null;
	}

	// Builds the ban words array
	$word_list = explode("\n", str_replace("\r\n", "\n", $contents));

	// Do the checking
	foreach ($word_list as $reg_exp)
	{
	    if ($reg_exp != "")
	    {
			$str_check = preg_replace("/" . $reg_exp . "/i", $bw_replace, $str_check);
		}
	}

	return $str_check;
}

function check_post_count(int $count_limit, int $sid, bool $topic_only, mysqli $db_conn) : bool | null
{
	$s_topic_count = 0;

	$sql = "SELECT COUNT(*) AS cc FROM (
			SELECT AID, UID FROM bbs WHERE SID = $sid " .
			($topic_only ? "AND TID = 0 " : "") .
			"AND sub_dt >= SUBDATE(NOW(), INTERVAL 1 DAY)
			ORDER BY AID DESC LIMIT $count_limit
			) AS s1 WHERE UID = " . $_SESSION["BBS_uid"];

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo("Query recent topic error: " . mysqli_error($db_conn));
		return null;
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$s_topic_count = $row["cc"];
	}
	mysqli_free_result($rs);

	return ($s_topic_count < $count_limit);
}

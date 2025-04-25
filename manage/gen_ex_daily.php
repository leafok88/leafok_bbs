<?php
	if (!isset($_SERVER["argc"]))
	{
		echo ("Invalid usage");
		exit();
	}

	chdir(dirname($_SERVER["argv"][0]));

	require_once "../lib/db_open.inc.php";
	require_once "../lib/common.inc.php";

	$sql = "SELECT section_config.SID, section_config.title AS s_title FROM section_config
			INNER JOIN section_class ON section_config.CID = section_class.CID
			WHERE section_config.enable AND section_class.enable AND ex_update
			ORDER BY section_class.sort_order, section_config.sort_order";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo ("Query section error: " . mysqli_error($db_conn));
		exit();
	}

	$new = false;

	while ($row = mysqli_fetch_array($rs))
	{
		echo ("更新 [".$row["s_title"]."] 中...\n");

		$buffer = shell_exec($PHP_bin . " ./gen_ex_section.php " . $row["SID"]);
		echo ($buffer . "\n");

		$new = true;
	}

	mysqli_free_result($rs);
	mysqli_close($db_conn);

	if ($new)
	{
		echo ("生成目录中...\n");

		$buffer = shell_exec($PHP_bin . " gen_ex_index.php");
		if (!$buffer || file_put_contents("../gen_ex/index.html", $buffer) == false)
		{
			echo ("Open output error!");
			exit();
		}
	}
?>

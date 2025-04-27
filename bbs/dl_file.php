<?php
	require_once "../lib/db_open.inc.php";
	require_once "../bbs/session_init.inc.php";

	$aid = (isset($_GET["aid"]) ? intval($_GET["aid"]) : 0);
	$force = (isset($_GET["force"]) && $_GET["force"] == "1");

	if ($force && !$_SESSION["BBS_priv"]->checklevel(P_ADMIN_M | P_ADMIN_S))
	{
		header('HTTP/1.0 403 Forbidden');
		echo ("Invalid usage");
		exit();
	}

	$sql = "SELECT * FROM upload_file WHERE AID = $aid AND deny = 0 AND deleted = 0";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		header('HTTP/1.0 500 Internal Server Error');
		echo "Query upload error: " . mysqli_error($db_conn);
		exit();
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$filename = $row["filename"];
		$size = $row["size"];
		$check = $row["check"];
		$url = "./upload/$aid";
	}
	else
	{
		header('HTTP/1.0 404 Not Found');
		echo ("File not exists");
		exit();
	}
	mysqli_free_result($rs);

	mysqli_close($db_conn);

	if (!$check && !$force)
	{
		header('HTTP/1.0 403 Forbidden');
		echo ("Not approved yet");
		exit();
	}

	if (!file_exists($url))
	{
		header('HTTP/1.0 404 Not Found');
		echo ("File not found");
		exit();
	}

	if (!($file = fopen($url, "rb")))
	{
		header('HTTP/1.0 404 Not Found');
		echo ("Open file error");
		exit();
	}

	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Accept-Length: " . filesize($url));
	header("Content-Disposition: attachment; filename=" . $filename);

	while (!feof($file)) {
		echo fread($file, 4096);
	}

	fclose ($file);
?>

<?
	require_once "../lib/db_open.inc.php";
	require_once "./section_list.inc.php";
	require_once "./session_init.inc.php";
	require_once "./s_favor.inc.php";
	require_once "./theme.inc.php";

	force_login();

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		),
		"data" => array(
			"section_hierachy" => array(),
		),
	);

	$s_favor = new section_favorite($_SESSION["BBS_uid"], $db_conn);

	// Load section list
	$ret = load_section_list($result_set["data"]["section_hierachy"],
		function (array $section, array $filter_param) : bool
		{
			return $_SESSION["BBS_priv"]->checkpriv($section["SID"], S_LIST);
		},
		function (array $section, array $filter_param) : mixed
		{
			return $filter_param["s_favor"]->is_in($section["SID"]);
		},
		$db_conn,
		array(
			"s_favor" => $s_favor,
		)
	);

	if ($ret == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Query section error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Cleanup
	unset($s_favor);

	mysqli_close($db_conn);

	// Output with theme view
	$theme_view_file = get_theme_file("view/s_favor", $_SESSION["BBS_theme_name"]);
	if ($theme_view_file == null)
	{
		exit(json_encode($result_set)); // Output data in Json
	}
	include $theme_view_file;
?>

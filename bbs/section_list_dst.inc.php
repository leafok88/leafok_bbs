<?
	require_once "../bbs/section_list.inc.php";

	function section_list_dst(mysqli $db_conn, int $sid_exclude = 0) : string
	{
		ob_start();

		// Load section list
		$section_hierachy = array();

		$ret = load_section_list($section_hierachy,
			function (array $section, array $filter_param) : bool
			{
				return ($section["SID"] != $filter_param["sid"] && $_SESSION["BBS_priv"]->checkpriv($section["SID"], S_POST));
			},
			function (array $section, array $filter_param) : mixed
			{
				return null;
			},
			$db_conn,
			array(
				"sid" => $sid_exclude,
			)
		);

		if ($ret == false)
		{
			echo mysqli_error($db_conn);
?>
			<option value="0">---数据查询错误---</option>
<?
		}
		else
		{
?>
			<option value="0">-----选择版块-----</option>
<?
		}
		
		foreach ($section_hierachy as $c_index => $section_class)
		{
?>
			<option value="0">==<? echo $section_class["title"]; ?>==</option>
<?
			foreach ($section_class["sections"] as $s_index => $section)
			{
?>
			<option value="<? echo $section["sid"]; ?>">&nbsp;&nbsp;├<? echo $section["title"]; ?></option>
<?
			}
		}

		unset($section_hierachy);

		$buffer = ob_get_clean();

		return $buffer;
	}
?>

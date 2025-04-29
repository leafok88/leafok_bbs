<?php
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

			echo <<<HTML
				<option value="0">---数据查询错误---</option>
			HTML;
		}
		else
		{
			echo <<<HTML
				<option value="0">-----选择版块-----</option>
			HTML;
		}

		foreach ($section_hierachy as $c_index => $section_class)
		{
			echo <<<HTML
				<option value="0">=={$section_class["title"]}==</option>
			HTML;

			foreach ($section_class["sections"] as $s_index => $section)
			{
				echo <<<HTML
				<option value="{$section["sid"]}">&nbsp;&nbsp;├{$section["title"]}</option>
				HTML;
			}
		}

		unset($section_hierachy);

		$buffer = ob_get_clean();

		return $buffer;
	}

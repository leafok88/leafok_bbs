<?php
	require_once "../bbs/s_favor.inc.php";
	require_once "../bbs/section_list.inc.php";

	function section_list_gen(mysqli $db_conn) : string
	{
		$cache_path = "./cache/section_list_".$_SESSION["BBS_uid"];
		$buffer = false;
		if (file_exists($cache_path))
		{
			if (filemtime($cache_path) >= $_SESSION["BBS_login_tm"])
			{
				$buffer = file_get_contents($cache_path);
			}
		}
		if ($buffer == false)
		{
			ob_start();
			
			$s_favor = new section_favorite($_SESSION["BBS_uid"], $db_conn);

			if ($_SESSION["BBS_uid"] > 0)
			{
				// Load favorite section list
				$section_hierachy = array();
			
				$ret = load_section_list($section_hierachy,
					function (array $section, array $filter_param) : bool
					{
						return ($_SESSION["BBS_priv"]->checkpriv($section["SID"], S_LIST) && $filter_param["s_favor"]->is_in($section["SID"]));
					},
					function (array $section, array $filter_param) : mixed
					{
						return null;
					},
					$db_conn,
					array(
						"s_favor" => $s_favor,
					)
				);

				if ($ret == false)
				{
?>
				<option value="0">---数据查询错误---</option>
<?php
				}
				else
				{
?>
				<option value="0">---我收藏的版块---</option>
<?php
				}


				foreach ($section_hierachy as $c_index => $section_class)
				{
?>
				<option value="-<?= $section_class["cid"]; ?>">==<?= $section_class["title"]; ?>==</option>
<?php
					foreach ($section_class["sections"] as $s_index => $section)
					{
?>
				<option value="<?= $section["sid"]; ?>">&nbsp;&nbsp;├<?= $section["title"]; ?></option>
<?php
					}
				}

				unset($section_hierachy);
			}

			// Load non-favorite section list
			$section_hierachy = array();
		
			$ret = load_section_list($section_hierachy,
				function (array $section, array $filter_param) : bool
				{
					return ($_SESSION["BBS_priv"]->checkpriv($section["SID"], S_LIST) && !$filter_param["s_favor"]->is_in($section["SID"]));
				},
				function (array $section, array $filter_param) : mixed
				{
					return null;
				},
				$db_conn,
				array(
					"s_favor" => $s_favor,
				)
			);

			if ($ret == false)
			{
?>
				<option value="0">---数据查询错误---</option>
<?php
			}
			else
			{
?>
				<option value="0">---看看别的版块---</option>
<?php
			}

			foreach ($section_hierachy as $c_index => $section_class)
			{
?>
				<option value="-<?= $section_class["cid"]; ?>">==<?= $section_class["title"]; ?>==</option>
<?php
				foreach ($section_class["sections"] as $s_index => $section)
				{
?>
				<option value="<?= $section["sid"]; ?>">&nbsp;&nbsp;├<?= $section["title"]; ?></option>
<?php
				}
			}

			unset($section_hierachy);

			$buffer = ob_get_clean();

			file_put_contents($cache_path, $buffer);
		}

		return $buffer;
	}
?>

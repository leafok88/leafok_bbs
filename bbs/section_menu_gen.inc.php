<?php
	require_once "../bbs/section_list.inc.php";

	function section_menu_gen(mysqli $db_conn) : string
	{
		$cache_path = "./cache/section_menu_" . $_SESSION["BBS_uid"];
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

			echo <<<HTML
				<tr>
					<td align="center">
			HTML;
			// Load section list
			$section_hierachy = array();

			$ret = load_section_list($section_hierachy,
				function (array $section, array $filter_param) : bool
				{
					return ($_SESSION["BBS_priv"]->checkpriv($section["SID"], S_LIST));
				},
				function (array $section, array $filter_param) : mixed
				{
					return null;
				},
				$db_conn);

			if ($ret == false)
			{
				echo mysqli_error($db_conn);
			}

			foreach ($section_hierachy as $c_index => $section_class)
			{
				echo <<<HTML
					</td>
				</tr>
				<tr>
					<td align="center">
						<a class="s5" href="#" onclick="return ch_cid({$section_class['cid']});">{$section_class["title"]}</a>
					</td>
				</tr>
				<tr>
					<td id="class_{$section_class['cid']}" align="center" style="display:none;">
				HTML;

				foreach ($section_class["sections"] as $s_index => $section)
				{
					$comment_f = LML($section['comment'], 80, true);
					echo <<<HTML
						<a class="s6" href="list.php?sid={$section['sid']}" title="{$comment_f}">{$section['title']}</a><br />
					HTML;
				}
			}

			echo <<<HTML
					</td>
				</tr>
			HTML;

			unset($section_hierachy);

			$buffer = ob_get_clean();

			file_put_contents($cache_path, $buffer);
		}

		return $buffer;
	}

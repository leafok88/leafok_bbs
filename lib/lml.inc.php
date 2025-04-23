<?php
function LML(string $source_str, bool $lml_tag, bool $use_proxy = true, int $width = 76) : string
{
	//$lml_tag		whether LML tag should be processed
	//$use_proxy	whether use proxy to display image or flash
	//$width		length of line, 0 means unlimited

	global $BBS_theme_current;

	//For compatibility with FB2000
	if ($lml_tag)
	{
		$source_str = FB2LML($source_str);
	}

	$lml_user_set = $lml_tag;
	$result_str = "";
	$pre = 0;
	$p_current = 0;
	$l_source = strlen($source_str);
	$quote_level = 0;
	$quote_color = array(
		"#90a040",
		"#b010b0",
		"#404040",
	);

	while ($p_current < $l_source)
	{
		$p_start = strpos($source_str, "[", $p_current);
		if ($p_start !== false)
		{
			if ($p_start > $p_current)
			{
				$result_str .= split_long_str(substr($source_str, $p_current, $p_start - $p_current), $pre, $width, $lml_tag);
			}

			$tag_arg = "";
			$tag_str = "";
			$tag_result = "";

			$p_space = strpos($source_str, " ", $p_start + 1);
			$p_end = strpos($source_str, "]", $p_start+1);
			if ($p_end === false)
			{
				$p_end = $l_source - 1;
			}
			if (($p_space !== false) && ($p_space < $p_end))
			{
				$p_tag_end = $p_space;
				if ($p_end > $p_tag_end)
					$tag_arg = trim(substr($source_str, $p_tag_end + 1, $p_end - $p_tag_end - 1));
			}
			else
			{
				$p_tag_end = $p_end;
			}
			if ($p_tag_end > $p_start)
			{
				$tag_str = strtolower(trim(substr($source_str, $p_start + 1, $p_tag_end - $p_start - 1)));
			}

			if ($lml_tag)
			{
				switch ($tag_str)
				{
					case "quote":
						$tag_result = "<span style=\"color: " . $quote_color[$quote_level % 3] . ";\">";
						$quote_level++;
						break;
					case "/quote":
						if ($quote_level > 0)
						{
							$quote_level--;
						}
						$tag_result = "</span>";
						break;
					case "bwf":
						$tag_result = "<span style=\"color: red;\">****</span>";
						break;
				}

				if ($lml_user_set)
				{
					switch ($tag_str)
					{
						case "nolml": //User disable LML
							$lml_user_set = false;
							$tag_result = "";
							break;
						case "left":
							$tag_result = "[";
							break;
						case "right":
							$tag_result = "]";
							break;
						case "bold":
						case "b":
							$tag_result = "<span style=\"font-weight: bold;\">";
							break;
						case "/bold":
						case "/b":
							$tag_result = "</span>";
							break;
						case "italic":
						case "i":
							$tag_result = "<span style=\"font-style: italic;\">";
							break;
						case "/italic":
						case "/i":
							$tag_result = "</span>";
							break;
						case "underline":
						case "u":
							$tag_result = "<span style=\"text-decoration: underline;\">";
							break;
						case "/underline":
						case "/u":
							$tag_result = "</span>";
							break;
						case "color":
							$tag_result = "<span style=\"color: " . htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8') . ";\">";
							break;
						case "/color":
							$tag_result = "</span>";
							break;
						case "size":
							$tag_result = "<span style=\"font-size: " .
								(is_numeric($tag_arg) ? intval($tag_arg * 4) . "px" : htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8')) . ";\">";
							break;
						case "/size":
							$tag_result = "</span>";
							break;
						case "align":
							$tag_result = "\n<p align=\"" . htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8') . "\">";
							break;
						case "/align":
							$tag_result = "</p>\n";
							break;
						case "image":
							if ($use_proxy)
							{
								$tag_result = "<img onmousewheel=\"return bbs_img_zoom(event, this)\" src=\"" .
									htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8') . "\" border=0>";
							}
							else
							{
								$tag_result = "<img src=\"" . htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8') . "\" border=0>";
							}
							break;
						case "link":
						case "url":
							if (preg_match("/script:/i", $tag_arg)) // Filter milicious code
							{
								$tag_arg = "#";
							}
							$tag_result = "<a class=\"s7\" href=\"" . htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8') . "\" target=_blank>";
							break;
						case "/link":
						case "/url":
							$tag_result = "</a>";
							break;
						case "email":
							$tag_result = "<a class=\"s7\" href=\"mailto:" . htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8') . "\">";
							break;
						case "/email":
							$tag_result = "</a>";
							break;
						case "article":
							$tag_result = "<a class=\"s7\" href=\"../bbs/view_article.php?tn=" .
								(isset($BBS_theme_current) ? $BBS_theme_current : "") . "&trash=1&id=" . intval($tag_arg) . "#"  . intval($tag_arg) . "\" target=_blank>";
							break;
						case "/article":
							$tag_result = "</a>";
							break;
						case "user":
							$tag_result = "<a class=\"s7\" href=\"show_profile.php?uid=" . intval($tag_arg) . "\" target=_blank>";
							break;
						case "/user":
							$tag_result = "</a>";
							break;
						case "marquee":
							$tag_result = "<marquee " . htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8') . ">";
							break;
						case "/marquee":
							$tag_result = "</marquee>";
							break;
						case "hide":
							$tag_result = "<span style=\"display: none;\">";
							break;
						case "/hide":
							$tag_result = "</span>";
							break;
						case "flash":
							$tag_result = "<a class=\"s7\" href=\"" . htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8') . "\" target=_blank>View Flash</a>";
							break;
					}
				}
			}

			if (!$lml_user_set)
			{
				switch ($tag_str)
				{
					case "lml": //User re-enable LML
						if ($lml_tag)
							$lml_user_set = true;
						break;
					case "left":
						$tag_result = "[";
						break;
					case "right":
						$tag_result = "]";
						break;
					case "image": //show URL only
						$tag_result = $tag_arg;
						break;
					case "bwf": //blocked word
						$tag_result = "****";
						break;
				}
			}

			$result_str .= split_long_str($tag_result, $pre, $width, $lml_tag);
			$p_current = $p_end + 1;
		}
		else
		{
			if ($l_source > $p_current)
			{
				$result_str .= split_long_str(substr($source_str, $p_current, NULL), $pre, $width, $lml_tag);
			}
			$p_current = $l_source;
		}
	}

	return $result_str;
}

function split_long_str(string $str, string &$pre, int $width = 76, bool $html_tag = false) : string
{
	//$pre			length of string before $str
	//$width		length of line, 0 means unlimited
	//$html_tag		whether html tag should be processed

	$str_r = "";
	$html_tag_begin = false;
	$len = strlen($str);

	for($i = 0; $i < $len; $i++)
	{
		$c = $str[$i];

		if ($c == "\n")
		{
			$str_r .= "\n";
			$pre = 0;
			continue;
		}

		if ($html_tag && $c == "<")
		{
			$html_tag_begin = true;
		}
		if (!$html_tag_begin)
		{
			//Process UTF-8 Chinese characters
			$v1 = ord($c);
			if (($v1 & 0b10000000) == 0b10000000) //head of multi-byte character
			{
				$v2 = ($v1 & 0b01111000) << 1;
				while ($v2 & 0b10000000)
				{
					$i++;
					$v3 = $str[$i];
					$c .= $v3;
					$v2 = ($v2 & 0b01111111 ) << 1;
				}

				// Each UTF-8 CJK character should use two character length for display
				if ($pre + 2 > $width)
				{
					$str_r .= "\n";
					$pre = 0;
				}
				$pre += 2;
			}
			else
			{
				$pre++;
			}
		}
		if ($html_tag && $c == ">")
		{
			$html_tag_begin = false;
		}

		if ($pre > $width)
		{
			$str_r .= "\n";
			$pre = 1;
		}

		$str_r .= $c;
	}
	return $str_r;
}

function FB2LML(string $str) : string
{
	$result = "";
	
	$lines = explode("\n", $str);
	foreach ($lines as $line)
	{
		$count = 0;
		if (preg_match("/^([:][[:space:]])*/", $line, $regs))
		{
			$count = strlen($regs[0]) / 2;
		}
		$result .= (str_repeat("[quote]", $count) . $line .
			str_repeat("[/quote]", $count) . "\n");
	}
	
	return $result;
}

function LMLtagFilter(string $str) : string
{
	$result = "";
	$len = strlen($str);
	
	for ($i = 0; $i < $len; $i++)
	{
		$c = $str[$i];
		switch($c)
		{
			case "[":
				$result .= "[left]";
				break;
			case "]":
				$result .= "[right]";
				break;
			default:
				$result .= $c;
				break;
		}
	}
	
	return $result;
}

?>

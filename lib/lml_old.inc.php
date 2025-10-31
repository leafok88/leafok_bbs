<?php
if (defined("_BBS_LML_LIB_"))
{
	return;
}
define("_BBS_LML_LIB_", 1);

$lml_total_exec_duration = 0; // For testing purpose

function LML(string | null $source_str, bool $lml_tag, int $width = 76, bool $quote_mode = false) : string
{
	//$lml_tag		whether LML tag should be processed
	//$width		length of line, 0 means unlimited
	//$quote_mode	whether output text is used as quoted content in text editor

	global $lml_total_exec_duration;
	global $BBS_theme_current;

	$time_start = microtime(true);

	if ($source_str == null)
	{
		$source_str = "";
	}

	//For compatibility with FB2000
	if (!$quote_mode)
	{
		$source_str = FB2LML($source_str);
	}

	$lml_disabled = !$lml_tag;
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
		if (!$lml_disabled && $p_start !== false)
		{
			if ($p_start > $p_current)
			{
				$result_str .= split_long_str(substr($source_str, $p_current, $p_start - $p_current), $pre, $width, $lml_tag);
			}

			$tag_arg = "";
			$tag_str = "";
			$tag_result = "";

			$p_space = strpos($source_str, " ", $p_start + 1);
			$p_end = strpos($source_str, "]", $p_start + 1);
			if ($p_end === false)
			{
				$result_str .= substr($source_str, $p_start, $l_source - $p_start);
				break;
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

			if (!$quote_mode)
			{
				switch ($tag_str)
				{
					case "plain": // User disable LML unrecoverably
						$lml_disabled = true;
						break;
					case "lml": // deprecated
						break;
					case "nolml": // deprecated
						break;
					case "left":
						$tag_result = "[";
						break;
					case "right":
						$tag_result = "]";
						break;
					case "bold":
					case "b":
						$tag_result = "<span style=\"font-weight: bold\">";
						break;
					case "/bold":
					case "/b":
						$tag_result = "</span>";
						break;
					case "italic":
					case "i":
						$tag_result = "<span style=\"font-style: italic\">";
						break;
					case "/italic":
					case "/i":
						$tag_result = "</span>";
						break;
					case "underline":
					case "u":
						$tag_result = "<span style=\"text-decoration: underline\">";
						break;
					case "/underline":
					case "/u":
						$tag_result = "</span>";
						break;
					case "color":
						$tag_result = "<span style=\"color: " . htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8') . "\">";
						break;
					case "/color":
						$tag_result = "</span>";
						break;
					case "size":
						$tag_result = "<span style=\"font-size: " .
							(is_numeric($tag_arg) ? intval($tag_arg * 4) . "px" : htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8')) . "\">";
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
						$tag_result = "<img src=\"" . htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8') . "\" border=0>";
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
						$tag_result = "<a class=\"s7\" href=\"/bbs/view_article.php?tn=" .
							(isset($BBS_theme_current) ? $BBS_theme_current : "") . "&trash=1&id=" . intval($tag_arg) . "#"  . intval($tag_arg) . "\" target=_blank>";
						break;
					case "/article":
						$tag_result = "</a>";
						break;
					case "user":
						$tag_result = "<a class=\"s7\" href=\"view_user.php?uid=" . intval($tag_arg) . "\" target=_blank>";
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
					case "flash":
						$tag_result = "<a class=\"s7\" href=\"" . htmlspecialchars($tag_arg, ENT_QUOTES | ENT_HTML401, 'UTF-8') . "\" target=_blank>View Flash</a>";
						break;
					case "quote":
						$tag_result = "<span style=\"color: " . $quote_color[$quote_level % 3] . "\">";
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
						$tag_result = "<span style=\"color: red\">****</span>";
						break;
					default:
						$tag_result = substr($source_str, $p_start, $p_end - $p_start + 1);
				}
			}

			if ($quote_mode)
			{
				switch ($tag_str)
				{
					case "plain": // User disable LML unrecoverably
						$lml_disabled = true;
						$tag_result = "[plain]";
						break;
					case "lml": // deprecated
						break;
					case "nolml": // deprecated
						break;
					case "left":
						$tag_result = "[left]";
						break;
					case "right":
						$tag_result = "[right]";
						break;
					case "image": //show URL only
						$tag_result = $tag_arg;
						break;
					case "bwf": //blocked word
						$tag_result = "****";
						break;
					default:
						$tag_result = substr($source_str, $p_start, $p_end - $p_start + 1);
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

	$time_end = microtime(true);
	$lml_total_exec_duration += ($time_end - $time_start);

	return $result_str;
}

function split_long_str(string $str, int &$pre, int $width = 76, bool $html_tag = false) : string
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

		// Skip special characters
		if ($c == "\r" || $c == "\7")
		{
			continue;
		}

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
			if ($v1 & 0x80) //head of multi-byte character
			{
				$v2 = ($v1 & 0x70) << 1;
				while ($v2 & 0x80)
				{
					$i++;
					$c .= $str[$i];
					$v2 = ($v2 & 0x7f) << 1;
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
	$lml_disabled = false;
	$result = "";

	$lines = explode("\n", $str);
	foreach ($lines as $line)
	{
		if ($lml_disabled)
		{
			$result .= $line . "\n";
			continue;
		}

		if (strstr($line, "[plain]"))
		{
			$lml_disabled = true;
			$result .= $line . "\n";
			continue;
		}

		$count = 0;
		if (preg_match("/^([:][[:space:]])*/", $line, $regs))
		{
			$count = strlen($regs[0]) / 2;
		}
		$result .= (str_repeat("[quote]", $count) . $line .
			str_repeat("[/quote]", $count) . "\n");
	}

	$patterns = array(
		"/\033\[([01]?;)*([0-9]{2};)?30(;[0-9]{2})?m/",
		"/\033\[([01]?;)*([0-9]{2};)?31(;[0-9]{2})?m/",
		"/\033\[([01]?;)*([0-9]{2};)?32(;[0-9]{2})?m/",
		"/\033\[([01]?;)*([0-9]{2};)?33(;[0-9]{2})?m/",
		"/\033\[([01]?;)*([0-9]{2};)?34(;[0-9]{2})?m/",
		"/\033\[([01]?;)*([0-9]{2};)?35(;[0-9]{2})?m/",
		"/\033\[([01]?;)*([0-9]{2};)?36(;[0-9]{2})?m/",
		"/\033\[([01]?;)*([0-9]{2};)?37(;[0-9]{2})?m/",
		"/\033\[([01]?;)*4[0-7]m/", // BG color only
		// Reset
		"/\033\[[0]?m/",
		// Unknown
		"/\033\[I/",
	);
	$replaces = array(
		"[/color][color black]",
		"[/color][color red]",
		"[/color][color green]",
		"[/color][color orange]", // yellow -> orange
		"[/color][color blue]",
		"[/color][color magenta]",
		"[/color][color cyan]",
		"[/color][color white]",
		"", // Ignore BG color
		// Reset
		"[/color]", // default -> black
		// Unknown
		"",
	);
	$result = preg_replace($patterns, $replaces, $result);

	return $result;
}

function lml_test()
{
	$test_str_in = array(
		"[left]ABCD[right]EFG",
		"A[u]B[italic]CD[/i]E[/u]F[b]G[/bold]",
		"A[url BC DE]测试a网址[/url]FG",
		"AB[email CDE]F[/eMAil]G01[emaiL]23456[/email]789",
		"A[user DE]BC[/User]FG",
		"[article A B CD]EF[  /article]G[article 789]123[/article]456",
		"A[ image  BCD]EFG",
		"AB[ Flash  CDE ]FG",
		"AB[bwf]CDEFG",
		"[lef]A[rightBCD[right]EF[left[left[]G[left",
		"A[ color  BCD]EF[/color]G[color black]0[/color][color magenta]1[color cyan]23[/color]4[color red]5[/color]6[color yellOw]7[/color]8[color green]9[color blue]0[/color]",
		"A[quote]B[quote]C[quote]D[quote]E[/quote]F[/quote]G[/quote]0[/quote]1[/quote]2[quote]3[/quote]4[/quote]56789",
		": ABCDE[quote]FG\r\nab[/quote]cd[quote]ef[quote]g\r\n: : 012[/quote]345[/quote]6789\nABC[quote]DEFG",
		"\033[1;35;42mABC\033[0mDE\033[334mF\033[33mG\033[12345\033[m",
		"123456",
		"[color red]Red[/color][plain][color blue]Blue[/color][plain]",
		"[color yellow]Yellow[/color][nolml][left][color blue]Blue[/color][right][lml][color red]Red[/color]",
		"[abc][left ][ right ][ colory ][left  \nABCD[left]EFG[right ",
		"ABCD]EFG",
		": : A123456789B123456789C123456789D123456789E123456789F123456789G123456789H123456789I123456789J123456789",
		"\033[0m\033[I             \033[1;32m;,                                           ;,\033[m",
		"\n01234567890123456789012345678901234567890123456789012345678901234567890123456789\n2\n01234567890123456789012345678901234567890123456789012345678901234567890123456789\n4\n5\n",
		"A[012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789]B",
	);

	echo ("Test #1\n");
	foreach($test_str_in as $str_in)
	{
		$str_out = LML($str_in, true, 80, false);
		echo ("Input(len=" . strlen($str_in) . "): " . $str_in . "\nOutput(len=" . strlen($str_out) . "): " . $str_out . "\n");
	}

	echo ("Test #2\n");
	foreach($test_str_in as $str_in)
	{
		$str_out = LML($str_in, true, 80, true);
		echo ("Input(len=" . strlen($str_in) . "): " . $str_in . "\nOutput(len=" . strlen($str_out) . "): " . $str_out . "\n");
	}

	echo ("Test #3\n");
	foreach($test_str_in as $str_in)
	{
		$str_out = LML($str_in, false, 80, false);
		echo ("Input(len=" . strlen($str_in) . "): " . $str_in . "\nOutput(len=" . strlen($str_out) . "): " . $str_out . "\n");
	}
}

if (isset($_SERVER["argv"][1]) && $_SERVER["argv"][1] == "test")
{
	lml_test();

	$page_exec_duration = round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000, 2);
	$lml_exec_duration = round($lml_total_exec_duration * 1000, 2);

	echo "\npage_exec_duration=$page_exec_duration, lml_exec_duration=$lml_exec_duration\n";
}

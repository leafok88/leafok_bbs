<?php
if (defined("_BBS_LML_LIB_"))
{
	return;
}
define("_BBS_LML_LIB_", 1);

$lml_total_exec_duration = 0; // For testing purpose

$lml_tag_disabled = false;
$lml_tag_quote_level = 0;
$lml_tag_quote_color = array(
	"#a0a010", // yellow
	"#408040", // green
	"#b010b0", // magenta
);

$lml_tag_ansi_color = array(
	30 => "black",
	31 => "red",
	32 => "green",
	33 => "orange", // yellow -> orange
	34 => "blue",
	35 => "magenta",
	36 => "cyan",
	37 => "sienna", // white -> sienna
);

$lml_tag_def = array(
	// Definition of tuple: lml_tag => array(lml_output, default_param, quote_mode_output)
	"plain" => array(NULL, NULL, NULL),
	"nolml" => array("", NULL, ""), // deprecated
	"lml" => array("", NULL, ""),   // deprecated
	"align" => array("\n<p align=\"%s\">", "", ""),
	"/align" => array("</p>\n", "", ""),
	"size" => array(NULL, "", ""),
	"/size" => array("</span>", "", ""),
	"left" => array("[", "", "[left]"),
	"right" => array("]", "", "[right]"),
	"bold" => array("<span style=\"font-weight: bold\">", "", ""),
	"/bold" => array("</span>", NULL, ""),
	"b" => array("<span style=\"font-weight: bold\">", "", ""),
	"/b" => array("</span>", NULL, ""),
	"italic" => array("<span style=\"font-style: italic\">", "", ""),
	"/italic" => array("</span>", NULL, ""),
	"i" => array("<span style=\"font-style: italic\">", "", ""),
	"/i" => array("</span>", NULL, ""),
	"underline" => array("<span style=\"text-decoration: underline\">", "", ""),
	"/underline" => array("</span>", NULL, ""),
	"u" => array("<span style=\"text-decoration: underline\">", "", ""),
	"/u" => array("</span>", NULL, ""),
	"color" => array(NULL, "", ""),
	"/color" => array(NULL, "", ""),
	"quote" => array(NULL, "", ""),
	"/quote" => array(NULL, "", ""),
	"url" => array(NULL, "", ""),
	"/url" => array("</a>", NULL, ""),
	"link" => array(NULL, "", ""),
	"/link" => array("</a>", NULL, ""),
	"email" => array("<a class=\"s7\" href=\"mailto:%s\">", "", ""),
	"/email" => array("</a>", NULL, ""),
	"user" => array("<a class=\"s7\" href=\"view_user.php?uid=%s\" target=_blank>", "", ""),
	"/user" => array("</a>", NULL, ""),
	"article" => array(NULL, "", ""),
	"/article" => array("</a>", NULL, ""),
	"marquee" => array("<marquee %s>", "", ""),
	"/marquee" => array("</marquee>", NULL, ""),
	"image" => array("<img src=\"%s\" border=0>", "", "%s"),
	"flash" => array("<a class=\"s7\" href=\"%s\" target=_blank>View Flash</a>", "", ""),
	"bwf" => array("<span style=\"color: red\">****</span>", "", "****"),
);

function lml_tag_filter(string $tag_name, string | null $tag_arg, bool $quote_mode) : string
{
	global $BBS_theme_current;
	global $lml_tag_disabled;
	global $lml_tag_quote_level;
	global $lml_tag_quote_color;

	$tag_result = "";

	switch ($tag_name)
	{
		case "plain":
			$lml_tag_disabled = true;
			$tag_result = ($quote_mode ? "[plain]" : "");
			break;
		case "link":
		case "url":
			if (preg_match("/script:/i", $tag_arg)) // Filter milicious code
			{
				$tag_arg = "#";
			}
			$tag_result = "<a class=\"s7\" href=\"" . $tag_arg . "\" target=_blank>";
			break;
		case "article":
			$tag_result = "<a class=\"s7\" href=\"/bbs/view_article.php?tn=" .
							(isset($BBS_theme_current) ? $BBS_theme_current : "") . "&trash=1&id=" . intval($tag_arg) .
							"#"  . intval($tag_arg) . "\" target=_blank>";
			break;
		case "color":
			$tag_result = ($quote_mode ? "" : "<span style=\"color: " . $tag_arg . "\">");
			break;
		case "/color":
			$tag_result = ($quote_mode ? "" : "</span>");
			break;
		case "quote":
			$lml_tag_quote_level++;
			$tag_result = "<span style=\"color: " . $lml_tag_quote_color[$lml_tag_quote_level % count($lml_tag_quote_color)] . "\">";
			break;
		case "/quote":
			if ($lml_tag_quote_level > 0)
			{
				$lml_tag_quote_level--;
				$tag_result = "</span>";
			}
			break;
		case "size":
			$tag_result = "<span style=\"font-size: " .
				(is_numeric($tag_arg) ? intval($tag_arg * 4) . "px" : $tag_arg) . "\">";
			break;
	}

	return $tag_result;
}

function LML(string | null $str_in, int $width = 80, bool $quote_mode = false, bool $html_trans = true) : string
{
	//$width		length of line, 0 means unlimited
	//$quote_mode	whether output text is used as quoted content in text editor

	global $lml_total_exec_duration;
	global $lml_tag_disabled;
	global $lml_tag_quote_level;
	global $lml_tag_quote_color;
	global $lml_tag_ansi_color;
	global $lml_tag_def;

	$time_start = microtime(true);

	if ($str_in == null)
	{
		$str_in = "";
	}

	$str_in_len = strlen($str_in);

	$str_out = "";

	$tag_start_pos = -1;
	$tag_name_pos = -1;
	$tag_end_pos = -1;
	$tag_param_pos = -1;
	$new_line = true;
	$fb_quote_level = 0;
	$tag_name_found = false;

	$lml_tag_disabled = false;
	$lml_tag_quote_level = 0;

	$line_width = 0;

	if ($width <= 0)
	{
		$width = PHP_INT_MAX;
	}

	for ($i = 0; $i < $str_in_len && $str_in[$i] != "\0"; $i++)
	{
		if (!$lml_tag_disabled && $new_line)
		{
			$fb_quote_level_last = $fb_quote_level;

			while (substr($str_in, $i, 2) == ": ") // FB2000 quote leading str
			{
				$fb_quote_level++;
				$lml_tag_quote_level++;
				$i += 2;
			}

			if (!$quote_mode && $lml_tag_quote_level > 0 && $fb_quote_level != $fb_quote_level_last)
			{
				$tag_output_buf = lml_tag_filter("color", $lml_tag_quote_color[$lml_tag_quote_level % count($lml_tag_quote_color)], $quote_mode);
				$str_out .= $tag_output_buf;
			}

			for ($k = 0; $k < $fb_quote_level; $k++)
			{
				$str_out .= ": ";
				$line_width += 2;
			}

			$new_line = false;
			$i--; // redo at current $i
			continue;
		}

		if ($lml_tag_disabled && $new_line)
		{
			$new_line = false;
		}
		
		if (!$quote_mode && !$lml_tag_disabled && $str_in[$i] == "\033" && $i + 1 < $str_in_len && $str_in[$i + 1] == "[") // Escape sequence
		{
			$valid_ansi_color = false;
			$highlight = false;
			$fg_color = 0;
			$bg_color = 0;

			$ansi_color = 0;
			for ($k = $i + 2;
				$k < $str_in_len && (ctype_digit($str_in[$k]) || $str_in[$k] == ";" || $str_in[$k] == "?" || $str_in[$k] == "m");
				$k++)
			{
				if ($str_in[$k] == ";" || $str_in[$k] == "m")
				{
					if ($ansi_color >= 30 && $ansi_color <= 37) // valid FG color
					{
						$fg_color = $ansi_color;
					}
					if ($ansi_color >= 40 && $ansi_color <= 47) // valid BG color
					{
						$bg_color = $ansi_color;
					}
					else if ($ansi_color == 0 || $ansi_color == 1) // highlight
					{
						$highlight = ($ansi_color == 1);
					}
					$ansi_color = 0;
				}
				else if (ctype_digit($str_in[$k]))
				{
					$ansi_color = $ansi_color * 10 + (ord($str_in[$k]) - ord("0"));
				}

				if ($str_in[$k] == "m")
				{
					break;
				}
			}

			if ($k < $str_in_len && $str_in[$k] == "m") // valid
			{
				if ($fg_color > 0)
				{
					$tag_output_buf = lml_tag_filter("color", $lml_tag_ansi_color[$fg_color], $quote_mode);
					$str_out .= $tag_output_buf;
				}
				else if ($bg_color > 0)
				{
					// ignore BG color
				}
				else // reset
				{
					$tag_output_buf = lml_tag_filter("/color", "", $quote_mode);
					$str_out .= $tag_output_buf;
				}
			}
			else if ($k < $str_in_len && ctype_alpha($str_in[$k]))
			{
				// unsupported ANSI CSI command
			}
			else
			{
				$k--;
			}

			$i = $k;
			continue;
		}

		if ($str_in[$i] == "\n") // jump out of tag at end of line
		{
			if (!$lml_tag_disabled && $tag_start_pos != -1) // tag is not closed
			{
				$tag_end_pos = $i - 1;
				$tag_output_len = $tag_end_pos - $tag_start_pos + 1;

				if ($line_width + $tag_output_len > $width)
				{
					$str_out .= "\n";
					$new_line = true;
					$line_width = 0;
					$i--; // redo at current $i
				}
				else
				{
					$str_out .= substr($str_in, $tag_start_pos, $tag_output_len);
					$line_width += $tag_output_len;
				}
			}

			if (!$lml_tag_disabled && $fb_quote_level > 0)
			{
				$lml_tag_quote_level -= $fb_quote_level;

				$tag_output_buf = lml_tag_filter("/color", "", $quote_mode);
				$str_out .= $tag_output_buf;

				$fb_quote_level = 0;
			}

			if ($new_line)
			{
				continue;
			}

			$tag_start_pos = -1;
			$tag_name_pos = -1;
			$new_line = true;
			$line_width = -1;
		}
		else if ($str_in[$i] == "\r" || $str_in[$i] == "\7")
		{
			continue; // Skip special characters
		}

		if (!$lml_tag_disabled && $str_in[$i] == "[")
		{
			if ($tag_start_pos != -1) // tag is not closed
			{
				$tag_end_pos = $i - 1;
				$tag_output_len = $tag_end_pos - $tag_start_pos + 1;
				$str_out .= substr($str_in, $tag_start_pos, $tag_output_len);
				$line_width += $tag_output_len;
			}

			$tag_start_pos = $i;
			$tag_name_pos = $i + 1;
		}
		else if (!$lml_tag_disabled && $str_in[$i] == "]" && $tag_name_pos >= 0)
		{
			$tag_end_pos = $i;

			// Skip space characters
			while ($tag_name_pos < $str_in_len && $str_in[$tag_name_pos] == " ")
			{
				$tag_name_pos++;
			}

			$k = $tag_name_pos;
			while ($k < $tag_end_pos && $k < $str_in_len && $str_in[$k] != " ")
			{
				$k++;
			}

			$tag_name = strtolower(substr($str_in, $tag_name_pos, $k - $tag_name_pos));

			if (isset($lml_tag_def[$tag_name]))
			{
				$tag_param_pos = -1;
				$tag_param_buf = "";

				if ($str_in[$k] == " ")
				{
					$tag_param_pos = $k + 1;
					while ($tag_param_pos < $str_in_len && $str_in[$tag_param_pos] == " ")
					{
						$tag_param_pos++;
					}
					$tag_param_buf = substr($str_in, $tag_param_pos, $tag_end_pos - $tag_param_pos);
					$tag_param_buf = htmlspecialchars($tag_param_buf, ENT_QUOTES | ENT_HTML401, 'UTF-8');
				}

				if ($str_in[$k] == " " || $str_in[$k] == "]")
				{
					if ($tag_param_pos == -1 &&
						$lml_tag_def[$tag_name][0] !== NULL &&
						$lml_tag_def[$tag_name][1] !== NULL) // Apply default param if not defined
					{
						$tag_param_buf = $lml_tag_def[$tag_name][1];
					}
					if (!$quote_mode)
					{
						if ($lml_tag_def[$tag_name][0] !== NULL)
						{
							$tag_output_buf = sprintf($lml_tag_def[$tag_name][0], $tag_param_buf);
						}
						else
						{
							$tag_output_buf = lml_tag_filter($tag_name, $tag_param_buf, false);
						}

						$str_out .= $tag_output_buf;
						// No change to $line_width becasue LML tag output as HTML tag should be 0-width
					}
					else // if ($quote_mode)
					{
						if ($lml_tag_def[$tag_name][2] !== NULL)
						{
							$tag_output_buf = sprintf($lml_tag_def[$tag_name][2], $tag_param_buf);
						}
						else
						{
							$tag_output_buf = lml_tag_filter($tag_name, $tag_param_buf, true);
						}

						$tag_output_len = strlen($tag_output_buf);

						if ($line_width + $tag_output_len > $width)
						{
							$str_out .= "\n";
							$new_line = true;
							$line_width = 0;
							$i--; // redo at current $i
							continue;
						}

						$str_out .= $tag_output_buf;
						$line_width += $tag_output_len; // Add width of special tags, [plain] [left] [right]
					}
				}
			}
			else // undefined tag
			{
				if ($line_width + 1 > $width)
				{
					$str_out .= "\n";
					$new_line = true;
					$line_width = 0;
					$i--; // redo at current $i
					continue;
				}

				$str_out .= "[";
				$line_width++;
				$i = $tag_start_pos; // restart from $tag_start_pos + 1
				$tag_start_pos = -1;
				$tag_name_pos = -1;
				continue;
			}

			$tag_start_pos = -1;
			$tag_name_pos = -1;
		}
		else if ($lml_tag_disabled || $tag_name_pos == -1) // not in LML tag
		{
			$c = $str_in[$i];
			$v = ord($c);

			if ($line_width + ($v & 0x80 ? 2 : 1) > $width)
			{
				$str_out .= "\n";
				$new_line = true;
				$line_width = 0;
				$i--; // redo at current $i
				continue;
			}

			if ($v & 0x80) // head of multi-byte character
			{
				$v = ($v & 0x70) << 1;
				while ($v & 0x80)
				{
					$i++;
					if ($i >= $str_in_len)
					{
						break;
					}
					$c .= $str_in[$i];
					$v = ($v & 0x7f) << 1;
				}
				$line_width++;
			}
			else if ($html_trans)
			{
				$c = htmlspecialchars($c, ENT_QUOTES | ENT_HTML401, 'UTF-8');
			}

			$str_out .= $c;
			$line_width++;
		}
		else // in LML tag
		{
			// Do nothing
		}
	}

	if (!$lml_tag_disabled && $tag_start_pos != -1) // tag is not closed
	{
		$tag_end_pos = $i - 1;
		$tag_output_len = $tag_end_pos - $tag_start_pos + 1;
		$str_out .= substr($str_in, $tag_start_pos, $tag_output_len);
		$line_width += $tag_output_len;
	}

	if (!$quote_mode && !$lml_tag_disabled && $lml_tag_quote_level > 0)
	{
		$tag_output_buf = lml_tag_filter("/quote", "", $quote_mode);
		$str_out .= $tag_output_buf;
	}

	$time_end = microtime(true);
	$lml_total_exec_duration += ($time_end - $time_start);

	return $str_out;
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
		$str_out = LML($str_in, 80, false);
		echo ("Input(len=" . strlen($str_in) . "): " . $str_in . "\nOutput(len=" . strlen($str_out) . "): " . $str_out . "\n");
	}
	printf("Test #1: Done\n\n");

	echo ("Test #2\n");
	foreach($test_str_in as $str_in)
	{
		$str_out = LML($str_in, 80, true);
		echo ("Input(len=" . strlen($str_in) . "): " . $str_in . "\nOutput(len=" . strlen($str_out) . "): " . $str_out . "\n");
	}
	printf("Test #2: Done\n\n");
}

if (isset($_SERVER["argv"][1]) && $_SERVER["argv"][1] == "test")
{
	lml_test();

	$page_exec_duration = round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000, 2);
	$lml_exec_duration = round($lml_total_exec_duration * 1000, 2);

	echo "\npage_exec_duration=$page_exec_duration, lml_exec_duration=$lml_exec_duration\n";
}

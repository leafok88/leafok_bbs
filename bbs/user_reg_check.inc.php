<?php
function check_str($str) : bool
{
	$filename = "../conf/deny_reg.conf";
	$contents = file_get_contents($filename);

	if ($contents == false)
	{
		echo ("Reversed words list not exist!\n");
		return false;
	}

	// Builds the reserved words array
	$word_list = explode("\n", str_replace("\r\n", "\n", $contents));

	// Do the checking
	foreach ($word_list as $reg_exp)
	{
	    if ($reg_exp != "" && preg_match("/" . $reg_exp . "/i", $str))
	    {
			return false;
		}
	}

	return true;
}
?>

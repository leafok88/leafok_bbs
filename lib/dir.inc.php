<?php
	function delTree($dir)
	{
		$files = array_diff(scandir($dir), array('.', '..'));
	
		foreach ($files as $file)
		{
			if (is_dir("$dir/$file"))
			{
				delTree("$dir/$file");
			}
			else
			{
				unlink("$dir/$file");
			}
		}
	
		return rmdir($dir);
	}
?>

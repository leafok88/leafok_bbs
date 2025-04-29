<?php
	require_once "../lib/vn_gif.inc.php";
	require_once "./session_init.inc.php";

	$_SESSION["BBS_vn_str"] = VN_gen_str(4);
	VN_gif_display($_SESSION["BBS_vn_str"]);

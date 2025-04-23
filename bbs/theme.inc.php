<?php
if (!defined("_BBS_THEME_INIT_"))
{
	define("_BBS_THEME_INIT_",1);

	$BBS_theme_set = array(
		"default" => array(
			"css/default" => "css/default.css",
			"js/lml_assistant" => "js/lml_assistant.js",
			"view/list" => "list.view.php",
			"view/post" => "post.view.php",
			"view/view_article" => "view_article.view.php",
			"view/search_article" => "search_article.view.php",
			"view/search_user" => "search_user.view.php",
			"view/member_service_guide" => "member_service_guide.view.php",
			"view/update_profile" => "update_profile.view.php",
			"view/preference" => "preference.view.php",
			"view/s_favor" => "s_favor.view.php",
			"view/section_setting" => "section_setting.view.php",
			"view/view_user" => "view_user.view.php",
			"view/score_detail" => "score_detail.view.php",
			"view/suicide" => "suicide.view.php",
			"view/msg_read" => "msg_read.view.php",
		),
		"xml" => array(
			"xsl/1" => "xsl/1.xsl",
			"view/view_article" => "view_article_xml.view.php",
		),
		"gen_ex" => array(
			"view/view_article" => "gen_ex_article.view.php",
		),
		"portal" => array(
			"css/default" => "../www/css/default.css",
			"view/view_article" => "view_article.view.php",
		),
	);

	$BBS_theme_current = "";

	function get_theme_file(string $view_name, string $theme_name = "") : string | null
	{
		global $BBS_theme_set;
		global $BBS_theme_current;

		if ($theme_name == "")
		{
			$theme_name = $BBS_theme_current; // Use current selected theme
		}

		if (!isset($BBS_theme_set[$theme_name]) || !isset($BBS_theme_set[$theme_name][$view_name]))
		{
			$theme_name = "default"; // fallback
		}

		$BBS_theme_current = $theme_name; // Remember current theme for later use

		if (!isset($BBS_theme_set[$theme_name][$view_name]))
		{
			return null; // View not exist
		}

		$file = "../bbs/themes/" . $theme_name . "/" . $BBS_theme_set[$theme_name][$view_name];

		if (!file_exists($file))
		{
			return $BBS_theme_set[$theme_name][$view_name]; // fallback file without theme
		}

		return $file;
	}
}
?>

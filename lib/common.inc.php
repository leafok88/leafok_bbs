<?php
	//Definition of const
	$BBS_sys_uid				=	600;
	$BBS_notice_sid				=	39;
	$BBS_default_cid			=	1;
	$BBS_default_sid			=	4;
	$BBS_name					=	"枫林在线";
	$BBS_host_name				=	"www.FengLin.info";
	$BBS_license_dt				=	"2025-03-02 10:00:00";
	$BBS_copyright_duration		=	"2001-2025";

	$BBS_max_user_per_email		=	3;

	$BBS_session_lifetime		=	60 * 60 * 24 * 7;
	$BBS_user_off_line			=	60 * 15;
	$BBS_keep_alive_interval	= 	60;
	$BBS_check_msg_interval		= 	30;

	$BBS_list_rpp_options		=	array(
		10,
		20,
		30,
		50,
	);

	$BBS_view_rpp_options		=	array(
		1,
		5,
		10,
		20,
	);

	$BBS_msg_rpp_options		=	array(
		5,
		10,
		20,
		30,
	);

	$BBS_exp					= array(
		PHP_INT_MIN,	// 0
		50,				// 1
		200,			// 2
		500,			// 3
		1000,			// 4
		2000,			// 5
		5000,			// 6
		10000,			// 7
		20000,			// 8
		30000,			// 9
		50000,			// 10
		60000,			// 11
		70000,			// 12
		80000,			// 13
		90000,			// 14
		100000,			// 15
		PHP_INT_MAX,	// 16
	);

	$BBS_level					= array(
		"新手上路",		// 0
		"初来乍练",		// 1
		"白手起家",		// 2
		"略懂一二",		// 3
		"小有作为",		// 4
		"对答如流",		// 5
		"精于此道",		// 6
		"博大精深",		// 7
		"登峰造极",		// 8
		"论坛砥柱",		// 9
		"☆☆☆☆☆",	// 10
		"★☆☆☆☆",	// 11
		"★★☆☆☆",	// 12
		"★★★☆☆",	// 13
		"★★★★☆",	// 14
		"★★★★★",	// 15
	);

	$BBS_life_immortal			= array(
		333,
		365,
		666,
		999,
	);

	$BBS_emoji_count			=	57;
	$BBS_upload_count_limit		=	3;
	$BBS_upload_size_limit		=	2; // MB

	$BBS_exp_score_rate			=	100;
	$BBS_score_transfer_fee		=	0.1; // 10%
	$BBS_nickname_change_fee	=	2;

	$BBS_stat_gen_interval		= 	60 * 60 * 8; // 8 hours

	$BBS_user_purge_duration	=	7;
	$BBS_article_purge_duration	=	180;
	$BBS_normal_log_retention	=	60;
	$BBS_critical_log_retention	=	365;
	$BBS_user_msg_retention		=	60;
	$BBS_new_article_period		=	365 * 40; // 40 years

	// PHP CLI path
	$PHP_bin					=	"php";

	// Keep this consistent with $DB_session_timezone in db_open.conf.php
	$BBS_timezone				=	'Asia/Shanghai';
	$BBS_runtime_tz				=	new DateTimeZone($BBS_timezone);

	// Assume DateTime fields retrieved from DB are in $BBS_runtime_tz timezone
	date_default_timezone_set($BBS_timezone);
?>

<?
//Log article operation into table bbs_article_log
function article_op_log(int $aid, int $uid, string $op_type, string $ip_addr, mysqli $db_conn = null) : mysqli_result | bool
{
	/*
		Type	Description
		A		Add article
		D		Delete article
		X		Delete article by Admin
		S		Restore article
		L		Lock article
		U		Unlock article
		M		Modify article
		T		Move article
		E		Set article as excerption
		O		Unset article as excerption
		F		Set article on top
		V		Unset article on top
		Z		Set article as trnasship
	*/

	$sql = "INSERT INTO bbs_article_op(AID, UID, type, op_dt, op_ip)
			VALUES($aid, $uid, '$op_type', NOW(), '$ip_addr')";

	$ret = mysqli_query($db_conn, $sql);

	return $ret;
}

//Add/Subtract user exp
function user_exp_change(int $uid, int $exp_change, mysqli $db_conn = null) : mysqli_result | bool
{
	$sql = "UPDATE user_pubinfo SET exp = exp + $exp_change WHERE UID = $uid";

	$ret = mysqli_query($db_conn, $sql);

	return $ret;
}

//Display error message in a pop-up window
function error_msg($msg, $back=false, $close=false)
{
	$ret = "<script language=\"JavaScript\">\n".
		"	alert(\"".str_replace("\n","\\\n",$msg)."\");\n".
		($back?"	history.go(-1);\n":"").
		($close?"	self.close();\n":"").
		"</script>";
	
	echo $ret;
}

?>

<?php
	require_once "../conf/smtp.conf.php";
	require_once "../lib/common.inc.php";
	require_once "Mail.php";
?>
<?php
function send_mail_do($db_conn)
{
	global $Mail_Type;
	global $Mail_Server;
	global $Mail_Server_Port;
	global $Mail_Auth;
	global $Mail_User;
	global $Mail_Pass;
	global $Mail_Sender;

	$MailFactory = new Mail();

	switch ($Mail_Type)
	{
		case "sendmail":
			$params['sendmail_path'] = '/usr/lib/sendmail';
			$Mail = $MailFactory->factory ("sendmail", $params);
			break;
		case "smtp":
			$params['host'] = $Mail_Server;
			$params['port'] = $Mail_Server_Port;
			$params['auth'] = $Mail_Auth;
			$params['username'] = $Mail_User;
			$params['password'] = $Mail_Pass;
			$Mail = $MailFactory->factory ("smtp", $params);
			break;
		default:
			$MailFactory = NULL;
			return -1;
	}

	$sql = "SELECT id, fromemail, fromname, toemail, toname, subject, body
			FROM email WHERE complete = 0 ORDER BY id DESC FOR UPDATE";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		echo "Query database failed: " . mysqli_error($db_conn);
		return -2;
	}

	while ($row = mysqli_fetch_array($rs))
	{
		$recipients = $row["toemail"];

        $headers = array(
            "Content-Type"				=> "text/plain; charset=\"utf-8\"",
			"Content-Transfer-Encoding"	=> "base64",
		    "From"						=> "\"=?UTF-8?B?" . base64_encode($row["fromname"]) . "?=\" <" . $row["fromemail"] . ">",
		    "To"						=> "\"=?UTF-8?B?" . base64_encode($row["toname"]) . "?=\" <" . $row["toemail"] . ">",
		    "Subject"					=> "=?UTF-8?B?" . base64_encode($row["subject"]) . "?="
		    );

		$body = base64_encode($row["body"]);

		$result = $Mail->send($recipients, $headers, $body);

		$sql = "UPDATE email SET send_dt = NOW(), complete = 1, error = " .
			(PEAR::isError($result) ? "1" : "0") . ", error_msg = '" .
			mysqli_real_escape_string($db_conn, (PEAR::isError($result) ? $result->getMessage() : "")) .
			"' WHERE id = " . $row["id"];

		$rs_mail = mysqli_query($db_conn, $sql);
		if ($rs_mail == false)
		{
			echo "Update database failed: " . mysqli_error($db_conn);
			return -3;
		}

		unset($recipients);
		unset($headers);
		unset($body);
	}

	mysqli_free_result($rs);

	$Mail = NULL;
	$MailFactory = NULL;

	return 0;
}

function send_mail($from, $fromname, $to, $toname, $subject, $body, $db_conn)
{
	global $Mail_Sender;

	//Default <from> email address
	if ($from == "")
	{
		$from = $Mail_Sender;
		$no_reply = true;
	}
	else
	{
		$no_reply = false;
	}

	$body .= ($no_reply ? "\n（本邮件来自一个无人值守的信箱，请不要回复本邮件。）\n" : "");

	$sql = "INSERT INTO email(fromemail, fromname, toemail, toname, subject, body, set_dt) VALUES('" . 
			mysqli_real_escape_string($db_conn, $from) . "', '" .
			mysqli_real_escape_string($db_conn, $fromname) . "', '" .
			mysqli_real_escape_string($db_conn, $to) . "', '" .
			mysqli_real_escape_string($db_conn, $toname) . "', '" .
			mysqli_real_escape_string($db_conn, $subject) . "', '" .
			mysqli_real_escape_string($db_conn, $body) . "', NOW())";
	
	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		//echo "Add mail failed: " . mysqli_error($db_conn);
		return false;
	}

	if (send_mail_do($db_conn) < 0)
	{
		return false;
	}

	return true;
}
?>

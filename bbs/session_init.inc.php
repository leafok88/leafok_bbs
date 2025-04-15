<?
require_once "../lib/db_open.inc.php";
require_once "../lib/common.inc.php";
require_once "../lib/client_addr.inc.php";
require_once "../bbs/user_login.inc.php";

if (!defined("_BBS_SESSION_INIT_"))
{
	define("_BBS_SESSION_INIT_", 1);

	function force_login($msg = 1)
	{
		if (!isset($_SESSION["BBS_uid"]) || $_SESSION["BBS_uid"] == 0)
		{
			$redir = $_SERVER["SCRIPT_NAME"] .
				(isset($_SERVER["QUERY_STRING"]) ? "?" . $_SERVER["QUERY_STRING"] : "");
			header ("Location: ../bbs/index.php?msg=$msg&redir=" . urlencode($redir));
			exit();
		}
	}

	function keep_alive($db_conn)
	{
		$sql = "SELECT current_action FROM user_online WHERE SID='" .
				session_id() . "'";
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			return false;
		}

		if ($row = mysqli_fetch_array($rs))
		{
			switch($row["current_action"])
			{
				case "exit":
					$sql = "DELETE FROM user_online WHERE SID='" .
							session_id() . "'";
					mysqli_query($db_conn, $sql);
						
					session_unset();
					session_destroy();
					force_login(2);
					break;
				case "reload":
					load_user_info($_SESSION["BBS_uid"], $db_conn);
					break;
			}
		}
		else
		{
			session_unset();
			session_destroy();
			force_login(3);
		}
		mysqli_free_result($rs);

		//Update user_online status
		$sql = "UPDATE user_online SET UID = " . $_SESSION["BBS_uid"] .
				", ip = '" . client_addr() . "', current_action = '', login_tm = '".
				date("Y-m-d H:i:s", $_SESSION["BBS_login_tm"]) . "', last_tm = NOW() " .
				"WHERE SID = '" . session_id() . "'";
		
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			return false;
		}

		$_SESSION["BBS_last_action_tm"] = time();

		return true;
	}

	// Log begin time
	$time_start = microtime(true);

	// Initiate Session
	session_name("BBS");
	session_set_cookie_params($BBS_session_lifetime, "/");
	session_cache_limiter("nocache");
	session_start();
	setcookie(session_name(), session_id(), time() + $BBS_session_lifetime, "/");

	if (!isset($_SESSION["BBS_uid"]))
	{
		$_SESSION["BBS_uid"] = 0;
		$_SESSION["BBS_priv"] = new user_priv(0, $db_conn);
		$_SESSION["BBS_username"] = "";
		$_SESSION["BBS_user_tz"] = new DateTimeZone($BBS_timezone);
		$_SESSION["BBS_theme_name"] = "default";
		$_SESSION["BBS_login_tm"] = time();
		$_SESSION["BBS_last_action_tm"] = time();
		$_SESSION["BBS_last_sub_tm"] = 0;
		$_SESSION["BBS_last_msg_check"] = 0;
		$_SESSION["BBS_new_msg"] = 0;

		// Recover session from user_online table
		$sql = "SELECT user_online.UID, user_list.username, login_tm, last_tm
				FROM user_online INNER JOIN user_list ON user_online.UID = user_list.UID
				WHERE SID = '" . session_id() . "'";
		$rs = mysqli_query($db_conn, $sql);
		if ($rs == false)
		{
			echo ("Query user_online error: " . mysqli_error($db_conn));
			exit();
		}

		if ($row = mysqli_fetch_array($rs))
		{
			//Load User Information
			if (load_user_info($row["UID"], $db_conn) == 0)
			{
				$_SESSION["BBS_uid"] = intval($row["UID"]);
				$_SESSION["BBS_username"] = $row["username"];
				$_SESSION["BBS_login_tm"] = strtotime($row["login_tm"]);
				$_SESSION["BBS_last_action_tm"] = strtotime($row["last_tm"]);
			}
		}
		mysqli_free_result($rs);

		if (client_addr() != "")
		{
			$sql = "INSERT INTO visit_log(dt, IP) VALUES(NOW(), '" . client_addr() . "')";
			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				echo ("Add visit log error: " . mysqli_error($db_conn));
				exit();
			}

			$sql = "DELETE FROM user_online WHERE SID = '" . session_id() . "'";
			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				echo ("Delete online user error: " . mysqli_error($db_conn));
				exit();
			}
		
			$sql = "INSERT INTO user_online(SID, UID, ip, login_tm, last_tm) VALUES('" .
					session_id() . "', " . $_SESSION["BBS_uid"] . ", '" .
					client_addr() . "', NOW(), NOW())";
			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				echo ("Add online user error: " . mysqli_error($db_conn));
				exit();
			}
		}
	}

	//Keep alive
	if (time() - $_SESSION["BBS_last_action_tm"] >= $BBS_keep_alive_interval)
	{
		if (!keep_alive($db_conn))
		{
			exit();
		}
	}
}
?>

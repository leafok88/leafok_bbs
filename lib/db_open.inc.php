<?
	include "../conf/db_conn.conf.php";

	function db_open($sql_charset = "utf8")
	{
		global $DB_hostname, $DB_username, $DB_password, $DB_database, $DB_session_timezone;

		mysqli_report(MYSQLI_REPORT_OFF);

		$mysqli = @new mysqli($DB_hostname, $DB_username, $DB_password, $DB_database);

		if ($mysqli->connect_errno)
		{
			echo ("Mysqli connection error: " . $mysqli->connect_error);
			return NULL;
		}

		$mysqli->set_charset($sql_charset);
		if ($mysqli->errno) {
			echo ("Mysqli error: " . $mysqli->error . "\n");
		}

		if (isset($DB_session_timezone))
		{
			$mysqli->query("SET time_zone = '" . $DB_session_timezone . "'");
			if ($mysqli->errno) {
				echo ("Mysqli error: " . $mysqli->error . "\n");
			}
		}

		return $mysqli;
	}

// Begin of PHP 7.x compatiblility

if(!function_exists('mysql_connect')){
	$dbname = "";

	function mysql_connect($dbhost, $dbuser, $dbpass){
		global $dbname;
		global $db_conn;
		mysqli_report(MYSQLI_REPORT_OFF);
		$db_conn = @new mysqli($dbhost, $dbuser, $dbpass, $dbname);
		if ($db_conn->connect_errno) {
			echo ("Mysqli connection error: " . $db_conn->connect_error);
			return NULL;
		}
		return $db_conn;
	}
	function mysql_select_db($dbname){
		global $db_conn;
		return mysqli_select_db($db_conn,$dbname);
	}
	function mysql_fetch_array($result){
		return mysqli_fetch_array($result);
	}
	function mysql_fetch_assoc($result){
		return mysqli_fetch_assoc($result);
	}
	function mysql_fetch_row($result){
		return mysqli_fetch_row($result);
	}
	function mysql_query($query){
		global $db_conn;
		return mysqli_query($db_conn,$query);
	}
	function mysql_free_result($result){
		return mysqli_free_result($result);
	}
	function mysql_num_rows($result){
		return mysqli_num_rows($result);
	}
	function mysql_insert_id(){
		global $db_conn;
		return mysqli_insert_id($db_conn);
	}
	function mysql_affected_rows(){
		global $db_conn;
		return mysqli_affected_rows($db_conn);
	}
	function mysql_escape_string($data){
		global $db_conn;
		return mysqli_real_escape_string($db_conn, $data);
	}
	function mysql_real_escape_string($data){
		global $db_conn;
		return mysqli_real_escape_string($db_conn, $data);
	}
	function mysql_close(){
		global $db_conn;
		return mysqli_close($db_conn);
	}
}

// End of PHP 7.x compatiblility

if (!isset($db_conn))
{
	$db_conn = db_open();

	if ($db_conn == NULL)
	{
		exit();
	}
}
?>

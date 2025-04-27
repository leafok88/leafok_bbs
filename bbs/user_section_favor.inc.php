<?php
if (!isset($_BBS_S_FAVOR_CLASS_INIT_))
{
	$_BBS_S_FAVOR_CLASS_INIT_=1;

	class section_favorite
	{
		var $s_list;
		var $s_count;

		function __construct($uid = 0, $db_conn = NULL)
		{
			$this->load_s_favor($uid, $db_conn);
		}

		function load_s_favor($uid, $db_conn)
		{
			$this->s_list = array();

			if ($uid == 0)
			{
				return 0;
			}

			if ($db_conn == NULL)
			{
				return -1;
			}

			$rs = mysqli_query($db_conn, "SELECT SID FROM section_favorite WHERE UID = $uid");
			if ($rs == false)
			{
				return -2;
			}
			while ($row = mysqli_fetch_array($rs))
			{
				array_push($this->s_list, $row["SID"]);
			}
			mysqli_free_result($rs);

			return 0;
		}

		function save_s_favor($uid, $db_conn)
		{
			if ($db_conn == NULL)
			{
				return -1;
			}

			if (mysqli_query($db_conn, "DELETE FROM section_favorite WHERE UID = $uid") == false)
			{
				return -2;
			}

			foreach ($this->s_list as $sid)
			{
				if (mysqli_query($db_conn, "INSERT INTO section_favorite(UID, SID) VALUES($uid, $sid)") == false)
				{
					return -3;
				}
			}

			return 0;
		}

		function is_in($sid)
		{
			return in_array($sid, $this->s_list);
		}
	}
}
?>

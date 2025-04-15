<?
if (!isset($_BBS_PRIV_CLASS_INIT_))
{
	$_BBS_PRIV_CLASS_INIT_=1;

	define("S_NONE",0x0);
	define("S_LIST",0x1);
	define("S_GETEXP",0x2);
	define("S_POST",0x4);
	define("S_MSG",0x8);
	define("S_MAN_S",0x20);
	define("S_MAN_M",0x60);	//(0x40 | 0x20)
	define("S_ADMIN",0xe0);	//(0x80 | 0x40 | 0x20)
	define("S_ALL",0xff);
	define("S_DEFAULT",0x3);	//0x1 | 0x2
	
	define("P_GUEST",0x0);	//游客
	define("P_USER",0x1);	//普通用户
//	define("P_AUTH_USER",0x2);	// Reserved
	define("P_MAN_S",0x4);	//副版主
	define("P_MAN_M",0x8);	//正版主
//	define("P_MAN_C",0x10);	// Reserved
	define("P_ADMIN_S",0x20);	//副系统管理员
	define("P_ADMIN_M",0x40);	//主系统管理员

	class user_priv
	{
		var $uid;
		var $level;
		var $g_priv;
		var $s_priv_list;
		
		function __construct($uid = 0, $db_conn = NULL)
		{
			$this->loadpriv($uid, $db_conn);
		}

		function checklevel($level)
		{
			return (($this->level & $level) ? true : false);
		}
		
		function setpriv($sid, $priv)
		{
			if ($sid > 0)
				$this->s_priv_list[$sid] = $priv;
			else
				$this->g_priv = $priv;
		}
		
		function getpriv($sid = 0)
		{
			if (isset($this->s_priv_list[$sid]))
				return $this->s_priv_list[$sid];
			else
				return ($sid >= 0 ? $this->g_priv : S_NONE);
		}
		
		function checkpriv($sid, $priv)
		{
			return (($this->getpriv($sid) & $priv) == $priv);
		}
		
		function loadpriv($uid = 0, $db_conn = NULL)
		{
			$this->uid = $uid;
			$this->level = ($uid == 0 ? P_GUEST : P_USER);
			$this->g_priv = S_DEFAULT;
			$this->s_priv_list = array();

			if ($db_conn == NULL)
			{
				return 1;
			}

			//Permission
			$sql = "SELECT p_post, p_msg FROM user_list WHERE UID = $uid AND verified";
			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				echo ("Query user list error: " . mysqli_error($db_conn));
				return 2;
			}
			if($row = mysqli_fetch_array($rs))
			{
				$this->g_priv |= ($row["p_post"] ? S_POST : 0);
				$this->g_priv |= ($row["p_msg"] ? S_MSG : 0);
			}
			mysqli_free_result($rs);

			//Admin
			$sql = "SELECT aid, major FROM admin_config WHERE UID = $uid
					AND enable AND (NOW() BETWEEN begin_dt AND end_dt)";
			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				echo ("Query admin info error: " . mysqli_error($db_conn));
				return 2;
			}
			
			if ($row = mysqli_fetch_array($rs))
			{
				$this->level |= ($row["major"] ? P_ADMIN_M : P_ADMIN_S);
				$this->g_priv |= ($row["major"] ? S_ALL : S_ADMIN);
			}
			mysqli_free_result($rs);
	
			//Section Master
			$sql = "SELECT section_master.SID, major FROM section_master
					INNER JOIN section_config ON section_master.SID = section_config.SID
					WHERE UID = $uid AND section_master.enable AND section_config.enable
					AND (NOW() BETWEEN begin_dt AND end_dt)";
			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				echo ("Query master info error: " . mysqli_error($db_conn));
				return 2;
			}
			while ($row = mysqli_fetch_array($rs))
			{
				$this->level |= ($row["major"] ? P_MAN_M : P_MAN_S);
				$this->setpriv($row["SID"], $this->getpriv($row["SID"])
					| ($row["major"] ? S_MAN_M : S_MAN_S));
			}
			mysqli_free_result($rs);

			//Section status
			$sql = "SELECT SID, exp_get, read_user_level, write_user_level FROM section_config
					INNER JOIN section_class ON section_config.CID = section_class.CID
					WHERE section_config.enable AND section_class.enable
					ORDER BY SID";
			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				echo ("Query section info error: " . mysqli_error($db_conn));
				return 2;
			}
			while ($row = mysqli_fetch_array($rs))
			{
				$priv = $this->getpriv($row["SID"]);
				if ($this->level < $row["read_user_level"])
				{
					$priv &= (~S_LIST);
				}
				if ($this->level < $row["write_user_level"])
				{
					$priv &= (~S_POST);
				}
				if (!$row["exp_get"])
				{
					$priv &= (~S_GETEXP);
				}
				$this->setpriv($row["SID"], $priv);
			}
			mysqli_free_result($rs);

	    	//Section ban
			$sql = "SELECT SID FROM ban_user_list WHERE UID = $uid AND enable
					AND (NOW() BETWEEN ban_dt AND unban_dt)";
			$rs = mysqli_query($db_conn, $sql);
			if ($rs == false)
			{
				echo ("Query section ban info error: " . mysqli_error($db_conn));
				return 2;
			}
			while ($row = mysqli_fetch_array($rs))
			{
				$this->setpriv($row["SID"],	$this->getpriv($row["SID"]) & (~S_POST));
			}
			mysqli_free_result($rs);

			return 0;
		}

		function levelname() : string
		{
			$ret = "游客";

			if ($this->level & P_ADMIN_M)
			{
				$ret = "主系统管理员";
			}
			else if ($this->level & P_ADMIN_S)
			{
				$ret = "副系统管理员";
			}
			else if ($this->level & P_MAN_M)
			{
				$ret = "正版主";
			}
			else if ($this->level & P_MAN_S)
			{
				$ret = "副版主";
			}
			else if ($this->level & P_USER)
			{
				$ret = "普通用户";
			}

			return $ret;
		}
	}
}
?>

<?php
function photo_path(int $uid, mysqli $db_conn) : string | false
{
	$photo_id = 0;
	$photo_enable = 0;
	$photo_ext = "";

	$sql = "SELECT photo, photo_enable, photo_ext FROM user_pubinfo WHERE UID = $uid";

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		return false;
	}

	if ($row = mysqli_fetch_array($rs))
	{
		$photo_id = $row["photo"];
		$photo_enable = $row["photo_enable"];
		$photo_ext = $row["photo_ext"];
	}
	mysqli_free_result($rs);

	if ($photo_id != 999)
	{
		$path = "images/face/" . str_repeat("0", 3 - strlen($photo_id)) . $photo_id . ".gif";
	}
	else
	{
		if ($photo_enable)
		{
			if ($photo_ext == "")
			{
				$path = "images/face/000.gif";
			}
			else
			{
				$path = "images/face/upload_photo/face_" . $uid . "." . $row["photo_ext"];
			}
		} 
		else
		{
			$path = "images/face/check.gif";
		}
	} 

	return $path;
}
?>

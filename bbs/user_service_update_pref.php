<?php
	require_once "../lib/db_open.inc.php";
	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./session_init.inc.php";
	require_once "./check_sub.inc.php";

	force_login();

	function check_input_data(string $input_str, string $id_str, array & $result_set, int $max_line_cnt) : bool
	{
		$bw_count = 0;
		$r_input_str = check_badwords($input_str, "****", $bw_count);
		if ($bw_count > 0)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => $id_str,
				"errMsg" => "非法内容已被过滤",
				"updateValue" => $r_input_str,
			));

			return false;
		}

		$r_input_str = LML($input_str, 80);
		if (split_line($r_input_str, "", 256, $max_line_cnt) != $r_input_str)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => $id_str,
				"errMsg" => "内容超过长度限制",
				"updateValue" => $input_str,
			));

			return false;
		}

		return true;
	}

	$user_tz = (isset($_POST["user_tz"]) ? $_POST["user_tz"] : "");
	$photo = (isset($_POST["photo"]) ? intval($_POST["photo"]) : 0);
	$introduction = str_replace("\r\n", "\n", (isset($_POST["introduction"]) ? $_POST["introduction"] : ""));
	$sign_1 = str_replace("\r\n", "\n", (isset($_POST["sign_1"]) ? $_POST["sign_1"] : ""));
	$sign_2 = str_replace("\r\n", "\n", (isset($_POST["sign_2"]) ? $_POST["sign_2"] : ""));
	$sign_3 = str_replace("\r\n", "\n", (isset($_POST["sign_3"]) ? $_POST["sign_3"] : ""));

	$result_set = array(
		"return" => array(
			"code" => 0,
			"message" => "",
			"errorFields" => array(),
		)
	);

	header("Content-Type:application/json; charset=utf-8");

	// Validate input data
	$timezone_identifiers = DateTimeZone::listIdentifiers();
	if (!in_array($user_tz, $timezone_identifiers, true))
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "user_tz",
			"errMsg" => "不存在的时区",
		));
	}

	check_input_data($introduction, "introduction", $result_set, 10);

	check_input_data($sign_1, "sign_1", $result_set, 10);
	check_input_data($sign_2, "sign_2", $result_set, 10);
	check_input_data($sign_3, "sign_3", $result_set, 10);

	if ($result_set["return"]["code"] != 0)
	{
		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Validate photo file
	$photo_file_count = (isset($_FILES['photo_file']['error']) ? count($_FILES['photo_file']['error']) : 0);
	if ($photo_file_count > 1)
	{
		$result_set["return"]["code"] = -1;
		array_push($result_set["return"]["errorFields"], array(
			"id" => "photo_file",
			"errMsg" => "只能上传单个文件",
		));

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Store photo file
	for ($i = 0; $i < $photo_file_count; $i++)
	{
		if (!isset($_FILES['photo_file']['error'][$i]) || $_FILES['photo_file']['error'][$i] != UPLOAD_ERR_OK)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "photo_file",
				"errMsg" => "上传文件错误",
			));

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$filesize = $_FILES['photo_file']['size'][$i];
		$filename = $_FILES['photo_file']['name'][$i];

		if ($filesize <= 0)
		{
			continue;
		}

		if ($filesize > 1024 * 16)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "photo_file",
				"errMsg" => "文件大小超过限制",
			));

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		switch ($ext)
		{
			case "bmp":
			case "gif":
			case "jpg":
			case "jpeg":
			case "png":
			case "tif":
			case "tiff":
				break;
			default:
				$result_set["return"]["code"] = -1;
				array_push($result_set["return"]["errorFields"], array(
					"id" => "photo_file",
					"errMsg" => "不支持的文件扩展名",
				));

				mysqli_close($db_conn);
				exit(json_encode($result_set));
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime_type = $finfo->file($_FILES['photo_file']['tmp_name'][$i]);
		$real_ext = array_search($mime_type, array(
				'bmp' => 'image/x-ms-bmp',
				'jpg' => 'image/jpeg',
				'png' => 'image/png',
				'gif' => 'image/gif',
				'tif' => 'image/tiff',
				), true);

		if ($real_ext === false)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "photo_file",
				"errMsg" => "不支持的文件格式",
			));

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if (($size = getimagesize($_FILES['photo_file']['tmp_name'][$i]))==NULL)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "photo_file",
				"errMsg" => "分析文件出错",
			));

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		if ($size[0] > 120 || $size[1] > 120)
		{
			$result_set["return"]["code"] = -1;
			array_push($result_set["return"]["errorFields"], array(
				"id" => "photo_file",
				"errMsg" => "图片尺寸超过限制",
			));

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}

		$file_path = "images/face/upload_photo/face_" . $_SESSION["BBS_uid"] . "." . $ext;

		if(!move_uploaded_file($_FILES['photo_file']['tmp_name'][$i], $file_path))
		{
			$result_set["return"]["code"] = -2;
			$result_set["return"]["message"] = "Copy file error";

			mysqli_close($db_conn);
			exit(json_encode($result_set));
		}
	}

	// Secure SQL statement
	$introduction = mysqli_real_escape_string($db_conn, $introduction);
	$sign_1 = mysqli_real_escape_string($db_conn, $sign_1);
	$sign_2 = mysqli_real_escape_string($db_conn, $sign_2);
	$sign_3 = mysqli_real_escape_string($db_conn, $sign_3);

	$sql = "UPDATE user_pubinfo SET user_timezone = '$user_tz', introduction = '$introduction', ".
		"sign_1 = '$sign_1', sign_2 = '$sign_2', sign_3 = '$sign_3', ".
		($photo_file_count > 0 ? "photo = 999, photo_enable = 0, photo_ext='$ext'" : "photo = $photo") .
		" WHERE UID=" . $_SESSION["BBS_uid"];

	$rs = mysqli_query($db_conn, $sql);
	if ($rs == false)
	{
		$result_set["return"]["code"] = -2;
		$result_set["return"]["message"] = "Update data error: " . mysqli_error($db_conn);

		mysqli_close($db_conn);
		exit(json_encode($result_set));
	}

	// Update user_tz in session data
	$_SESSION["BBS_user_tz"] = new DateTimeZone($user_tz);

	mysqli_close($db_conn);
	exit(json_encode($result_set));

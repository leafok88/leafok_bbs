<?
	require_once "../lib/db_open.inc.php";
?>
<?
set_time_limit(3600);

if (!isset($_GET["aid"]))
	$aid=0;
else
	$aid=intval($_GET["aid"]);

if (!isset($_GET["force"]))
	$force=0;
else
	$force=intval($_GET["force"]);

$rs=mysql_query("select * from upload_file where AID=$aid and deny=0 and deleted=0")
	or die("Query dl error!");
if($row=mysql_fetch_array($rs))
{
	$filename=$row["filename"];
	$size=$row["size"];
	$check=$row["check"];
	$url="./upload/$aid";
	$ext=strtolower(substr($filename,(strrpos($filename,".") ? strrpos($filename,".")+1 : 0)));
}
else
{
	echo ("记录不存在！");
	exit();
}
mysql_free_result($rs);

mysql_close($db_conn);

if ($check==0 && $force==0)
{
	echo ("文件未审核！");
	exit();
}

if (!file_exists($url))
{
	echo ("文件不存在！");
	exit();
}

$file = fopen($url,"rb")
	or die ("Open file error!");
Header("Content-type: application/octet-stream");
Header("Accept-Ranges: bytes");
Header("Accept-Length: ".filesize($url));
Header("Content-Disposition: attachment; filename=".$filename);
while (!feof ($file)) {
	echo fread($file,1024);
}
fclose ($file);
?>

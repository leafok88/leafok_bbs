<?
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}

	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./user_level.inc.php";

	header('Content-Type: text/xml; charset=UTF-8');
	echo("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n");
	echo("<?xml-stylesheet type='text/xsl' href='" . get_theme_file('xsl/1') . "' version='1.0'?>\n");
?>
<Topic>
	<Subject>
		<SectionId><? echo $result_set["data"]["sid"]; ?></SectionId>
		<SectionTitle><? echo $result_set["data"]["section_title"]; ?></SectionTitle>
		<TopicId><? echo $result_set["data"]["id"]; ?></TopicId>
		<TopicTitle><? echo htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'); ?></TopicTitle>
	</Subject>
	<Articles>
<?
	foreach ($result_set["data"]["articles"] as $article)
	{
?>
		<Article>
			<PostUserId><? echo $article["uid"]; ?></PostUserId>
			<PostUserName><? echo htmlspecialchars($article["username"], ENT_HTML401, 'UTF-8'); ?></PostUserName>
			<PostUserNickName><? echo htmlspecialchars($article["nickname"], ENT_HTML401, 'UTF-8'); ?></PostUserNickName>
			<rank><? echo user_level($article["exp"]); ?></rank>
			<credit><? echo $article["exp"]; ?></credit>
			<photo><? echo $article["photo_path"]; ?></photo>
			<ArticleId><? echo $article["aid"]; ?></ArticleId>
			<ArticleTitle><? echo htmlspecialchars($article["title"], ENT_HTML401, 'UTF-8'); ?><? if ($article["transship"]) { ?> [转载]<? } ?></ArticleTitle>
			<ExpressionIcon><? echo $article["icon"]; ?></ExpressionIcon>
			<PostDateTime><? echo $article["sub_dt"]->format("Y-m-d H:i:s (\U\T\C P)"); ?></PostDateTime>
			<PostIP><? echo $article["sub_ip"]; ?></PostIP>
			<Content><![CDATA[<? echo LML($article["content"], true, false, 1024); ?>]]></Content>
			<Length><? echo $article["length"]; ?></Length>
		</Article>
<?
	}
?>		
	</Articles>
</Topic>

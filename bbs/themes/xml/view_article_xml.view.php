<?php
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
		<SectionId><?= $result_set["data"]["sid"]; ?></SectionId>
		<SectionTitle><?= $result_set["data"]["section_title"]; ?></SectionTitle>
		<TopicId><?= $result_set["data"]["id"]; ?></TopicId>
		<TopicTitle><?= htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8'); ?></TopicTitle>
	</Subject>
	<Articles>
<?php
	foreach ($result_set["data"]["articles"] as $article)
	{
?>
		<Article>
			<PostUserId><?= $article["uid"]; ?></PostUserId>
			<PostUserName><?= htmlspecialchars($article["username"], ENT_HTML401, 'UTF-8'); ?></PostUserName>
			<PostUserNickName><?= htmlspecialchars($article["nickname"], ENT_HTML401, 'UTF-8'); ?></PostUserNickName>
			<rank><?= user_level($article["exp"]); ?></rank>
			<credit><?= $article["exp"]; ?></credit>
			<photo><?= $article["photo_path"]; ?></photo>
			<ArticleId><?= $article["aid"]; ?></ArticleId>
			<ArticleTitle><?= htmlspecialchars($article["title"], ENT_HTML401, 'UTF-8'); ?><? if ($article["transship"]) { ?> [转载]<? } ?></ArticleTitle>
			<ExpressionIcon><?= $article["icon"]; ?></ExpressionIcon>
			<PostDateTime><?= $article["sub_dt"]->format("Y-m-d H:i:s (\U\T\C P)"); ?></PostDateTime>
			<PostIP><?= $article["sub_ip"]; ?></PostIP>
			<Content><![CDATA[<?= LML($article["content"], true, false, 1024); ?>]]></Content>
			<Length><?= $article["length"]; ?></Length>
		</Article>
<?php
	}
?>		
	</Articles>
</Topic>

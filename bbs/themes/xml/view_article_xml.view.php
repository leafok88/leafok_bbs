<?php
	// Prevent load standalone
	if (!isset($result_set))
	{
		exit();
	}

	require_once "../lib/lml.inc.php";
	require_once "../lib/str_process.inc.php";
	require_once "./user_level.inc.php";

	$xsl_file = get_theme_file('xsl/1');

	$title = htmlspecialchars($result_set["data"]["title"], ENT_HTML401, 'UTF-8');

	header('Content-Type: text/xml; charset=UTF-8');

	echo <<<HTML
	<?xml version="1.0" encoding="UTF-8" ?>
	<?xml-stylesheet type='text/xsl' href='{$xsl_file}' version='1.0'?>
	<Topic>
		<Subject>
			<SectionId>{$result_set["data"]["sid"]}</SectionId>
			<SectionTitle>{$result_set["data"]["section_title"]}</SectionTitle>
			<TopicId>{$result_set["data"]["id"]}</TopicId>
			<TopicTitle>{$title}</TopicTitle>
		</Subject>
		<Articles>
	HTML;

	foreach ($result_set["data"]["articles"] as $article)
	{
		$username = htmlspecialchars($article["username"], ENT_HTML401, 'UTF-8');
		$nickname = htmlspecialchars($article["nickname"], ENT_HTML401, 'UTF-8');
		$level = user_level($article["exp"]);
		$content = LML($article["content"], true, false, 130);

		$transship_info = "";
		if ($article["transship"])
		{
			$transship_info = <<<HTML
				 [转载]
			HTML;
		}

		echo <<< HTML
			<Article>
				<PostUserId>{$article["uid"]}</PostUserId>
				<PostUserName>{$username}</PostUserName>
				<PostUserNickName>{$nickname}</PostUserNickName>
				<rank>{$level}</rank>
				<credit>{$article["exp"]}</credit>
				<photo>{$article["photo_path"]}</photo>
				<ArticleId>{$article["aid"]}</ArticleId>
				<ArticleTitle>{$title}{$transship_info}</ArticleTitle>
				<ExpressionIcon>{$article["icon"]}</ExpressionIcon>
				<PostDateTime>{$article["sub_dt"]->format("Y-m-d H:i:s (\U\T\C P)")}</PostDateTime>
				<PostIP>{$article["sub_ip"]}</PostIP>
				<Content><![CDATA[{$content}]]></Content>
				<Length>{$article["length"]}</Length>
				<Visible>{$article["visible"]}</Visible>
			</Article>

		HTML;
	}

	echo <<<HTML
		</Articles>
	</Topic>
	HTML;

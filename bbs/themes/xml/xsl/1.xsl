<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html"  indent="no" />
<xsl:template match="/">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title><xsl:value-of select="Topic/Subject/TopicTitle"/></title>
<link REL='Stylesheet' HREF='css/default.css' TYPE='text/css'/>
</head>

<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" >
<!--头信息//-->
<STYLE>
.WithBreaks { word-wrap:break-word;width:520;white-Space:pre;}
.WithBreaks2 { word-wrap:break-word;width:500}
.NormalValue { word-wrap:normal; width:600}
textarea.content
{	overflow-y:visible;
	border:0px;
	font-size:12pt;
	font-family:Fixedsys 宋体;
	line-height:150%;
	overflow:visible;
	border-width:0px;
	width:100%;
	height:40px;
}
SPAN.title_normal
{
	color: #909090;
}
SPAN.title_deleted
{
	color: red;
	text-decoration: line-through;
}
TD.content_normal
{
}
TD.content_deleted
{
	text-decoration: line-through;
}
</STYLE>
<!--头结束//-->
	<table width="98%" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td width="100%" >
				<!--
				//-->
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td align="right" width="5"></td>
						<td colspan="2">
							<xsl:element name="a">
								<xsl:attribute name="href">https://www.fenglin.info</xsl:attribute>
								<xsl:attribute name="target">_blank</xsl:attribute>
										<xsl:element name="img">
									<xsl:attribute name="src">/images/logo/leafok.gif</xsl:attribute>
									<xsl:attribute name="border">0</xsl:attribute>
									<xsl:attribute name="alt">枫林在线论坛</xsl:attribute>
								</xsl:element>
							</xsl:element>
						</td>
					</tr>
					<tr>
						<td align="right" width="5"></td>
						<td width="80">主题[<xsl:value-of select="Topic/Subject/TopicId"/>] ：</td>
						<td class="WithBreaks2"><xsl:value-of select="Topic/Subject/TopicTitle"/></td>
					</tr>
					<tr>
						<td align="right" width="5"></td>
						<td width="80">所属版块：</td>
						<td class="WithBreaks2"><xsl:value-of select="Topic/Subject/SectionTitle"/>[<xsl:value-of select="Topic/Subject/SectionId"/>]</td>
					</tr>
					<tr height="10">
						<td align="right">&#9;</td>
						<td align="right"></td>
						<td>&#9;</td>
					</tr>
					<tr bgcolor="#666666">
						<td colspan="3" height="1">&#9;</td>
					</tr>
					<tr height="10">
						<td align="right">&#9;</td>
						<td align="right"></td>
						<td>&#9;</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td width="100%" height="10">&#9;</td>
		</tr>
	</table>

	<!--文章开始//-->
	<xsl:apply-templates select="Topic/Articles"/>
	<!--文章结束//-->

	<table width="98%" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td width="100%" >
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr bgcolor="#666666">
					<td colspan="3" height="1">&#9;</td>
				</tr>
				<tr>
					<td colspan="3" height="10">&#9;</td>
				</tr>
				<tr>
					<td colspan="3">
						<p align="center" style="color:gray; font-size:12px;">
							Copyright (C) 2001-2025 枫林在线  All Rights Reserved
						</p>
					</td>
				</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
</xsl:template>

<xsl:template match="Articles">
	<xsl:for-each select="Article">
		<xsl:element name="a">
			<xsl:attribute name="Name"><xsl:value-of select="ArticleId"/></xsl:attribute>
		</xsl:element>
		<table width="98%" border="0" cellspacing="0" cellpadding="0" align="center" >
			<tr bgcolor="#0066CC" height="1">
				<td width="2%"></td>
				<td width="60%"></td>
				<td width="30%"></td>
				<td width="6%"></td>
				<td width="2%"></td>
			</tr>
			<tr bgcolor="#E1E9FF" height="25">
				<td></td>
				<td>
					<font color="#3366CC">发贴者：</font>
					<xsl:element name="a">
						<xsl:attribute name="href">view_user.php?uid=<xsl:value-of select="PostUserId"/></xsl:attribute>
						<xsl:attribute name="target">_blank</xsl:attribute>
						<font color="#3366CC"><b>
							<xsl:value-of select="PostUserName "/>
							(<xsl:value-of select="PostUserNickName "/>)
						</b></font>
					</xsl:element>
					<font color="#000000">经验值：
						<xsl:value-of select="credit"/>
						(<xsl:value-of select="rank"/>)
					</font>
				</td>
				<td align="center"><font color="#000000"><xsl:value-of select="PostDateTime" /></font></td>
				<td align="right"><font color="#FF6633"><b><a href="#top"><font color="#3366CC">Top</font></a></b></font></td>
				<td></td>
			</tr>
			<tr height="2">
				<td colspan="5"></td>
			</tr>
			<tr>
				<td>
				</td>
				<td>
					<xsl:element name="img">
						<xsl:attribute name="src">/bbs/images/expression/<xsl:value-of select="ExpressionIcon"/>.gif</xsl:attribute>
					</xsl:element>
					<xsl:if test="Visible = 1">
						<span class="title_normal"><xsl:value-of select="ArticleTitle"/></span>
					</xsl:if>
					<xsl:if test="Visible = 0">
						<span class="title_deleted"><xsl:value-of select="ArticleTitle"/></span>
					</xsl:if>
				</td>
				<td align="center">
					来自：<xsl:value-of select="PostIP"/>
				</td>
				<td colspan="2">
					</td>
				</tr>
				<tr>
				<td>
				</td>
				<xsl:if test="Visible = 1">
					<td colspan="3" class="content_normal">
						<pre><xsl:value-of select="Content" disable-output-escaping="yes"/></pre>
					</td>
				</xsl:if>
				<xsl:if test="Visible = 0">
					<td colspan="3" class="content_deleted">
						<pre><xsl:value-of select="Content" disable-output-escaping="yes"/></pre>
					</td>
				</xsl:if>
				<td>
				</td>
			</tr>
			<tr height="10">
				<td colspan="5">
				</td>
			</tr>
		</table>
	</xsl:for-each>
</xsl:template>
</xsl:stylesheet>

function text_process(textArea, t_begin, t_end)
{
    // Obtain the index of the first selected character
	var start = textArea.selectionStart;
	// Obtain the index of the last selected character
	var finish = textArea.selectionEnd;
	// Obtain the selected text
	var ch_text = t_begin + textArea.value.substring(start, finish) + t_end;
	// Update textArea
	textArea.value = textArea.value.substring(0, start) + ch_text + textArea.value.substring(finish + 1);
}

function b_bold(textArea)
{
	text_process(textArea, "[b]", "[/b]");
}

function b_italic(textArea)
{
	text_process(textArea, "[i]", "[/i]");
}

function b_underline(textArea)
{
	text_process(textArea, "[u]", "[/u]");
}

function b_size(textArea)
{
	var arg = prompt("请输入希望设置的字体大小：","");
	if (arg != "" && arg != null)
	{
		text_process(textArea, "[size " + arg + "]", "[/size]");
	}
}

function b_color(textArea)
{
	var arg = prompt("请输入希望设置的字体颜色：", "");
	if (arg != "" && arg != null)
	{
		text_process(textArea, "[color " + arg + "]", "[/color]");
	}
}

function b_link(textArea)
{
	var arg1 = prompt("请输入希望链接的URL：", "http://");
	if (arg1 != ""  && arg1 != null)
	{
		var arg2 = prompt("请输入希望链接的名称：", arg1);
		if (arg2 !="" && arg2 != null)
		text_process(textArea, "", "[link " + arg1 + "]" + arg2 + "[/link]");
	}
}

function b_article(textArea)
{
	var arg1 = prompt("请输入希望链接的主题文章编号：","");
	if (arg1 != ""  && arg1 != null)
	{
		var arg2 = prompt("请输入希望链接的名称：", arg1);
		if (arg2 !="" && arg2 != null)
		text_process(textArea, "", "[article " + arg1 + "]" + arg2 + "[/article]");
	}
}

function b_email(textArea)
{
	var arg = prompt("请输入希望发送的邮件地址：","@");
	if (arg != "" && arg != null)
	{
		text_process(textArea, "", "[email " + arg + "]" + arg + "[/email]");
	}
}

function b_image(textArea)
{
	var arg = prompt("请输入希望插入的图片的URL：","http://");
	if (arg != "" && arg != null)
	{
		text_process(textArea, "", "[image " + arg + "]");
	}
}

function b_marquee(textArea)
{
	var arg = prompt("请输入滚动字幕的参数：","");
	if (arg !="" && arg != null)
	{
		text_process(textArea, "[marquee " + arg + "]", "[/marquee]");
	}
}

function b_left(textArea)
{
	text_process(textArea, "", "[left]");
}

function b_right(textArea)
{
	text_process(textArea, "", "[right]");
}

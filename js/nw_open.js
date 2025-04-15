function NW_open(url,name,w,h)
{
	hwnd=window.open(url,name,"width="+w+",height="+h+",top=0,left=0,toolbar=no,scrollbars=yes,menubar=no,statusbar=0,location=no");
	hwnd.focus();
	return false;
}

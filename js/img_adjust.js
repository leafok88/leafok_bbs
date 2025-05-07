function img_adjust(img, width)
{
	if (img.width > width)
	{
		img.width = width;
	}
	return false;
}

function bbs_img_zoom(e, o)
{
    var zoom = parseInt(o.style.zoom, 10) || 100;
    zoom += e.wheelDelta / 12;
    if (zoom > 0)
	{
		o.style.zoom = zoom + '%';
	}
    return false;
}

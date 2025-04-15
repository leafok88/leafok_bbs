function image_adjust(width)
{
	for (img of document.images)
	{
		if (img.width > width)
		{
			img.width = width;
		}
	}
}

function bbs_img_zoom(e, o)
{
    var zoom = parseInt(o.style.zoom, 10) || 100;
    zoom += e.wheelDelta / 12;
    if (zoom > 0) o.style.zoom = zoom + '%';
    return false;
}

window.addEventListener("load", (e) => {
	image_adjust(550);
});

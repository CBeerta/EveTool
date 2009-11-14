function round ( val, precision ) {
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +    revised by: Onno Marsman
    // *     example 1: round(1241757, -3);
    // *     returns 1: 1242000
    // *     example 2: round(3.6);
    // *     returns 2: 4

    return parseFloat(parseFloat(val).toFixed(precision));
}

function numberFormat(nStr) 
{
    nStr = round(nStr, 2);
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + String(x[1]).substr(0,2) : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1))
	{
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	
	return x1 + x2;
}

$(document).ready(function() { 
    $("a#fb_location").fancybox();
    $("a#fb_character").fancybox();
    $("a#fb_item").fancybox();
});


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

function toggle_it(itemID){
    // Toggle visibility between none and inline
    if ((document.getElementById(itemID).style.display == 'none'))
    {
        document.getElementById(itemID).style.display = '';
    } 
	else 
	{
        document.getElementById(itemID).style.display = 'none';
    }
}



// jQuery Stuff in here
$(document).ready(function() { 

	// Fancybox Popups
    $("a#fb_location").fancybox({ 'hideOnContentClick': false });
    $("a#fb_character").fancybox({ 'hideOnContentClick': false });
    $("a#fb_item").fancybox({ 'hideOnContentClick': false });
    $("a#fb_fitting").fancybox({ 'hideOnContentClick': false, 'frameWidth': 850, 'frameHeight': 550});
    $("a#fb_image").fancybox({ 'hideOnContentClick': false, 'frameWidth': 810, 'frameHeight': 360});
	
	
	// Navigation Effects
	$("h2.t1production").click(function(){
		$("ul#t1production").toggle("slow");
	});
	
	$("h2.t2production").click(function(){
		$("ul#t2production").toggle("slow");
	});

	$("h2.information").click(function(){
		$("ul#information").toggle("slow");
	});

	$("h2.configuration").click(function(){
		$("ul#configuration").toggle("slow");
	});

	
});


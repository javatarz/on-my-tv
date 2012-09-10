addLoadEvent(registerEvents);
function registerEvents() {
	
	var epLinks = getElementsByClassName("eplink",'a',document.getElementById("month_box"));
	for (ep in epLinks)
	{
		epLinks[ep].onmouseover = doPopup;
		epLinks[ep].onmouseout = undoPopup;
	}
}

function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft
		curtop = obj.offsetTop
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft
			curtop += obj.offsetTop
		}
	}
	return [curleft,curtop];
}

function findWindowSize() {
  var myWidth = 0, myHeight = 0;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    myWidth = window.innerWidth;
    myHeight = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    myWidth = document.documentElement.clientWidth;
    myHeight = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;
  }

  return [myWidth,myHeight];
}

var posx = 0;
var posy = 0;
var aj;

var cache = new Array();
var qstr = '';

function doPopup(e) {
	var targ;
	if (!e) var e = window.event;
	if (e.target) targ = e.target;
	else if (e.srcElement) targ = e.srcElement;
	if (targ.nodeType == 3) // defeat Safari bug
		targ = targ.parentNode;



	if (!e) var e = window.event;
	if (e.pageX || e.pageY) 	{
		posx = e.pageX;
		posy = e.pageY;
	}
	else if (e.clientX || e.clientY) 	{
		posx = e.clientX + document.body.scrollLeft
			+ document.documentElement.scrollLeft;
		posy = e.clientY + document.body.scrollTop
			+ document.documentElement.scrollTop;
	}

	if(isUndefined(cache[targ.id])) {
		qstr = targ.id;
		aj = new ajax ('query.php', 
		{
		postBody: 'q=' + targ.id, 
		update: $('pop'), 
		onComplete: showPopup
		});
		
	} else {
		qstr = targ.id;
		showPopup(false);	
	}

	window.event ? window.event.cancelBubble = true : e.stopPropagation();
	
}


function showPopup(req) {
	if(req !== false) {
		cache[qstr] = req.responseText;
	}
	var pop = document.getElementById("pop");
	tpos = findPos(pop);
	wsz = findWindowSize();

	if((posx+parseInt(pop.style.width)+45) > wsz[0]) {
		pop.style.left = (posx - parseInt(pop.style.width) - 25) + 'px';
	} else {
		pop.style.left = (posx + 25) + 'px';
	}

	pop.style.top = (posy+25) + 'px';
	if(cache[qstr] != '') {
		pop.innerHTML = cache[qstr];
		qstr = false;
	}
	pop.style.display = '';
	return false;
}

function undoPopup(e) {

	if(aj) {
		aj.transport.abort();
		aj = null;
	}

	var pop = document.getElementById("pop");
	pop.style.display = 'none';

	return false;
}


addLoadEvent(NeREvent);

function NeREvent(e) {
	
	var trows = getElementsByClassName("ep",'tr',document.getElementById("dtable"));

	var hash=location.hash;

	for (tr in trows)
	{
		
		if (hash){	
			if(trows[tr].nextSibling.id == hash) {
				
			}
		}
		trows[tr].onclick = ToggleVisibility;
		if((tr % 2 == 0)) { trows[tr].style.background = '#565656'; }
	}


	function ToggleVisibility(e) {
		var targ;
		if (!e) var e = window.event;
		if (e.target) targ = e.target;
		else if (e.srcElement) targ = e.srcElement;
		if (targ.nodeType == 3) // defeat Safari bug
		targ = targ.parentNode;
			
			var r = document.getElementById("x_" + targ.id.substring(2));
			
			if(r != null) {
				if(r.style.display == 'none') {
					r.style.display = '';
				} else {
					r.style.display = 'none';
				}
			}
	}
}

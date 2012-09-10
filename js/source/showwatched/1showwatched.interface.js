
function registerEvents2() {
	var unwatchedLinks = getElementsByClassName("isunwatched",'a',document.getElementById("month_box"));
	var watchedLinks = getElementsByClassName("iswatched",'a',document.getElementById("month_box"));
	for (unw in unwatchedLinks)
	{
		unwatchedLinks[unw].onclick = doWatchStatus;
	}

	for (wal in watchedLinks)
	{
		watchedLinks[wal].onclick = doUnWatchedStatus;
	}
}

var curCli;

function doWatchStatus(e) {
	if(isUndefined(curCli) || !curCli) {
		var targ;
		if (!e) var e = window.event;
		if (e.target) targ = e.target;
		else if (e.srcElement) targ = e.srcElement;
		if (targ.nodeType == 3) // defeat Safari bug
			targ = targ.parentNode;
		var qry = targ.href;
		curCli = targ;
		aj = new ajax (qry, 
			{
			postBody: 'x=1',
			update: targ,
			onComplete: doWatchClassUpdate
			});

		window.event ? window.event.cancelBubble = true : e.stopPropagation();
		return false;
	} else {
		window.event ? window.event.cancelBubble = true : e.stopPropagation();
		return false;
	}
}

function doUnWatchedStatus(e) {
	if(isUndefined(curCli) || !curCli) {
		var targ;
		if (!e) var e = window.event;
		if (e.target) targ = e.target;
		else if (e.srcElement) targ = e.srcElement;
		if (targ.nodeType == 3) // defeat Safari bug
			targ = targ.parentNode;
		var qry = targ.href;
		curCli = targ;
		qry = qry.replace(/\/watched\//i,'/unwatched/');
		
		aj = new ajax (qry, 
			{
			postBody: 'x=1',
			update: targ, 
			onComplete: doUnWatchClassUpdate
			});

		window.event ? window.event.cancelBubble = true : e.stopPropagation();
		return false;
	} else {
		window.event ? window.event.cancelBubble = true : e.stopPropagation();
		return false;
	}
}


function doWatchClassUpdate(e) {
	curCli.className = 'iswatched';
	curCli.onclick = doUnWatchedStatus;
	var parow = curCli.parentNode.parentNode;

	var replink = getElementsByClassName('eplink',false,parow);
	var rseasep = getElementsByClassName('seasep',false,parow);
	if(replink.length > 0) {
		for (rep in replink)
		{	
			curclass = replink[rep].className;
			if(isString(curclass)) {
				curclass = curclass.replace(/showwatched/i,'');
			}
			replink[rep].className = curclass + ' showwatched';
		}
	}

	if(rseasep.length > 0) {
		for (rep in rseasep)
		{	
			curclass = rseasep[rep].className;
			if(isString(curclass)) {
				curclass = curclass.replace(/showwatched/i,'');
			}
			rseasep[rep].className = curclass + ' showwatched';
		}
	}

	curCli = false;
}

function doUnWatchClassUpdate(e) {
	curCli.className = 'isunwatched';
	curCli.onclick = doWatchStatus;
	var parow = curCli.parentNode.parentNode;
	var replink = getElementsByClassName('eplink',false,parow);
	var rseasep = getElementsByClassName('seasep',false,parow);
	if(replink.length > 0) {
		for (rep in replink)
		{	
			curclass = replink[rep].className;
			if(isString(curclass)) {
				curclass = curclass.replace(/showwatched/i,'');
			}
			replink[rep].className = curclass;
		}
	}

	if(rseasep.length > 0) {
		for (rep in rseasep)
		{	
			curclass = rseasep[rep].className;
			if(isString(curclass)) {
				curclass = curclass.replace(/showwatched/i,'');
			}
			rseasep[rep].className = curclass;
		}
	}

	curCli = false;
}

addLoadEvent(registerEvents2);
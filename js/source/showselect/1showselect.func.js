window.onload = setup;

function setup() {
	var nmButton = document.getElementById('sall');
	var lmButton = document.getElementById('snone');
	nmButton.onclick = checkAll;
	lmButton.onclick = unCheckAll;

	var checks = document.getElementsByTagName('input');

	for(var i = 0; i < checks.length; i++) {

		var cbox = checks[i];
		if(cbox.className == 'checkbox') {
			cbox.onclick = checkText;
		}
	}
}

function checkAll() { 
	var el = document.forms[0].elements; 
	for(var i = 0 ; i < el.length ; ++i) {
	   if(el[i].type == "checkbox") {  

		   if(el[i].checked != true) {
				el[i].checked = true; 

				if(el[i].parentNode.parentNode.className == 'normalletter new') {
					el[i].parentNode.parentNode.className='checkedletter new';
				} else if(el[i].parentNode.parentNode.className == 'normalletter prem') {
					el[i].parentNode.parentNode.className='checkedletter prem';
				} else {
					el[i].parentNode.parentNode.className='checkedletter';
				}
		   }
		} 
		
	} 
} 

function unCheckAll() { 
	var i=0; 
	var el = document.forms[0].elements; 
	for( var i=0 ; i < el.length; i++) {
		if(el[i].type == "checkbox") {
			if(el[i].checked == true) {
				el[i].checked=false; 
				if(el[i].parentNode.parentNode.className == 'checkedletter new') {
					el[i].parentNode.parentNode.className='normalletter new';
				} else if(el[i].parentNode.parentNode.className == 'checkedletter prem') {
					el[i].parentNode.parentNode.className='normalletter prem';
				} else {
					el[i].parentNode.parentNode.className='normalletter';
				}
			}
		}
	} 
}

function checkText(e) {

	var targ;
	if (!e) var e = window.event;
	if (e.target) targ = e.target;
	else if (e.srcElement) targ = e.srcElement;
	if (targ.nodeType == 3) // defeat Safari bug
	targ = targ.parentNode;
	
	if (targ.checked)
	{	
		var pw = document.getElementById('check' + targ.value);
		if(pw) {
			if(pw.className == 'normalletter new') {
				pw.className='checkedletter new';
			} else if(pw.className == 'normalletter prem') {
				pw.className='checkedletter prem';
			} else {
				pw.className='checkedletter';
			}
		}

	} else {
		
		var pw = document.getElementById('check' + targ.value);
		if(pw) {
			if(pw.className == 'checkedletter new') {
				pw.className='normalletter new';
			} else if(pw.className == 'checkedletter prem') {
				pw.className='normalletter prem';
			} else {
				pw.className='normalletter';
			}
		}
		
	}	
}

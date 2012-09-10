addLoadEvent(registerEvents54);

var settingsLinkOpacity;
var loginLinkOpacity;

function registerEvents54() {

	var settingsLink = $('settingslink');

	settingsLink.onclick = doSettingsPopup;
	
	settingsLinkOpacity = new fx.Opacity('optionsarea', {duration: 120});
	$('optionsarea').style.display = "";
	settingsLinkOpacity.hide();

	$('closesettingslink').onclick = hideSettingsPopup;

	var loginLink = $('loginlink');
	var cLoginLink = $('closeloginlink');


	if(loginLink) {
		loginLink.onclick = doLoginPopup;
	}

	loginLinkOpacity = new fx.Opacity('login', {duration: 120, onComplete: 
		function(){ 
			if($('login').style.display != 'none' && loginLinkOpacity.now != 0) {
				$('logemail').focus();
			} else {
				$('logemail').blur();
			}
		}
	});
	$('login').style.display = "";
	loginLinkOpacity.hide();

	if(cLoginLink) {
		cLoginLink.onclick = hideLoginPopup;
	}


	
}


function doLoginPopup(e) {
	loginLinkOpacity.toggle();
	
	window.event ? window.event.cancelBubble = true : e.stopPropagation();
	return false;
}

function hideLoginPopup(e) {
	loginLinkOpacity.toggle();

	window.event ? window.event.cancelBubble = true : e.stopPropagation();
	return false;
}

function doSettingsPopup(e) {
	settingsLinkOpacity.toggle();
	
	window.event ? window.event.cancelBubble = true : e.stopPropagation();
	return false;
}

function hideSettingsPopup(e) {
	settingsLinkOpacity.toggle();
	
	window.event ? window.event.cancelBubble = true : e.stopPropagation();
	return false;
}
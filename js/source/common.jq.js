$(document).ready(function() {

    $('a#settingslink').click(function() {
        $('div#optionsarea').slideToggle('fast');
        return false;
    });
    
    $('a#closesettingslink').click(function() {
        $('div#optionsarea').slideToggle('fast');
        return false;
    });
    
    $('a#loginlink').click(function() {
        $('div#login').slideToggle('fast');
        return false;
    });
    
    $('a#closeloginlink').click(function() {
        $('div#login').slideToggle('fast');
        return false;
    });
    

   
});

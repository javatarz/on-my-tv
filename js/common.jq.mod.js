$(document).ready(function() {

    $('a#linkslink').click(function() {
        $('div#linksarea').slideToggle('fast');
        return false;
    });
    
    $('a#closelinksslink').click(function() {
        $('div#linksarea').slideToggle('fast');
        return false;
    });
    
    $('a#settingslink').click(function() {
        $('div#optionsarea').slideToggle('fast');
        return false;
    });
    
    $('a#closesettingslink').click(function() {
        $('div#optionsarea').slideToggle('fast');
        return false;
    });
    
    $('a#loginlink').click(function() {
        $('div#register').slideUp('fast',function() {
            $('div#login').slideToggle('fast');
            $("input#logemail").focus();
        });
        
        return false;
    });
    
    $('a#closeloginlink').click(function() {
        $('div#login').slideToggle('fast');
        return false;
    });
    
    
    $('a#registerlink').click(function() {
        $('div#login').slideUp('fast',function() {
            $('div#register').slideToggle('fast');
            $("input#regemail").focus();
        });
        
        return false;
    });
    
    $('a#closeregisterlink').click(function() {
        $('div#register').slideToggle('fast');
        return false;
    });

   
});

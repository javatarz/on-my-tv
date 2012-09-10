
function do_watched() {
    $.post($(this).attr('href'),{x:'1',urid: $(this).attr('id')}, 
        function(data) 
        {  
            it = $('#' + data);
            it.text('<');
            it.attr({'href': it.attr('href').replace(/\/watched\//i,'/unwatched/')});
            
            it.removeClass('isunwatched');
            it.addClass('iswatched');
            
            xt = $('#j_' + data.substring(2));
            xt.addClass('showwatched');
            
            it.unbind('click');
            it.click(do_un_watched);
        }
    );
    
    return false;
}

function do_un_watched() {
    $.post($(this).attr('href'),{x:'1',urid: $(this).attr('id')}, 
        function(data) 
        {  
            it = $('#' + data);
            it.text('>');
            it.attr({'href': it.attr('href').replace(/\/unwatched\//i,'/watched/')});
            
            it.removeClass('iswatched');
            it.addClass('isunwatched');
            
            xt = $('#j_' + data.substring(2));
            xt.removeClass('showwatched');
            
            it.unbind('click');

            it.click(do_watched);
        }
    );
    
    return false
}
        
$(document).ready(function() {
    $('a.isunwatched').click(do_watched);
    $('a.iswatched').click(do_un_watched);

});
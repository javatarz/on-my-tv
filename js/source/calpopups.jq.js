var cache = new Array();

var qstr = undefined;

var xhr = undefined;

function updatePopup() {

    if(qstr != undefined) {
        
        xhr = $.get('query.php',{q:qstr}, 
            function(data) 
            {  
              
                $('div#pop').html(data);
                cache[qstr] = data;
                position_show();
                qstr = undefined;
            }
        );
    } else {
        abort_inprogress();
        $('div#pop').hide();
    }

}

function position_show() {
    pop = $('div#pop');
    w_h = $(window).height();
    w_w = $(window).width();
    opr = $('#' + qstr).offset();
    
    if((opr.top + 25 + pop.height()) <= w_h) {
        opr.top += 25;
    } else {
        opr.top -= (pop.height());
    }
    
    if((opr.left + 25 + pop.width()) <= w_w) {
        opr.left += 50;
    } else {
        opr.left -= (pop.width());
    }
    pop.css(opr);
    pop.show();
    
}

function abort_inprogress() {
    if(xhr != undefined) {
        xhr.abort();
    }
}

$(document).ready(function() {
    $('a.eplink').hover(
        function () 
        {
           qstr = $(this).attr('id');
           if(cache[qstr] == undefined) {
                abort_inprogress();
                setTimeout('updatePopup()', 300);   
            } else {
                abort_inprogress();
                $('div#pop').html(cache[qstr]);
                position_show();
                qstr = undefined;
            }
            return false;
        }, 
        function () {
            abort_inprogress();
            qstr = undefined;
            $('div#pop').hide();
            return false;
        }
    );
});
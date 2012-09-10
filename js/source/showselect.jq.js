function check_all() {
    $('input.checkbox').attr('checked', true);
    p = $('input.checkbox').parent().parent();
    p.removeClass('normalletter');
    p.addClass('checkedletter');
}

function check_none() {
    $('input.checkbox').attr('checked', false);
    p = $('input.checkbox').parent().parent();
    p.removeClass('checkedletter');
    p.addClass('normalletter');
}

function check_box() {

    p = $(this).parent().parent();
    
    if($(this).attr('checked')) {
        p.removeClass('normalletter');
        p.addClass('checkedletter');
    } else {
        p.removeClass('checkedletter');
        p.addClass('normalletter');
    }
}

$(document).ready(function() {
    $('#sall').click(check_all);
    $('#snone').click(check_none);
    $('input.checkbox').click(check_box);
});
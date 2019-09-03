$(document).ready(function(){
    $(document).scroll(function() {
        styleNav();
    });
    
    $('#success-slider').unslider({
        arrows : {
            prev: '<a class="unslider-arrow prev"><i class="fa fa-long-arrow-left" aria-hidden="true"></i></a>',
            next: '<a class="unslider-arrow next"><i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>'
        },
        nav : true
    });
});

function styleNav(){
    var scroll_start = 0;
    scroll_start = $(this).scrollTop();
    
    if(window.innerWidth >= 992){
        if(scroll_start > 1) {
            $(".primary-header .row").css('background-color', '#f26522');
            $('.primary-header .row').css('opacity','0.95');
            $('.primary-header .row').addClass('shadow');
        } else {
            $('.primary-header .row').css('background-color', 'transparent')
            $('.primary-header .row').css('opacity','1');
            $('.primary-header .row').removeClass('shadow');
        }
    }
}
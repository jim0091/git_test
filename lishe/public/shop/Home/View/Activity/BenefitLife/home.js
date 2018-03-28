$(document).scroll(function(){
    if($(document).scrollTop() >=300){
        $('.elevator').fadeIn(300);
        $('.elevator p:nth-child(1)').addClass('elevator-active').siblings().removeClass('elevator-active');
    }else{
        $('.elevator').fadeOut(300);
    }
    if($(document).scrollTop()>=700){
        $('.elevator p:nth-child(2)').addClass('elevator-active').siblings().removeClass('elevator-active');
    }
    if($(document).scrollTop()>=1300){
        $('.elevator p:nth-child(3)').addClass('elevator-active').siblings().removeClass('elevator-active');
    }
    if($(document).scrollTop()>=2400){
        $('.elevator p:nth-child(4)').addClass('elevator-active').siblings().removeClass('elevator-active');
    }
    if($(document).scrollTop()>=3100){
        $('.elevator p:nth-child(5)').addClass('elevator-active').siblings().removeClass('elevator-active');
    }
    //console.log($(document).scrollTop());
});
function floorTop(){
    var floor_arr = [];
    $('.Dragon-parent').each(function(i){
        floor_arr[i-1] = $('.Dragon-parent').eq(i).offset().top;
    });
    $('.elevator p').click(function(){
        var floorA = $(this).index();
        $('html,body').animate({
            scrollTop : floor_arr[floorA] + 'px'
        });
    });
}
$('.elevator-top').click(function(){
    $(this).addClass('elevator-active').siblings().removeClass('elevator-active');
    $('html,body').animate({
        scrollTop : 0 + 'px'
    });
})

floorTop();
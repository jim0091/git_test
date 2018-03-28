$(function(){
var swiper = new Swiper('.swiper-container', {
    pagination: '.swiper-pagination',
    slidesPerView: 2,
    paginationClickable: true,
    spaceBetween:-150


});
var html = document.documentElement;
var whtml  =html.getBoundingClientRect().width;
html.style.fontSize = whtml /7.5 + "px";

$('.swiper-slide').css({marginRight:0});
$('.swiper-slide').css({marginLeft:'0.2rem'});
$('.swiper-slide').css({width:'2rem'});
$('.swiper-wrapper li').css({width:'2rem'});
var size=$('.slide_ul li').length;
var width=parseInt($('.slide_ul li').width())+20;
$('.slide_ul').css({width:size*width});
    //购物车数量
    function count(){
    var all=parseInt($('.shop_cart p').html());
    if(all==0){
        $('.shop_cart p').css({'display':'none'});
    }else if(all!=0){
        $('.shop_cart p').css({'display':'block'});
    }
    }
    count();
    $('.recommend-shop img').on('touchend',function(){
        var all=parseInt($('.shop_cart p').html());
        $('.shop_cart p').html(all + 1);
        count();
    })
    $('.recommend').on('click','.recommend-shop img',function(){
        clearTimeout(time);
        $('.shop_cart p').addClass('click');
        var time = setTimeout(function(){
            $('.shop_cart p').removeClass('click');
        },500)
    })

})
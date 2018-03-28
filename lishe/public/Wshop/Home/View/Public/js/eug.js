//删除
var self=null;
$('.content_children').on('click','li>.children_delete',function(e){
    e.stopPropagation();
        self=$(this);
    $('.modal-content,.modal_confirm').fadeIn();
    $('body').on('click',function(){
        $('.modal-content,.modal_confirm').fadeOut();
        self="";
    })
    $('.modal-content_btn_affirm').click(function(){
        self.parent().remove();
        $('.modal-content,.modal_confirm').fadeOut();
        allPrice();
    })
    $('.modal-content_btn_cancel').click(function(){
        $('.modal-content,.modal_confirm').fadeOut();
        self="";
    })
});
//选择
var arr=[];
function unique(data){
    data = data || [];
    var a = {};
    len = data.length;
    for (var i=0; i<len;i++){
        var v = data[i];
        if (typeof(a[v]) == 'undefined'){
            a[v] = 1;
        }
    };
    data.length=0;
    for (var i in a){
        data[data.length] = i;
    }
    return data;
}
$('.content_children').on('click','li>.children_default',function(e){
    e.stopPropagation();
    $(this).toggleClass('children_corr');
    var aaa=$(this).parent().attr('data');
    if($(this).hasClass('children_corr')){
        arr.push(aaa);
        $('.value_text').val(unique(arr));
    }else{
        for(var i=0;i<arr.length;i++){
            if(aaa==arr[i]){
               arr.splice(i,1);
            }
            $('.value_text').val(arr);
        }
    }
    $('.content_children li .children_corr').each(function(){
        var a=$(this).parent().attr('data');
        arr.push(a)
    });
    $('.value_text').val(unique(arr));
    //console.log($('.value_text').val())
    var chknum = $(".content_children li .children_default").size();
    var chk = 0;
    $(".content_children li .children_default").each(function () {
        if($(this).hasClass("children_corr")){
            chk++;
        }
    });
    if(chknum==chk){//全选
        $(".footer_default").addClass('footer_corr');
    }else{//不全选
        $(".footer_default").removeClass('footer_corr')
    }
    allPrice()
})
function allPrice(){
    var price=0;
    var handling=0;
    $(".content_children li .children_default").each(function () {
        if($(this).hasClass("children_corr")){
            price+=parseInt($(this).siblings('.children_price').children('.price_two').html());
            //handling+=$(this).siblings('.children_price').children('.children_text_two').children('span.handling').html();
            //handling++;.children('.children_text_two')
        }
    });
    $('.btn_all').html($(".content_children li .children_corr").size());
    $('.price_all').html("￥"+price);
    $('.two_integral').html(price*100+"积分");
    $('.two_poundage').html('￥'+(price*0.09).toFixed(2));
    $('#total_price').val((price*0.09).toFixed(2));
}
//全选
$('.footer_default').on('click',function(){
    $(this).toggleClass('footer_corr');
    var arr_1=[]
    if( $(".footer_default").hasClass('footer_corr')){
        $('.content_children li>.children_default').addClass('children_corr');
        $('.content_children li .children_corr').each(function(){
            var a=$(this).parent().attr('data');
            arr_1.push(a)
        });
        $('.value_text').val(arr_1);
        //console.log($('.value_text').val())
    }else{
        $('.content_children li>.children_default').removeClass('children_corr');
        arr=[];
        $('.value_text').val(arr);
        //console.log($('.value_text').val())
    }
    allPrice();
})



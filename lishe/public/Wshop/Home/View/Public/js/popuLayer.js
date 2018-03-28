/**
**弹出加载中提示
**status，open:打开，close:关闭，content:内容，refer：跳转地址
**content:提示内容    
**/
function opLayer(status,content,refer){
    if (status == 'open') {
        $("#goodcover").show();
        $(".Wtankdiv").show();
        $(".Tanksentences").html(content);
        $("html").addClass('select-options');
        if (refer) {
            $(".TankConfirm").attr("href",refer);
        };        
    }else{                
        $("#goodcover").hide();
        $(".Wtankdiv").hide();
        $("html").removeClass('select-options');
    }
}
$(function(){ 
    $(".Tankclose").click(function(){
        opLayer('close','');
    });
    $(".Tanksurebtn").click(function(){
        opLayer('close','');
    });
});
<?php   
/**
  +----------------------------------------------------------------------------------------
 *  OrderModel
  +----------------------------------------------------------------------------------------
 * @author   	awen
 * @version  	$Id: OrderModel.class.php v001 2016-11-29
 * @description 订单操作
  +-----------------------------------------------------------------------------------------
 */
namespace Home\Model;
use Think\Model;
class CollectionModel extends CommonModel{
	public function __construct(){
        $this->dbshopFav = M("sysuser_shop_fav");//店铺收藏 
        $this->dbuserFav = M('sysuser_user_fav');//商品收藏、缺货登记表  
	}
	

    //获取用户店铺收藏
    public function getShopList($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->dbshopFav->where($condition)->select();
        }
    }
    //删除收藏店铺
    public function delShop($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->dbshopFav->where($condition)->delete();
        }
    }
    //获取用户商品收藏
    public function getItemList($condition,$limit){
        if (!$condition) {
            return false;
        }else{
            return $this->dbuserFav->where($condition)->limit($limit)->select();
        }
    }  
    //获取收藏商品个数
    public function getItemCount($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->dbuserFav->where($condition)->count('gnotify_id');
        }
    }  
    //删除收藏商品
    public function delItem($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->dbuserFav->where($condition)->delete();
        }
    }
}  
?>  	
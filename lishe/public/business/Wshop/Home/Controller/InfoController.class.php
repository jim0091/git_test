<?php
namespace Home\Controller;
class InfoController extends CommonController {
		public function __construct(){
		parent::__construct();
        $this->itemModel = M('sysitem_item');//商品表
        $this->itemDescModel = M('sysitem_item_desc');//商品描述
        $this->freepostageItemModel = M('syspromotion_freepostage_item');//包邮信息表
        $this->freepostageModel = M('syspromotion_freepostage');//包邮信息表
        $this->propsModel = M('syscategory_props');//属性表
        $this->propValuesModel = M('syscategory_prop_values');//属性值表
        $this->itemSkuModel = M("sysitem_sku");//库存表
        $this->siteAreaModel = M('site_area');//地区表
        $this->userAddressModel = M('sysuser_user_addrs');//用户地址表
        $this->cartModel = M('systrade_cart');//购物车表
        $this->itemStatusModel = M('sysitem_item_status');//商品状态表

	}
    public function index(){
        $this->assign('currUrl',$_SERVER['PHP_SELF']);
    	$itemId = I('get.itemId');
        if (!empty($itemId)) {
            //商品信息
            $itemInfo = $this->itemModel->where('item_id = '.$itemId)->find();
            //商品描述
            $itemDesc = $this->itemDescModel->where('item_id = '.$itemId)->find();
            
            //字符串转数组方便调用
            if (!empty($itemInfo['list_image'])) {
                $newItemInfoImage = explode(',',$itemInfo['list_image']);
                $itemInfo['new_list_images'] = $newItemInfoImage;            
            }

            //优惠信息（包邮）
            if ($itemInfo['shop_id']) {
                $freepostageInfo = $this->freepostageModel->where('shop_id = '.$itemInfo['shop_id'])->find();
                if ($freepostageInfo['freepostage_id']) {
                    $freepostageItemInfo = $this->freepostageItemModel->where('freepostage_id = '.$freepostageInfo['freepostage_id'])->find();
                    if ($freepostageItemInfo['item_id'] == 0) {
                        $freepostageLimitMoney = "全场满&nbsp;".sprintf("%.2f",$freepostageInfo['limit_money'])."包邮";
                    }else{
                        $freepostageLimitMoney = "满 ".sprintf("%.2f",$freepostageInfo['limit_money'])." 包邮";
                    }
                }
            }

            //商品属性
            $arrItemSpecDesc = unserialize($itemInfo['spec_desc']);
            
            $specValue;
            $specValueId;
            if (is_array($arrItemSpecDesc)) {
                foreach ($arrItemSpecDesc as $key => $value) {
                    $specValue .= $key.",";
                    foreach ($value as $k => $val) {
                        $specValueId .= $k.",";
                    }
                }
            }

            //查属性表
            if (!empty($specValue)) {
                $where['prop_id'] = array('in',$specValue);;
                $propsList = $this->propsModel->where($where)->select();
                
            }
            //var_dump($propsList);
            //查属性值表
            if (!empty($specValueId)) {
                $where['prop_value_id'] = array('in',$specValueId);
                $propValuesList = $this->propValuesModel->where($where)->select();
            }
            //var_dump($propValuesList);

            //合并两个数组
            $newPropsValuesList = array();
            foreach ($propsList as $key => $value) {
                $newPropsValuesList[$key] = $value;
                foreach ($propValuesList as $k => $val) {
                    if ($val['prop_id'] == $value['prop_id']) {                    
                        $newPropsValuesList[$key]['item'][$k] = $val;
                    }
                }                
            }

            //查询库存表
            $sKuList = $this->itemSkuModel->table('sysitem_sku sku,sysitem_sku_store store')->where('sku.sku_id = store.sku_id and sku.item_id = '.$itemId)->select();

            //更多精选商品
            $itemList = $this->itemModel->where('cat_id ='.$itemInfo['cat_id'])->order('profit_rate desc')->limit(10)->select();

            //地区
            $area = $this->siteAreaModel->where(array('jd_pid' => 0))->select();

            //收货地址查询
            $userAddressList = $this->userAddressModel->where('user_id ='.$this->uid)->select();
            $newUserAddressList = array();
            foreach ($userAddressList as $key => $value) {
                $newUserAddressList[$key] = $value;
                $bNewadd = strstr($value['area'],':',true);
                $newaddId = str_replace('/','_', trim(strstr($value['area'],':'),':'));
                $newUserAddressList[$key]['newadd'] =  $bNewadd.$value['addr'];
                $newUserAddressList[$key]['newaddid'] = $newaddId;
            }
            //商品状态
            $itemStatus = $this->itemStatusModel->where('item_id='.$itemInfo['item_id'])->find();
            
            $this->assign('itemInfo',$itemInfo);
            $itemDesc['pc_desc'] = $this->charback($itemDesc['pc_desc']);
            $this->assign('itemDesc',$itemDesc);
            $this->assign('freepostageLimitMoney',$freepostageLimitMoney);
            $this->assign('propsList',$propsList);
            $this->assign('propsListEmpty','<span class="js-sku-s1">请选择</span>');
            $this->assign('propValuesList',$propValuesList);
            $this->assign('newPropsValuesList',$newPropsValuesList);
            $this->assign('sKuList',$sKuList);
            $this->assign('itemList',$itemList);
            $this->assign('area',$area);
            $this->assign("newUserAddressList",$newUserAddressList);
            $this->assign("itemStatus",$itemStatus);

            $this->display('info');
        }else{
            $this->error('您访问的商品不存在！');
        }

        
    }
    /**
     * 获取地区
     */
    public function ajaxList(){
        // 获取参数
        $pid = I("post.pid");
        if(empty($pid)){
            $pid = 0;
        }
        // 获取地区列表
        $reg_arr = $this->getAreaList($pid);
    
        // 判断是否存在数据 不存在返回null
        if(empty($reg_arr)){
            echo 'null';exit;
        }
    
        // 返回JSON串
        
        echo json_encode($reg_arr);
        exit;
    }
    //获取地区表数据
    public function getAreaList($pid){     
        $area = $this->siteAreaModel->where('jd_pid = '.$pid)->select();
        return $area;
    }



    // 选择不同地区时调用京东接口判断库存 
    public function checkJdStock(){
        $item_id = I('post.item_id','','intval'); // 得到库存的ids
        $jd_ids = I('post.jd_ids','','trim');    
        $where['item_id'] = $item_id;
        $res = $this->itemModel->field('jd_sku')->where($where)->find();
        if($res['jd_sku']>0){       
            $sku[]=array(
                'id'=>$item_id,
                'num'=>1
            );
            $data=array(
                'items'=>json_encode($sku),
                'area'=>trim($jd_ids,'_')
            );
            $result = $this->requestPost(C('API_STORE').'checkCartStock',$data);    
            $retArr = json_decode($result,true);
            $stock=array('status'=>$retArr['data'][$item_id],'msg'=>$retArr['msg']);
        }else{
            $stock=array('status'=>33,'msg'=>'有货');
        }       
        echo $stock['status'];
    }
    
    public function _before_addItemCart(){
        if(empty($this->uid)){
            redirect('Login/login/index');
        }
    }
    //加入购物车
    public function addItemCart(){
        //商品id
        $itemId = I('post.itemId');
        //库存id
        $skuId = I('post.skuId');
        //购买数量
        $quantity = I('post.quantity');

        if ($itemId && $skuId && $quantity) {

            $itemStatus = $this->itemStatusModel->where('item_id='.$itemId)->find();
            if (!$itemStatus && $itemStatus['approve_status'] == 'instock') {
                echo 0;
                exit();
            }

            //查询购物车是否有该商品，如果有的话就直接增加数量
            $cartInfo = $this->cartModel->where('user_id = '.$this->uid.' and sku_id = '.$skuId)->find();
            if ($cartInfo) {
                $res = $this->cartModel->where('cart_id = '.$cartInfo['cart_id'])->setInc('quantity',$quantity);
                if ($res) {
                    echo 1;
                }else{
                    echo 0;
                }
            }else{
                //查询商品详细信息
                $itemInfo = $this->itemModel->where('item_id = '.$itemId)->find();
                //查询库存详细信息
                $skuInfo = $this->itemSkuModel->where('sku_id ='.$skuId)->find();


                //购物车数据
                $data['user_ident']= md5($this->uid);//会员ident,会员信息和session生成的唯一值
                $data['user_id'] = $this->uid;//用户id
                $data['shop_id'] = $itemInfo['shop_id'];//店铺ID
                $data['obj_type'] = 'item';//购物车对象类型
                $data['obj_ident'] = 'item_'.$skuInfo['sku_id'];//item_商品id
                $data['item_id'] = $itemId;//商品id
                $data['sku_id'] = $skuInfo['sku_id'];//sku的id
                $data['title'] = $itemInfo['title'];//商品标题
                $data['image_default_id'] = $itemInfo['image_default_id'];//商品默认图
                $data['quantity'] = $quantity;//数量
                $data['created_time'] = time();//加入购物车时间

                $result = $this->cartModel->data($data)->add();
                if ($result) {
                    echo 1;
                }else{
                    echo 0;
                }
            }
            
        }else{
            //参数错误
            echo 0;
        }
    }
    public function charback($str){
        $str=str_replace(array("&#039;","&quot;","&lt;","&gt;","&amp;reg;","&amp;","&nbsp;",'<p><br />','</p><br />','<br>','\\'),array("'","\"","<",">","&reg;","&"," ",'<p>','</p>','<br />',''),$str);
        $str=preg_replace( '@<script(.*?)</script>@is','&lt;script\1&lt;/script&gt;',$str);
        $str=preg_replace( '@<iframe(.*?)</iframe>@is','',$str);        
        return preg_replace('@<style(.*?)</style>@is', '',$str);
    }
    //根据商品规格获取商品价格
    public function getSkuPrice(){
        $skuId = I('post.skuId');
        if (!$skuId) {
            echo 0;
            exit();
        }
        $skuInfo = $this->itemSkuModel->where('sku_id ='.$skuId)->field('price')->find();
        echo sprintf("%.2f", $skuInfo['price']);

    }
    
    //根据条件查询对应的商品
    public function selectSkuItem(){
        $propValueId = I("post.propValueId");//商品属性值ids返回一个array
        //var_dump($propValueId[0]);
        $itemId = I("post.itemId");
        //$sKuList = M("sysitem_sku")->where('item_id = '.$itemId)->select();
        $sKuList = $this->itemSkuModel->table('sysitem_sku sku,sysitem_sku_store store')->where('sku.sku_id = store.sku_id and sku.item_id = '.$itemId)->select();
        //var_dump($sKuList);
        //$sKuStoreList = M("sysitem_sku_store")->where('item_id = '.$itemId)->select();
        $newSkuList = array(); 
        foreach ($sKuList as $key => $value) {
            $newSkuList = $value;
            $newSkuList['item'] = unserialize($value['spec_desc']);
        }
        //var_dump($newSkuList);
        //var_dump($newSkuList['item']['spec_value_id']);

        //$res = in_array($propValueId[0], $newSkuList['item']['spec_value_id']);
        //var_dump($res);
        //$res = array();
        $res;
        foreach ($propValueId as $key => $value) {
            // $res['rs'] = in_array($propValueId[$key], $newSkuList['item']['spec_value_id']);
            // $res['store'] = $value['store'];
            $res = in_array($propValueId[$key], $newSkuList['item']['spec_value_id']);
            if ($res == false) {
                break;
            }
        }
        echo json_encode($res);
             

    }

}
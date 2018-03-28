<?php
namespace Home\Controller;
use Think\Controller;
class GoodsController extends CommonController
{
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

        $this->item=D("Item");
    }
    //商品详情
    public function info(){
        $itemId = I('get.itemId');
        if(empty($itemId)){
            $this->retError(-1,'商品ID不能为空');
            exit;
        }
        //商品信息
        $itemInfo = $this->item->getItemInfo($itemId,"jd_sku,item_id,cat_id,title,bn,price,mkt_price,weight,image_default_id,list_image,spec_desc,brand_id");
        $brandObj = M('syscategory_brand');
        $brandwhere['brand_id'] = $itemInfo['brand_id'];
        $itemInfo['brand_id'] = $brandObj->where($brandwhere)->field('brand_id,brand_name,brand_alias,brand_logo')->find();
        if(empty($itemInfo)){
            $this->retError(-2,'无此商品！');
        }
        //商品描述
        $itemDesc =$this->item->getItemDesc($itemId);
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
        $specValue="";
        $specValueId="";
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
        //查属性值表
        if (!empty($specValueId)) {
            $where['prop_value_id'] = array('in',$specValueId);
            $propValuesList = $this->propValuesModel->where($where)->select();
        }
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
        //$sKuList =$this->item->getSkuList($itemId);
        $sKuList = $this->getSkuList($itemId);
        //更多精选商品
        $itemList =$this->item->getRecommendItemList($itemInfo,10);
        //地区
        $area = $this->siteAreaModel->where(array('jd_pid' => 0))->select();
        //收货地址查询
            if(empty($this->uid)){
                $this->uid=0;
            }
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
        $itemStatus =$this->item->getItemStatus($itemInfo);
        $itemDesc['pc_desc'] = $this->filterImage($this->charback($itemDesc['pc_desc']));
        //地址列表
        if($this->uid){
        	$conditionssssss=array('user_id'=>$this->uid);
        	$modelUser = D('User');
        	$addrList=$modelUser->getUserAddressList($conditionssssss);
        	if($addrList){
        		foreach($addrList as $k=>$v){
        			$addrArr=explode(':',$v['area']);
        			$addrList[$k]['area']=$addrArr[0];
        			$addrList[$k]['area_id']=$addrArr[1];
        		}
        	}
        }
        
        $data['itemInfo']=$itemInfo;//商品详情
        //$data['propValuesList']=$newPropsValuesList;
        $data['itemStatus']=$itemStatus;//商品状态
        $data['skuList']=$sKuList;//sku列表
        $data['itemDesc']=$itemDesc;//商品详情
        $data['itemList']=$itemList;//更多精选商品
        $data['addressList']=$addrList;//更多精选商品
        $this->retSuccess($data);
    }
    
    
    /**
     * 查询商品的Sku列表
     * @author lihongqiang 2017-01-17
     * @param 商品的ID $itemId
     */
    //获取商品sku列表
//     public function getSkuList($itemId){
//     	$itemId=(int)$itemId;
//     	if($itemId<1){
//     		return false;
//     	}
//     	return M('sysitem_sku')->field("sku.item_id,sku.sku_id,sku.title,sku.price,sku.mkt_price,sku.status,sku.sold_quantity,(store.store-store.freez) as store,sku.spec_desc,sku.spec_desc")->table('sysitem_sku sku,sysitem_sku_store store')->where('sku.sku_id = store.sku_id and sku.item_id = '.$itemId)->select();
//     }
     public function getSkuList($itemId){
     	if($itemId){
     		$Model = M('sysitem_sku');
     		$where['item_id'] = $itemId;
     		$SkuInfo = $Model->where($where)->select();
     		$sysitemSkuStoreObj = M('sysitem_sku_store');
     		foreach ($SkuInfo as &$V){
     			$where['sku_id'] = $V['sku_id'];
     			$where['item_id'] = $itemId;
     			$findSku = $sysitemSkuStoreObj->where($where)->find();
     			$V['store'] = $findSku['store'] - $findSku['freez'];
     			if($V['store']<0){
     				$V['store'] = 0;
     			}
     		}
     		return $SkuInfo;
     	}else{
     		$this->retError(-1,"商品ID不能为空");
     	}
     }
     
    private function filterImage($str){
        $str=stripslashes($str);
        $url=array();
        preg_match_all('/src=\"(.*?(jpg|jpeg|gif|png))/', $str, $url);
        if(empty($url)){
            return false;
        }
        $url=$url[1];
        return $url;
    }
    public function charback($str){
        $str=str_replace(array("&#039;","&quot;","&lt;","&gt;","&amp;reg;","&amp;","&nbsp;",'<p><br />','</p><br />','<br>','\\'),array("'","\"","<",">","&reg;","&"," ",'<p>','</p>','<br />',''),$str);
        $str=preg_replace( '@<script(.*?)</script>@is','&lt;script\1&lt;/script&gt;',$str);
        $str=preg_replace( '@<iframe(.*?)</iframe>@is','',$str);
        return preg_replace('@<style(.*?)</style>@is', '',$str);
    }
    
    
    
    /**
     * @name App商品(搜索)接口
     * @version 1.0
     * @method get
     * @param string $keywords
     * @author lihongqiang 
     */
    public function search(){
    	$keywords = I("get.keywords");//获取get请求的关键字
    	if(empty($keywords)){
    		$Info['message'] = "请输入您要搜索的关键字";
    		$this->retError(-1,$Info['message']);
    	}else{
    		//$this->appReceiveSearchGoodsKeywords($keywords);
    		$goodsObj = M('sysitem_item');
    		$like = '%' . $keywords . '%';
    		$where['title'] = array('like', $like);
    		$uid = $this->uid;
    		if(empty($uid)){
    			$where['profit_rate'] = array('GT', 30);
    		}else{
    			$comId=$this->comId;
    			if(empty($comId)){
    				$where['profit_rate'] = array('GT', 30);
    			}else{
    				if($comId<0){
    					$where['profit_rate'] = array('GT', 30);
    				}else{
    					$company_configObj = M('company_config');
    					$condition['com_id'] = $comId;
    					$profit_config = $company_configObj->where($condition)->field('com_id,profit_rate')->find();
    					if($profit_config){
    						$where['profit_rate'] = array('GT', $profit_config['profit_rate']);
    					}else{
    						$where['profit_rate'] = array('GT', 30);
    					}
    				}
    			}
    		}
    		
    		$goodsData = $goodsObj->alias('a')->join('sysitem_item_status as b ON b.item_id = a.item_id and b.approve_status="onsale"')->where($where)->field('a.*')->select();
    		if(!empty($goodsData)){
	    		$this->retSuccess($goodsData,"操作成功");
    		}else{
    			$this->retSuccess(null,"没有相关数据");
    		}
    	}
    }

    /**
     * @name App商品(详情)接口
     * @version 1.0
     * @method get
     * @param string 商品ID:itemId 
     * @author lihongqiang
     */
    public function details(){
    	$itemId = I("get.itemId");//获取get请求的关键字
    	if($itemId){
    		$Obj = M('sysitem_item_desc');
    		$itemDesc = $this->itemDescModel->where('item_id = '.$itemId)->find();
    		$itemDesc['pc_desc'] = $this->charback($itemDesc['pc_desc']);
    		$this->assign('itemDesc',$itemDesc);
    		$this->display();
    	}else{
    		exit;
    	}
    }
    
    //接口返回错误信息
    public function retError($errCode=1,$msg='操作失败'){
    	$ret=array(
    			'result'=>100,
    			'errcode'=>$errCode,
    			'msg'=>$msg
    	);
    	echo json_encode($ret);
    	exit;
    }
    
    //接口返回结果
    public function retSuccess($data=array(),$msg='操作成功'){
    	$ret=array(
    			'result'=>100,
    			'errcode'=>0,
    			'msg'=>$msg,
    			'data'=>$data
    	);
    	echo json_encode($ret);
    	exit;
    }
    
    
    //收集APP搜索商品的关键字
    public function appReceiveSearchGoodsKeywords($keywords){
    	$app_searchgoods_keywordsObj = M('app_searchgoods_keywords');
    	$where['keywords'] = $keywords;
    	$findData = $app_searchgoods_keywordsObj->where($where)->find();
    	if($findData){
    		$bool = $app_searchgoods_keywordsObj->where($where)->setInc('frequency',1);
    		if($bool){
    			return true;
    		}else{
    			return false;
    		}
    	}else{
    		$data['keywords'] = $keywords;
    		$uid = $this->uid;
    		if($uid){
    			$data['user_id'] = $uid;
    		}else{
    			$data['user_id'] = 0;
    		}
    		$data['createTime'] = date("Y-m-d H:i:s");
    		$data['createIp'] = $_SERVER['REMOTE_ADDR'];
    		$boolData = $app_searchgoods_keywordsObj->add($data);
    		if($boolData){
    			return $boolData;
    		}else{
    			return false;
    		}
    	}
    }
    


}
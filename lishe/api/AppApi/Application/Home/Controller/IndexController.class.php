<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller
{
    public function __construct(){
        parent::__construct();
        $this->paymentsModel = M("ectools_payments");//支付表
        $this->userAccountModel = M('sysuser_account');//用户登录表
        $this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
        $this->tradeModel=M("systrade_trade");//订单表
        $this->userDepositModel = M('sysuser_user_deposit');
        $this->userDataDepositLogModel = M('sysuser_user_deposit_log');//消费/充值日志
        $this->modelShufflingDetail = M('mall_shuffling_figure_detail');
        $this->modelShuFigure = M("mall_shuffling_figure");
        $this->banner=D("banner");
        $this->item=D("Item");
    }
    //商城首页（全部）
    public function indexs(){
        $cate_id=I("get.cate_id");
        if(empty($cate_id)){
            $cate_id=1;
        }
        $data=new \stdClass();
        if($cate_id==1){
        	$id=$this->banner->getAppBanner();
        	$res=$this->banner->getAppBannerList($id,$cate_id);
        	$data->bannerList=$res;
        	// $data->recommendItem=$this->banner->getIndexRecommendItem($cate_id);//获取推荐商品
        	$res=$this->banner->getIndexFloor($cate_id);
        	$arr=$this->banner->getIndexFloorItem($res);
        	foreach($res as $key => $val){
        		$res[$key]['items']=$arr[$val['index_id']];
        	}
        	$data->floors=$res;
        }else{
        	//$id=$this->banner->getAppBanner();
        	//$res=$this->banner->getAppBannerList($id,$cate_id);
        	//$data->bannerList=$res;
        	//$data->recommendItem=$this->banner->getIndexRecommendItem($cate_id);//获取推荐商品
        	$res=$this->banner->getIndexFloor($cate_id);
        	$arr=$this->banner->getIndexFloorItem($res);
        	foreach($res as $key => $val){
        		$res[$key]['items']=$arr[$val['index_id']];
        	}
//         	var_dump($res[0]['items'][0]);exit;
//         	var_dump($res);exit;
        	$data->floors=$res[0];
        	$data->commodity = $res[1]['items'];
        }
//         var_dump($data->floors);exit;
        $this->retSuccess($data,"返回成功");
    }
    
    public function index(){
    	$cate_id=I("get.cate_id");
    	if(empty($cate_id)){
    		$cate_id=1;
    	}
    	if($cate_id==1){
    		$bannerList = C('APP_Home_Lunbo')['bannerList'];
    		$data->bannerList = $bannerList;
    		$APP_Home_Floor = C('APP_Home_Floor');
    		$itemInfoArray = array();
    		foreach ($APP_Home_Floor as &$V){
    			$V['items'] = $this->getConfigItem($V['items']);
    		}
    		$data->floors = $APP_Home_Floor;
    		$this->retSuccess($data);
    	}else{
    		if($cate_id==2){
    			$APP_CateConfig = C('APP_CateConfig1');
    		}
    		if($cate_id==3){
    			$APP_CateConfig = C('APP_CateConfig2');
    		}
    		if($cate_id==4){
    			$APP_CateConfig = C('APP_CateConfig3');
    		}
    		if($cate_id==5){
    			$APP_CateConfig = C('APP_CateConfig4');
    		}
    		if($cate_id==6){
    			$APP_CateConfig = C('APP_CateConfig5');
    		}
    		
//     		$itemInfoArray = array();
//     		foreach ($APP_CateConfig as &$V){
//     			$V['items'] = $this->getConfigItem($V[0]['items']);
//     		}
// 			var_dump($APP_CateConfig);exit;
    		$APP_CateConfig[0]['items'] = $this->getConfigItem($APP_CateConfig[0]['items']); 
//     		var_dump($APP_itemList1);exit;
//     		$APP_CateConfig[0]
    		$APP_itemList2 = $this->getConfigItem($APP_CateConfig[1]);
    		$data->floors= $APP_CateConfig[0];
    		$data->commodity =$APP_itemList2;
    		$this->retSuccess($data,"返回成功");
    	}
    }
    
    public function getConfigItem($ItemIdString){
    	//$ItemIdString = '54164,30508,8731,8732,30512,30510,30507,12324';
    	if(empty($ItemIdString)){
    		return null;
    	}else{
    		$Item_itemObj = M('sysitem_item');
    		$ItemIdArray = explode(",", $ItemIdString);
    		$ItemInfoArray = array();
    		for ($i=0;$i<count($ItemIdArray);$i++){
    			$where['item_id'] = $ItemIdArray[$i];
    			$findItem = $Item_itemObj->where($where)->field('item_id,image_default_id,title,price')->find();
    			if($findItem){
    				array_push($ItemInfoArray, $findItem);
    				//$ItemIdArray[$i] = $findItem;
    			}
    		}
    		//$ItemIdArray =array_filter($ItemIdArray);
    		//$ItemIdArray = array_merge($ItemIdArray);
    		return $ItemInfoArray;
    	}
    }
    
    
    
    //商城首页分类
    public function cate_floors(){
        $cate_id=I("get.cate_id");
        if(empty($cate_id)){
            $cate_id=1;
        }
        $data=new \stdClass();
        $id=$this->banner->getAppBanner();
        $res=$this->banner->getAppBannerList($id,$cate_id);
        $data->bannerList=$res;
        $res=$this->banner->getIndexFloor($cate_id);
        $arr=$this->banner->getIndexFloorItem($res);
        foreach($res as $key => $val){
            $res[$key]['items'][]=$arr[$val['index_id']];
        }
       // $data->recommendItem=$this->banner->getIndexRecommendItem($cate_id);//获取推荐商品
        $data->floors=$res;//楼层，分类支持多楼层，虽然平时只有一个楼层
        //分类商品列表
        $this->retSuccess($data,"返回成功");
    }
    //分类商品列表
    public function item_list(){
        $page=I('get.page')-1;
        $count=I("get.count");
        $cat=I("get.cat_id")?I("get.cat_id"):50;
        $fields="item_id,shop_id,title,price,mkt_price,image_default_id,is_offline";
        //获取商品列表
        $items=$this->item->getItemList($page,$count,$cat,$fields);
        //下架状态
        $res=$this->item->filterItem($items);
        $this->retSuccess($res);
    }


    public function categoryList(){
        $cates=C('INDEX_TEIM_LIST');
        $this->retSuccess($cates);
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


}
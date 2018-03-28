<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {	
	
	public function __construct(){
		parent::__construct();
		
		$GD10086Url = C('GD10086');
		header("Access-Control-Allow-Credentials: true");
		header('Access-Control-Allow-Origin:'.$GD10086Url);//跨域请求
		
		//如果商城清除cookie，则清除所有的cookie和session，实现同步退出
		$accountCookie=cookie('account');
		if(empty($accountCookie)){
			session(null);
			cookie('account',null);
			cookie('LSUID',null);
			cookie('UNAME',null);
		}
		
		$accountSession=session('account');
		if(!empty($accountSession)){
			$account=$accountSession['member'];
		}
		if(empty($accountSession) && !empty($accountCookie)){
			$account=json_decode($accountCookie,true);
		}
		
		if(!empty($account['id']) and !empty($account['comId']) and !empty($account['account'])){
			$this->uid=$account['id'];
			$this->comId=$account['comId'];
			$this->account=$account['account'];
			$this->userName=$account['userName'];
			$this->index=$account['index'];
			//个人信息
			$userInfo = $this->userInfo();
			$this->assign('userInfo',$userInfo);
		}else{
			session(null);
			cookie(null);
			header("Location:/shop.php/Sign");
			exit;
		}
		$action=strtolower(ACTION_NAME);		
		$control=strtolower(CONTROLLER_NAME);
		$this->action=$action;
		$this->control=$control;
		
		$this->assign('uid',$this->uid);
		$this->assign('comId',$this->comId);
		$this->assign('account',$this->account);
		$this->assign('userName',$this->userName);
		$this->assign('action',$action);
		$this->assign('control',$control);
		$this->assign('root',C('ROOT')); 
	}
	
	//模拟提交
	public function requestPost($url='', $data=array()) {
        if(empty($url) || empty($data)){
            return false;
        }
        $o="";
        foreach($data as $k=>$v){
            $o.="$k=".$v."&";
        }
        $param=substr($o,0,-1);
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS, $param);
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }
    
    public function requestJdPost($url='', $data=''){
    	if(empty($url) || empty($data)){
    		return false;
    	}
    	$ch=curl_init();//初始化curl
    	curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
    	curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    	curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
    	curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
    	$return=curl_exec($ch);//运行curl
    	curl_close($ch);
    	return $return;
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
    //文件日志
    public function makeLog($type='',$data=''){
		if(!empty($type)){
			@file_put_contents(C('DIR_LOG').$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
		}
	}

    //获取用户资料
	public function userInfo(){
		//取出个人积分
		$userInfo['point'] = M('sysuser_user_deposit')->where('user_id='.$this->uid)->getField('balance');		
		//优惠券
		$userInfo['coupon'] = M('sysuser_user_coupon')->where('user_id ='.$this->uid)->count();
		//购物车
		$userInfo['cartCount'] = M("systrade_cart")->where('user_id ='.$this->uid)->count();
		$userInfo['cart'] = M("systrade_cart")->table('sysitem_item a,systrade_cart b')->where('a.item_id=b.item_id and b.user_id='.$this->uid)->field('b.cart_id,b.shop_id,b.item_id,b.sku_id,b.title,b.image_default_id,b.quantity,a.price,a.weight')->select();
		foreach ($userInfo['cart'] as $key => $value) {			
			$userInfo['cart'][$key]['price'] = sprintf("%.2f",$value['price']);
		}

		//$user = D('systradeCart')->relation(true)->where('user_id ='.$this->uid)->select();

		//用户信息表
		$userInfo['user'] = M('sysuser_user')->where('user_id ='.$this->uid)->field('name')->find();
		//礼舍币表
		$userInfo['points']= M('sysuser_user_points')->where('user_id ='.$this->uid)->find();

		//浏览记录
		$userInfo['browList'] = M('sysitem_item_history')->where('user_id ='.$this->uid)->select();
		return $userInfo; 
	}
}
<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {	
	
	public function __construct(){
		parent::__construct();
		
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
			$this->index=urldecode($account['index']);
			//个人信息
			$userInfo = $this->userInfo();
			$this->assign('userInfo',$userInfo);
		}else{
			$this->uid='';
			$this->comId='';
			$this->account='';
			$this->userName='';
		}
		$action=strtolower(ACTION_NAME);		
		$control=strtolower(CONTROLLER_NAME);
		$comDomain=$this->getComDomain();
		$this->action=$action;
		$this->control=$control;
		$this->comDomain='http://'.$comDomain.'.lishe.cn';
		
		$this->assign('uid',$this->uid);
		$this->assign('comId',$this->comId);
		$this->assign('account',$this->account);
		$this->assign('userName',$this->userName);
		$this->assign('action',$action);
		$this->assign('control',$control);
		$this->assign('root',C('ROOT')); 
		$this->assign('comDomain',$this->comDomain);
	}
	
	//超级管理员登录 赵尊杰 2016-09-01
	public function superAdminLogin($userName,$password,$mark){
		$loginUid=1;
		$loginAccount='13800008888';
		$loginPass=md5('lishe000888');
		$loginUserName='超级管理员';
		if($userName==$loginAccount){
			if($loginPass!=md5($password)){
				echo json_encode(array(-1,'超级管理员密码不正确',-1));
				exit;
			}
			$account=array(
        		'id'=>$loginUid,
        		'account'=>$loginAccount,
        		'userName'=>$loginUserName
    		);
			$condition['mark']=$mark;
			$userCompany=M('company_config')->field('com_id,refer,index')->where($condition)->find();
			if(empty($userCompany['com_id'])){
				echo json_encode(array(-2,'找不到该企业的信息！',-2));
				exit;
			}
			$account['comId']=$userCompany['com_id'];
			$account['index']=urlencode($userCompany['index']);					
			$account['refer']=urlencode($userCompany['refer']);
			
			session('account',array('member'=>$account));
    		cookie('account',json_encode($account));
    		cookie('LSUID',$loginUid);
    		cookie('UNAME',$data['empName']);
    		echo json_encode(array($loginUid,'登录成功！',$userCompany['refer']));
    		exit;
		}
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
    
    //增加授权模拟请求方法 赵尊杰 2016-07-01
    public function accreditPost($url,$data,$user,$password){
        if(empty($url) || empty($data) || empty($user) || empty($password)){
            return false;
        }
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch,CURLOPT_USERPWD,''.$user.':'.$password.'');       
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
			$userIUnfo['cartSumPrice'] += sprintf("%.2f",$value['price']); 
		}

		//$user = D('systradeCart')->relation(true)->where('user_id ='.$this->uid)->select();

		//用户信息表
		$userInfo['user'] = M('sysuser_user')->where('user_id ='.$this->uid)->field('name')->find();
		//礼舍币表
		$userInfo['points']= M('sysuser_user_points')->where('user_id ='.$this->uid)->find();
		//商品收藏
		$map = array(
			'user_id' => $this->uid,
			'object_type' => 'goods',
		);
		$userInfo['fav'] = M('sysuser_user_fav')->where($map)->select();
		//浏览记录
		$userInfo['browList'] = M('sysitem_item_history')->where('user_id ='.$this->uid)->select();
		//判断今天昨天一天前
	   	//获取今天凌晨的时间戳
	   	$day = strtotime(date('Y-m-d',time()));
	   	//获取昨天凌晨的时间戳
	  	$pday = strtotime(date('Y-m-d',strtotime('-1 day')));
		foreach($userInfo['browList'] as $key=>$val){
			if($val['add_time'] >= $day){
				//今天
				$userInfo['history']['today'][]=$val;
			}else if($val['add_time'] >= $pday && $val['add_time'] < $day){
				$userInfo['history']['yesterday'][]=$val;
			}else{
				$userInfo['history']['other'][]=$val;
			}
			
		}
		return $userInfo; 
	}
	
	public function getComDomain(){
		$host=$_SERVER['HTTP_HOST'];
    	$domain=current(explode(".",$host));
    	if(empty($domain)){
			$domain='www';
		}
    	return $domain;
    }
}
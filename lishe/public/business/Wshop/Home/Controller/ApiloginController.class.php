<?php  
namespace Home\Controller;
class ApiloginController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->userAccountModel = M('sysuser_account');//用户登录表
	}

	//用户自动登录 comid/1472542918735/mobile/15222380129/sign/698ef57ac8e1800f6ac4f551da8235e8
	public function testlogin(){
		$key = "06483BBD4D2149E3AF44281AF5EC217F";
		$comId = '1472542918735';
		$mobile = '15222380129';
		$newSign = md5("LISHEWANG"."key=".$key."comId=".$comId."mobile=".$mobile);
		var_dump($newSign);
	}

	//用户自动登录
	public function login(){
		$comId = I("get.comid");
		$thisComId = '1472542918735';
		$mobile = I("get.mobile");
		$sign = I("get.sign");
		//userCenter：用户中心,order:订单中心,wsIndex:商城首页
		$des = I("get.des");
		$key = "06483BBD4D2149E3AF44281AF5EC217F";
		if($mobile){
			$oldSign = md5("LISHEWANG"."key=".$key."comId=1472542918735mobile=".$mobile);
			$newSign = md5("LISHEWANG"."key=".$key."comId=".$comId."mobile=".$mobile);
			if($sign==$newSign or $sign==$oldSign){
				if($comId!=$thisComId){
					$comId=$thisComId;
				}
				if($this->xhLogin($comId,$mobile)){
					if (trim($des) == 'wsIndex') {
						$this->redirect('Welfare/index');
					}else if (trim($des) == 'order') {
						$this->redirect('Order/orderStatus');
					}else if (trim($des) == 'welfare') {
						$this->redirect('Welfare/index');
					}else{
						$this->redirect('User/userCenter');
					}					
				}else{
					echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
					echo "<script language=\"JavaScript\">\r\n"; 
					echo " alert(\"不是该企业员工，登录失败！\");\r\n"; 
					echo "window.location.href=\"http://www.lishe.cn/business/wshop.php\"\r\n"; 
					echo "</script>";
					exit();
				}
			}else{
				echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
				echo "<script language=\"JavaScript\">\r\n"; 
				echo " alert(\"非法登录！\");\r\n";                                            
				echo "window.location.href=\"http://www.lishe.cn/business/wshop.php\"\r\n"; 
				echo "</script>";
				exit(); 				
			}
		}else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"自动登录失败！\");\r\n"; 
			echo "window.location.href=\"http://www.lishe.cn/business/wshop.php\"\r\n"; 
			echo "</script>";
			exit(); 
		}
	}
	
	//登录
	public function xhLogin($comId,$userName){
		$sign=md5('com_id='.$comId.'&phone_num='.$userName.C('API_KEY'));
		$data=array(
        	'phone_num'=>$userName,
        	'com_id'=>$comId,
        	'sign'=>$sign
        );
        $login=$this->requestPost(C('API').'mallUser/empLoginNew',$data);
        
        $uclogin=json_decode($login,TRUE);
        $data=$uclogin['data']['info'];
		if(empty($data['userId'])){
			//用户信息不存在
			return false;
		}else{
			//更新本地信息
			$balance=array(
        		'deposit'=>$data['balance']/100,
        		'balance'=>$data['balance'],
        		'commonAmount'=>$data['commonAmount'],
        		'limitAmount'=>$data['limitAmount'],
        		'comId'=>$data['comId'],
        		'comName'=>$data['comName']
        	);
        	
			$condition['mobile']=$userName;
			$checkUser=$this->userAccountModel->field('user_id')->where($condition)->find();
			if(empty($checkUser['user_id'])){
				//如果没有发现本地信息，注册用户
	        	$user=array(
	        		'login_account'=>$userName,
	        		'mobile'=>$userName,
	        		'login_password'=>'sync'
	        	);
	        	$info=array(
	        		'ls_user_id'=>$data['userId'],
	        		'name'=>$data['empName'],	
	        		'username'=>$data['empName']
	        	);		        	
	        	$userId=A('User')->register($user,$info,$balance);
			}else{
				//检测登录权限
				if($data['comId']!=$comId){						
					//不是该企业用户
					return false;
				}				
				//更新积分
				$userId=$checkUser['user_id'];
				A('User')->syncBalance($userId,$balance);
			}				
    		$account=array(
        		'id'=>$userId,
        		'comId'=>$data['comId'],
        		'account'=>$userName,
        		'userName'=>$data['empName']
    		);
    		session('account',json_encode($account));
    		cookie('account',json_encode($account));
    		return true;
    	}        
	}

	
}

<?php
namespace Home\Controller;
class CactiveController extends CommonController {
	/**
	 * 章锐
	 * 2016/8/17-2016/8/18
	 * */
		public function __construct(){
		parent::__construct();
		$this->comId=session('comId');
        $this->companyConfigModel = M('company_config');//公司配置表
        $this->userDepositModel = M('sysuser_user_deposit');//用户积分表
        $this->userAccountModel = M('sysuser_account');//用户登录信息
		$this->assign('status',$this->status);
		$this->dShow=D('Show');		
	}

    public function index(){
		$comId=$this->comId;
		$condition['com_id']=$comId;
		$condition['is_delete']=0;
		$templete=$this->dShow->getCompanyInfo($condition,"templete_header,templete_footer");
		$this->assign('templete',$templete);
    	$this->display();        
    }
    //卡激活操作
    public function cActive(){
        $this->checkActivate();

    }

    //激活时验证用户账号
    public function checkActivate(){
        $cobj = I('post.cobj');
        $mobile = I('post.mobile');   
        $card = I('post.card');   
        $cpwd = I('post.cpwd');   

        $res=$this->checkMember();
        if($res[0]==100){
        	//查询本地会员信息
        	$userInfo=$this->userAccountModel->where('mobile='.$res[1]['phoneNum'])->field('user_id')->find();      
	        $account = array(
	            'id'=>$userInfo['user_id'],
	            'account'=>$res[1]['phoneNum'],
	            'userName'=>$res[1]['empName'],
	            'comId'=>$res[1]['comId']
	        );
	        session('account',$account);				
			cookie('account',json_encode($account));
        	
            //更新本地积分 
            $pdata=array(
                'deposit' => $res[1]['balance']/100,
                'balance' => $res[1]['balance'],
                'commonAmount' => $res[1]['commonAmount']
            ); 
            $this->userDepositModel->where('user_id ='.$userInfo['user_id'])->save($pdata);
            
            //查询公司
            $companyInfo = $this->companyConfigModel->field('config_id,com_id,refer')->where('com_id ='.$res[1]['comId'])->find();
            //充值成功cobj为1（客户）返回到商城首页，cobj为2（员工）返回到二级域名            
            if ($cobj == 2 ) {
                echo json_encode(array(100,'充值成功！当前可用积分：'.$res[1]['balance'],$companyInfo['refer']));
            }else{
                echo json_encode(array(100,'充值成功！当前可用积分：'.$res[1]['balance'],"http://www.lishe.cn/shop.html"));
            }
            
        }else{
            if ($res[2] == 3013) {//该手机号码未被注册
                //注册充值->成功后跳转到设置密码页面
                //跳转到用户设置密码页面
                echo json_encode(array(1,'激活成功，设置密码后积分到账！'));
            }elseif($res[2] == 3045){//积分卡卡号或密码错误
                //提示错误
                echo json_encode(array(3,'积分卡卡号或密码错误'));

            }elseif($res[2] == 3048){//员工所属公司和积分卡所属公司不一致
                //错误提示
                echo json_encode(array(3,'员工所属公司和积分卡所属公司不一致'));
            }else{
                echo json_encode($res);
            }
            
        }
    }
    //本地注册
    public function register($account,$info,$balance){
        $account['createtime']=time();
        $account['modified_time']=time();
        $userId=$this->userAccountModel->add($account);
        if($userId>0){
            $info['user_id']=$userId;
            M('sysuser_user')->add($info);
            $balance['user_id']=$userId;
            $this->userDepositModel->add($balance);
        }
        return $userId;
    }

    //检测用户的注册和激活状态
    public function checkMember(){
        $cobj = I('post.cobj');
        $mobile = I('post.mobile');   
        $card = I('post.card');   
        $cpwd = I('post.cpwd');   
		$comId=$this->comId;
        $sign=md5('activeSource='.$comId.'&cardPwd='.$cpwd.'&cardno='.$card.'&phoneNum='.$mobile.'&source=pc&step=1&userType='.$cobj.C('API_KEY'));
	    $data=array(
            'activeSource'=>$comId,
            'cardno'=>$card,
            'cardPwd'=>$cpwd,
            'phoneNum'=>$mobile,
            'userType'=>$cobj,
            'source'=>'pc',
            'step'=>1,
            'sign'=>$sign
        );
      	$res=$this->requestPost(C('API').'mallPointCard/exchange',$data);
        $return=json_decode($res,TRUE);
        if($return['result']==100){
            $data=$return['data']['info'];
            return array(100,$data,$return['errcode']);
        }elseif ($return['result']==3) {
            return array(3,$return['msg'],$return['errcode']);
        }else{
            return array(-1,$return['msg'],$return['errcode']);
        }
    }

    //设置密码
    public function doSetPwd(){
        $pwd = I('pwd');
        $name = I('name');
        $email = I('email');
        $cobj = I('cobj');
        $mobile = I('mobile');   
        $card = I('card');   
        $cpwd = I('cpwd');   		
		$comId=$this->comId;
		if($cobj==1){
       		$sign=md5('activeSource='.$comId.'&cardPwd='.$cpwd.'&cardno='.$card.'&loginPwd='.$pwd.'&phoneNum='.$mobile.'&source=pc&step=2&userType='.$cobj.C('API_KEY'));
			
		}else if($cobj==2){
       		$sign=md5('activeSource='.$comId.'&cardPwd='.$cpwd.'&cardno='.$card.'&email='.$email.'&loginPwd='.$pwd.'&phoneNum='.$mobile.'&source=pc&step=2&userName='.$name.'&userType='.$cobj.C('API_KEY'));
		}		
	    $data=array(
            'activeSource'=>$comId,
            'cardno'=>$card,
            'cardPwd'=>$cpwd,
            'phoneNum'=>$mobile,
            'userType'=>$cobj,
            'source'=>'pc',
            'step'=>2,
	    	'loginPwd'=>$pwd,
            'sign'=>$sign
        );
		if($cobj==2){
			$data['userName']=$name;
			$data['email']=$email;
		}
        $return=$this->requestPost(C('API').'mallPointCard/exchange',$data);
	  	$res=json_decode($return,TRUE);
        if($res['result']==100){
            $datas=$res['data']['info'];
            $result=array(100,$datas,$res['errcode']);
        }elseif ($return['result']==3) {
            $result=array(3,$res['msg'],$res['errcode']);
        }else{
            $result=array(-1,$res['msg'],$res['errcode']);
        }
		if($result[0]==100){
			if(empty($datas['comName'])){
				$datas['comName']="暂无";
			}
            $user=array(
                'login_account'=>$datas['phoneNum'],
                'mobile'=>$datas['phoneNum'],
                'login_password'=>'activate'
            );
            $info=array(
                'ls_user_id'=>$datas['userId'],
                'name'=>$datas['empName'],
                'username'=>$datas['empName']
            );
            $balance=array(
                'deposit'=>$datas['balance']/100,
                'balance'=>$datas['balance'],
                'commonAmount'=>$datas['commonAmount'],
                'limitAmount'=>$datas['limitAmount'],
                'comId'=>$datas['comId'],
                'comName'=>$datas['comName']
            );
            $userId=$this->register($user,$info,$balance);	
			if($userId){
		        $account = array(
		            'id'=>$userId,
		            'account'=>$datas['phoneNum'],
		            'userName'=>$datas['empName'],
		            'comId'=>$datas['comId']
		        );
		        session('account',$account);	
				cookie('account',json_encode($account));
		        $addres = $this->userAccountModel->where('user_id ='.$userId)->save(array('login_password'=>md5($pwd)));
	       		if(!$addres){
	                echo json_encode(array(0,'密码设置失败！'));
	       		}
	       		
	       		//更新本地积分 
	            $pdata=array(
	                'deposit' => $datas['balance']/100,
	                'balance' => $datas['balance'],
	                'commonAmount' => $datas['commonAmount']
	            ); 
	            $this->userDepositModel->where('user_id ='.$userId)->save($pdata);
	            if ($cobj==2){
	                //查询公司
	                $companyInfo = $this->companyConfigModel->field('config_id,com_id,refer')->where('com_id ='.$datas['comId'])->find();
				    echo json_encode(array(1,'密码设置成功！当前可用积分：'.$datas['balance'],$companyInfo['refer']));
	            }else{
	                echo json_encode(array(1,'密码设置成功！当前可用积分：'.$datas['balance'],'http://www.lishe.cn/shop.html'));
	            }					
			}

		}else{
            echo json_encode($result);
			
		}

    }
	//单客户卡激活
	public function customerIndex(){
		$templete['templete_header']='commonHeader';
		$templete['templete_footer']='commonFooter';
		$this->assign('templete',$templete);
    	$this->display();        
	}
    //单客户激活时验证用户账号
    public function customerCheckActivate(){
        $cobj = I('post.cobj');
        $mobile = I('post.mobile');   
        $card = I('post.card');   
        $cpwd = I('post.cpwd');   

        $res=$this->customerCheckMember();
        if($res[0]==100){
        	//查询本地会员信息
        	$userInfo=$this->userAccountModel->where('mobile='.$res[1]['phoneNum'])->field('user_id')->find();      
	        $account = array(
	            'id'=>$userInfo['user_id'],
	            'account'=>$res[1]['phoneNum'],
	            'userName'=>$res[1]['empName'],
	            'comId'=>$res[1]['comId']
	        );
	        session('account',$account);				
			cookie('account',json_encode($account));
        	
            //更新本地积分 
            $pdata=array(
                'deposit' => $res[1]['balance']/100,
                'balance' => $res[1]['balance'],
                'commonAmount' => $res[1]['commonAmount']
            ); 
            $this->userDepositModel->where('user_id ='.$userInfo['user_id'])->save($pdata);
            
            echo json_encode(array(100,'充值成功！当前可用积分：'.$res[1]['balance'],"http://www.lishe.cn/shop.html"));
            
        }else{
            if ($res[2] == 3013) {//该手机号码未被注册
                //注册充值->成功后跳转到设置密码页面
                //跳转到用户设置密码页面
                echo json_encode(array(1,'激活成功，设置密码后积分到账！'));
            }elseif($res[2] == 3045){//积分卡卡号或密码错误
                //提示错误
                echo json_encode(array(3,'积分卡卡号或密码错误'));

            }elseif($res[2] == 3048){//员工所属公司和积分卡所属公司不一致
                //错误提示
                echo json_encode(array(3,'员工所属公司和积分卡所属公司不一致'));
            }else{
                echo json_encode($res);
            }
            
        }
    }	
    //检测用户的注册和激活状态
    public function customerCheckMember(){
        $cobj = I('post.cobj');
        $mobile = I('post.mobile');   
        $card = I('post.card');   
        $cpwd = I('post.cpwd');   
		$comId='-1';
        $sign=md5('activeSource='.$comId.'&cardPwd='.$cpwd.'&cardno='.$card.'&phoneNum='.$mobile.'&source=pc&step=1&userType='.$cobj.C('API_KEY'));
	    $data=array(
            'activeSource'=>$comId,
            'cardno'=>$card,
            'cardPwd'=>$cpwd,
            'phoneNum'=>$mobile,
            'userType'=>$cobj,
            'source'=>'pc',
            'step'=>1,
            'sign'=>$sign
        );
      	$res=$this->requestPost(C('API').'mallPointCard/exchange',$data);
        $return=json_decode($res,TRUE);
        if($return['result']==100){
            $data=$return['data']['info'];
            return array(100,$data,$return['errcode']);
        }elseif ($return['result']==3) {
            return array(3,$return['msg'],$return['errcode']);
        }else{
            return array(-1,$return['msg'],$return['errcode']);
        }
    }
    //设置密码
    public function customerDoSetPwd(){
        $pwd = I('pwd');
        $name = I('name');
        $email = I('email');
        $cobj = I('cobj');
        $mobile = I('mobile');   
        $card = I('card');   
        $cpwd = I('cpwd');   		
		$comId='-1';
		if($cobj==1){
       		$sign=md5('activeSource='.$comId.'&cardPwd='.$cpwd.'&cardno='.$card.'&loginPwd='.$pwd.'&phoneNum='.$mobile.'&source=pc&step=2&userType='.$cobj.C('API_KEY'));
			
		}else if($cobj==2){
       		$sign=md5('activeSource='.$comId.'&cardPwd='.$cpwd.'&cardno='.$card.'&email='.$email.'&loginPwd='.$pwd.'&phoneNum='.$mobile.'&source=pc&step=2&userName='.$name.'&userType='.$cobj.C('API_KEY'));
		}		
	    $data=array(
            'activeSource'=>$comId,
            'cardno'=>$card,
            'cardPwd'=>$cpwd,
            'phoneNum'=>$mobile,
            'userType'=>$cobj,
            'source'=>'pc',
            'step'=>2,
	    	'loginPwd'=>$pwd,
            'sign'=>$sign
        );
		if($cobj==2){
			$data['userName']=$name;
			$data['email']=$email;
		}
        $return=$this->requestPost(C('API').'mallPointCard/exchange',$data);
	  	$res=json_decode($return,TRUE);
        if($res['result']==100){
            $datas=$res['data']['info'];
            $result=array(100,$datas,$res['errcode']);
        }elseif ($return['result']==3) {
            $result=array(3,$res['msg'],$res['errcode']);
        }else{
            $result=array(-1,$res['msg'],$res['errcode']);
        }
		if($result[0]==100){
			if(empty($datas['comName'])){
				$datas['comName']="暂无";
			}
            $user=array(
                'login_account'=>$datas['phoneNum'],
                'mobile'=>$datas['phoneNum'],
                'login_password'=>'activate'
            );
            $info=array(
                'ls_user_id'=>$datas['userId'],
                'name'=>$datas['empName'],
                'username'=>$datas['empName']
            );
            $balance=array(
                'deposit'=>$datas['balance']/100,
                'balance'=>$datas['balance'],
                'commonAmount'=>$datas['commonAmount'],
                'limitAmount'=>$datas['limitAmount'],
                'comId'=>$datas['comId'],
                'comName'=>$datas['comName']
            );
            $userId=$this->register($user,$info,$balance);	
			if($userId){
		        $account = array(
		            'id'=>$userId,
		            'account'=>$datas['phoneNum'],
		            'userName'=>$datas['empName'],
		            'comId'=>$datas['comId']
		        );
		        session('account',$account);	
				cookie('account',json_encode($account));
		        $addres = $this->userAccountModel->where('user_id ='.$userId)->save(array('login_password'=>md5($pwd)));
	       		if(!$addres){
	                echo json_encode(array(0,'密码设置失败！'));
	       		}
	       		
	       		//更新本地积分 
	            $pdata=array(
	                'deposit' => $datas['balance']/100,
	                'balance' => $datas['balance'],
	                'commonAmount' => $datas['commonAmount']
	            ); 
	            $this->userDepositModel->where('user_id ='.$userId)->save($pdata);
                echo json_encode(array(1,'密码设置成功！当前可用积分：'.$datas['balance'],'http://www.lishe.cn/shop.html'));
			}

		}else{
            echo json_encode($result);
			
		}

    }

    // 兑换干洗券
    public function zzgxCoupon() {
    
    	$exchRate = C('ZZGX_EXCH_RATE');
    	$exchRate = empty($exchRate) ? 0 : $exchRate;
    	$multiple = 100;
    	$curURL = U('Cactive/zzgxCoupon','',true,true);
    
    	$where = array();
    	$where['user_id'] = $this->uid;
    	$mobile = $this->userAccountModel->where($where)->getField('mobile');
    
    	$this->assign ( 'multiple', $multiple);
    	$this->assign ( 'exchRate', $exchRate);
    	$this->assign ( 'mobile', $mobile);
    	$this->assign ( 'curURL', urlencode($curURL));
    	$this->display ( 'zzgxCoupon' );
    }

}
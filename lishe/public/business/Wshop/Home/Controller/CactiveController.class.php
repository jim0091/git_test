<?php
namespace Home\Controller;
class CactiveController extends CommonController {
		public function __construct(){
		parent::__construct();
        $this->companyConfigModel = M('company_config');//公司配置表
        $this->userDepositModel = M('sysuser_user_deposit');//用户积分表
        $this->userAccountModel = M('sysuser_account');//用户登录信息
	}

    public function index(){

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
        $code = I('post.code'); 
        if (empty($cobj)) {
            echo json_encode(array(-1,'请选择卡类型','-1'));
            exit;            
        }       
        if(empty($mobile)){
            echo json_encode(array(-2,'手机号码为空','-2'));
            exit;
        }      
        if(empty($card)){
            echo json_encode(array(-3,'卡账号不能为空','-3'));
            exit;
        }    
        if(empty($cpwd)){
            echo json_encode(array(-4,'卡密码不能为空','-4'));
            exit;
        }
        if(strtolower(session('imgCode'))!=strtolower($code)){
            echo json_encode(array(-5,'图片验证码不正确','-5'));
            exit;
        }
        $res=$this->checkMember();
        //var_dump($res);
        // var_dump($res[1]['comId']);
        //exit;
        if($res[0]==100){

            //查询公司mark
            $companyInfo = $this->companyConfigModel->field('config_id,com_id,mark')->where('com_id ='.$res[1]['comId'])->find();

            //更新本地积分 
            $pdata=array(
                'deposit' => data[1]['balance']/100,
                'balance' => data[1]['balance'],
                'commonAmount' => data[1]['commonAmount']
                );           
            $this->userDepositModel->where('user_id =1'.$res[1]['userId'])->save($pdata);


            //充值成功cobj为1（客户）返回到商城首页，cobj为2（员工）返回到二级域名            
            if ($cobj == 2 ) {
                echo json_encode(array(100,'充值成功！','http://test.lishe.cn/business/index.php/'.ucfirst($companyInfo['mark'])));
            }else{
                echo json_encode(array(100,'充值成功！',"http://test.lishe.cn/shop.html"));
            }
            
        }else{
            if ($res[2] == 3013) {//该手机号码未被注册
                //注册充值->成功后跳转到设置密码页面

                //本地注册
                $user=array(
                    'login_account'=>$res[1]['phoneNum'],
                    'mobile'=>$res[1]['phoneNum'],
                    'login_password'=>'activate'
                );
                $info=array(
                    'ls_user_id'=>$res[1]['userId'],
                    'name'=>$res[1]['empName'],
                    'username'=>$res[1]['empName']
                );
                $balance=array(
                    'deposit'=>$res[1]['balance']/100,
                    'balance'=>$res[1]['balance'],
                    'commonAmount'=>$res[1]['commonAmount'],
                    'limitAmount'=>$res[1]['limitAmount'],
                    'comId'=>$res[1]['comId'],
                    'comName'=>$res[1]['comName']
                );
                $userId=$this->register($user,$info,$balance);
                //跳转到用户设置密码页面
                echo json_encode(array(1,'注册充值成功，请设置您的登录密码！'));


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
        $code = I('post.code'); 

        $sign=md5('cardno='.$card.'&cardPwd='.$cpwd.'&phoneNum='.$mobile.'&userType='.$cobj.'&source=h5&step=1'.C('API_KEY'));
        $data=array(
            'cardno'=>$card,
            'cardPwd'=>$cpwd,
            'phoneNum'=>$mobile,
            'userType'=>$cobj,
            'source'=>'h5',
            'step'=>1,
            'sign'=>$sign
        );
        //$res=$this->requestPost(C('API').'mallPointCard/exchange',$data);
        $res ='{
            "result": 3,
            "errcode": 3013,
            "msg": "ok",
            "data": {
                "info": {
                    "freezeAmount": 0,
                    "balance": 60000,
                    "empName": "景积分",
                    "commonAmount": 60000,
                    "limitAmount": 0,
                    "phoneNum": "15602960147",
                    "comId": "1467166836740",
                    "comName": 1467166836740,
                    "userId": 11215
                }
            }
        }';
        $return=json_decode($res,TRUE);
        $account = array(
            'userId'=>$return['data']['info']['userId'],
            'account'=>$return['data']['info']['phoneNum'],
            'userName'=>$return['data']['info']['empName'],
            'usertype'=>$cobj,
            'comId'=>$return['data']['info']['comId']
        );
        session('account',$account);
        if($return['result']==100){
            $data=$return['data']['info'];
            return array(100,$data,$return['errcode']);
        }elseif ($return['result']==3) {
            $data=$return['data']['info'];
            return array(3,$data,$return['errcode']);
        }
        else{
            return array(-1,$return['msg'],$return['errcode']);
        }
    }

    //设置密码
    public function setPwd(){
        $this->display();
    }
    //设置密码
    public function doSetPwd(){
        $userSession = session('account');
        $userId = $userSession['userId'];
        $pwd = I('post.pwd');
        var_dump($pwd);
        
        $res = $this->userAccountModel->where('user_id ='.$userId)->save(array('login_password'=>md5($pwd)));
        var_dump($res);
        if ($res) {
            if ($userSession['usertype'] = 2 ) {
                //查询公司mark
                $companyInfo = $this->companyConfigModel->field('config_id,com_id,mark')->where('com_id ='.$userSession['comId'])->find();
                echo json_encode(array(1,'设置成功！','http://test.lishe.cn/business/index.php/'.ucfirst($companyInfo['mark'])));
            }else{
                echo json_encode(array(1,'设置成功！','http://test.lishe.cn/shop.html'));
            }
        }else{
            echo json_encode(array(0,'设置失败！'));
        }
    }


}
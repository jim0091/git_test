<?php
namespace Home\Controller;
use Think\Controller;
class InfoController extends CommonController {
	public function __construct(){
		parent::__construct();
		if(empty($this->uid)){
			header("Location:".__SHOP__."/Sign/index");
			exit;
		}			
		$this->modelInfo=D('Info');	
	}

    //欢迎页面
    public function index(){
        $userInfo = $this->modelInfo->getUser('user_id='.$this->uid);
        $userDepositInfo = $this->modelInfo->getUserDepositInfo('user_id='.$this->uid);

        //查询所属公司
        if (!empty($this->comId)) {
            $companyInfo = $this->modelInfo->getCompanyInfo("com_id=".$this->comId,'com_name');
            $this->assign('companyInfo',$companyInfo);
        }

        //推荐商品
        $browList = $this->modelInfo->getHistoryList('user_id='.$this->uid,'cat_id',12,'rand()');
        $catIds = '';
        foreach ($browList as $key => $value) {
            $catIds .= $value['cat_id'].',';
        }
        if (empty($catIds)) {
            $catIds = '662';
        }
        $condition = " AND cat_id in (".trim($catIds,',').")";
        //产品利润率
        if($this->uid && $this->comId){
            if ($this->comId == '-1') {
                $profitRate = C("PROFIT_RATE");
                if (!empty($profitRate)) {
                    $condition .=" AND (price-cost_price)/price >=".$profitRate;
                }
            }else{
                $profitRate = $this->modelInfo->getCompanyConf('com_id='.$this->comId);
                if (!empty($profitRate['profit_rate'])) {
                    $condition .=" AND (price-cost_price)/price >=".$profitRate['profit_rate']/100;
                }
            }
        }       
        $itemList = $this->modelInfo->getItemList($condition,'item_id,title,price,image_default_id',12,'rand()');
		//积分详细
		$map = array(
			'user_id' => $this->uid
		);
		$pointList = M('sysuser_user_deposit_detail')->where($map)->select();
        $this->assign('pointList',$pointList);
        $this->assign('userInfo',$userInfo);
        $this->assign('userDepositInfo',$userDepositInfo);
        $this->assign('itemList',$itemList);
        $this->display();
    }

    //个人信息
    public function assetCenter(){ 
    	$userDeposit = $this->modelInfo->getUserDeposit('user_id ='.$this->uid);
        //实例化分页类
        $p = I('p');
        if (empty($p)) {
            $p = 1;
        }
        $size=20;
        $sign=md5('page_num='.$p.'&page_size='.$size.'&phone_num='.$this->account.C('API_KEY'));
        $data=array(
            'phone_num'=>$this->account,
            'page_num'=>$p,
            'page_size'=>$size,
            'sign'=>$sign,
            );
        $res=$this->requestPost(C('API').'mallUser/queryUserTradeList',$data);  
        $res = trim($res, "\xEF\xBB\xBF");//去除BOM头
        $info=json_decode($res,TRUE);
        if($info['result']==100){
            //通讯成功 
            $page = new \Think\Page($info['data']['totalnum'],$size);
            $rollPage = 5; //分页栏显示的页数个数；
            $page -> setConfig('first' ,'首页');
            $page -> setConfig('last' ,'尾页');
            $page -> setConfig('prev' ,'上一页');
            $page -> setConfig('next' ,'下一页');
            $start = $page->firstRow;  //起始行数
            $pagesize = $page->listRows;   //每页显示的行数
            $limit = "$start,$pagesize";
            $style = "badge";
            $onclass = "pageon";
            $pagestr = $page -> show($style,$onclass);  //组装分页字符串
            $this->assign('depositLogList',$info['data']['info']);
            $this->assign('pagestr',$pagestr);
        }
        $pointType=array(
            '0'=>'充值',
            '1'=>'赠送积分',
            '2'=>'礼舍积分',
            '3'=>'订单支付',
            '4'=>'订单退款',
            '5'=>'发放分公司',
            '6'=>'扣减',
            '7'=>'总公司发放',
            '8'=>'积分卡兑换',
            '9'=>'返积分',
            '10'=>'正章干洗券积分兑换',
            '11'=>'订单返积分',
        );  
        $this->assign('pointType',$pointType);   
        $this->assign('userDeposit',$userDeposit);
        $this->display();
    }
    //修改个人资料
    public function infomessage(){
        $userInfo = $this->modelInfo->getUserInfo('user_id='.$this->uid);
        $this->assign('userInfo',$userInfo);
        $this->display();
    }
    //充值
    public function recharge(){
    	//获取积分充值规则
        $data=array(
            'terminalType'=>'WAP'
        );
        $url=C('API').'pointActive/getAllPointActive';
        $res = json_decode($this->requestPost($url,$data),true);
        $this->assign('rules',$res['data']['info']);
    	$this->display();
    }
    //安全中心
    public function security(){
        $this->display();
    }
    //修改登录密码
    public function editLoginPwd(){
        $this->display('changepassword');
    }

    // 修改登录密码操作 
    public function changePwd(){
        $oldPass=I('post.oldpwd','','trim');
        $newPass=I('post.pwd','','trim');
        $rePass=I('post.rpwd','','trim');
        if($oldPass==''){
            echo json_encode(array(0,'请填写旧密码！'));
            exit;
        };
        if($newPass==''){
            echo json_encode(array(0,'请填写新密码！'));
            exit;
        };
        if(strlen($newPass) < 6 || strlen($newPass) > 18){
            echo json_encode(array(0,'密码长度不能小于6，超过18！'));
            exit;
        };
        if($newPass != $rePass){
            echo json_encode(array(0,'密码和确认密码必须相同！'));
            exit;
        }
        $condition['user_id'] = $this->uid;     
        $accountInfo = $this->modelInfo->getAccountInfo($condition);
        $phoneNum=$accountInfo['mobile'];
        $sign=md5('doType=up&newPass='.$newPass.'&oldPass='.$oldPass.'&phoneNum='.$phoneNum.C('API_KEY'));
        $data=array(
            'phoneNum'=>$phoneNum,
            'oldPass'=>$oldPass,
            'doType'=>'up',
            'newPass'=>$newPass,
            'sign'=>$sign
            );
        $res=$this->requestPost(C('API').'mallUser/updatePass',$data);  
        $res = trim($res, "\xEF\xBB\xBF");//去除BOM头
        $info=json_decode($res,TRUE);
        if($info['result']==100){
            echo json_encode(array(1,'修改成功！'));
            exit;
        }else{
            echo json_encode(array(0,$info['msg']));
            exit;
        }
    }
    //修改支付密码
    public function editPayPwd(){
        $this->display('changepaypwd');
    }
    // 修改支付密码操作 
    public function changePayPwd(){
        $oldPass=I('post.oldpwd','','trim');
        $newPass=I('post.pwd','','trim');
        $rePass=I('post.rpwd','','trim');
        if($oldPass==''){
            echo json_encode(array(0,'请填写旧密码！'));
            exit;
        };
        if($newPass==''){
            echo json_encode(array(0,'请填写新密码！'));
            exit;
        };
        if(strlen($newPass) < 6 || strlen($newPass) > 18){
            echo json_encode(array(0,'密码长度不能小于6，超过18！'));
            exit;
        };
        if($newPass != $rePass){
            echo json_encode(array(0,'密码和确认密码必须相同！'));
            exit;
        }
        $condition['user_id'] = $this->uid; 
        //查询原始密码
        $pwd = $this->modelInfo->getPwd($condition);
        if ($pwd['md5_password'] == md5($newPass)) {
            echo json_encode(array(0,'新密码不能与旧密码相同！'));
            exit;                
        }    
        if ($pwd['md5_password'] != md5($oldPass)) {
            echo json_encode(array(0,'支付旧密码不正确！'));
            exit;                   
        }
        $data['md5_password'] = md5($newPass);
        $res = $this->modelInfo->updatePayPassword($condition,$data);        
        if($res){
            echo json_encode(array(1,'修改成功！'));
            exit;
        }else{
            echo json_encode(array(0,'修改失败！'));
            exit;
        }
    }
    //找回支付密码
    public function findPaypwd(){        
        $condition['user_id'] = $this->uid;     
        $accountInfo = $this->modelInfo->getAccountInfo($condition);
        $this->assign('phoneNum',$accountInfo['mobile']);
        $this->display('findpassword');
    }
    //发送手机验证码
    public function sendPhoneCode(){        
        vendor('SendPhoneCode.SendCode','','.php');
        $phone = I("post.phone"); 
        $randomNumber=rand(10000,99999);
        session('phoneCode',md5($randomNumber));
        cookie('phoneCode',md5($randomNumber),3600);  
        $content = "您的礼舍验证码：".$randomNumber."。";
        $sendCode = new \SendCode();
        $codeResult = $sendCode->sendPhoneCode($phone,$content);
        if ($codeResult['message'] == "成功") {
            echo 1;
        }else{
            echo 0;
        }
    }
    //验证手机验证码
    public function checkPhoneCode(){
        $phoneCode = I('post.code');
        if (md5($phoneCode) == cookie('phoneCode') || md5($phoneCode) == session('phoneCode')){
            echo 1;
        }else{
            echo 0;
        }
    }
    //修改支付密码
    public function doRetrievePwd(){  
        $payPwd = I('post.newPwd');
        $rePayPwd = I('post.reNewPwd');
        if (empty($payPwd)) {
            echo json_encode(array(0,'密码不能为空！'));
            exit();
        }
        if ($payPwd != $rePayPwd) {
            echo json_encode(array(0,'密码和确认密码必须相同！'));
            exit();
        }
        $data['md5_password'] = md5($payPwd);
        $condition['user_id'] = $this->uid;
        $res = $this->modelInfo->updatePayPassword($condition,$data);  
        if ($res) {
            echo json_encode(array(1,'修改成功！'));
        }else{
            echo json_encode(array(0,'修改失败，请重试！'));
        }
    }
    //我的意见反馈
    public function feedback(){        
        $condition['user_id'] = $this->uid;
        //实例化分页类
        $count = $this->modelInfo->getFeedBackCount($condition);
        $size=20;
        $page = new \Think\Page($count,$size);
        $rollPage = 5; //分页栏显示的页数个数；
        $page -> setConfig('first' ,'首页');
        $page -> setConfig('last' ,'尾页');
        $page -> setConfig('prev' ,'上一页');
        $page -> setConfig('next' ,'下一页');
        $start = $page->firstRow;  //起始行数
        $pagesize = $page->listRows;   //每页显示的行数
        $limit = "$start,$pagesize";
        $style = "badge";
        $onclass = "pageon";
        $pagestr = $page -> show($style,$onclass);  //组装分页字符串
        $feedbackList = $this->modelInfo->getFeedBack($condition,$limit);
        $this->assign('pagestr',$pagestr);
        $this->assign('feedbackList',$feedbackList);
        $this->display();
    }
    //我的意见反馈详情
    public function feedbackInfo(){
        $feedbackId = I('feedbackId');
        if (empty($feedbackId)) {
            $this->error("系统繁忙，请稍后重试！");
        }
        $condition['feedback_id'] = $feedbackId;
        $feedbackInfo = $this->modelInfo->getFeedBackInfo($condition);
        $catIdstr = trim($feedbackInfo['cat_id'],',');   
        if ($catIdstr) {
            $condition['cat_id'] = array('in',$catIdstr);
            $field = 'cat_name';
            $catNameList = $this->modelInfo->getCatName($condition,$field);    
            $feedbackInfo['catNameStr'] = '';
            if (is_array($catNameList)) {
                foreach ($catNameList as $key => $value) {
                    $feedbackInfo['catNameStr'] .= $value['cat_name'].">";
                }
            }
        }   
        $feedbackInfo['catNameStr'] = trim($feedbackInfo['catNameStr'],'>');
        $this->assign('feedbackInfo',$feedbackInfo);
        $this->display();
    }



}
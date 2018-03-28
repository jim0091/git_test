<?php
namespace Home\Controller;
class IndexController extends CommonController {
    private $load = "";
		public function __construct(){
		parent::__construct();
        $this->paymentsModel = M("ectools_payments");//支付表
        $this->userAccountModel = M('sysuser_account');//用户登录表
        $this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
        $this->tradeModel=M("systrade_trade");//订单表
        $this->userDepositModel = M('sysuser_user_deposit');
		$this->indexActivityModel=M('syspromotion_activity');
		$this->indexActivityItemModel=M('syspromotion_activity_item');
        $this->indexModel = D('Index');		
        $this->dMallConf=D('Mallconfigure');
	}

    public function index(){
        //获取轮播图片
        $shuFigureId = $this->indexModel->getShuFigure('identify = "xyindex"','shuffling_id');
        if (empty($shuFigureId)) {
            $shuFigureId = 1;
        }
        $shuDetailList = $this->indexModel->getShufflingDetail('shuffling_id='.$shuFigureId.' and status = 1 and is_delete = 0');

        //首页内容组合
        $path ="./shop/Home/View/Index/index/0/";
        $files = array();
        $defFiles = array();
        if (is_dir($path)) {
            $defFiles = $this->dir($path);
        }
        $comFiles =array();
        if ($this->uid) { 
            //根据comId查询显示对应的模块
            if ($this->comId != -1 && !empty($this->comId)) {
                //获取集团名称
                $comInfo = $this->indexModel->getComGroup('com_id ='.$this->comId);
                if (!empty($comInfo)) {
                    $path ='./shop/Home/View/Index/index/'.$comInfo.'/';
                    $comFiles = $this->dir($path);
                }                
            }
            //获取用户积分           
            $userDeposit = $this->indexModel->getUserDeposit('user_id='.$this->uid);
            $this->assign('userDeposit',$userDeposit);
        }
        
        foreach ($defFiles as $key => $value) {
            $newDefFile[] = 'Index/index/0/'.$value;
        }
        if (!empty($comFiles)) {
            foreach ($comFiles as $key => $value) {
                $newComFiles[] = 'Index/index/'.$comInfo.'/'.$value;
            }
            foreach ($newDefFile as $key => $value) {
                foreach ($newComFiles as $k => $val) { 
                    $newDefFileArr =  explode('/', $value);
                    $newComFilesArr = explode('/', $val);
                    if ($newDefFileArr[3] == $newComFilesArr[3]) {
                        $newDefFile[$key] = $val;
                    }else{
                        $newDefFile[] = $val;                      
                    }
                }
            }
        }
        $files = array_unique($newDefFile);
        $len=count($files);
        for( $k=0; $k<=$len; $k++){
            for($j=$len-1; $j>$k; $j--){
                $filesArr = explode('_', $files[$j]);
                $filesArrT = explode('_', $files[$j-1]);
                if( $filesArr[1] < $filesArrT[1] ){
                    $temp = $files[$j];
                    $files[$j] = $files[$j-1];
                    $files[$j-1] = $temp;
                }
            }
        }
        $content='';
        foreach ($files as $key => $value) {
            $content.=file_get_contents(C('SYS_DIR')."/shop/Home/View/".$value.'.html');
        }
        $this->assign("content",$content);
        $this->assign("shuDetailList",$shuDetailList);
        $this->display();
    }


    private function dir($path){
        $files = array();
        if ($dh = opendir($path)) {
            while (($file = readdir($dh)) !== false) {
                if (!in_array($file, array('.','..'))) {
                    $files[] = basename($file,".html");                    
                }
            } 
            closedir($dh);
            return $files;
        }
        return false;
    }



    //动态生成模板
    public function showTemp(){ 
        $show = I('isShow');
        $groupId=I('groupId');  
        $ret=array(
            'code'=>0,
            'msg'=>'unkonw'
        );        
        if(!$groupId){
            $ret['msg']='ID标识不存在';
            return $ret;
        }        
        $where=array(
            'group_id'=>$groupId
        );        
        $groupInfo=$this->dMallConf->getAdGroup($where); 
        if(!$groupInfo){
            $ret['msg']='广告组不存在';
            return $ret;           
        }
        if($groupInfo['template_id']){
            unset($where);
            $where=array(
                'template_id'=>$groupInfo['template_id']
            );
            $tempInfo=$this->dMallConf->getThistemp($where);
        }
        if(!$tempInfo){  
            $ret['msg']='模板不存在';
            return $ret;              
        }
        //当前页的方法
        $tempMethod=$tempInfo['method'];
        if(!$tempMethod){
            $ret['msg']='未定义模板方法';
            return $ret;              
        }     
        //广告区的细节
        unset($where);
        $where=array(
            'area_id'=>$groupInfo['area_id']
        );
        $areaInfo=$this->dMallConf->getThisAdArea($where);
        if(!$areaInfo){
            $ret['msg']='广告区不存在';
            return $ret;
        }
        //模块信息
        unset($where);
        $where=array(
            'module_id'=>$groupInfo['module_id']
        );      
        $moduleInfo=$this->dMallConf->getThismodule($where);
        if(!$moduleInfo){
            $ret['msg']='广告模块不存在';
            return $ret;
        }       
        $postions=$this->getRelationPostion($groupInfo['group_id']); //所有广告位
        //$info=array_slice($postions,0,4);
        if(empty($postions)){
            $ret['msg']='还未选择广告位';
            return $ret;            
        }

        //文件路径及文件名
        $this->load=C('INDEX_TEMP').$areaInfo['identify'].'/'.$groupInfo['com_group'].'/'.$moduleInfo['module_id'].'_'.$moduleInfo['order_sort'].'.html';
        $this->assign('info',$postions);
        $this->assign('groupInfo',$groupInfo);
        if (!empty($show)) {            
            $this->display('Index/temp/'.$tempInfo['method']);
        }
        
    }
    /*
 * 取出广告组中所有广告位的数据
 * */
    private function getRelationPostion($groupId){
        if(!$groupId){
            return null;
        }
        $where['group_id']=$groupId;
        $relationPos=$this->dMallConf->getGroupFieldPos($where);
        if(!$relationPos){
            return null;
        }
        unset($where);
        $where=array(
            'position_id'=>array('in',$relationPos)
        );
        $postions=$this->dMallConf->getAdCondPostion($where,'*','order_sort desc');
        return $postions;
    }
    public function dyTemp(){        
        $ret=array(
            'code'=>0,
            'msg'=>'unkonw'
        );  
        $this->showTemp();
        $groupId=I('groupId'); 
        if(!$groupId){
            $ret['code'] = 0;
            $ret['msg']='ID标识不存在！';
            $this->ajaxReturn($ret);   
        } 
        $arrTmplParseString = C('TMPL_PARSE_STRING');
        $url = $arrTmplParseString['__LISHE_URL__'].'/shop.php/Index/showTemp/isShow/show/groupId/'.$groupId;
        $content=file_get_contents($url);
        file_put_contents($this->load,$content);
        $ret['code'] = 1;
        $ret['msg']='模板生成完成！';
        $this->ajaxReturn($ret);    
        
    }


    //微信支付异步通知
    public function asyNotify(){

        vendor('WxpayAPI.Util.WxPayPubHelper','','.class.php');
        //使用通用通知接口
        $notify = new \Notify_pub();

        //存储微信的回调
        $xml = file_get_contents('php://input');
        $notify->saveData($xml);
        
        $arr = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        $checkSign=$notify->checkSign();
        if($checkSign == FALSE){
            $this->log_result('wxpay',"\r\n【签名失败】".date('Y-m-d H:i:s')."\r\n");
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $this->log_result('wxpay',"\r\n【签名成功】".date('Y-m-d H:i:s')."\r\n");
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
            $notify->setReturnParameter("return_msg","OK");//设置返回码
        }
        $returnXml = $notify->returnXml();        
        //以log文件形式记录回调信息
        $this->log_result('wxpay',"\r\n【接收到的notify通知】".date('Y-m-d H:i:s')."  checkSign:".$checkSign."\r\n".$xml."\r\n");

        if($checkSign == TRUE){
        	$returnCode=$notify->data["return_code"];
        	$resultCode=$notify->data["result_code"];
        	$this->log_result('wxpay',"\r\n【更新订单开始】".date('Y-m-d H:i:s')." returnCode:".$returnCode." resultCode:".$resultCode."\r\n");
            if ($returnCode == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
                $this->log_result('wxpay',"【通信出错】".date('Y-m-d H:i:s')."\r\n".$xml."\r\n");
                exit;
            }
            if($resultCode == "FAIL"){
                //此处应该更新一下订单状态，商户自行增删操作
                $this->log_result('wxpay',"【业务出错】".date('Y-m-d H:i:s')."\r\n".$xml."\r\n");
                exit;
            }
            
            $paymentId=trim($arr['out_trade_no']);
            $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentId)->find();  
            if ($paymentInfo && $paymentInfo['status'] != 'succ') {
            	$this->log_result('wxpay',"\r\n【更新积分开始】".date('Y-m-d H:i:s')." returnCode:".$returnCode." resultCode:".$resultCode."\r\n");
            	

                //用户登录信息表
                $uid=$paymentInfo['user_id'];
                $totalFee=$arr['total_fee']/100;
                $balance=$arr['total_fee'];
                $userAccountInfo = $this->userAccountModel->where('user_id ='.$uid)->find();
                if(empty($userAccountInfo)){
					$this->log_result('wxpay',"\r\n【获取用户信息失败】".date('Y-m-d H:i:s')."\r\n");
					exit; 
				}
				
				//同步一企一舍积分
                $sign=md5('orderno='.$paymentId.'&phoneNum='.$paymentInfo['user_name'].'&pointsAmount='.$balance.'&pointsType=1&terminalType=WAP'.C('API_KEY'));
                $this->log_result('wxpay',"\r\n【更新一企一舍积分】".date('Y-m-d H:i:s')." addPoint:".$balance." sign:".$sign."\r\n");
                $url=C('API').'mallPoints/rechargeNew';
                $data=array(
                    'phoneNum'=>$paymentInfo['user_name'],
                    'pointsAmount'=>$balance,
                    'orderno'=>$paymentId,
                    'pointsType'=>1,
                    'terminalType'=>'WAP',
                    'sign'=>$sign
                );      
                $return=$this->requestPost($url,$data);
                $this->log_result('wxpay','【一企一舍充值】url:'.$url.'data:'.json_encode($data).'return:'.$return."\r\n");                
                $retArr=json_decode($return,true);
                
                if($retArr['result']==100){			
					$this->log_result('wxpay',"\r\n【更新本地积分】".date('Y-m-d H:i:s')." totalFee:".$totalFee." balance:".$retArr['data']['info']['amount']."\r\n");
	                //支付成功，更新本地积分
	                $this->userDepositModel->where('user_id ='.$uid)->setInc('deposit',$totalFee);
	                $this->userDepositModel->where('user_id ='.$uid)->setInc('balance',$retArr['data']['info']['amount']);
	                $this->userDepositModel->where('user_id ='.$uid)->setInc('commonAmount',$retArr['data']['info']['amount']);
					//更新支付主表
	                $zdata['cur_money'] = $totalFee;
	                $zdata['pay_type'] = 'recharge';
	                $zdata['pay_app_id'] = 'wxpay';
	                $zdata['pay_name'] = '微信支付';
	                $zdata['memo'] = '微商城充值[微信支付]';
	                $zdata['payed_time'] = time();
	                $zdata['status'] = 'succ';
	                $zdata['trade_no'] = $arr['transaction_id'];
	                $zdata['ls_trade_no'] = $retArr['data']['info']['transno'];
	                $this->log_result('wxpay',"\r\n【更新支付主表】".date('Y-m-d H:i:s')." curMoney:".$totalFee." payType:".$zdata['pay_type']."\r\n");
	                $zres = $this->paymentsModel->where('payment_id ='.$paymentInfo['payment_id'])->data($zdata)->save();
					$this->log_result('wxpay',"\r\n【更新主表结果】".date('Y-m-d H:i:s')." paymentId:".$paymentInfo['payment_id']." return:".$zres."\r\n");
					echo 'succ';
				}else{
					$this->log_result('wxpay','【一企一舍充值失败】url:'.$url.'data:'.json_encode($data).'return:'.$return."\r\n");
				}
            }else{
                $this->log_result('wxpay',"【支付信息已更新】".date('Y-m-d H:i:s')."！\r\n");
                echo 'succ';
            }
        }
    }

    //积分充值
    public function inteRechApi(){
        $sign=md5('orderno='.$tradeNo.'&phoneNum='.$mobile.'&pointsAmount='.$addPoint.'&pointsType=1'.C('API_KEY'));
        $url=C('API').'mallPoints/recharge';
        $data=array(
            'phoneNum'=>$mobile,
            'pointsAmount'=>$addPoint,
            'orderno'=>$tradeNo,
            'pointsType'=>1,
            'sign'=>$sign
        );      
        $return=$this->requestPost($url,$data);
    }

    //记录日志
    public function log_result($type,$content){
        $file  = 'logs/'.$type.'/log'.date('Ymd').'.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个  
        file_put_contents($file, $content,FILE_APPEND);
    }




    //支付宝异步通知
    public function alipayNotify(){
        vendor('Alipay.lib.alipay_notify','','.class.php');
        vendor('Alipay.alipay','','.config.php');        
        //计算得出通知验证结果
        $alipayConfig = alipayConfig();
        $alipayNotify = new \AlipayNotify($alipayConfig);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result) {
            //验证成功            
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no = $_POST['out_trade_no'];//商户订单号
            $trade_no = $_POST['trade_no'];//支付宝交易号
            $trade_status = $_POST['trade_status'];//交易状态
            if($_POST['trade_status'] == 'TRADE_SUCCESS') {
                //普通即时到账的交易成功状态
                $paymentId=trim($out_trade_no);
                $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentId)->find();
                $this->log_result('alipay',"\r\n【更新积分开始】".date('Y-m-d H:i:s')." status:".$paymentInfo['status']."\r\n");
                if ($paymentInfo && $paymentInfo['status'] != 'succ') {
                    $this->log_result('alipay',"\r\n【更新积分开始】".date('Y-m-d H:i:s')." returnCode:".$_POST['total_fee']."\r\n");
                    //用户登录信息表
                    $uid=$paymentInfo['user_id'];
                    $totalFee=$_POST['total_fee'];//单位元
                    $balance=$_POST['total_fee']*100;//单位积分
                    $userAccountInfo = $this->userAccountModel->where('user_id ='.$uid)->find();
                    if(empty($userAccountInfo)){
                        $this->log_result('alipay',"\r\n【获取用户信息失败】".date('Y-m-d H:i:s')."\r\n");
                        exit; 
                    }
                    
                    //同步一企一舍积分
                    $sign=md5('orderno='.$paymentId.'&phoneNum='.$paymentInfo['user_name'].'&pointsAmount='.$balance.'&pointsType=1&terminalType=WAP'.C('API_KEY'));
                    $this->log_result('alipay',"\r\n【更新一企一舍积分】".date('Y-m-d H:i:s')." addPoint:".$balance." sign:".$sign."\r\n");
                    $url=C('API').'mallPoints/rechargeNew';
                    $data=array(
                        'phoneNum'=>$paymentInfo['user_name'],
                        'pointsAmount'=>$balance,
                        'orderno'=>$paymentId,
                        'pointsType'=>1,
                        'terminalType'=>'WAP',
                        'sign'=>$sign
                    );      
                    $return=$this->requestPost($url,$data);
                    $this->log_result('alipay','【一企一舍充值】url:'.$url.'data:'.json_encode($data).'return:'.$return."\r\n");                
                    $retArr=json_decode($return,true);
                    
                    if($retArr['result']==100){         
                        $this->log_result('alipay',"\r\n【更新本地积分】".date('Y-m-d H:i:s')." totalFee:".$totalFee." balance:".$balance."\r\n");
                        //支付成功，更新本地积分
                        $this->userDepositModel->where('user_id ='.$uid)->setInc('deposit',$totalFee);
                        $this->userDepositModel->where('user_id ='.$uid)->setInc('balance',$balance);
                        $this->userDepositModel->where('user_id ='.$uid)->setInc('commonAmount',$balance);
                        //更新支付主表
                        $zdata['cur_money'] = $totalFee;
                        $zdata['pay_type'] = 'recharge';
                        $zdata['pay_app_id'] = 'alipay';
                        $zdata['pay_name'] = '支付宝支付';
                        $zdata['memo'] = '微商城充值[支付宝支付]';
                        $zdata['payed_time'] = time();
                        $zdata['status'] = 'succ';
                        $zdata['trade_no'] = $trade_no;
                        $zdata['ls_trade_no'] = $retArr['data']['info']['transno'];
                        $this->log_result('alipay',"\r\n【更新支付主表】".date('Y-m-d H:i:s')." curMoney:".$totalFee." payType:".$zdata['pay_type']."\r\n");
                        $zres = $this->paymentsModel->where('payment_id ='.$paymentInfo['payment_id'])->data($zdata)->save();
                        $this->log_result('alipay',"\r\n【更新主表结果】".date('Y-m-d H:i:s')." paymentId:".$paymentInfo['payment_id']." return:".$zres."\r\n");
                        echo "success";
                    }else{
                        $this->log_result('alipay','【一企一舍充值失败】url:'.$url.'data:'.json_encode($data).'return:'.$return."\r\n");
                    }
                }else{
                    $this->log_result('alipay',"【支付信息已更新】".date('Y-m-d H:i:s')."！\r\n");
                    echo "success";
                }
            }else if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                $this->log_result('alipay',"【普通即时到账】".date('Y-m-d H:i:s')."！\r\n");
            }else{
                $this->log_result('alipay',"【交易失败】".date('Y-m-d H:i:s')."！\r\n");
            }
        }else{
            //验证失败
            $this->log_result('alipay',"【验证失败】".date('Y-m-d H:i:s')."verify_result:".$verify_result."\r\n");
            echo "fail";
        }
    }


}
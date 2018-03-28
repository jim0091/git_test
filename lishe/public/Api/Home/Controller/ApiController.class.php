<?php
/**
  +------------------------------------------------------------------------------
 * ApiController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: ApiController.class.php v001 2016-09-09
 * @description 本地接口封装
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class ApiController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelReturn=M('systrade_return');
		
		$this->modelActivityTrade=M('company_activity_trade');
		$this->modelActivityOrder=M('company_activity_order');
		$this->modelCompanyConf=M('company_config');
		
		$this->modelItem=M('sysitem_item');
		$this->modelItemSku=M('sysitem_sku');
		$this->modelItemStatus=M('sysitem_item_status');
		$this->modelPayments=M('ectools_payments');
		$this->modelPaybill = M('ectools_trade_paybill');//支付子表
		
		$this->modelDeposit=M('sysuser_user_deposit');
		$this->modelDepositLog=M('sysuser_user_deposit_log');
		$this->modelAccount=M('sysuser_account');
	}
	
	public function index(){
		echo 'Forbidden';
	}
	
	//异步获取登录状态及登录信息
	public function getLoginInfo(){
		$data=array($this->uid,$this->comId,$this->account,$this->userName,urldecode($this->index),urldecode($this->refer));		
		echo json_encode($data);
	}

	
	//手动同步订单到第三方 赵尊杰 2016-06-15
	public function syncOrder(){
		$tid = I('get.tid');
		$itype = I('get.itype');
		if(empty($itype)){
			$itype=2;
		}
		if(!empty($tid)){
			$sync=A('Jd')->syncJdOrder(array($tid),0,$itype);
			echo json_encode($sync);
		}else{
			echo json_encode(array('code'=>-100,'msg'=>'同步订单失败，错误信息：没有订单号'));
		}		
	}
	
	//返积分接口 赵尊杰 2016-10-20
	public function apiDoReturn(){
		$paymentId = I('get.paymentId');
		if(!empty($paymentId)){
			$condition=array(
				'payment_id'=>$paymentId
			);
			$tradeReturn=$this->modelReturn->where($condition)->find();
			if(empty($tradeReturn['return_id'])){
				$this->retError(1001,'返积分失败，错误信息：找不到订单！');
			}
			if($tradeReturn['return_status']=='TRADE_FINISHED'){
				$this->retError(1002,'返积分失败，错误信息：此订单已返还积分！');
			}
			if(empty($tradeReturn['user_id'])){
				$this->retError(1003,'返积分失败，错误信息：找不到用户信息！');
			}
			if(empty($tradeReturn['tids'])){
				$this->retError(1004,'返积分失败，错误信息：购物订单单号为空！');
			}
			if(empty($tradeReturn['return_fee'])){
				$this->retError(1005,'返积分失败，错误信息：返现金额为0！');
			}
			$trade=$this->modelTrade->field('tid,return_status')->where('tid IN ('.$tradeReturn['tids'].')')->select();
			if(empty($trade)){
				$this->retError(1006,'返积分失败，错误信息：找不到购物订单！');
			}
			foreach($trade as $key=>$value){
				if($value['return_status']=='TRADE_FINISHED'){
					$this->retError(1007,'返积分失败，错误信息：存在已返现的订单！');
				}
			}
			
			$user=$this->modelAccount->field('mobile')->where('user_id='.$tradeReturn['user_id'])->find();
			$addPoint=$tradeReturn['return_fee']*100;		
			$return=A('Point')->pointRecharge($paymentId,$user['mobile'],$addPoint,'new');
			$ret=json_decode($return,true);
			if($ret['result']==100){
				if($ret['errcode']==0){
					$this->modelReturn->where($condition)->setInc('returned_fee',$tradeReturn['return_fee']);
					$returnData=array(
						'return_status'=>'TRADE_FINISHED',
						'modifyine_time'=>time(),
						'return_memo'=>$return
					);
					$this->modelReturn->where($condition)->save($returnData);
					$tradeData=array(
						'return_status'=>'TRADE_FINISHED'
					);
					$this->modelTrade->where('tid IN ('.$tradeReturn['tids'].')')->save($tradeData);
				}else{
					$this->makeLog('return','error:1008,data:'.json_encode($returnData));
					$this->retError(1008,'返积分失败，错误信息：'.$ret['msg']);
				}
			}else{
				$this->makeLog('return','error:1009,return:'.$return);
				$this->retError(1009,'返积分失败，错误信息：接口通讯失败');
			}			
		}else{
			$this->retError(1000,'返积分失败，错误信息：没有订单号');
		}		
	}	
		
	//查询京东订单详情 赵尊杰 2016-06-23
	public function getJdOrderInfo(){
		$orderId = I('post.orderId');//京东订单号
		$ret=A('Jd')->getJdOrderInfo($orderId);
		$this->retSuccess($ret['data'],'查询成功');
	}
	
	//查询京东物流单号 章锐 
	public function getJdInfoForExpress(){
		$orderId = I('post.orderId');//京东订单号
		$ret=A('Jd')->getJdOrderInfo($orderId);
		if(!empty($ret['data']['cOrder'])){
			foreach($ret['data']['cOrder'] as $key=>$value){
				$Orders[]=$value['jdOrderId'];
			}
		}	
		$jdOrders=implode(',', $Orders);
		$this->retSuccess($jdOrders,'查询成功');
	}
	
	//查询订单物流 赵尊杰 2016-06-23
	public function getExpress(){ 
		header("Content-type:text/html;charset=utf-8");
		$orderId = I('post.orderId');//第三方订单号，京东子订单号
		$ret=A('Jd')->getJdExpress($orderId);
		$this->retSuccess($ret['data'],'查询成功');
	}
	
	public function getJdAfterSale(){
		$orderId=I('get.orderId');//京东子订单号，即物流单号，不是京东订单号
		$sku=I('get.sku');
		$param=array(
			'jdOrderId'=>$orderId,
			'skuId'=>$sku
		);
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/afterSale/getCustomerExpectComp',json_encode($param));
        $ret=json_decode($result,true);
        echo "<pre>";	
    	print_r($ret);
    	echo "</pre>";
	}
	
	public function addJdAfterSale(){
		$param=array(
			'jdOrderId'=>'',//京东子订单号，即物流单号，不是京东订单号
			'questionDesc'=>'',
			'questionDesc'=>'',//问题描述，必填项
		);
	}	
	
	/*
	 * 一企一舍域名校验   章锐 2016/11/3
	 * */
	public function checkComDomain(){
		$data=I('');
		$domainName=$data['domainName'];
		$comId=$data['com_id'];
		if(!empty($domainName)){
			$check=$this->modelCompanyConf->where(array('com_domain'=>$domainName))->field('com_domain,com_id')->find();
			if(!empty($check)){
				//域名已存在
				if($check['com_id']!=$comId){
					$this->retError(1001,'域名已存在');
					$this->makeLog('checkDomianName',"校验不通过,域名：".$domainName."已存在-(".date("Y/m/d H:i:s").")");
				}else{
					$this->retSuccess('success',"域名可用,当前使用域名,校验通过!");
					$this->makeLog('checkDomianName',"域名可用,当前使用域名,校验通过,域名：".$domainName."可用-(".date("Y/m/d H:i:s").")");						
				}
			}else{
				//域名可用
				$this->retSuccess('success',"域名可用,校验通过!");
				$this->makeLog('checkDomianName',"域名可用,校验通过,域名：".$domainName."可用-(".date("Y/m/d H:i:s").")");
			}
		}else{
			$this->retError(1000,'参数为空');
			$this->makeLog('checkDomianName',"参数为空-(".date("Y/m/d H:i:s").")");
		}	
		
	}
	
	/*
	 * 一企一舍域名,模板设置，域名设置，设置logo添加/修改  章锐 2016/11/3
	 * */
	public function editCompanyInfo(){
		$data=I('');
		if(!empty($_FILES['com_logo']['tmp_name'])){
		    $upload = new \Think\Upload();// 实例化上传类
		    $upload->maxSize   =  3145728 ;// 设置附件上传大小
		    $upload->exts      =  array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		    $upload->rootPath  =  './Upload/catLogo/'; // 设置附件上传根目录
		    // 上传单个文件 
		    $info = $upload->uploadOne($_FILES['com_logo']);
		    if(!$info){// 上传错误提示错误信息
				$this->retError(1006,'错误信息:'.$upload->getError());
				$this->makeLog('modifyComConf',"错误信息".$upload->getError().",数据:".$dates.",logo信息:".json_encode($_FILES['com_logo'])."-(".date("Y/m/d H:i:s").")");
		    }else{// 上传成功 获取上传文件信息
		        $data['com_logo']='/Upload/catLogo/'.$info['savepath'].$info['savename'];
		    }			
		}				  
		if(!empty($data)){
			if(!empty($data['com_id'])){
				//检测com_id是否存在，存在编辑，不存在新增
				//检测域名是否有存在
				$checkComId=$this->modelCompanyConf->where(array('com_id'=>$data['com_id']))->field('config_id')->find();
				$checkDomain=$this->modelCompanyConf->where(array('com_domain'=>$data['com_domain']))->getField('com_id');
				if(empty($data['templete'])){
					$this->retError(1002,'错误信息：模板参数为空');
					$this->makeLog('modifyComConf',"模板参数为空,数据:".$dates.",logo信息:".json_encode($_FILES['com_logo'])."-(".date("Y/m/d H:i:s").")");
				}
				if($data['com_domain']){
					$data['mark']=$data['com_domain'];
				}
				if($checkComId){
					//编辑公司配置表
					if(!empty($checkDomain) && ($checkDomain != $data['com_id'])){
						$this->retError(1002,'错误信息:域名已存在');
						$this->makeLog('modifyComConf',"域名已存在,数据:".$dates.",logo信息:".json_encode($_FILES['com_logo'])."-(".date("Y/m/d H:i:s").")");
					}
					$res=$this->modelCompanyConf->where(array('com_id'=>$data['com_id']))->field('com_logo,com_domain,mark,templete')->save($data);
					if($res){
						//删除域名缓存文件
						@unlink("/data/www/show/Show/Runtime/Data/".$data['com_domain'].".lishe.cn.php");
						$this->retSuccess('success',"修改同步成功");
						$this->makeLog('modifyComConf',"修改同步成功,数据:".$dates.",logo信息:".json_encode($_FILES['com_logo'])."-(".date("Y/m/d H:i:s").")");
					}else{
						$this->retError(1004,'修改同步失败');
						$this->makeLog('modifyComConf',"修改同步失败,sql语句:".M('company_config')->getLastSql().",数据:".$dates.",logo信息:".json_encode($_FILES['com_logo'])."-(".date("Y/m/d H:i:s").")");
					}
				}else{
					//添加公司配置表
					if($checkDomain){
						$this->retError(1003,'错误信息:域名已存在');
						$this->makeLog('modifyComConf',"域名已存在,数据:".$dates.",logo信息:".json_encode($_FILES['com_logo'])."-(".date("Y/m/d H:i:s").")");
					}
					$data['creat_time']=time();
					$data['refer']="http://www.lishe.cn/shop.php/Best";
					$data['index']="http://www.lishe.cn/shop.php/Best";
					$data['templete_header']="commonHeader";
					$data['templete_footer']="commonFooter";
					$res=$this->modelCompanyConf->add($data);
					if($res){
						$this->retSuccess('success',"添加同步成功");
						$this->makeLog('modifyComConf',"添加同步成功,数据:".$dates.",logo信息:".json_encode($_FILES['com_logo'])."-(".date("Y/m/d H:i:s").")");
					}else{
						$this->retError(1005,'添加同步失败');
						$this->makeLog('modifyComConf',"添加同步失败,sql语句:".M('company_config')->getLastSql().",数据:".$dates.",logo信息:".json_encode($_FILES['com_logo'])."-(".date("Y/m/d H:i:s").")");
					}								
				}
			}else{
				$this->retError(1001,'错误信息:comId为空');
				$this->makeLog('modifyComConf',"comId为空,数据:".$dates.",logo信息:".json_encode($_FILES['com_logo'])."-(".date("Y/m/d H:i:s").")");
			}
		}else{
			$this->retError(1000,'错误信息:参数为空');
			$this->makeLog('modifyComConf',"参数为空,数据:".$dates.",logo信息:".json_encode($_FILES['com_logo'])."-(".date("Y/m/d H:i:s").")");
		}
	}
}
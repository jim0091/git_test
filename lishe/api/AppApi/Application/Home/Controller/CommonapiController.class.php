<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[测试接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Home/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[TestController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-1-4 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<Angelljoy@sina.com>		@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Home\Controller;
use Think\Controller;
class CommonapiController extends Controller {
	public function _initialize() {
		header ( "content-type:text/html;charset=utf-8" );
	}
	// 选择不同地区时调用京东接口判断库存 20160613 start
	public function checkJdStock($item_id,$jd_ids,$num){
		if(empty($num)){
			$num=1;
		}
		$where['item_id'] = $item_id;
		$modelItem=M('sysitem_item');
		$res = $modelItem ->field('jd_sku')->where($where) -> find();
		$stock=array('status'=>34,'msg'=>'无货');//初始化
		if($res['jd_sku']>0){
			$url=C('API_AOSERVER').'jd/product/checkstock';
			$data='{"skuNums":[{"skuId":'.$res['jd_sku'].',"num":'.$num.'}],"area":"'.$jd_ids.'"}';
			$result = $this->requestJdPost($url,$data);
			$retArr = json_decode($result,true);
			if($retArr['data'][0]['stockStateId']==33 or $retArr['data'][0]['stockStateId']==39 or $retArr['data'][0]['stockStateId']==40){
				$url=C('API_AOSERVER').'jd/product/checkRep';
				$data='{"skuIds":"'.$res['jd_sku'].'"}';
				$result=$this->requestJdPost($url,$data);
				$retArr=json_decode($result,true);
				if($retArr['code']==100){
					if($retArr['data'][0]['saleState']==0){
						$stock=array('status'=>34,'msg'=>'无货');
					}else{
						$url=C('API_AOSERVER').'jd/product/checkAreaLimit';
						$areas=explode('_',$jd_ids);
						$data='{"skuIds":"'.$res['jd_sku'].'","province":'.intval($areas[0]).',"city":'.intval($areas[1]).',"county":'.intval($areas[2]).',"town":'.intval($areas[3]).'}';
						$result=$this->requestJdPost($url,$data);
						$retArr=json_decode($result,true);
						if($retArr['code']==100){
							if($retArr['data'][0]['isAreaRestrict']==true){
								$stock=array('status'=>34,'msg'=>'无货');
							}else{
								$stock=array('status'=>33,'msg'=>'有货');
							}
						}
					}
				}
			}else{
				$stock=array('status'=>34,'msg'=>'无货');
			}
		}else{
			$stock=array('status'=>33,'msg'=>'有货');
		}
		return $stock;
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
	
	
}
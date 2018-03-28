<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[Home模块接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[CommonApi/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[IndexController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-2-27 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace CommonApi\Controller;
use CommonApi\Service\IndexService;

use Common\Common\Classlib\UploadFile\UploadImages;

use Common\Controller\CommonController;
use Think\Controller;
class IndexController extends CommonController {
	//继承RootController,代表执行本控制器内的所以方法都需要登录
	//继承CommonController,代表执行本控制器内的所以方法都不需要登录
	
    public function index(){
    	$errorInfo['status'] = -1000;
    	$errorInfo['msg'] = "参数缺失";
    	$errorInfo['message'] = "添加失败";
    	$this->retError($errorInfo);
    }
    
    
    //企业的banner图配置（一企一舍HR后台回调）
    public function bannerconfig(){
    	$postData = I('post.');
    	if(empty($postData)){
    		$errorInfo['status'] = -1000;
    		$errorInfo['msg'] = "没有提交任何数据";
    		$errorInfo['message'] = "添加失败";
    		$this->retError($errorInfo);
    	}else{
    		$com_id = $postData['com_id'];
    		$item_id = $postData['item_id'];
    		$hr_id =  $postData['hr_id'];
    		if(empty($com_id)||empty($item_id)||empty($hr_id)){
    			$errorInfo['status'] = -1001;
    			$errorInfo['msg'] = "参数缺失";
    			$errorInfo['message'] = "添加失败";
    			$this->retError($errorInfo);
    		}else{
    			$Client = new IndexService();
    			$count = $Client->getCountNumber($com_id);
    			if($count < 3){
    				if($_FILES){
    					$upload = new UploadImages();
    					$uploadConfig['rootPath'] = './Public/';//根路径
    					$uploadConfig['savePath'] = 'Businesscircle/bannerconfig/';//相对根路径
    					$uploadInfo = $upload->ImagesUpload($uploadConfig);
    					if ($uploadInfo){
    						//foreach ($uploadInfo as $info){
    						//	$data['bannerimages'] = $info['imagesPath'];//类型，默认为1
    						//}
    						$data['bannerimages'] = $uploadInfo[0]['imagesPath'];
    					}
    					$data['com_id'] = $com_id;//企业ID
    					$data['type'] = $postData['type'];//类型，默认为1
    					$data['item_id'] = $item_id;//商品的ID
    					$data['link'] = $postData['link'];//链接地址
    					$data['hr_id'] = $hr_id;//HR的ID
    					$data['status'] = 1;//类型，默认为1
    					$data['sort_number'] = $Client->getSortNumber($com_id);//排序数字
    					$data['create_time'] = getNow();//创建时间
    					$data['update_time'] = getNow();//更新时间
    					$boolData = $Client->add($data);
    					if($boolData){
    						$successInfo['status'] = 1000;
    						$successInfo['message'] = "添加成功";
    						$this->retSuccess($successInfo);
    					}else{
    						$errorInfo['status'] = -1004;
    						$errorInfo['msg'] = "新增数据未成功，请检查参数是否正确";
    						$errorInfo['message'] = "服务繁忙，添加失败";
    						$this->retError($errorInfo);
    					}
    				}else{
    					$errorInfo['status'] = -1003;
    					$errorInfo['msg'] = "未选择上传的图片文件";
    					$errorInfo['message'] = "添加失败，请选择上传文件";
    					$this->retError($errorInfo);
    				}
    			}else{
    				$errorInfo['status'] = -1002;
    				$errorInfo['msg'] = "每个企业的商城最多允许上传三张banner图";
    				$errorInfo['message'] = "添加失败，数量超限，最多允许三张，请下架或删除后再添加";
    				$this->retError($errorInfo);
    			}
    		}
    	}
    }
    
    
    
    
}
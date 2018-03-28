<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[上传图片类];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Common/Common/Classlib/UploadFile];
 * +----------------------------------------------------------------------
 * |@Name:			[UploadImages.class.php];
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3
 * +----------------------------------------------------------------------
 * |@License:(http://www.Apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-03-02 10:35
 * +----------------------------------------------------------------------
 * |@Email:		  <lhq@lishe.cn>			@Overtime:	2016-03-02
 * +----------------------------------------------------------------------
 *  */

namespace Common\Common\Classlib\UploadFile;
use Think\Upload;

class UploadImages {
	
	/**
	 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @author lihongqiang  2017-03-13
	 * @param string $uploadConfig['rootPath'] //文件上传保存的根路径
	 * @param string $uploadConfig['savePath'] //保存的相对路径
	 * @param string $uploadConfig['subName'] //子目录创建方式
	 * @return array
	 */
	public function ImagesUpload($uploadConfig){
		$upload = new Upload();
		// 		if(empty($uploadConfig['rootPath'])){
		// 			$upload->rootPath = './Public/';
		// 		}else{
		// 			$upload->rootPath = $uploadConfig['rootPath'];
		// 		}
		$upload->rootPath = C('ROOT_PUBLIC');
		if(empty($uploadConfig['savePath'])){
			$upload->savePath = 'UploadCommon/';//文件上传的保存路径（相对于根路径），不存在会自动创建
		}else{
			$upload->savePath = $uploadConfig['savePath'];
		}
		$upload->maxSize = 3145728;//文件上传的最大文件大小（以字节为单位），0为不限大小
		$upload->saveName = array('getFileName','');//上传文件命名规则，[getFileName]-自定义函数名，[1]-参数，多个参数使用数组
		$upload->exts     = array('jpg', 'gif', 'png', 'jpeg');//允许上传的文件后缀
		$upload->saveExt  = 'png';//上传文件的保存后缀，不设置的话使用原文件后缀
		$upload->autoSub  = true;//自动使用子目录保存上传文件 默认为true
		$upload->replace  = false;//存在同名文件是否是覆盖，默认为false
		if(empty($uploadConfig['subName'])){
			$upload->subName = array('date','Ymd'); //子目录创建方式，采用数组或者字符串方式定义
		}else{
			$upload->subName = $uploadConfig['subName'];
		}
		if($upload->savePath == 'UploadCommon/'){
			$upload->subName  = array('date','Ymd'); //子目录创建方式，采用数组或者字符串方式定义
		}
		$uploadInfo   =   $upload->upload();
		if($uploadInfo){
			//$imagesPath = array();
			foreach ($uploadInfo as $key => &$v){
				////$imagesPath[$v['key']][$key] = $v['savepath'].$v['savename'];//使用这个比较好
				////$imagesPath[$v['key']] = $v['savepath'].$v['savename'];
				$v['imagesPath'] = $v['savepath'].$v['savename'];
			}
			return $uploadInfo;
		}else{
			return false;
		}
	}
}
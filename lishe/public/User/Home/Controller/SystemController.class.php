<?php 
/**
  +------------------------------------------------------------------------------
 * SystemController
  +------------------------------------------------------------------------------
 * @author   	awen
 * @version  	$Id: SystemController.class.php v001 2017-01-06
 * @description 系统功能
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class SystemController extends CommonController {
	public function index(){

	}
  	//创建目录
	public function creatDir($fileName){
		if(strpos($fileName,'/')){
			$dirArray = explode('/',$fileName);
			array_pop($dirArray);
			foreach($dirArray as $val){
				$dir .= $val.'/';
				$oldumask = umask(0);
				if(!is_dir($dir)){
					mkdir($dir,0777);
				}
				chmod($dir,0777);
				umask($oldumask);
			}
			return true;
		}
		return false;
	}
	
	//生成文件名称
	function randomFilename(){
		return date('YmdHis').rand(100,999);
    }
    
	//上传图片
	public function uploadImg(){
		$inputName=isset($_GET['inputName'])?trim($_GET['inputName']):'';
		$channel=isset($_GET['channel'])?trim($_GET['channel']):'';
		$types=isset($_GET['types'])?trim($_GET['types']):'';
		$fileAllow=C('IMG_ALLOW');
		$fileName=$this->randomFilename();
		if($channel=='goods'){
			$sourcePath=C('DIR_SOURCE_IMG');//原图存放目录
			$thumbPath=C('DIR_THUMB_IMG');//压缩后的小图
			$this->creatDir($sourcePath);
			$this->creatDir($thumbPath);
			
			if(is_uploaded_file($_FILES[$inputName]['tmp_name'])){
				//先上传原图
				$reMesg=$this->uploadFile($inputName,$sourcePath,$fileName,$fileAllow);
				if($reMesg['code']<=0){
					echo json_encode(array('0',$reMesg['code']));
				}else{
					//对原图进行压缩
					$sourceFile=$sourcePath.$fileName.$reMesg['suffix'];
					$thumbSize=$this->format($sourceFile,C('SIZE_THUMB_IMG'));
					$thumbFile=$thumbPath.$fileName.$reMesg['suffix'];
					$this->compressPic($sourceFile,$goodsFile,$goodsSize['width'],$goodsSize['height']);
					$this->compressPic($sourceFile,$thumbFile,$thumbSize['width'],$thumbSize['height']);
					echo json_encode(array('1','/'.$thumbPath.$fileName.$reMesg['suffix']));
				}
			}
		}
	}
  
  
	//上传文件
	//$inputName:文件域名称,$filePath:文件上传路径,$newFileName:新文件名(不带后缀)
	public function uploadFile($inputName,$filePath,$newFileName,$allowFile = array('.png','.gif','.jpg','.rar','.zip','.doc','.pdf'),$allowSize=0){
		if($allowSize==0){
			$allowSize=C('UPLOAD_IMAGE_MAX_SIZE')*1024*1024;
		}
		$upload['code']='1';
		$suffix = strtolower(substr($_FILES[$inputName]['name'],-4));
		if(!in_array($suffix,$allowFile)){
			$upload['code']='-3';
		}
		if(intval($upload['code'])>0 and $allowSize>0 and ($_FILES[$inputName]['size']>$allowSize)){
			$upload['code']='-2';
		}
		if(empty($newFileName)){
			$newFileName=date('YmdHis').rand(1000,9999);
		}
		if(intval($upload['code'])>0){
			if(is_uploaded_file($_FILES[$inputName]['tmp_name'])){
				if(!move_uploaded_file($_FILES[$inputName]['tmp_name'],$filePath.$newFileName.$suffix)){
					$upload['code']='0';
				}else{
					$upload['name']=$newFileName;
					$upload['suffix']=$suffix;
				}
			}else{
				$upload['code']='-1';
			}
		}
		return $upload;
	}

  public function format($img,$sizeStr){
  		$sizeArr=explode(',',$sizeStr);
  		$width=$sizeArr[0];
  		$height=$sizeArr[1];
		$arr=getimagesize($img);
		if($width>=$arr[0] and $height>=$arr[1]){
			return array('width'=>$arr[0],'height'=>$arr[1]);
		}
		if($width<$arr[0]){
			$newHeight=($width/$arr[0])*$arr[1];
			if($newHeight>$height){
				return array('width'=>($height/$arr[1])*$arr[0],'height'=>$height);
			}
			return array('width'=>$width,'height'=>$newHeight);
		}
		
		if($height<$arr[1]){
			return array('width'=>($height/$arr[1])*$arr[0],'height'=>$height);
		}
	}

	//压缩图片
	//使用方法:compressPic(压缩目标,储存图片名,图片长,图片宽)
	public function compressPic($imgPath,$imgName,$imgX,$imgY){
		$arr=getimagesize($imgPath);
		switch($arr['mime']){
			case "image/jpeg" :
		        $resource = imagecreatefromjpeg($imgPath);
		        break;
			case  "image/gif" :
		        $resource = imagecreatefromgif($imgPath);
		        break;        
			case  "image/png" :
		        $resource = imagecreatefrompng($imgPath);
		        break;
			case "image/wbmp" :
		        $resource =imagecreatefromwbmp($imgPath);        
		        break;
		}
		
		$resources=imagecreatetruecolor($imgX,$imgY);
		if(!imagecopyresized($resources,$resource,0,0,0,0,$imgX,$imgY,$arr[0],$arr[1])){
			return -1;
			exit;
		}
		
		switch($arr['mime']){
			case "image/jpeg" :
		        $im = imagejpeg($resources,$imgName);
		        break;
			case  "image/gif" :
		        $im = imagegif($resources,$imgName);
		        break;
			case  "image/png" :
		        $im = imagepng($resources,$imgName);
		        break;
			case "image/wbmp" :
		        $im = imagewbmp($resources,$imgName);
		        break;
		}	
		if($im){
			return 1;
		}else{
			return 0;        
		}
	}
	
	//删除图片
	public function delImg(){
		$file=isset($_GET['file'])?trim($_GET['file']):'';
		$channel=isset($_GET['channel'])?trim($_GET['channel']):'';
		if(!empty($file) and !empty($channel)){
			if($channel=='goods'){
				@unlink($file);
				@unlink(str_replace('thumb','source',$file));
			}			
		}
	}

}
?>
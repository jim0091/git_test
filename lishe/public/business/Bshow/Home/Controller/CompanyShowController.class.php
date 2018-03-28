<?php   
namespace Home\Controller;
class CompanyShowController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->modelTemp=D('TempSet');
		$this->modelCompanyCfg=M('company_config');
	}
	public function index(){
		
		$this->display();
	}
	//登录界面设置
	public function modify(){
		$comId = $this->comId;
		$comConInfo = $this->modelCompanyCfg->where('com_id ='.$comId)->find();
		$this->assign('comCon',$comConInfo);
		$this->assign('comConInfo',json_decode($comConInfo['com_copyright']));
		$this->assign('show',I('local'));
		$this->display();
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
		$fileName=$this->randomFilename();
		if($channel=='brand'){	
			//品牌缩略图尺寸大于100*100的正方形图片
			if(is_uploaded_file($_FILES[$inputName]['tmp_name']) and !empty($types)){
				$size=getimagesize($_FILES[$inputName]['tmp_name']);
				$width=$size[0];
				$height=$size[1];
				if($types=='logo'){
					if($width>300 or $height>90){
						echo json_encode(array('-4','图片尺寸不合要求'));
						exit;
					}
				}
			}
			
			$filePath=C('DIR_BRAND_LOGO');
			
			//上传图片		
			$reMesg=$this->uploadFile($inputName,$filePath,$fileName);
			if($reMesg['code']<=0){
				echo json_encode(array('0',$reMesg['code']));
			}else{
				echo json_encode(array('1',$filePath.$fileName.$reMesg['suffix']));
			}
		}
	}
 	//上传文件
	//$inputName:文件域名称,$filePath:文件上传路径,$newFileName:新文件名(不带后缀)
	public function uploadFile($inputName,$filePath,$newFileName,$allowFile = array('.png','.gif','.jpg'),$allowSize=0){
		$upload['code']='1';
		$suffix = strtolower(substr($_FILES[$inputName]['name'],-4));
		if(!in_array($suffix,$allowFile)){
			$upload['code']='文件不符合';
		}
		if(intval($upload['code'])>0 and $allowSize>0 and ($_FILES[$inputName]['size']>$allowSize*1024)){
			$upload['code']='-2';
		}
		if(empty($newFileName)){
			$newFileName=date('YmdHis').rand(1000,9999);
		}
		if(intval($upload['code'])>0){
			if(is_uploaded_file($_FILES[$inputName]['tmp_name'])){
				if(!move_uploaded_file($_FILES[$inputName]['tmp_name'],$filePath.$newFileName.$suffix)){
					$upload['code']=0;

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
 
	//删除图片
	public function delImg(){
		$file=isset($_GET['file'])?trim($_GET['file']):'';
		$channel=isset($_GET['channel'])?trim($_GET['channel']):'';
		if(!empty($file) and !empty($channel)){
			if($channel=='brand'){
				$file=C('DIR_BRAND_LOGO').$file;
				@unlink($file);
			}
		
		}
	}
	//上传结果
	public function uploadres(){
		$fileLoad=I('file');
		$fileLoad='/business/'.$fileLoad;
		$condition['com_id']=$this->comId;
		if($fileLoad){
			$data['com_logo']=$fileLoad;
			$where['com_logo']=$fileLoad;
			$resinfo=$this->modelCompanyCfg->where($where)->find();
			if($resinfo){
				$datas="无需重新上传!";
				echo $datas;
				exit;
			}
			$res=$this->modelCompanyCfg->where($condition)->data($data)->save();
			if($res){
				$datas="上传成功!";
			}else{
				$datas="上传失败!";
				
			}
			echo $datas;
		}
	}
	//版权信息
	public function copyright(){
		$comId = $this->comId;
		$comdomain  = I('post.comdomain');
		$comadd  = I('post.comadd');
		$comphone  = I('post.comphone');
		$comcop  = I('post.comcop');
		$data['com_domain'] = $comdomain;
		$data['com_copyright'] = json_encode(array('comdomain' => $comdomain,'comadd' => $comadd,'comphone' => $comphone, 'comcop' => $comcop));
		$res = $this->modelCompanyCfg->where('com_id ='.$comId)->save($data);
		if ($res !== false) {
			echo "success";
		}else{
			echo "false";
		}
		
	}


	//修改模板
	public function updateTemp(){
		$comId = $this->comId;
		$tempId = I('post.tempId');
		$tempName=$this->modelTemp->getTempName($tempId);
		$data['templete']=$tempName;
		$res=$this->modelCompanyCfg->where('com_id ='.$comId)->data($data)->save();
		echo $tempId;
	}
	//模板设置
	public function tempSet(){
		if(I('type')){
			$condition['temp_type']=I('type');
		}
		if(I('price')){
			$price=I('price');
			if($price == 1){
				$condition['temp_price']=0;
			}else if($price == 2){
				$condition['temp_price']=array('gt',0);
			}
		}
		if(I('color') && I('color')>0){
			$condition['temp_color']=I('color');
		}
		$condition['isdelete']=0;
		$number=$this->modelTemp->getTempCount($condition);
		$size = 4;
		$page = new \Think\PageAjax($number, $size);
		$rollPage = 5;
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";	
		$field="temp_id,temp_pic,temp_name_zh,temp_price,temp_status,temp_name";
		$order="temp_id desc";
		$tempInfo=$this->modelTemp->getTempInfo($condition,$field,$limit,$order);
		$style = "";
		$onclass = "thispage";
		$pagestr = $page -> show($style,$onclass,"");  //组装分页字符串	
		$pageinfo['current']=I('p');
		$pageinfo['count']=$number;
		//公司所用模板
		$comId=$this->comId;
		$comUseTpl=$this->modelTemp->comTemp($comId);
		$this->assign('isuse',$comUseTpl['templete']);
		$pageinfo['totalpage']=ceil($number/$size);
		$this->assign('pageinfo',$pageinfo);
		$this->assign('pagestr',$pagestr);
		$this->assign('list',$tempInfo);
		$this-> display();
	}

}
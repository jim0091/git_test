<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[IndexController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-1-4 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2016
 * +----------------------------------------------------------------------
 * |chr(108).chr(105).chr(104).chr(111).chr(110).chr(103).chr(113).chr(105).chr(97).chr(110).chr(103)
 * +----------------------------------------------------------------------
 *  */
namespace Businesscircle\Controller;
use Think\Cache\Driver\Redis;

use Common\Controller\CommonController;

use Common\Common\Classlib\UploadFile\UploadImages;

use Think\Upload;

use Think\Controller;
class TestController extends CommonController {
	//继承RootController,代表执行本控制器内的所以方法都需要登录
	//继承CommonController,代表执行本控制器内的所以方法都不需要登录
	//public 表示全局，类内部外部子类都可以访问；
	//private表示私有的，只有本类内部可以使用；
	//protected表示受保护的，只有本类或子类或父类中可以访问；
	public function testtime(){
		$time = '2017-03-17 18:31:07';
		$wordTime = wordTime($time);
		var_dump($wordTime);exit;
	}
	
	//四舍五入
	public function round(){
		$a = 275.700;
		$b = $a*100;
		var_dump($b);
	}
	
	public function echojson(){
		$string = '{ ["result"]=> int(1) ["errcode"]=> int(1000) ["msg"]=> string(43) "鏈嶅姟 [api/h5card/getWxUserInfo] 鏈畾涔 " }';
		$jsonarray = json_decode($string);
		var_dump($jsonarray);
		
	}
	
	
	public function testnull(){
		$condition['com_id'] = '1474620100027';
		$condition['status'] = C('DATA_STATUS')[1]['key'];
		$condition['ls_status'] = C('LS_STATUS')[1]['key'];
		//$condition['sort_number'] = array('NEQ','NULL');
		$ModelObj = D('BusinesscircleTopic');
		$data = $ModelObj->where($condition)->order('stick DESC,sort_num DESC,create_time DESC')->select();
		$count = $ModelObj->where($condition)->count();
		var_dump($count);
		var_dump($data);
	}

	public function microtime(){
		var_dump(time());
		var_dump(microtime(false));
		var_dump(getMillisecond());
		var_dump(mb_strlen(getFileName(),'UTF8'));
		var_dump(mb_strlen('UploadImages/Businesscircle/Topic/'.getFileName(),'UTF8'));
		var_dump(getFileName());
		var_dump(ord('l'));
		var_dump(chr(108).chr(105).chr(104).chr(111).chr(110).chr(103).chr(113).chr(105).chr(97).chr(110).chr(103));
	}
	
	public function testAccount(){
		//'API'=>'http://10.46.122.136:8080/lshe.framework.protocol.http/api/
		$userName = '13066899989';
		$password = '123456';
		$sign = md5('login_pwd=' . $password . '&phone_num=' . $userName . C('API_KEY'));
		$data = array(
				'phone_num' => $userName,
				'login_pwd' => $password,
				'sign' => $sign
		);
		$login = $this->requestPost(C('API') . 'mallUser/empLogin', $data);
		//$login = $this->requestPost('http://192.168.1.186:8080/lshe.framework.protocol.http/api/mallUser/empLogin', $data);
		//$login = $this->requestPost('http://10.46.122.136:8080/lshe.framework.protocol.http/api/mallUser/empLogin', $data);
		$uclogin = json_decode($login, TRUE);
		$data = $uclogin['data']['info'];
		var_dump($login);
		var_dump($uclogin);
		var_dump($data);
	}
	
	public function testCatMobile(){
		$mobileNumber = 13066899989;
		$str = cutMobile($mobileNumber);
		var_dump($str);exit;
	}
	
	/**
	 * 测试上传图片
	 * 2017-03-13 11:17
	 */
//     public function Upload(){
//     	$upload = new Upload();// 实例化上传类
//     	$upload->maxSize = 3145728;//文件上传的最大文件大小（以字节为单位），0为不限大小
//     	$upload->rootPath = C('ROOT_PUBLIC');//文件上传保存的根路径
//     	$upload->savePath = 'Businesscircle/bannerconfig/';//文件上传的保存路径（相对于根路径），不存在会自动创建
//     	//$upload->saveName = date("YmdHis").mt_rand(100000, 999999);//上传文件的保存规则，支持数组和字符串方式定义
//     	$upload->saveName = array('getFileName','');//上传文件命名规则，[getFileName]-自定义函数名，[1]-参数，多个参数使用数组
//     	$upload->exts     = array('jpg', 'gif', 'png', 'jpeg');//允许上传的文件后缀
//     	$upload->saveExt  = 'png';//上传文件的保存后缀，不设置的话使用原文件后缀
//     	$upload->autoSub  = true;//自动使用子目录保存上传文件 默认为true
//     	$upload->replace  = false;//存在同名文件是否是覆盖，默认为false
//     	$upload->subName  = array('date','Y-m'); //子目录创建方式，采用数组或者字符串方式定义
    	
//     	$info   =   $upload->upload();
//     	var_dump($info);
//     	exit;
//     	if($_FILES['images1']){
//     		$imgPath1 =$upload->rootPath.$info['images1']['savepath'].$info['images1']['savename'];
//     	}
//     	if($_FILES['images2']){
//     		$imgPath2 =$upload->rootPath.$info['images2']['savepath'].$info['images2']['savename'];
//     	}
//     	if($_FILES['images3']){
//     		$imgPath3 =$upload->rootPath.$info['images3']['savepath'].$info['images3']['savename'];
//     	}
    	
//     	var_dump($imgPath1,$imgPath2,$imgPath3);
//     }
    
    
//     public function index1(){
//     	if($_FILES){
//     		var_dump(count($_FILES));
//     		//$uploadConfig['rootPath'] = './NewApi/Public/';//根路径
//     		////savePath规则：UploadImages:所有图片的上传地址;Businesscircle:模块名;bannerconfig:具有代表性的名字
//     		$uploadConfig['savePath'] = 'UploadImages/Businesscircle/bannerconfig/';//相对根路径  
//     		$upload = new UploadImages();
//     		$uploadInfo = $upload->ImagesUpload($uploadConfig);
//     		var_dump(count($uploadInfo));
//     		var_dump($uploadInfo);
//     		exit;
//     		foreach ($uploadInfo as $info){
//     			$data['bannerimages'] = $info['imagesPath'];//类型，默认为1
//     		}
//     		$data['com_id'] = $postData['com_id'];//企业ID
//     		$data['type'] = $postData['type'];//类型，默认为1
//     		var_dump($data);
//     	}else{
//     		exit('//请选择上传文件');
//     	}
//     }
    
    
    public function index(){
    	$this->Index_route();
    }
    
    
    protected function Index_route(){
    	$data['hehehe'] = 'hehehe';
    	$data['xxixxi'] = (I('get.'));
    	$successInfo['status'] = 1000;
    	$successInfo['message']="可以";
    	$successInfo['data'] = $data;
    	$this->retSuccess($successInfo);
    }
    
    //public 表示全局，类内部外部子类都可以访问；
    //private表示私有的，只有本类内部可以使用；
    //protected表示受保护的，只有本类或子类或父类中可以访问；
   
    
    
    public function tarr(){
    	$c = array();
    	$a[0] = 0;
    	$a[1] = 1;
    	$a[2] = 2;
    	$a[3] = 3;
    	$a[4] = 4;
    	$a[5] = 5;
    	array_push($c, $a);
    	$b[0] = 6;
    	$b[1] = 7;
    	$b[2] = 8;
    	$b[3] = 9;
    	$b[4] = 10;
    	$b[5] = 11;
    	array_push($c, $b);
    	var_dump($c);exit;
    }
    
    
    
    
    
    
    
    
//     //点击分类取出不同的商品
//     public function getItemInfo($item_config_id){
//     	$com_id = $_SESSION['com_id'];
//     	$Service = new IndexService();
//     	$where['item_config_id'] = $item_config_id;
//     	$where['com_id'] = $com_id;
//     	$where['disabled'] = 0;//未禁用
//     	$field = 'item_config_id,cat_name,cat_banner,recommend,item_ids';
//     	$goodsIdConf = $Service->find($where,$field);
//     	if($goodsIdConf){
//     		if(!empty($goodsIdConf['recommend'])){
//     			$recommendArray = explode(',', $goodsIdConf['recommend']);
//     			if(!empty($recommendArray)){
//     				$goodsInfo = $Service->getItem($recommendArray);
//     			}else{
//     				//取一级类的更多
//     				if(!empty($goodsIdConf['item_ids'])){
//     					$recommendArray = explode(',', $goodsIdConf['item_ids']);
//     					if(!empty($recommendArray)){
//     						$goodsInfo = $Service->getItem($recommendArray);
//     					}else{
//     						//去找是否存在二级分类
//     						$towCataIdConf = $Service->getTowCate($item_config_id,$com_id);
//     						if(!empty($towCataIdConf)){
//     							if(!empty($towCataIdConf['recommend'])){
//     								$recommendArray2 = explode(',', $towCataIdConf['recommend']);
//     								if(!empty($recommendArray2)){
//     									$goodsInfo = $Service->getItem($recommendArray2);
//     								}else{
//     									//取二级的更多
//     									if(!empty($towCataIdConf['item_ids'])){
//     										$recommendArray3 = explode(',', $towCataIdConf['item_ids']);
//     										if(!empty($recommendArray3)){
//     											$goodsInfo = $Service->getItem($recommendArray3);
//     										}else{
//     											//连二级的更多都没有了，特殊处理
//     										}
//     									}else{
//     										//二级的更多不存在
//     									}
//     								}
//     							}else{
//     								//取二级的更多
//     								if(!empty($towCataIdConf['item_ids'])){
//     									$recommendArray3 = explode(',', $towCataIdConf['item_ids']);
//     									if(!empty($recommendArray3)){
//     										$goodsInfo = $Service->getItem($recommendArray3);
//     									}else{
//     										//连二级的更多都没有了，特殊处理
//     									}
//     								}else{
//     									//二级的更多不存在
//     								}
//     							}
//     						}else{
//     							//一级没取到，二级也不存在，特殊处理
//     						}
//     					}
//     				}else{
//     					//去找是否存在二级分类
//     					$towCataIdConf = $Service->getTowCate($item_config_id,$com_id);
//     					if(!empty($towCataIdConf)){
//     						if(!empty($towCataIdConf['recommend'])){
//     							$recommendArray2 = explode(',', $towCataIdConf['recommend']);
//     							if(!empty($recommendArray2)){
//     								$goodsInfo = $Service->getItem($recommendArray2);
//     							}else{
//     								//取二级的更多
//     								if(!empty($towCataIdConf['item_ids'])){
//     									$recommendArray3 = explode(',', $towCataIdConf['item_ids']);
//     									if(!empty($recommendArray3)){
//     										$goodsInfo = $Service->getItem($recommendArray3);
//     									}else{
//     										//连二级的更多都没有了，特殊处理
//     									}
//     								}else{
//     									//二级的更多不存在
//     								}
//     							}
//     						}else{
//     							//取二级的更多
//     							if(!empty($towCataIdConf['item_ids'])){
//     								$recommendArray3 = explode(',', $towCataIdConf['item_ids']);
//     								if(!empty($recommendArray3)){
//     									$goodsInfo = $Service->getItem($recommendArray3);
//     								}else{
//     									//连二级的更多都没有了，特殊处理
//     								}
//     							}else{
//     								//二级的更多不存在
//     							}
//     						}
//     					}else{
//     						//一级没取到，二级也不存在，特殊处理
//     					}
//     				}
//     			}
//     		}else{
//     			//取一级类的更多
//     			if(!empty($goodsIdConf['item_ids'])){
//     				$recommendArray = explode(',', $goodsIdConf['item_ids']);
//     				if(!empty($recommendArray)){
//     					$goodsInfo = $Service->getItem($recommendArray);
//     				}else{
//     					//去找是否存在二级分类
//     					$towCataIdConf = $Service->getTowCate($item_config_id,$com_id);
//     					if(!empty($towCataIdConf)){
//     						if(!empty($towCataIdConf['recommend'])){
//     							$recommendArray2 = explode(',', $towCataIdConf['recommend']);
//     							if(!empty($recommendArray2)){
//     								$goodsInfo = $Service->getItem($recommendArray2);
//     							}else{
//     								//取二级的更多
//     								if(!empty($towCataIdConf['item_ids'])){
//     									$recommendArray3 = explode(',', $towCataIdConf['item_ids']);
//     									if(!empty($recommendArray3)){
//     										$goodsInfo = $Service->getItem($recommendArray3);
//     									}else{
//     										//连二级的更多都没有了，特殊处理
//     									}
//     								}else{
//     									//二级的更多不存在
//     								}
//     							}
//     						}else{
//     							//取二级的更多
//     							if(!empty($towCataIdConf['item_ids'])){
//     								$recommendArray3 = explode(',', $towCataIdConf['item_ids']);
//     								if(!empty($recommendArray3)){
//     									$goodsInfo = $Service->getItem($recommendArray3);
//     								}else{
//     									//连二级的更多都没有了，特殊处理
//     								}
//     							}else{
//     								//二级的更多不存在
//     							}
//     						}
//     					}else{
//     						//一级没取到，二级也不存在，特殊处理
//     					}
//     				}
//     			}else{
//     				//去找是否存在二级分类
//     				$towCataIdConf = $Service->getTowCate($item_config_id,$com_id);
//     				if(!empty($towCataIdConf)){
//     					if(!empty($towCataIdConf['recommend'])){
//     						$recommendArray2 = explode(',', $towCataIdConf['recommend']);
//     						if(!empty($recommendArray2)){
//     							$goodsInfo = $Service->getItem($recommendArray2);
//     						}else{
//     							//取二级的更多
//     							if(!empty($towCataIdConf['item_ids'])){
//     								$recommendArray3 = explode(',', $towCataIdConf['item_ids']);
//     								if(!empty($recommendArray3)){
//     									$goodsInfo = $Service->getItem($recommendArray3);
//     								}else{
//     									//连二级的更多都没有了，特殊处理
//     								}
//     							}else{
//     								//二级的更多不存在
//     							}
//     						}
//     					}else{
//     						//取二级的更多
//     						if(!empty($towCataIdConf['item_ids'])){
//     							$recommendArray3 = explode(',', $towCataIdConf['item_ids']);
//     							if(!empty($recommendArray3)){
//     								$goodsInfo = $Service->getItem($recommendArray3);
//     							}else{
//     								//连二级的更多都没有了，特殊处理
//     							}
//     						}else{
//     							//二级的更多不存在
//     						}
//     					}
//     				}else{
//     					//一级没取到，二级也不存在，特殊处理
//     				}
//     			}
//     		}
//     	}
//     	return $goodsInfo;
//     }
    
    
    
    public function testtoken(){
    	$Redis = new Redis();
    	$userInfo = $Redis->get('3a414a7fe42939143b51d2cb7801d665');
    	var_dump($userInfo);exit;
    }
    
    public function testService(){
    	$data['account'] = session('account');
    	$data['opassword'] = $_POST['opassword'];
    	$data['npassword'] = $_POST['npassword'];
    	vendor('jsonRPC.jsonRPCService');
    	$Service = new \jsonRPCService(C('Mbsvc_URL'));//'Mbsvc_URL' => 'http://localhost:8080/Mbsvc/Service',
    	$ret = $Service->editPassword(json_encode($data));
    	$obj = json_decode($ret);
    	if (C('UFG_CONST.SUCCESS') == $obj->result) {
    		$this->assign('message', '修改成功');
    	} else {
    		$this->assign('message', '修改失败, 请重试');
    	}
    }
    
    
    public function hehes(){
    	
    }
    
    
}
<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {	
	
	public function __construct() {
		parent::__construct();	
				
		//如果商城清除cookie，则清除所有的cookie和session，实现同步退出
		$accountCookie=cookie('account');
		if(empty($accountCookie)){
			session(null);
			cookie('account',null);
			cookie('LSUID',null);
			cookie('UNAME',null);
		}
		$account = array();
		$accountSession=session('account');
		if(!empty($accountSession)){
			$account=$accountSession['member'];
		}
		if(empty($accountSession) && !empty($accountCookie)){
			$account=json_decode($accountCookie,true);
		}		
		
		$path=str_replace('/Show/Home/Controller','',dirname(__FILE__));
		include_once($path.'/plugin/mobile/checkMobile.php');
		$mobile_detect = new \Mobile();
		if($mobile_detect->isMobile() and ($mobile_detect->is('iOS') or $mobile_detect->version('Android'))){			
			$host=$_SERVER['HTTP_HOST'];
			$domain=current(explode('.',$host));
			$condition['com_domain']=$domain;
			$company=M('company_config')->field('com_id,wshop_refer,wshop_index')->where($condition)->find();
			if(!empty($company['wshop_index'])){
				if(empty($account['id'])){
					header("Location:http://www.lishe.cn/wshop.php/Login/login");
					exit;
				}
				$refer=$company['wshop_index'];
			}else{
				$refer='http://www.lishe.cn/wshop.php';
			}
			
			header("Location:".$refer);
			exit;
		}
		
		$this->assign('userId',$account['id']);
		$this->assign('userName',$account['userName']);
		//个人积分
		if($account['id']){
			$point=D('Show')->getPersonPoint($account['id']);
			$this->assign('point',$point);
			//购物车数量
			$cartNumber=D('Show')->getCartNumber($account['id']);
			$this->assign('cartNumber',$cartNumber);
		}
		
		$this->uid=$account['id'];
		$this->comId=$account['comId'];
		$this->userName=$account['userName'];
		$this->index=$account['index'];
		$this->refer=$account['refer'];
		$this->assign('root',C('ROOT')); 
		$this->assign('action',$action);
		$host=$_SERVER['HTTP_HOST'];
		$dataFile='Show/Runtime/Data/'.$host.'.php';
		if(!file_exists($dataFile)){
			$hosts=current(explode(".",$host));
			$condition['com_domain']=$hosts;
			$condition['is_delete']=0;
			$field="com_name,com_logo,com_copyright,com_id,mark,status,index";
			$companyInfo=D('Show')->getCompanyInfo($condition,$field);
			if(empty($companyInfo['com_id'])){
				echo 'No site found';
				exit;
			}
			if($companyInfo['status']==0){
				echo 'Not audited';
				exit;
			}
			session('comId',$companyInfo['com_id']);
			$this->cachedFile($dataFile,"<?php	\n\$companyInfo=".var_export($companyInfo,TRUE).";\n?>",false);
		}else{
			include_once($dataFile);
		}
		$footInfo=json_decode($companyInfo['com_copyright'],true);
		$this->referIndex=$companyInfo['index'];
		$this->status=$companyInfo['status'];
		if(!session('comId')){
			session('comId',$companyInfo['com_id']);
		}
		//图片前缀
		$this->assign('img',C('IMGSRC'));
		if(!empty($companyInfo['com_logo'])){
			$logo=C('IMGSRC').$companyInfo['com_logo'];
		}else{
			$logo='';
		}
		$this->assign('logo',$logo);
		$this->assign('cname',$companyInfo['com_name']);
		$this->assign('info',$footInfo);
		$this->assign('control',$companyInfo['mark']);
		//推荐阅读
		if(session('comId')){
			$rememberRead=D('Show')->rememberRead(session('comId'));
			foreach($rememberRead as $key=>$value){
				switch($value['category']){
						case 1:
							$rememberRead[$key]['catname']= "行业聚焦";
							break;
						case 2:
							$rememberRead[$key]['catname']= "媒体报道";
							break;
						case 3:
							$rememberRead[$key]['catname']= "企业公告";
							break;												
				}
				
			}
			$this->assign('rememberRead',$rememberRead);
		}
	}
	
	//超级管理员登录 赵尊杰 2016-09-01
	public function superAdminLogin($userName,$password,$mark){
		$loginUid=1;
		$loginAccount='13800008888';
		$loginPass=md5('lishe000888');
		$loginUserName='超级管理员';
		if($userName==$loginAccount){
			if($loginPass!=md5($password)){
				echo json_encode(array(-1,'超级管理员密码不正确',-1));
				exit;
			}
			$account=array(
        		'id'=>$loginUid,
        		'account'=>$loginAccount,
        		'userName'=>$loginUserName
    		);
			$condition['mark']=$mark;
			$userCompany=M('company_config')->field('com_id,refer,index')->where($condition)->find();
			if(empty($userCompany['com_id'])){
				echo json_encode(array(-2,'找不到该企业的信息！',-2));
				exit;
			}
			$account['comId']=$userCompany['com_id'];
			$account['index']=urlencode($userCompany['index']);					
			$account['refer']=urlencode($userCompany['refer']);
			
			session('account',array('member'=>$account));
    		cookie('account',json_encode($account));
    		cookie('LSUID',$loginUid);
    		cookie('UNAME',$data['empName']);
    		echo json_encode(array($loginUid,'登录成功！',$userCompany['refer']));
    		exit;
		}
	}
		
	//模拟提交
	public function requestPost($url='', $data=array()) {
        if(empty($url) || empty($data)){
            return false;
        }
        $o="";
        foreach($data as $k=>$v){
            $o.="$k=".$v."&";
        }
        $param=substr($o,0,-1);
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS, $param);
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }
    

    //同步京东订单
    public function jdOrderPost($paymentid){                          
        $data=array(
            'paymentId'=>$paymentid
        );

        $url=C('API_STORE').'syncOrder';
        return $this->requestPost($url,$data);
    }
    
    //创建目录
	public function creatDir($fileName,$type='obj'){
		if(strpos($fileName,'/')){
			$dirArray = explode('/',$fileName);
			array_pop($dirArray);
			if($type=='obj'){
				$dir = ROOT;
			}else{
				$dir = UE_ROOT;
			}
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
	
	//缓存数据方法
	public function cachedFile($fileName,$data,$serialize=true){
		$fp = @fopen($fileName,'w');
		if($fp){
			if($serialize){
				$data=@serialize($data);
			}
			@fwrite($fp,$data);
			@fclose($fp);
			return true;
		}else{
			@fclose($fp);
			return false;//生成失败：目录不可写
		}		
	}
	
	/**
	 *包邮运费计算
	 *$comId公司id
	 *$itemId商品id
	 *$address
	**/
	public function freePost($comId, $items, $address){

		//查询所有的商品		
		foreach ($items as $kId => $vId) {
			$itemList[] =M('sysitem_item')->field('item_id,shop_id,price,weight')->where('item_id ='.$vId['itemId'])->find();
		}

		$newItemList=array();
		//计算店铺消费总金额			
		foreach($itemList as $kItem=>$vItem){
		    if(!isset($newItemList[$vItem['shop_id']])){
		        $newItemList[$vItem['shop_id']]=$vItem;
		    }else{
		    	$newItemList[$vItem['shop_id']]['price'] += floatval($vItem['price']);
		        $newItemList[$vItem['shop_id']]['wight'] += floatval($vItem['weight']);
		    }
		}
		
		//判断是否有comId
		$freePostArr = C('FREE_POST');
		$freePost = $freePostArr[$comId];
		
		if (!empty($freePost)) {
			$priceSum = 0;
			foreach($itemList as $key=>$value){
			  	$priceSum += $value['price'];
			}
		}
		//if($priceSum >= !empty(C('FREE_POST')[$comId])),这是原有判断条件
		//修正为下
		if (!empty($freePost) && $priceSum >= $freePost) {
			//包邮
			foreach ($newItemList as $kNitem => $vNitem) {
				$newItemList[$kNitem]['freePost'] = 0;
			}
		}else{

			//查询包邮表
			$postInfo=M('syspromotion_freepostage')->field('freepostage_id,shop_id,limit_money')->select();		

			//计算是否包邮
			foreach ($newItemList as $kNitem => $vNitem) {
				foreach ($postInfo as $kPost => $vPost) {
					if ($vNitem['shop_id'] == $vPost['shop_id']) {
						if ($vPost['limit_money'] > $vNitem['price']) {
							//需要计算运费
							$newItemList[$kNitem]['isFreePost'] = 1;
						}else{
							//包邮
							$newItemList[$kNitem]['isFreePost'] = 0;
						}
					}
				}
				
			}

			//计算区域运费				
			$addrArr=explode(':',$address);
			$addrDefaultIdArr=explode('/',$addrArr[1]);
			$addrFeeProvince=$addrDefaultIdArr[0];//省
	        $addrFeeCity=$addrDefaultIdArr[1];//市
	        $addrFeeArea=$addrDefaultIdArr[2];//区

			foreach ($newItemList as $kNitem => $vNitem) {
				//大于0是需要计算运费的
				if ($vNitem['isFreePost'] > 0) {			
					$dlytmplInfo = M('syslogistics_dlytmpl')->where('shop_id='.$vNitem['shop_id'])->find();
					$dlytmpFeeConf=unserialize($dlytmplInfo['fee_conf']);
					
		            foreach($dlytmpFeeConf as $kDlytmp=>$vDlytmp){

						if(count($dlytmpFeeConf) == 1){
			                $shopFeeAreaTotal = floatval($vDlytmp['start_fee']) + (intval($vNitem['weight']) * floatval($vDlytmp['add_fee']));
			            }

		                $shopPressAreaArr=array();
		                $shopPressAreaArr=explode(',',$vDlytmp['area']);
		                 
		               
		                if(in_array($addrFeeProvince,$shopPressAreaArr)){  //省
		                   $shopFeeAreaTotal += floatval($vDlytmp['start_fee']) + (intval($vNitem['weight']) * floatval($vDlytmp['add_fee']));
		                }

		                 if(in_array($addrFeeCity,$shopPressAreaArr)){ //市  $addrFeeCity
		                   $shopFeeAreaTotal += floatval($vDlytmp['start_fee']) + (intval($vNitem['weight']) * floatval($vDlytmp['add_fee']));
		                }

		                 if(in_array($addrFeeArea,$shopPressAreaArr)){ //区  $addrFeeArea
		                   $shopFeeAreaTotal += floatval($vDlytmp['start_fee']) + (intval($vNitem['weight']) * floatval($vDlytmp['add_fee']));
		                }
		                                
		            }				
					$newItemList[$kNitem]['freePost'] = $shopFeeAreaTotal;
				}else{
					$newItemList[$kNitem]['freePost'] = 0;
				}
			}
		}
		return $newItemList;


	}

}
<?php
/**
 +------------------------------------------------------------------------------
* WgiftController
+------------------------------------------------------------------------------
* @author   	高龙 <goolog@126.com>
* @version  	$Id: WgiftController.class.php v001 2016-11-02
* @description 微商城控制器
+------------------------------------------------------------------------------
*/
namespace Home\Controller;
class GiftController extends CommonController{

	public function __construct(){
		parent::__construct();
		$this->giftCard = M('gift_card');
		$this->giftSearchTag = M('gift_search_tag');
		$this->giftTrade = M('gift_trade');
		$this->company = M('company_config');
		$this->sysitemItem = M('sysitem_item');
		$this->giftReceiverAddr = M('gift_receiver_addr');
		$this->sysuserAccount = M('sysuser_account');
		$this->giftPost = M('gift_post');
		//$this->wgiftCatItem = D('GiftCatItem');
	}

	// 	/**
	// 	 * 分类列表
	// 	 * @author Gaolong
	// 	 */
	// 	public function catList(){
	// 		//获取分类
	// 		$catList = $this->wgiftCat->order('sort ASC')->select();
	// 		$catArr = array();
	// 		$this->assign('catList', $catList);
	// 		$this->display('catList');
	// 	}

	// 	/**
	// 	 * 添加分类
	// 	 * @author Gaolong
	// 	 */
	// 	public function addCat(){
	// 		$catName = I('post.catName', '', 'strip_tags');
	// 		$catSort = I('post.catSort', 5, 'intval');
	// 		$ret = array('code'=>-1, 'msg'=>'unkown error');

	// 		if(empty($catName)){
	// 			$ret['msg'] = '分类名不能为空';
	// 			$this->ajaxReturn($ret);
	// 		}
	// 		if(!is_numeric($catSort) || $catSort < 1){
	// 			$ret['msg'] = '排序错误';
	// 			$this->ajaxReturn($ret);
	// 		}
	// 		$data = array();
	// 		$data['cat_name'] = $catName;
	// 		$data['sort'] = $catSort;
	// 		$result = $this->wgiftCat->add($data);
	// 		if($result){
	// 			$ret['code'] = 1;
	// 			$ret['msg'] = 'success';
	// 		}else{
	// 			$ret['msg'] = 'fail';
	// 		}
	// 		$this->ajaxReturn($ret);
	// 	}

	// 	/**
	// 	 * 设置状态 (-1.禁用 1.正常 )
	// 	 * @author Gaolong
	// 	 */
	// 	public function setCatStatus(){
	// 		$catId = I('post.cat_id', -1, 'intval');
	// 		$status = I('post.status', 0, 'intval');
	// 		$ret = array('code'=>-1, 'msg'=>'unkown error');
	// 		if(!is_numeric($catId) || $catId < 1){
	// 			$ret['msg'] = 'invalid cat_id';
	// 			$this->ajaxReturn($ret);
	// 		}
	// 		if($status !=1 && $status != -1){
	// 			$ret['msg'] = 'invalid status';
	// 			$this->ajaxReturn($ret);
	// 		}
	// 		$result = $this->wgiftCat->where("wcat_id=$catId")->setField('status',$status);
	// 		if($result){
	// 			$ret['code'] = 1;
	// 			$ret['msg'] = 'success';
	// 		}else{
	// 			$ret['msg'] = 'fail';
	// 		}
	// 		$this->ajaxReturn($ret);
	// 	}

	// 	/**
	// 	 * 设置排序
	// 	 * @author Gaolong
	// 	 */
	// 	public function setCatSort(){
	// 		$catId = I('post.cat_id','-1','intval');
	// 		$sort = I('post.sort','-1','intval');
	// 		$ret = array('code'=>-1, 'msg'=>'unkown error');
	// 		if(!is_numeric($catId) || $catId < 1){
	// 			$ret['msg'] = 'invalid cat_id';
	// 			$this->ajaxReturn($ret);
	// 		}
	// 		if(!is_numeric($sort) || $sort < 1){
	// 			$ret['msg'] = 'invalid sort';
	// 			$this->ajaxReturn($ret);
	// 		}
	// 		$result = $this->wgiftCat->where("wcat_id=$catId")->setField('sort',$sort);
	// 		if($result){
	// 			$ret['code'] = 1;
	// 			$ret['msg'] = 'success';
	// 		}else{
	// 			$ret['msg'] = 'fail';
	// 		}
	// 		$this->ajaxReturn($ret);
	// 	}

	// 	/**
	// 	 * 获取商品列表
	// 	 * @author Gaolong
	// 	 */
	// 	function itemList(){
	// 		//获取商品
	// 	}

	// 	/**
	// 	 * 添加分类商品
	// 	 * @author Gaolong
	// 	 */
	// 	function addCatItem(){

	// 	}

	// 	/**
	// 	 * 设置商品分类状态
	// 	 * @author Gaolong
	// 	 */
	// 	function setItemStatus(){

	// 	}

	// 	/**
	// 	 * 交易列表
	// 	 * @author Gaolong
	// 	 */
	// 	function tradeList(){

	// 	}

	// 	/**
	// 	 * 交易详情
	// 	 * @author Gaolong
	// 	 */
	// 	function tradeDetail(){

	// 	}

	//贺卡列表
	public function cardList(){
		$cardList = $this->giftCard->where('status!=-1')->select();
		$this->assign('cardList',$cardList);
		$this->display('cardList');
	}
	//添加贺卡
	public function addCard(){
		if(IS_POST){
			$blessTitle = I('post.blessTitle');
			$blessWord = I('post.blessWord');
				
			$data = array();
			$data['bless_title'] = $blessTitle;
			$data['bless_word'] = $blessWord;
				
			$result = $this->giftCard->add($data);
			if(!is_numeric($result)){
				$this->error('添加失败！');
			}
				
			$cardId = $result;
			$cardFile = $_FILES['cardfile'];
			//上传合贺卡图片
			if(!empty($cardFile['name'])){
				$fileinfo = $this->uploadCardFile($cardFile, $cardId);
				if(!empty($fileinfo['thumb'])){
					//生成缩略图
					$data = array();
					$data['card_pic'] = $fileinfo['savepath'];
					$data['card_pic_thumb'] = $fileinfo['thumb'];
					$this->giftCard->where("card_id=$cardId")->save($data);
					$this->success("添加成功!");
				}else{
					$this->success("图片保存失败！");
				}
			}
		}else{
				
			$this->display('addCard');
		}
	}

	//设置贺卡状态
	public function setCardStatus(){
		//card_id:cardId,status:status
		$cardId = I('post.card_id',-2,'intval');
		$status = I('post.status',-2,'intval');
		$ret = array('code'=>1, 'msg'=>'unkown error');
		if(!is_numeric($cardId) || $cardId < 1){
			$ret['msg'] = '贺卡id有误';
			$this->ajaxReturn($ret);
		}
		if($status !== 1 && $status !== 0){
			$ret['msg'] = '非法状态';
			$this->ajaxReturn($ret);
		}
		$result = $this->giftCard->where("card_id=$cardId")->setField('status',$status);
		if($result){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['msg'] = 'fail';
		}
		$this->ajaxReturn($ret);
	}

	//编辑贺卡
	public function editCard(){

	}

	//搜索标签列表
	public function tagList(){
		$tagList = $this->giftSearchTag->order('sort,tag_id DESC')->select();
		$this->assign('tagList',$tagList);
		$this->display('tagList');
	}

	//添加标签
	public function addTag(){
		$tagName = I('post.tagName','', 'trim');
		//$sort = I('post.sort',2000, 'trim,intval');
		$ret = array('code'=> -1, 'msg'=>'unkown error');
		if(empty($tagName)){
			$ret['msg'] = 'empty tagName!';
			$this->ajaxReturn($ret);
		}
		$result = $this->giftSearchTag->add(array('tag_name'=>$tagName));
		if($result){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['msg'] = 'fail';
		}
		$this->ajaxReturn($ret);
	}

	public function setSort(){
		$tagId = I('post.tagId', -1, 'intval');
		$sort = I('post.sort', -1, 'intval');
		$ret = array('code'=>1, 'msg'=>'unlown error');
		if(empty($tagId) || $tagId < 1){
			$rets['msg'] = 'invalid tagId!';
			$this->ajaxReturn($ret);
		}
		if(empty($sort) || $sort < 1){
			$rets['msg'] = 'invalid sort!';
			$this->ajaxReturn($ret);
		}
		$result = $this->giftSearchTag->where("tag_id=$tagId")->setField('sort', $sort);
		if(is_numeric($result)){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['msg'] = 'fail';
		}
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 更新标签
	 */
	public function updateTag(){
		$tagId = I('post.tagId',-1,'intval');
		$tagName = I('post.tagName','','trim');
		$sort = I('post.tagSort',100,'intval');
		$priority = I('post.priority',0,'intval');
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		
		if(!is_numeric($tagId) || $tagId < 1){
			$ret['msg'] = 'invalid tagId!';
			$this->ajaxReturn($ret);
		}
		
		if(empty($tagName)){
			$ret['msg'] = 'empty tagName!';
			$this->ajaxReturn($ret);
		}
		
		if(!is_numeric($sort) || $sort < 1){
			$ret['msg'] = 'invalid sort';
			$this->ajaxReturn($ret);
		}
		if($priority != 1 && $priority != 0){
			$priority = 0;
		}
		$data =array();
		$data['tag_name'] = $tagName;
		$data['sort'] = $sort;
		$data['priority'] = $priority;
		$result = $this->giftSearchTag->where("tag_id=$tagId")->save($data);
		if(is_numeric($result)){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['msg'] = 'fail';
		}
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 删除标签
	 */
	public function delTag(){
		$tagId = I('post.tagId', -1, 'intval');
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		if(!is_numeric($tagId) || $tagId < 1){
			$ret['code'] = 'invalid tagId!';
			$this->ajaxReturn($ret);
		}
		$result = $this->giftSearchTag->where("tag_id=$tagId")->delete();
		if($result){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['code'] = 'fial!';
		}
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 交易列表
	 */
	public function tradeList(){
		$geiverMobile 	= I('get.geiverMobile', '', 'trim');
		$receiverMobile = I('get.receiverMobile', '', 'trim');
		$startdate 		= I('get.startdate', '', 'trim');
		$enddate 		= I('get.enddate', '', 'trim');
		$page 		= I('get.p', 1, 'intval');
		$listRows 	= 20;
		
		$where = array();
		if(!empty($geiverMobile)){
			//送礼人
			//account表
			$map = array();
			$map['mobile'] = $geiverMobile;
			$userId = $this->sysuserAccount->where($map)->getField('user_id');
			if($userId){
				$where['user_id'] = $userId;
			}else{
				$where['user_id'] = 0;
			}
		}
		
		if(!empty($receiverMobile)){
			//gift post	
			$map = array();
			$map['receiver_phone'] = $receiverMobile;
			$tidArr = $this->giftReceiverAddr->where($map)->field('tid')->select();
			if(!empty($tidArr)){
				$tmpArr = array();
				foreach ($tidArr as $val){
					$tmpArr[] = $val['tid'];
				}
				$where['tid'] = array('in', $tmpArr);
			}else{
				$where['tid'] = 0;
			}
		}
		
		if(!empty($startdate) && !empty($enddate)){
			$where['create_time'] = array(array('egt', $startdate),array('elt', $enddate)) ;
		}else{
			if(!empty($startdate)){
				//create_time
				$where['create_time'] = array('egt',$startdate);
			}else if(!empty($enddate)){
				//create_time
				$where['create_time'] = array('elt',$enddate);
			}
		}
		//加载交易信息
		$where['from'] = 'gift';
		$tradList = $this->giftTrade->where($where)->page($page, $listRows)->order('create_time DESC')->select();
		$count = $this->giftTrade->where($where)->count('id');
		
		//遍历交易信息
		$comIdArr = array();
		//$itemIdArr = array();
		$userIdArr = array();
		$tidArr = array();
		foreach ($tradList as $trade){
			$tidArr[] = $trade['tid'];
			$comIdArr[] = $trade['com_id'];
			//$itemIdArr[] = $trade['item_id'];
			$userIdArr[] = $trade['user_id'];
		}
		//加载公司信息
		$comArr = array();
		if(!empty($comIdArr)){
			$where = array();
			$where['com_id'] = array('in' ,$comIdArr);
			$comList = $this->company->field('com_id, com_name')->where($where)->select();
			foreach ($comList as $com){
				$comArr[$com['com_id']] = $com['com_name'];
			}
		}
		//加载收礼人信息
		$receiverArr = array();
		if(!empty($tidArr)){
			$where = array();
			$where['tid'] = array('in' ,$tidArr);
			$receiverList = $this->giftReceiverAddr->where($where)->select();
			foreach ($receiverList as $receiver){
				$receiverArr[$receiver['tid']] = $receiver;
			}
		}
		//加载赠送人信息
		$userArr = array();
		if(!empty($userIdArr)){
			$where = array();
			$where['user_id'] = array('in' ,$userIdArr);
			$userList = $this->sysuserAccount->field('user_id, mobile')->where($where)->select();
			foreach ($userList as $user){
				$userArr[$user['user_id']] = $user['mobile'];
			}
		}
		//加载赠送信息
		$postArr = array();
		if(!empty($tidArr)){
			$where = array();
			$where['tid'] = array('in' ,$tidArr);
			$postList = $this->giftPost->where($where)->select();
			foreach ($postList as $post){
				$postArr[$post['tid']] = $post;
			}
		}
		
		//加载商品数据
// 		$where = array();
// 		$where['item_id'] = array('in' ,$itemIdArr);
// 		$itemList = $this->sysitemItem->field('item_id, image_default_id')->where($where)->select();
// 		$itemArr = array();
// 		foreach ($itemList as $item){
// 			$itemArr[$item['item_id']] = $item['image_default_id'];
// 		}
		$page=new \Think\Page($count,$listRows);
		$this->assign('page',$page->show());
		
		$this->assign('geiverMobile', $geiverMobile);
		$this->assign('receiverMobile', $receiverMobile);
		$this->assign('startdate', $startdate);
		$this->assign('enddate', $enddate);
		$this->assign('comArr', $comArr);
		$this->assign('userArr', $userArr);
		$this->assign('receiverArr', $receiverArr);
		$this->assign('postArr', $postArr);
//		$this->assign('itemArr', $itemArr);
		$this->assign('count', $count);
		$this->assign('tradList', $tradList);
		$this->display('tradeList');
	}
	
	/**
	 * @param $contractFile 上传贺卡文件
	 */
	private function uploadCardFile($cardFile, $cardId){
		$saveName =  md5($cardId);
		$saveDir = './Upload/gift/card/';
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize = 1024 * 1024 * 5 ;// 设置附件上传大小 5M
		//$upload->exts = array('doc', 'pdf');// 设置附件上传类型
		$upload->savePath = '';
		$upload->autoSub = false;
		$upload->saveName = $saveName;
		$upload->replace = true;
		$upload->rootPath  =  $saveDir; // 设置附件上传根目录
		// 上传文件
		$info  =  $upload->uploadOne($cardFile);

		if(!$info) {// 上传错误提示错误信息
			$this->error($upload->getError());
		}
		// 上传成功
		$saveName = $info['savename'];
		$savePath = $saveDir.$saveName;
		$thumb = $saveDir.str_replace('.', '_thumb.', $saveName);
		//生成缩略图
		$imgSize = getimagesize($savePath);
		$sw = intval($imgSize[0] / 3);//缩放大小
		$sh = intval($imgSize[1] / 3);
		$image = new \Think\Image();
		$image->open($savePath);
		$image->thumb($sw, $sh)->save($thumb);
		return array('savepath'=>$savePath, 'thumb'=>$thumb);
	}
}

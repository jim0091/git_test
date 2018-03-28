<?php
/**
 +------------------------------------------------------------------------------
 * PostController
 +------------------------------------------------------------------------------
 * @author   	高龙 <goolog@126.com>
 * @version  	$Id: PostController.class.php v001 2016-11-01
 * @description 赠送控制器
 +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Think\Controller;
class PostController extends CommonController {
   
	public function __construct(){
		parent::__construct();
		$this->giftTrade = M('gift_trade');
		$this->sysitemItem = M('sysitem_item');
		$this->giftPost = M('gift_post');
		$this->giftCard = M('gift_card');
	}
	
	public function edit(){
		$tid = session('post_tid');
		if(empty($tid)){
			redirect('/gift.php');
		}
		//加载商品
		$itemInfo = $this->giftTrade->where("tid=$tid")->field('item_id,title,spec_nature_info')->find();
		//获取商品图片
		if(!empty($itemInfo['item_id'])){
			$itemImg = $this->sysitemItem->where("item_id={$itemInfo['item_id']}")->getField('image_default_id');
			$itemInfo['itemImg'] = $itemImg;
		}
		$this->weixinConfig();
		//加载贺卡
		$this->assign('itemInfo', $itemInfo);
		$this->display('edit');
	}
	
	private function weixinConfig(){
		$jsapi_ticket = S('weixin_ticket'); //获取ticket
		$appid = C('WEIXIN_APPID');
		if(empty($jsapi_ticket)){
			$secret = C('WEIXIN_APPSECRET');
			$grant_type = 'client_credential';
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type={$grant_type}&appid={$appid}&secret={$secret}";
			$result = curl($url);
			$result = json_decode($result, true);
			if(empty($result['access_token'])){
				return false;
			}
			
			$accessToken = $result['access_token'];
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$accessToken}&type=jsapi";
			$result = curl($url);
			$result = json_decode($result, true);
			if($result['errmsg'] != 'ok'){
				return false;
			}
			$jsapi_ticket = $result['ticket'];
			S('weixin_ticket',$jsapi_ticket, 7000);
		}
		
		$noncestr = md5('lishe_gift'.time());
		$timestamp = time();
		$url = 'http://'.$_SERVER['HTTP_HOST'].__SELF__;
		$str = "jsapi_ticket={$jsapi_ticket}&noncestr={$noncestr}&timestamp={$timestamp}&url={$url}";
		$signature = sha1($str);
		
		$this->assign('appid',$appid);
		$this->assign('noncestr',$noncestr);
		$this->assign('timestamp',$timestamp);
		$this->assign('signature',$signature);
	}
	
	//保存
	public function save(){
		if(!IS_POST) exit();
		$tid = session('post_tid');
		$ret = array('code'=>-1,'msg'=>'unkown error');
		if(empty($tid)){
			$ret['msg'] = '保存失败（empty tid）';
			$this->ajaxReturn($ret);
		}
		$postId = I('post.post_id',-1,'intval');
		$cardId = I('post.card_id',-1,'intval');
		$blessTitle = I('post.bless_title','','strip_tags,stripslashes');
		$blessWord = I('post.bless_word','','strip_tags,stripslashes');
		$giverName = I('post.giver_name','','strip_tags,stripslashes');
		$isAnonymous = I('post.is_anonymous', 0,'intval');
		$receiverName = I('post.receiver_name','','strip_tags,stripslashes');
		$receiverPhone = I('post.receiver_phone','','strip_tags,stripslashes');
		
		if(!is_numeric($cardId) && $cardId < 1){
			$ret['msg'] = '请选择贺卡';
			$this->ajaxReturn($ret);
		}
		if(empty($blessTitle)){
			$ret['msg'] = '祝福标题不能为空';
			$this->ajaxReturn($ret);
		}
		if(empty($blessWord)){
			$ret['msg'] = '祝福语不能为空';
			$this->ajaxReturn($ret);
		}
		if(empty($giverName)){
			$ret['msg'] = '赠送者姓名不能为空';
			$this->ajaxReturn($ret);
		}
		if($isAnonymous !== 1 && $isAnonymous !==0){
			$ret['msg'] = '参数错误';
			$this->ajaxReturn($ret);
		}
		if(empty($receiverName)){
			$ret['msg'] = '接收者姓名不能为空';
			$this->ajaxReturn($ret);
		}
		$reg = '/^1\d{10}$/';
		if(!preg_match($reg, $receiverPhone)){
			$ret['msg'] = '手机号有误';
			$this->ajaxReturn($ret);
		}
		
		//检查贺卡是否正常
		$where = array();
		$where['card_id'] = $cardId;
		$where['status'] = 1;
		$card = $this->giftCard->where($where)->field('card_id,card_pic')->find();
		if(empty($card)){
			$ret['msg'] = '抱歉，贺卡已禁用，请重新选择';
			$this->ajaxReturn($ret);
		}
		//验证该tid是否已经存在
		$result = false;
		$data = array();
		$data['user_id'] = $this->uid;
		$data['card_id'] = $cardId;
		$data['card_url'] = $card['card_pic'];
		$data['bless_title'] = $blessTitle;
		$data['bless_word'] = $blessWord;
		$data['is_anonymous'] = $isAnonymous;
		$data['giver_name'] = $giverName;
		$data['receiver_phone'] = $receiverPhone;
		
		if(is_numeric($postId) && $postId > 0){//更新
			$result = $this->giftPost->where("post_id=$postId")->save($data);
		}else{
			//添加
			$data['tid'] = $tid;
			$data['post_status'] = 0;
			$result = $this->giftPost->add($data);
			$postId = $result;
		}
		
		if(is_numeric($result)){
			session('post_tid', null);//销毁
			$ret['code'] = 1;
			$ret['msg'] = 'success';
			$ret['postid'] = $postId;
			$ret['shareurl'] = $this->createShareURL($tid, $postId);//分享URL
			$this->ajaxReturn($ret);
		}else{
			$ret['msg'] = '保存失败';
		}
		$this->ajaxReturn($ret);
	}
	
	//创建分享URL,包含tid, postid
	private function createShareURL($tid, $postId){
		$data = array();
		$data['tid'] = $tid;
		$data['postid'] = $postId;
		$dataStr = json_encode($data);
		$flag = authCode($dataStr,'ENCODE',C('KEY'));
		$flag = str_replace('+','%2B',$flag); //替‘+’
		return 'http://'.$_SERVER['HTTP_HOST'].U('Receive/open').'?flag='.$flag;
	}
	
	//已分享回调
	public function shared(){
		$postId = I('post.post_id',-1,'intval');
		if(!is_numeric($postId) || $postId < 1){
			echo 'valide postid';
			exit();
		}
		$result = $this->giftPost->where("post_id=$postId")->setField('post_status',1);//已分享
		if(is_numeric($result)){
			echo 'success';
		}else{
			echo 'fail';
		}
	}
	//贺卡列表
	public function cardlist(){
		$cardList = $this->giftCard->where('status=1')->select();
		$this->assign('cardList',$cardList);
		$this->display('cardlist');
	}
	//选择和卡
	public function chooseCard(){
		if(!IS_POST) exit;
		$cardid = I('post.cardid','-1','intval');
		$ret = array('code'=>-1, 'msg'=>'unkown errro');
		if(!is_numeric($cardid) || $cardid < 1){
			$ret['msg'] = 'invalid cardid';
			$this->ajaxReturn($ret);
		}
		$where = array();
		$where['card_id'] = $cardid;
		$where['status'] = 1;
		$card = $this->giftCard->where($where)->find();
		if(!empty($card)){
			session('postCardInfo',$card);//保存贺卡信息
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['msg'] = 'not exist';
		}
		$this->ajaxReturn($ret);
	}
	//获取我的贺卡信息
	public function myCard(){
		$card = session('postCardInfo');
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		$data = array();
		if(!empty($card)){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
			$data['card_id'] = $card['card_id'];
			$data['bless_title'] = $card['bless_title'];
			$data['bless_word'] = $card['bless_word'];
			$data['card_pic_thumb'] = $card['card_pic_thumb'];
		}
		$ret['data'] = $data;
		$this->ajaxReturn($ret);
	}
}
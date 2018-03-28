<?php
namespace Home\Controller;
class IndexController extends CommonController {

	public function index(){
		if(empty($this->uid)){
			header("Location:".__APP__."/Lishe/login");
		}else{
			header("Location:http://www.lishe.cn/member-index.html");
		}
	}

	//商品浏览记录
	public function itemHistory(){
		if (empty($this->uid)) {
			header("Location:".__APP__."/Lishe/login");
		}else{
			$itemHistoryModel = M("sysitem_item_history");
			$data['user_id'] = $this->uid;
			$data['item_id'] = I('post.itemId');
			$data['title'] = I('post.itemTitle');
			$data['img'] = I('post.itemImg');
			$data['price'] = I('post.itemPrice');
			$data['add_time'] = time();
			if ($data) {
				$historyCount = $itemHistoryModel->where('user_id ='.$this->uid)->count();
				$isHistory = $itemHistoryModel->where('user_id ='.$this->uid.' and item_id ='.$data['item_id'])->count();
				if ($isHistory < 1 ) {
					if ($historyCount > 9) {
						$historyInfo = $itemHistoryModel->where('user_id ='.$this->uid)->order('add_time asc')->find();
						$itemHistoryModel->where('history_id ='.$historyInfo['history_id'])->save($data); 
					}else{
						$itemHistoryModel->add($data);
					}					
				}

			}
		}
	}

	//清除商品浏览记录
	public function delHistory(){
		if ($this->uid) {
			$res = M("sysitem_item_history")->where('user_id ='.$this->uid)->delete();
			if ($res) {
				echo json_encode(array(1,'删除成功！'));
			}else{
				echo json_encode(array(0,'删除失败！'));
			}
		}else{
			echo json_encode(array(0,'请登录！'));
		}
	}

	//删除购物车商品
	public function delCart(){
		$cartId = I('post.cartId');
		if ($cartId) {
			$this->tradeCartModel = M("systrade_cart");	
			$res = $this->tradeCartModel->where('cart_id ='.$cartId)->delete();
			if ($res) {
				echo json_encode(array(1,'删除成功！'));
			}else{
				echo json_encode(array(0,'删除失败！'));
			}
		}else{
			echo json_encode(array(0,'获取购物车id失败！'));
		}
	}
		
}
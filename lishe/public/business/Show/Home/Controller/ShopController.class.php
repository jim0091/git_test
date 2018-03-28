<?php
namespace Home\Controller;
class ShopController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->modelShopCat=M('sysshop_shop_cat');
		$this->modelItem=M('sysitem_item');
	}

	/**
	*time 20160616 
	*新增 商品分类 start
	*/
	public function goodsType(){
		// var_dump(I('get.'));
		// var_dump($_SESSION);
		$admin_name = I('get.admin_name','','trim');
		$item_id = I('get.item_id',0,'intval');
		$shop_id = I('get.shop_id',0,'intval');

		$this -> assign('type_admin_name',$admin_name);

		$this -> assign('type_item_id',$item_id);

		$this -> assign('type_shop_id',$shop_id);
		// echo $shop_id;
		$where = array(
			'shop_id'=> $shop_id,
			'parent_id' => 0

			);
		// 得到第一级
        $parentId = $this->modelShopCat -> where($where)->select();

        // var_dump($parentId);
        $this -> assign('parentId',$parentId);

		$this -> display('main');
	}

	// 判断是否有二级分类
	public function sonLiShe(){
		// echo "hahah";
		$cat_id = I('post.cat_id',0,'intval');
		$shop_id = I('post.shop_id',0,'intval');
		// echo $cat_id.'_'.$shop_id;


		$where = array(
            'shop_id'=> $shop_id,
            'parent_id'=> $cat_id
			);
		// 得到第二级
        $sonId = $this->modelShopCat -> where($where)->select();
        // $this -> assign('sonId',$sonId);
        // var_dump($sonId);
        if(!empty($sonId) and $sonId){
            // echo 2;
                     
            $html .=  "<select name=\"use_platform_two\" class=\"x-input\" >";
            $html .=  "<option value=\"-1\">-----无-----</option>";
            foreach($sonId as $row){
            $html .= "<option class=\"parentLiShe\" value='".$row['cat_id']."'>".$row['cat_name']."</option>";
            }
            $html .= "</select>";

            echo $html;

        }else{
        	echo 1; //表示没有下一级
        }


	}

	// 执行插入操作
	public function insertItem(){
		// var_dump(I('post.'));
		$item_id = I('post.type_item_id',0,'intval');//得到产品的ID

		$shop_id = I('post.type_shop_id',0,'intval');		 
		$parent_id = I('post.use_platform','','trim');

		$son_id = I('post.use_platform_two','','trim'); // 得到二级目录的修改信息
		$son_id = ','.$son_id.',';

		$data = array(
            'shop_cat_id' => $son_id
			);

		$update = $this->modelItem ->where('item_id = '.$item_id)-> data($data) ->save();

		if($update){

         $this -> success("商品分类设置成功！","http://www.lishe.cn/shop/item/itemList.html");
			// echo "<script>alert('商品分类设置成功！');</script>";
			// echo "<script>window.history.back();</script>";
			// exit;

		}else{

			// echo "<script>alert('商品分类未设置！');</script>";
			// echo "<script>window.history.back();</script>";
			// exit;

         $this -> error("商品分类未设置！","http://www.lishe.cn/shop/item/itemList.html");

		}  
	}



}
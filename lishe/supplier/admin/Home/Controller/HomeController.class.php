<?php
namespace Home\Controller;
class HomeController extends CommonController {
    public function __construct(){
        parent::__construct();
        $this->supplier_user=M("supplier_user");
        $this->supplier_item=M("supplier_item");
        $this->supplier_order=M("supplier_order");
        $this->home=D("Home");
    }
    public function index(){
       //echo "index";
        header("location:".U("Home/goodsList"));
   }
    //商品列表页
    public function goodsList(){
        $page=empty($_GET['p'])?1:$_GET['p'];
        $like=empty($_GET['like'])?'':$_GET['like'];
        $id=empty($_GET['id'])?'':$_GET['id'];
        $status=empty($_GET['status'])?'':$_GET['status'];
        $cat_id_1=empty($_GET['cat_id_1'])?'':$_GET['cat_id_1'];
        $where=array();
        if(!empty($like)){
            $where['build_people']=array('like',"%$like%");
            $this->assign("like",$like);
        }
        if(!empty($status)){
            $where["status"]=$status;
        }
        if(!empty($id)){
            $where['id']=$id;
        }
        if(!empty($cat_id_1)){
            $where['cat_id_1']=$cat_id_1;
            $this->assign("cat_id_1",$cat_id_1);
        }
        $count=$this->supplier_item->where($where)->where("supplier_id=$this->adminId")->count();
        $list=$this->supplier_item->where($where)->where("supplier_id=$this->adminId")->page($page.',20')->select();
        $catList=$this->home->getAllCat();
        if(!empty($list)){
            $brandList=$this->home->getFieldByGoods($list,'brand_id','syscategory_brand');
            $cats=$this->home->getFieldByGoods($list,'cat_id',"syscategory_cat");
            $shopList=$this->home->getFieldByGoods($list,'shop_id',"sysshop_shop");
            $this->assign('brandList',$brandList);
            $this->assign('shopList',$shopList);
            $this->assign('cats',$cats);
        }
        $this->assign('catList',$catList);
        $page=new \Think\Page($count,20);
        $this->assign('page',$page->show());
        $this->assign('list',$list);
        $this->display('adminGoodsList');
    }
    public function goodsDetail(){
            $id=I("get.sitem_id");
            if(empty($id)){
                //$this->ajaxReturn("111","ID为空！",-1);
                exit;
            }
            $item=$this->home->getDetail($id,'sitem_id','supplier_item');
            $supplier_user=$this->home->getDetail($item['supplier_id'],'supplier_id','supplier_user');
            $shopList=$this->home->getAllList("sysshop_shop","shop_id,shop_name");
            $sku=$this->home->getList($id,'sitem_id','supplier_item_sku');
            $shop=$this->home->getDetail($item['shop_id'],'shop_id','sysshop_shop');
            $cat=$this->home->getDetail($item['cat_id'],'cat_id','syscategory_cat');
            $brand=$this->home->getDetail($item['brand_id'],'brand_id','syscategory_brand');
            $users=$this->home->getAllSupplierList();
            $this->assign('shop',$shop);
            $this->assign('shopList',$shopList);
            $this->assign('cat',$cat);
            $this->assign('brand',$brand);
            $this->assign('supplier_user',$supplier_user);
            $this->assign('item',$item);
            $this->assign('sku',$sku);
            $this->assign("users",$users);
            $this->display("goodsDetail");

    }
    public function add_goods(){
        $this->display('adminAddGoods');
    }
    public function orderList(){
        $page=empty($_GET['p'])?1:$_GET['p'];
        $like=empty($_GET['like'])?'':$_GET['like'];
        $id=empty($_GET['id'])?'':$_GET['id'];
        $cat_id_1=empty($_GET['cat_id_1'])?'':$_GET['cat_id_1'];
        $where=array();
        if(!empty($like)){
            $where['title']=array('like',"%$like%");
            $this->assign("like",$like);
        }
            $where["status"]='1';
        $where['is_self']='0';
        if(!empty($id)){
            $where['id']=$id;
        }
        if(!empty($cat_id_1)){
            $where['cat_id_1']=$cat_id_1;
            $this->assign("cat_id_1",$cat_id_1);
        }
        $list=$this->home->getOrdersById($this->adminId,$page.',20',$where);
        $warehouses=$this->home->getWarehouseName($list);
        $this->assign('warehouses',$warehouses);
        $count=$list['count'];
        if(!empty($count)){
            unset($list['count']);
        }
        if(!empty($list)){
            $list=$this->home->getSkuCountByOrders($list,"",$this->adminId);
        }
        $page=new \Think\Page($count,20);
        $this->assign('page',$page->show());

        $this->assign('list',$list);
        $this->display('adminOrderList');
    }

    public function editOrder(){
        $id=(int)I("get.order_id");

        $list=$this->home->getSkuCountByOrders(null,$id,$this->adminId);
        $list=reset($list);
        $this->assign('skus',$list['skus']);
        $this->assign('prices',$list['prices']);
        $this->display("editOrder");
    }
    //编辑商品详情
    public function editOrderGoods(){
        $data=I("post.");
        if($this->home->editOrderGoods($data)){
            $data['status']=1;
            $data['msg']="成功";
            $this->ajaxReturn($data);
        }else{
            $data['status']=-1;
            $data['msg']="失败";
            $this->ajaxReturn($data);
        }
    }
    //获取订单详情
    public function getOrderDetail(){
        $id=I("get.order_id");
        $list=$this->home->getSkuCountByOrders(null,$id);
    }
    public function checkOrder(){
        $order_id=I('post.order_id');
        $status=I("post.status");
        $remarks=I("post.remarks");
        if($this->home->checkOrder($order_id,$status,$this->adminId,$remarks)){
            echo '1';
        }else{
            echo "0";
        }
    }
    //修改密码界面
    public function editPasswdView(){
        $this->display("adminEditPasswd");
    }
    //修改密码接口
    public function editPasswd(){
        $old_passwd=I("post.oldPasswd");
        $new_passwd=I("post.newPasswd");
        $uid=$this->adminId;

        if(!$this->home->checkPasswd($uid,$old_passwd)){
            $this->error("旧密码输入不正确！");
        }
        if($this->home->editPasswd($uid,$new_passwd)){
            $this->success("修改成功");
        }else{
            $this->error("I'm sorry 修改失败了");
        }
    }
    //等待发货
    public function waitSend(){
        $page=empty($_GET['p'])?1:$_GET['p'];
        $like=empty($_GET['like'])?'':$_GET['like'];
        $id=empty($_GET['id'])?'':$_GET['id'];
        $cat_id_1=empty($_GET['cat_id_1'])?'':$_GET['cat_id_1'];
        $where=array();
        if(!empty($like)){
            $where['title']=array('like',"%$like%");
            $this->assign("like",$like);
        }
        $where["status"]='4';
        $where['is_self']='0';
        if(!empty($id)){
            $where['id']=$id;
        }
        if(!empty($cat_id_1)){
            $where['cat_id_1']=$cat_id_1;
            $this->assign("cat_id_1",$cat_id_1);
        }
        $list=$this->home->getOrdersById($this->adminId,$page.',20',$where);
        $warehouses=$this->home->getWarehouseName($list);
        $this->assign('warehouses',$warehouses);
        $count=$list['count'];
        if(!empty($count)){
            unset($list['count']);
        }
        if(!empty($list)){
            $list=$this->home->getSkuCountByOrders($list,"",$this->adminId);
        }
        $page=new \Think\Page($count,20);
        $this->assign('page',$page->show());
        $this->assign('list',$list);
        $this->display("adminWaitSendList");
    }
    //订单详情
    public function orderDetail(){
        $id=I("get.order_id");
        $list=$this->home->getSkuCountByOrders(null,$id,$this->adminId);
        $list=reset($list);
        $this->assign('skus',$list['skus']);
        $this->assign('prices',$list['prices']);
        $this->display("orderDetail");
    }
    //已发货列表
    public function sended(){
        $page=empty($_GET['p'])?1:$_GET['p'];
        $like=empty($_GET['like'])?'':$_GET['like'];
        $id=empty($_GET['id'])?'':$_GET['id'];
        $cat_id_1=empty($_GET['cat_id_1'])?'':$_GET['cat_id_1'];
        $where=array();
        if(!empty($like)){
            $where['title']=array('like',"%$like%");
            $this->assign("like",$like);
        }
        $where["status"]='5';
        $where['is_self']='0';
        if(!empty($id)){
            $where['id']=$id;
        }
        if(!empty($cat_id_1)){
            $where['cat_id_1']=$cat_id_1;
            $this->assign("cat_id_1",$cat_id_1);
        }
        $list=$this->home->getOrdersById($this->adminId,$page.',20',$where);
        $warehouses=$this->home->getWarehouseName($list);

        $this->assign('warehouses',$warehouses);
        $count=$list['count'];
        if(!empty($count)){
            unset($list['count']);
        }
        if(!empty($list)){
            $list=$this->home->getSkuCountByOrders($list,"",$this->adminId);
        }
        $page=new \Think\Page($count,20);
        $this->assign('page',$page->show());
        $this->assign('list',$list);
        $this->display("adminSendedList");
    }
    public function cancelOrder(){
        $oid=I("post.oid");
        if($this->home->cancel($oid)){
            $data['status']=1;
            $data['msg']="取消成功";
            $this->ajaxReturn($data);
        }
        $res=$this->home->setSended($oid,"4");
        if($res){
            $data['status']=1;
            $data['msg']="成功";
            $this->ajaxReturn($data);
        }
        else{
            $data['status']=-3;
            $data['msg']="设置发货状态错误";
            $this->ajaxReturn($data);
        }

    }
    //同步商品到顺风
    public function syncGoods(){
        $items_id=I('goods_ids');
        $where="&& sku_id in ( ".$items_id.")";
        $skus=$this->home->getAllSkus("sku_id as skuNo,title as itemName,spec_info as standardDescription,barcode as barcode1",$where);
        $this->home->pushGoods($skus);
    }
    //获取分类
    public function getCat(){
        $pid=I("get.cat_id");
        $where=array(
            "parent_id"=>$pid
        );
        $list=$this->home->getAllList("syscategory_cat","cat_id,cat_name,parent_id",$where);
        $this->ajaxReturn($list);
    }
    //发货
    public function sendOrder(){
        $order_id=I("post.order_id",0);
        $remarks=I('post.remarks');
        $data=array();
        if($order_id<=0){
            $data['status']=-2;
            $data['msg']="订单号不能为空";
            $this->ajaxReturn($data);
        }
        $res="";
        if($this->home->orderIsSf($order_id)){
            $skus=$this->home->getSkus($order_id,"ssku_id as skuNo,number as qty, $order_id as lot",false);
            $res=$this->home->purchase($skus,$order_id,$remarks);
            if(!$res){
                $data['status']=-2;
                $data['msg']="推送失败";
                $this->ajaxReturn($data);
            }
        }

        $res=$this->home->setSended($order_id,"5");
        if($res){
            $data['status']=1;
            $data['msg']="成功";
            $this->ajaxReturn($data);
        }
        else{
            $data['status']=-3;
            $data['msg']="设置发货状态错误";
            $this->ajaxReturn($data);
        }
    }
    //临时入库接口

    //已入库列表
    public function storage(){
        $page=empty($_GET['p'])?1:$_GET['p'];
        $like=empty($_GET['like'])?'':$_GET['like'];
        $id=empty($_GET['id'])?'':$_GET['id'];
        $cat_id_1=empty($_GET['cat_id_1'])?'':$_GET['cat_id_1'];
        $where=array();
        if(!empty($like)){
            $where['title']=array('like',"%$like%");
            $this->assign("like",$like);
        }
        $where["status"]='6';
        if(!empty($id)){
            $where['id']=$id;
        }
        if(!empty($cat_id_1)){
            $where['cat_id_1']=$cat_id_1;
            $this->assign("cat_id_1",$cat_id_1);
        }
        $list=$this->home->getOrdersById($this->adminId,$page.',20',$where);
        $warehouses=$this->home->getWarehouseName($list);

        $this->assign('warehouses',$warehouses);
        $count=$list['count'];
        if(!empty($count)){
            unset($list['count']);
        }
        if(!empty($list)){
            $list=$this->home->getSkuCountByOrders($list,"",$this->adminId);
        }
        $page=new \Think\Page($count,20);
        $this->assign('page',$page->show());
        $this->assign('list',$list);
        $this->display("adminStorageList");
    }


}
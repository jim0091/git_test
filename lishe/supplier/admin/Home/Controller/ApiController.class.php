<?php
namespace Home\Controller;
use Think\Controller;

class ApiController extends Controller {
    public function __construct(){
        parent::__construct();
        $this->supplier_user=M("supplier_user");
        $this->supplier_item=M("supplier_item");
        $this->supplier_history=M("supplier_callback_history");
        $this->supplier_saleorder_history=M("supplier_saleorder_history");
        $this->home=D("Home");
    }

    //顺丰推送－－入库单明细
    public function storageCallBack(){
        @A('Sms')->send("17727427180","已入库！");
        $put=I("put.");
        $json=I("post.storageCallBack");
        $json=$_POST['storageCallBack'];
        //$json='{"companyCode":"SZLS","purchaseOrders":[{"closeDate":"2016-10-11 13:46:10","erpOrder":"14","erpOrderType":"采购订单","items":[{"actualQty":"2.00","inventoryStatus":"20","planQty":"100.00","receiptTime":"2016-10-11 13:46:10","skuNo":"32273"},{"actualQty":"98.00","inventoryStatus":"10","planQty":"100.00","receiptTime":"2016-10-11 13:46:10","skuNo":"302020"},{"actualQty":"1.00","inventoryStatus":"20","planQty":"50.00","receiptTime":"2016-10-11 13:46:10","skuNo":"32273"},{"actualQty":"49.00","inventoryStatus":"10","planQty":"50.00","receiptTime":"2016-10-11 13:46:10","skuNo":"1002180"},{"actualQty":"100.00","inventoryStatus":"10","planQty":"200.00","receiptTime":"2016-10-11 13:46:10","skuNo":"32273"},{"actualQty":"98.00","inventoryStatus":"10","planQty":"200.00","receiptTime":"2016-10-11 13:46:10","skuNo":"996808"},{"actualQty":"2.00","inventoryStatus":"20","planQty":"200.00","receiptTime":"2016-10-11 13:46:10","skuNo":"996808"}],"receiptId":"SZLS16101101548434","status":"3900","warehouseCode":"571DCF"}]}';
        $d=array(
            'json'=>$json
        );
        $this->supplier_history->add($d);

        $arr=json_decode($json);
        if(empty($arr)){
            echo '{"result":100, "errcode":1002, "msg":"json解析失败"}';
            exit;
        }
        //开始设置订单入库数量
        $jsons=json_decode($json,true);
        $jsons=$jsons['purchaseOrders'];
        foreach($jsons as $key => $val){
            $storages=array();
            $damages=array();
            foreach($val['items'] as $k => $v){
                if($v['inventoryStatus']==10){
                    $storages[]=$v;
                }else{
                    $damages[]=$v;
                }
            }
            $this->home->editOrderGoodsStorage($storages,$val['erpOrder']);
            if(!empty($damages)){
                $this->home->editOrderGoodsStorage($damages,$val['erpOrder'],"damage_number");
            }
        }
        $re=$this->home->setOrderStatus($arr->purchaseOrders,"6");
        if(!$re){
            echo '{"result":100, "errcode":1002, "msg":"修改状态失败，订单号不存在，或者已经修改过了"}';
            exit;
        }
        $res=$this->home->saveCallBack($arr->purchaseOrders);
        if(!$res){
            echo '{"result":100, "errcode":1002, "msg":"插入记录失败"}';
            $re=$this->home->setOrderStatus($arr->purchaseOrders,"5");
            exit;
        }

        echo '{"result":100, "errcode":0, "msg":"成功"} ';

    }

    //顺丰推送－－出库单明细
    public function saleCallBack(){
        //$json=$_POST['saleCallBack'];
        $json='{"saleOrders":[{"actualShipDateTime":"2017-02-17 15:01:23","carrier":"顺丰速运","carrierProduct":"云仓专配隔日","containers":[{"containerItems":[{"actualQty":"1","inventoryStatus":"10","lot":"","skuNo":"13890","weight":"1.5","weightUm":"KG"},{"actualQty":"1","inventoryStatus":"10","lot":"","skuNo":"19441","weight":"1.5","weightUm":"KG"}],"containerNo":"783504283217","containerType":"0","weight":"1.5","weightUm":"KG"}],"dataStatus":"2900","erpOrder":"17021712042554001","isSplit":"N","items":[{"actualQty":"1","qtyUm":"EA","skuNo":"13890"},{"actualQty":"1","qtyUm":"EA","skuNo":"19441"}],"shipmentId":"OLSKJ170217120074442758","userDef2":"DEFAULT","warehouseCode":"755DCD","wayBillNo":"783504283217"}]}';
        $d=array(
            'json'=>$json
        );
       // $this->supplier_saleorder_history->add($d);
        $arr=json_decode($json,true);
        if(empty($arr)){
            echo '{"result":100, "errcode":1002, "msg":"json解析失败"}';
            exit;
        }
        $order=$arr['saleOrders'];
        $updates=array();
        foreach($order as $key => $val){
            $updates[$val['erpOrder']]=$val['items'];
            unset($data);
            $data[$val['erpOrder']]=array(
                'actualShipDateTime'=>$val['actualShipDateTime'],
                'carrierProduct'=>$val['carrierProduct'],
                'dataStatus'=>$val['dataStatus'],
                'erpOrder'=>$val['erpOrder'],
                'shipmentId'=>$val['shipmentId'],
                'wayBillNo'=>$val['wayBillNo']
            );
            $insert=$data;
            $insert['json']=$json;
            $this->home->saleCallBack($insert);
            $res=$this->home->setSaleOrderStatus($data,$val['dataStatus'],$val['items']);
            if(!$res){
                echo '{"result":100, "errcode":1002, "msg":"接收失败！"}';
                exit;
            }
        }
        echo '{"result":100, "errcode":0, "msg":"成功"} ';
    }
    //接口返回结果
    public function retSuccess($data=array(),$msg='操作成功'){
        $ret=array(
            'result'=>100,
            'errcode'=>0,
            'msg'=>$msg,
            'data'=>$data
        );
        echo json_encode($ret);
        exit;
    }
    public function sendOrderStatus(){
        $oid=I('get.oid',0);
        if($oid<=0){
            echo '{"result":100, "errcode":1002, "data":"订单号不能为空！"}';
            exit;
        }
        $data=$this->home->orderSendStatus($oid);
        //echo '{"result":100, "errcode":0, "msg":"成功"} ';
        $this->retSuccess($data);
        exit;
    }

}
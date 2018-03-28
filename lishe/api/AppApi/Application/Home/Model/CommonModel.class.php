<?php
/**
  +----------------------------------------------------------------------------------------
 *  CommonModel
  +----------------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: CommonModel.class.php v001 2016-02-04
 * @description 公共类
  +-----------------------------------------------------------------------------------------
 */
	namespace Home\Model;
	use Think\Model;
	
class CommonModel extends Model{
	    public function pageStart($page,$pageSize){
    		return ($page-1)*$pageSize;
    	}


        public function getDetail($id,$field_name,$table_name){
            $where=array(
                $field_name=>$id
            );
            $model=M($table_name);
            return $model->where($where)->find();
        }
        public function getList($id,$field_name,$table_name){
            $model=M($table_name);
            $where=array(
                $field_name=>$id
            );
            return $model->where($where)->select();
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
        
    }
?>
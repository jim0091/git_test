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
        //文件日志
        public function makeLog($type='',$data=''){
            if(!empty($type)){
                @file_put_contents(C('DIR_LOG').$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
            }
        }
        
    }
?>
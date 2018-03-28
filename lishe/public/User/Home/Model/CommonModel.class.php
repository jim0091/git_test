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
        
    }
?>
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

class CommonModel extends Model {
	public function __construct() {
		parent::__construct ();
	}
	public function pageStart($page, $pageSize) {
		return ($page - 1) * $pageSize;
	}
	/**
	 * 管理员操作记录日志表
	 *
	 * *
	 */
	public function markTradeLog($data) {
		$ip = get_client_ip ();
		$data ['created_time'] = time ();
		$data ['ip'] = $ip;
		return M ( 'system_admin_trade_log' )->data ( $data )->add ();
	}
	public function getDetail($id, $field_name, $table_name) {
		$where = array (
				$field_name => $id 
		);
		$model = M ( $table_name );
		return $model->where ( $where )->find ();
	}
	public function getList($id, $field_name, $table_name) {
		$model = M ( $table_name );
		$where = array (
				$field_name => $id 
		);
		return $model->where ( $where )->select ();
	}
}
?>
<?php

namespace Home\Model;
use Think\Model;

/**
 * 数据库微送礼相关模型
 * @author Gaolong
 */
class GiftTradeModel extends Model{
	
	public function __construct(){
		$this->company = M('company_config');
	}
	
}

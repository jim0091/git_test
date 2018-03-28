<?php

/**
 * app推送消息 - 数据对象模型.
 *
 * @author zivss <guolee226@gmail.com>
 *
 * @version TS3.0
 */
class MessageSystemModel extends Model
{
    protected $tableName = 'message_system';
    protected $fields = array('id', 'content', 'ctime', 'row_id', 'type', 'to_uids', 'remark');

    protected $type = array(
        'system' => '系统推送',
        'event' => '活动推送',
    );

     /**
     * 获取用户列表，后台使用;
     *
     * @param int   $limit
     *                     结果集数目，默认为20
     *
     * @return array 推送消息
     */

    public function getMessageSysForAdmin($limit = 20)
    {
        $listData = $this->order(' id DESC')->findPage($limit);
        foreach ($listData['data'] as $key => $value) {
            $listData['data'][$key]['type'] = $this->type[$value['type']];
            $listData['data'][$key]['ctime'] = empty($value['ctime']) ?  '默认推送' :  date("Y-m-d H:i:s",$value['ctime']);
        }
        return $listData;
    }   

    public function getType()
    {
        return $this->type;
    }
}

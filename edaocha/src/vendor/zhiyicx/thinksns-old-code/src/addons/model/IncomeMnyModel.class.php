<?php


/**
 * 流水记录模型 - 数据对象模型.
 *
 * @author jason <yangjs17@yeah.net>
 *
 * @version TS3.0
 */
class IncomeMnyModel extends Model
{
    protected $tableName = 'income_mny';
    protected $fields = array('id','luck_draw_id', 'out_trade_no', 'money', 'type', 'take_state','uid','create_time','edit_time','edit_uid','table', 'is_del', '_pk' => 'id');

    public $templateFile = '';            // 模板文件


    /**
     * 获取流水记录列表.
     *
     * @param array  $map   查询条件
     * @param int    $limit 结果集数目，默认为10
     * @param string $order 排序字段
     *
     * @return array
     */
    public function getList($map, $limit = 10, $order = null, $max = null)
    {
        $order = !empty($order) ? $order : 'id DESC';
        $incomelist = $this->where($map)->order($order);
        if ($max > 0) {
            $incomelist = $this->findPage($limit, $max);
        } else {
            $incomelist = $this->findPage($limit);
        }

        return $incomelist;
    }


    /**
     * @param $map
     * @return mixed
     *
     * 导出全部流水记录
     */
    public function reportAll($map){
        $order = !empty($order) ? $order : 'id DESC';
        $incomelist = $this->where($map)->order($order)->select();
        return $incomelist;
    }


    /**
     * @param $uid
     * @return bool
     *
     * 检查用户是否参与抽奖
     */
    public function checkIsLuck($uid)
    {
        $count = model('Cache')->get('LuckDraw_' . $uid);
        if ($count) {
            //已经参与了，返回FALSE
            return false;
        } else {
            $map['uid'] = $uid;
            $map['type'] = 0;
            $map['table'] = 'luck_draw';
            $count = $this->where($map)->count();
            if ($count) {
                model('Cache')->set('LuckDraw_' . $uid, $count);
                //已经参与了，返回FALSE
                return false;
            } else {
                //没有参与返回TRUE
                return true;
            }
        }
    }


    /**
     * @param $id
     * @return array
     *
     * 确认提现
     */
    public function doToCash($id){
        if(is_array($id)){
            $ids = implode(',',$id);
        }else{
            $ids = $id;
        }
        $map = array();
        $map['take_state'] = 2;
        $map['edit_time'] = time();
        $map['edit_uid'] = $GLOBALS['ts']['mid'];
        $res = $this->where('id in('.$ids.')')->save($map);
        if($res===false){
            $return = array('status' => '0');
        }else{
            $table = '`'.C('DB_PREFIX').'income_mny` as i';
            $list = D()->table($table)->field('i.uid,i.money,u.uname,u.phone')
                ->where('i.id in('.$ids.')')
                ->join('cy_user u ON u.uid = i.uid')->select();
            foreach($list as $v){
                //发送短信消息和APP消息推送
                $data = array(
                    'content' => '【奥豆app】叮！'.$v['uname'].'你在奥豆抽到的'.$v['money'].'元现金已成功转账到你的支付宝～关注奥豆app，红包福利享不停！',
                    'ctime' => time(),
                    'row_id' => 0,
                    'type' => 'system',
                    'to_uids' => $v['uid'],
                    'remark'=> '',
                );
                model('MessageSystem')->add($data);

                if($v['phone']){
                    $message = '叮！'.$v['uname'].'：你的福利我们已经发放，快去看看吧！';
                    model('Sms')->sendMessage($v['phone'], $message);
                }
            }

            $return = array('status' => 1);
        }
        return $return;
    }


}

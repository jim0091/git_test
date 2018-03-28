<?php


/**
 * 抽奖模型 - 数据对象模型.
 *
 * @author jason <yangjs17@yeah.net>
 *
 * @version TS3.0
 */
class LuckDrawModel extends Model
{
    protected $tableName = 'luck_draw';
    protected $fields = array('id','title', 'start_time', 'end_time', 'planNum', 'hasNum','planMoney','useMoney','status','create_time','create_uid', 'is_del', '_pk' => 'id');

    public $templateFile = '';            // 模板文件


    /**
     * 获取抽奖列表列表.
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
        $lucklist = $this->where($map)->order($order);
        if ($max > 0) {
            $lucklist = $this->findPage($limit, $max);
        } else {
            $lucklist = $this->findPage($limit);
        }

        return $lucklist;
    }


    /**
     * @param array $luck
     * @return bool
     *
     * 添加抽奖活动
     */
    public function addLuckDraw(array $luck){
        // # 判断用户名是否被注册
        if ($luck['title'] and !$this->checkTitle($luck['title'])) {
            $this->error = '该活动名称已存在，请使用其他名称';
            return false;
        }

        $luck['start_time'] = strtotime($luck['start_time']);
        $luck['end_time'] = strtotime($luck['end_time']);
        $luck['create_time'] = time();
        $luck['create_uid'] = $GLOBALS['ts']['mid'];

        if (($uid = $this->add($luck))) {
            return true;
        }
        $this->error = L('PUBLIC_ADD_USER_FAIL');

        return false;
    }


    /**
     * @param $title
     * @param null $luckID
     * @return bool
     *
     * 检查抽奖活动标题是否存在
     */
    public function checkTitle($title, $luckID = null)
    {
        $id = $this->where('`is_del` = 0 AND `title` LIKE "'.$title.'"')->field('`id`')->getField('id');
        if ($id == $luckID or !$id) {
            return true;
        }
        return false;
    }






}

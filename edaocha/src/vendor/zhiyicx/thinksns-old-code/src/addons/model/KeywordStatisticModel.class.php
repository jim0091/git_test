<?php


/**
 * Class KeywordStatisticModel
 *
 * 关键词搜索统计模型
 */
class KeywordStatisticModel extends Model
{
    protected $tableName = 'keyword_statistic';
    protected $fields = array('id','keyword', 'type', 'result', 'all_search', 'month_search','week_search','day_search', '_pk' => 'id');

    public $templateFile = '';            // 模板文件


    /**
     * 关键词列表.
     *
     * @param array  $map   查询条件
     * @param int    $limit 结果集数目，默认为10
     * @param string $order 排序字段
     *
     * @return array
     */
    public function getList($map, $limit = 20, $order = null)
    {
        $order = !empty($order) ? $order : 'all_search DESC,id DESC';
        $list = $this->where($map)->order($order)->findPage($limit);
        return $list;
    }


    /**
     * @param $keyword
     * @param $type
     * @param $result
     *
     * 关键词添加修改
     */
    public function setKeywordStatistic($keyword,$type,$result){
        $map = array();
        $map['keyword'] = $keyword;
        $map['type'] = $type;
        $kwInfo = model('KeywordStatistic')->field('id')->where($map)->find();
        if($kwInfo){
            $save = array();
            $save['id'] = $kwInfo['id'];
            $save['all_search'] = array('exp','all_search+1');
            $save['result'] = $result;
            $save['month_search'] = array('exp','month_search+1');
            $save['week_search'] = array('exp','week_search+1');
            $save['day_search'] = array('exp','day_search+1');
            model('KeywordStatistic')->save($save);
        }else{
            $map['result'] = $result;
            $map['all_search'] = array('exp','all_search+1');
            $map['month_search'] = 1;
            $map['week_search'] = 1;
            $map['day_search'] = 1;
            model('KeywordStatistic')->add($map);
        }
    }


    /**
     *
     * 清除当天所有搜索次数
     *
     */
    public function clean_day(){
        $map = array();
        $map['day_search'] = 0;
        $this->where('')->save($map);
    }


    /**
     *
     * 清除本周所有搜索次数
     *
     */
    public function clean_week(){
        $map = array();
        $map['week_search'] = 0;
        $this->where('')->save($map);
    }


    /**
     *
     * 清除本月所有搜索次数
     *
     */
    public function clean_month(){
        $map = array();
        $map['month_search'] = 0;
        $this->where('')->save($map);
    }


}

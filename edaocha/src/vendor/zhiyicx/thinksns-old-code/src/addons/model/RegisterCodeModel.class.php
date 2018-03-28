<?php


/**
 * 邀请码模型 - 数据对象模型.
 *
 * @author jason <yangjs17@yeah.net>
 *
 * @version TS3.0
 */
class RegisterCodeModel extends Model
{
    protected $tableName = 'register_code';
    protected $fields = array('id','code', 'create_time', 'create_uid', 'uid', 'is_audit', 'is_del', '_pk' => 'id');

    public $templateFile = '';            // 模板文件


    /**
     * 获取邀请码列表.
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
        $codelist = $this->where($map)->order($order);
        if ($max > 0) {
            $codelist = $this->findPage($limit, $max);
        } else {
            $codelist = $this->findPage($limit);
        }

        return $codelist;
    }


    /**
     * @param $map
     * @param int $limit
     * @param null $order
     * @param null $max
     * @return mixed
     *
     * 邀请码绑定列表
     */
    public function getBindList($map,$limit = 20, $order = null, $max = null){
        $table = '`'.C('DB_PREFIX').'register_bind` as b';
        $order = !empty($order) ? $order : 'b.id DESC';
        if($limit=='all'){
            $list = D()->table($table)->field('b.id,c.code as code_id,b.uid,b.bind_time')->where($map)->join('cy_register_code as c on c.id=b.code_id')->order($order)->findAll();
        }else{
            if ($max > 0) {
                $list = D()->table($table)->field('b.id,c.code as code_id,b.uid,b.bind_time')->where($map)->join('cy_register_code as c on c.id=b.code_id')->order($order)->findPage($limit, $max);
            } else {
                $list = D()->table($table)->field('b.id,c.code as code_id,b.uid,b.bind_time')->where($map)->join('cy_register_code as c on c.id=b.code_id')->order($order)->findPage($limit);
            }
        }
        return $list;
    }


    /**
     * @param $id
     * @return mixed
     *
     * 根据邀请码ID获取绑定次数
     */
    public function getBindCount($id){
        $table = '`'.C('DB_PREFIX').'register_bind`';

        $count = D()->table($table)->where('code_id='.$id)->count();

        return $count;
    }


    /**
     * @param $map
     * @return mixed
     *
     * 绑定关系删除
     */
    public function delBindCode($map){
        $table = '`'.C('DB_PREFIX').'register_bind`';
        $res = D()->table($table)->where($map)->delete();
        return $res;
    }


    /**
     * @param $map
     * @return mixed
     *
     * 邀请码绑定添加
     */
    public function codeBind($map){
        $table = '`'.C('DB_PREFIX').'register_bind`';
        $res = D()->table($table)->add($map);
        return $res;
    }
}

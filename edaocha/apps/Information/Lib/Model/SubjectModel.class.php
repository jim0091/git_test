<?php
namespace Apps\Information\Model;
use Apps\Information\Common;
use Model;
use Apps\Information\Model\Cate as CateModel;
/**
 * 资讯-主题模型
 *
 * @package Apps\Information\Model\Subject
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Subject extends Model
{
    /**
     * 主键设置
     *
     * @var string
     **/
    protected $pk = 'id';
    /**
     * 表名
     *
     * @var string
     **/
    protected $tableName = 'information_list';
    /**
     * 数据表字段
     *
     * @var array
     **/
    protected $fields = array('id', 'cid', 'subject', 'abstract', 'content', 'author', 'ctime', 'rtime', 'hits', 'isPre', 'isDel', 'isTop', 'logo', 'tag', 'created_name', 'comment', 'zreditor', 'copyfrom', 'isYuan', 'isYuan_2', 'author_mane', 'modified_userid', 'modified_username', 'created_ip', 'status', 'isLink', 'linkurl', 'notice');
    /**
     * 储存单例对象
     *
     * @var object self
     **/
    protected static $_instance;
    /**
     * 储存数据
     *
     * @var array
     **/
    protected $_data = array();
    /**
     * 清理数据
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function cleanData()
    {
        $this->_data = array();
    }
    /**
     * 按照键名获取数据表设置数据的键值
     *
     * @param  string $name 数据键名
     * @return unknow
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getFieldValue($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        return null;
    }
    /**
     * @param $tag
     * @return $this
     *
     * 设置文章标签
     */
    public function setTag($tag)
    {
        $this->_data['tag'] = t($tag);
        return $this;
    }
    /**
     * 设置ID
     *
     * @param  int    $id 字段ID的值
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setId($id)
    {
        $this->_data['id'] = intval($id);
        return $this;
    }
    /**
     * 设置分类ID
     *
     * @param  int    $cid 分类ID
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setCate($cid)
    {
        $this->_data['cid'] = intval($cid);
        return $this;
    }
    /**
     * 设置是否外部链接
     *
     * @param  int    $linkurl 1或0
     * @param  string    $notice url
     * @return object self
     * @author Beelee Zhu<383621328@qq.com>
     **/
    public function setLinkurl($isLink,$linkurl = '')
    {
        $this->_data['isLink'] = $isLink ? 1 : 0;
        $this->_data['linkurl'] = addslashes($linkurl);
        return $this;
    }
    /**
     * 设置主题
     *
     * @param  string $subject 标题
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setSubject($subject)
    {
        $this->_data['subject'] = t($subject);
        return $this;
    }
    /**
     * 设置摘要
     *
     * @param  string $abstract 摘要
     * @return onject self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setAbstract($abstract)
    {
        $this->_data['abstract'] = t($abstract);
        return $this;
    }
    /**
     * 设置值t过滤
     *
     * @param  array  ('field' => value)
     * @return onject self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setField($param = array())
    {
        foreach( $param as $k => $v ){
            $this->_data[$k] = t($v);
        }
        return $this;
    }
    /**
     * 设置值单选过滤
     *
     * @param  array  ('field' => value)
     * @return onject self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setRadio($param = array())
    {
        foreach( $param as $k => $v ){
            $this->_data[$k] = $v ? 1 : 0;
        }
        return $this;
    }
    /**
     * 设置值状态
     *
     * @param  int  状态数
     * @return onject self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setStatus($status)
    {
        $this->_data['status'] = intval($status);
        return $this;
    }
    /**
     * 设置内容
     *
     * @param  string $content 内容
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setContent($content)
    {
        $this->_data['content'] = addslashes(h($content));
        return $this;
    }
    /**
     * 设置作者用户UID
     *
     * @param  int    $uid 用户UID
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setAuthor($uid,$add = true)
    {
        if($add){
            $this->_data['author'] = intval($uid);
            $this->_data['created_ip'] = get_client_ip();
        }
        $this->_data['modified_userid'] = intval($uid);
        return $this;
    }
    /**
     * 设置创建时间
     *
     * @param  float  $time 时间
     * @return object self
     * @author Seven Du <lovevipdsw@vip.q.com>
     **/
    public function setCTime($time = null)
    {
        if (!is_numeric($time) && $time) {
            $time = strtotime($time);
        } else {
            $time = time();
        }
        $this->_data['ctime'] = $time;
        return $this;
    }
    /**
     * 设置更新时间
     *
     * @param  float  $time 时间
     * @return object Self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setRTime($time = null)
    {
        if (!is_numeric($time) && $time) {
            $time = strtotime($time);
        } else {
            $time = time();
        }
        $this->_data['rtime'] = $time;
        return $this;
    }
    /**
     * 设置阅读数
     *
     * @param  int    $hits read nunber
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setHits($hits)
    {
        $this->_data['hits'] = intval($hits);
        return $this;
    }
    /**
     * set table isPre value
     *
     * @return object self
     * @author Seven Du <lovevipdsw"vip.qq.com>
     **/
    public function setIsPre($isPre)
    {
        $this->_data['isPre'] = $isPre ? 1 : 0;
        return $this;
    }
    /**
     * 设置是否是删除的
     *
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setIsDel($isDel)
    {
        $this->_data['isDel'] = $isDel ? 1 : 0;
        return $this;
    }
    /**
     * 设置是否是推荐
     *
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setIsTop($isTop)
    {
        $this->_data['isTop'] = $isTop ? 1 : 0;
        return $this;
    }
    /**
     * 设置封面图
     *
     * @return object self
     **/
    public function setLogo($logo)
    {
        $this->_data['logo'] = intval($logo);
        return $this;
    }
    /**
     * 获取表临时数据
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getData()
    {
        return $this->_data;
    }
    /**
     * 设置错误信息
     *
     * @return falsh
     * @author
     **/
    public function setError($message)
    {
        $this->error = $message;
        return false;
    }
    /**
     * 添加主题
     *
     * @return bool
     * @author Seven Du <>
     **/
    public function add()
    {
        /* 判断是否存在标题 */
        if (!$this->getFieldValue('subject')) {
            return $this->setError('标题不能为空');
            /* 判断分类是否存在 */
        } elseif (!CateModel::getInstance()->setId($this->getFieldValue('cid'))->hasById()) {
            return $this->setError('请选择正确的分类');
            /* 判断摘要是否存在 */
        } elseif (!$this->getFieldValue('abstract')) {
            return $this->setError('摘要不能为空');
            /* # 判断摘要是否超出长度限制 */
        } elseif (Common::strlen($this->getFieldValue('abstract')) > 200) {
            return $this->setError('摘要不得超出200字');
            /* 判断是否存在内容 */
        } elseif (!$this->getFieldValue('content')) {
            return $this->setError('主题内容不得为空');
            /* 判断是否超出长度限制 */
        } /*elseif (Common::strlen($this->getFieldValue('content')) > 10000) {
            return $this->setError('主题内容不得超出10000字！');
        }*/
        $this->setId(null); // 删除里面的id参数，防止sql出错
        return parent::add($this->getData());
    }
    /**
     * 判断是否存在主题
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function has()
    {
        return $this->where(array('id' => array('eq', $this->getFieldValue('id'))))->field('id')->count() > 0;
    }
    /**
     * 检查是否是预发布
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function hasIsPre()
    {
        return $this->where(array('id' => array('eq', $this->getFieldValue('id'))))->field('isPre')->getField('isPre') > 0;
    }
    /**
     * 检查是否是通过审核
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getStatus()
    {
        $map = array();
        $map['id'] = $this->getFieldValue('id');
        $info = $this->field('status')->where($map)->find();
        return $info['status'];
    }
    /**
     * 更新主题数据
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function update()
    {
        /* # 判断是否不存在主题 */
        if (!$this->has()) {
            return $this->setError('更新的主题信息不存在');
            /* 判断是否存在标题 */
        } elseif ($this->getFieldValue('subject') !== null && !$this->getFieldValue('subject')) {
            return $this->setError('标题不能为空');
            /* 判断分类是否存在 */
        } elseif ($this->getFieldValue('cid') !== null && !CateModel::getInstance()->setId($this->getFieldValue('cid'))->hasById()) {
            return $this->setError('请选择正确的分类');
            /* 判断摘要是否存在 */
        } elseif ($this->getFieldValue('abstract') !== null && !$this->getFieldValue('abstract')) {
            return $this->setError('摘要不能为空');
            /* # 判断摘要是否超出长度限制 */
        } elseif ($this->getFieldValue('abstract') !== null && Common::strlen($this->getFieldValue('abstract')) > 200) {
            return $this->setError('摘要不得超出200字');
            /* 判断是否存在内容 */
        } elseif ($this->getFieldValue('content') !== null && !$this->getFieldValue('content')) {
            return $this->setError('主题内容不得为空');
            /* 判断是否超出长度限制 */
        }
        $id = $this->getFieldValue('id');
        return $this->where(array('id' => array('eq', $id)))->save($this->getData());
    }
    /**
     * 主题后台专用
     *
     * @param  int    $num     分页参数
     * @param  int    $id      主题ID
     * @param  int    $cid     分类ID
     * @param  string $subject 主题名称
     * @param  int    $isPre   是否是预发布
     * @param  int    $isTop   是否是推荐
     * @param  array  $uids    用户列表
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getAdminData($num = 20, $id = 0, $cid = 0 , $tag = '', $abstract = '', $created_name = '', $zreditor = '', $copyfrom = '', $author = 0, $ip = '', $subject = '', $stime = '', $etime = '', $status = 1,$ctime='')
    {
        $sql = '`status` = '.$status;
        $where = array();
        $id && array_push($where, '`id` = '.$id);
        $cid && array_push($where, '`cid` = '.$cid);
        $tag && array_push($where, '`tag` LIKE \'%'.$tag.'%\'');
        $abstract && array_push($where, '`abstract` LIKE \'%'.$abstract.'%\'');
        $created_name && array_push($where, '`created_name` LIKE \'%'.$created_name.'%\'');
        $zreditor && array_push($where, '`zreditor` LIKE \'%'.$zreditor.'%\'');
        $copyfrom && array_push($where, '`copyfrom` LIKE \'%'.$copyfrom.'%\'');
        $author && array_push($where, '`author` = '.$author);
        $ip && array_push($where, '`ip` LIKE \'%'.$ip.'%\'');
        $subject && array_push($where, '`subject` LIKE \'%'.$subject.'%\'');
        $stime && array_push($where, '`ctime` > '.strtotime($stime));
        $etime && array_push($where, '`ctime` < '.strtotime($etime));
        if(!empty($ctime)){
            if(!empty($ctime[0]) && !empty($ctime[1])){
                array_push($where,'`ctime` > '.strtotime($ctime[0]),'`ctime` < '.strtotime($ctime[1]));
            }elseif(!empty($ctime[0])){
                array_push($where,'`ctime` > '.strtotime($ctime[0]) );
            }elseif(!empty($ctime[1])){
                array_push($where,'`ctime`< '.strtotime($ctime[1]) );
            }
        }
        if (is_array($where) && $where) {
            $where = implode(' and ', $where);
            $sql .= ' AND ('.$where.')';
        }
        unset($where);
        $res=$this->where($sql)->order('`id` DESC')->findPage($num);
//        echo $this->getLastSql();
        return $res;
    }
    /**
     * 获取资讯列表
     *
     * @param  int   $pageNum = 20 每页展示的条数
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getList($pageNum = 20,$max_id = 0)
    {
        $where = '`isPre` != 1 AND `isDel` != 1 AND `status` = 1';
        $max_id && $where .= ' AND `id` < ' . $max_id ;
        if ($this->getFieldValue('cid') >= 1) {
            $where .= ' AND `cid` = '.$this->getFieldValue('cid');
        }
        if ($this->getFieldValue('status')) {
            $where .= ' AND `status` = '.$this->getFieldValue('status');
        }
        return $this->where($where)->order('`id` DESC')->findPage($pageNum);
    }
    /**
     * 获取主题信息
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getSubject()
    {
        $data = $this->where('`id` = '.$this->getFieldValue('id'))->find();
        $data['content'] = stripslashes($data['content']);
        return $data;
    }
    /**
     * 批量删除主题
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delete(array $ids)
    {
        $ids = array_map(function ($int) {
            return intval($int);
        }, $ids);
        return  M($this->tableName)->where(array('id' => array('IN', $ids)))->delete();
    }
    public function subjectStatus(array $ids,$status = 1){
        $ids = array_map(function ($int) {
            return intval($int);
        }, $ids);
        return  $this->where(array('id' => array('IN', $ids)))->save(array('status'=>$status));
    }
    /**
     * 获取当前模型单例
     *
     * @return obect self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self('Information\SubjectModel');
        }
        return self::$_instance;
    }
    /**
     * 获取热门推荐
     *
     * @param  int   $num     获取数量,默认9条
     * @param  int   $hotTime 热门事件，单位小时
     * @return Array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getHot($num = 9, $hotTime = 0)
    {
        $num = intval($num);
        $where = '`isPre` != 1 AND `isDel` != 1';
        $hotTime = intval($hotTime);
        if ($hotTime > 0) {
            $hotTime = 60 * 60 * 24 * $hotTime;
            $hotTime = time() - $hotTime;
            $where .= ' AND `ctime` > '.intval($hotTime);
        }
        return $this->where($where)->order('`hits` DESC')->limit($num)->field('`id`, `subject`,`logo`')->select();
    }
    /**
     * @param int $pageNum
     * @param $id
     * @return mixed
     *
     * 获取相关资讯列表
     */
    public function getInformationList($pageNum = 4,$id)
    {
        $where = '`isPre` != 1 AND `isDel` != 1';
        if ($this->getFieldValue('cid') >= 1) {
            $where .= ' AND `cid` = '.$this->getFieldValue('cid');
        }
        if($id){
            $where .= ' AND `id` <>'.$id;
        }
        return $this->where($where)->order('RAND()')->findPage($pageNum);
    }
    /**
     * @param $key
     * @param int $pageNum
     * @return mixed
     *
     * 关键字搜索
     */
    public function searchInformation($key,$pageNum = 8)
    {
        $where = array();
        $where['isPre'] = array('neq','1');
        $where['isDel'] = array('neq','1');
        if ($key) {
            $where['subject'] = array('LIKE', '%'.t($key).'%');
        }
        $data = $this->where($where)->order('`ctime` DESC')->findPage($pageNum);
        return $data;
    }
} // END class Subject extends Model
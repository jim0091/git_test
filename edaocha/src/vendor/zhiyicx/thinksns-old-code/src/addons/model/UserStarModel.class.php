<?php

/**
 * 周星模型 - 数据对象模型.
 *
 * @author zivss <guolee226@gmail.com>
 *
 * @version TS3.0
 */
class UserStarModel extends Model
{
    protected $tableName = 'user_star';
    protected $fields = array('id', 'uid', 'ctime', 'reason', 'display_order','popularity','change');

    /**
     * 添加周星用户.
     *
     * @param int $uid 用户ID
     *
     * @return bool 是否添加成功
     */
    public function addStarUser($uid,$data)
    {
        $data['uid'] = $uid;
        $data['ctime'] = time();
        if($this->add($data)){
            return true;
        }        
        return false;
    }


    public function getUserStarInfo($uid)
    {
        $data = $this->where(array('uid' => $uid))->find();
        return $data;
    }

    public function hasStar($uid)
    {
        if($this->where(array('uid' => $uid))->find()){
            return true;
        }
        return false;
    }

    public function editStarUser($uid,$data)
    {   
        $this->where(array('uid' => $uid))->save($data);
        return true;
    }

     /**
     * 获取用户列表，后台使用;
     *
     * @param int   $limit
     *                     结果集数目，默认为20
     *
     * @return array 用户列表信息
     */

    public function getStarUserList($limit = 20)
    {
        $listData = $this->order(' display_order ASC')->findPage($limit);
        foreach ($listData['data'] as $key => $value) {
            $userinfo = model('User')->getUserInfo($value['uid']);
            $listData['data'][$key]['uname'] = $userinfo['uname'];
            $listData['data'][$key]['ctime'] = date("Y-m-d H:i:s",$value['ctime']);
            $listData['data'][$key]['DOACTION'] = '<a href="'.U('admin/User/editStarUser', array('uid' => $value['uid'])).'">编辑</a>&nbsp;-&nbsp;<a href="javascript:;" onclick="admin.delStarUser('.$value['uid'].')">删除</a>';
            unset($userinfo);
        }
        return $listData;
    }

    public function getStarUserForApi($limit = 10)
    {   
        $listData = $this->order(' display_order ASC')->findPage($limit);
        foreach ($listData['data'] as $key => $value) {
            $userinfo = model('User')->getUserInfo($value['uid']);
            $listData['data'][$key]['uname'] = $userinfo['uname'];
            $listData['data'][$key]['avatar'] = $userinfo['avatar_big'];
            $listData['data'][$key]['remark'] = D('UserRemark')->getRemark($GLOBALS['ts']['mid'], $value['uid']);
            $listData['data'][$key]['is_follow'] = model('Follow')->where(array('uid' => $GLOBALS['ts']['mid'],'fid' => $value['uid']))->count();
            $privacy = model('UserPrivacy')->getPrivacy($GLOBALS['ts']['mid'], $value['uid']);//判断个人空间是否有权限
            $listData['data'][$key]['space_privacy'] = $privacy['space'];
            unset($userinfo);
        }
        return $listData;
    }

    public function delStarUser($ids)
    {
        $uid_array = $this->_parseIds($ids);
        $map['uid'] = array(
                'IN',
                $uid_array,
        );
        $result = $this->where($map)->delete();
        if(!$result){
            $this->error = '删除周星用户失败'; // 彻底删除帐号失败
            return false;
        }else{
            return true;
        }
    }

    private function _parseIds($ids)
    {
        // 转换数字ID和字符串形式ID串
        if (is_numeric($ids)) {
            $ids = array(
                    $ids,
            );
        } elseif (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        // 过滤、去重、去空
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $id_array[] = intval($id);
            }
        }
        $id_array = array_unique(array_filter($id_array));

        if (count($id_array) == 0) {
            $this->error = L('PUBLIC_INSERT_INDEX_ILLEGAL'); // 传入ID参数不合法
            return false;
        } else {
            return $id_array;
        }
    }
}

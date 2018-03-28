<?php
/**
 * 后台，邀请码管理控制器.
 *
 * @author zhl
 *
 * @version TS3.0
 */
// 加载后台控制器
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
tsload(SRC_PATH.'/vendor/PHPExcel.php');
class RegisterCodeAction extends AdministratorAction
{

    private function _initPageTab(){
        $this->pageTab[] = array('title' => '邀请码列表', 'tabHash' => 'index', 'url' => U('admin/RegisterCode/index'));
        $this->pageTab[] = array('title' => '邀请码绑定列表', 'tabHash' => 'getCodeBindList', 'url' => U('admin/RegisterCode/getCodeBindList'));
        $this->pageTab[] = array('title' => '添加邀请码', 'tabHash' => 'addCode', 'url' => U('admin/RegisterCode/addCode'));
        //$this->pageTab[] = array('title' => '统计报表', 'tabHash' => 'report', 'url' => U('admin/RegisterCode/report'));
    }

    /**
     * 邀请码管理 - 邀请码列表.
     */
    public function index()
    {
        $_REQUEST['tabHash'] = 'index';
        // tab选项
        $this->_initPageTab();
        $this->pageKeyList = array('id', 'code', 'uid','bind_count', 'is_audit', 'DOACTION');
        $this->searchKey = array('id', 'code', 'uid');
        $this->pageButton[] = array('title' => '搜索邀请码', 'onclick' => "admin.fold('search_form')");
        //$this->pageButton[] = array('title' => '删除邀请码', 'onclick' => 'admin.addUserGroup()');

        $map = array();
        !empty($_POST['id']) && $map['id'] = intval($_POST['id']);
        !empty($_POST['code']) && $map['code'] = t($_POST['code']);
        !empty($_POST['uid']) && $map['uid'] = intval($_POST['uid']);
        $map['is_del'] = 0;
        $map['is_audit'] = 1;
        $list = model('RegisterCode')->getList($map,20);
        foreach ($list['data'] as &$value) {
            if($value['uid']){
                $value['uid'] = getUserName($value['uid'])."(ID:".$value['uid'].")";
            }else{
                $value['uid'] = '未指定';
            }
            if($value['is_audit']){
                $value['is_audit'] = '已生效';
            }else{
                $value['is_audit'] = '未生效';
            }
            //查询邀请码绑定人数
            $value['bind_count'] = model('RegisterCode')->getBindCount($value['id']);
            $value['DOACTION'] = "<a href='".U('admin/RegisterCode/delCode', array('id' => $value['id']))."'>删除".'</a>';
            //$value['DOACTION'] = "<a href='".U('admin/UserGroup/addUsergroup', array('user_group_id' => $value['user_group_id']))."'>".L('PUBLIC_EDIT').'</a>&nbsp;-&nbsp;';
            //$value['DOACTION'] .= "<a href='".U('admin/Config/permissionset', array('gid' => $value['user_group_id']))."'>".L('PUBLIC_PERMISSION_GROUP_CONFIGURATION').'</a>&nbsp;';
        }

        $this->_listpk = 'id';
        $this->allSelected = true;
        $this->displayList($list);
    }


    /**
     *
     * 邀请码准备添加
     *
     */
    public function addCode(){
        // tab选项
        $this->_initPageTab();
        //查询市场部用户组-排除已有邀请码的
        $map = array();
        $map['user_group_id'] = 10; //市场部门
        $groupList = model('UserGroupLink')->field('uid')->where($map)->select();
        $newGroupList = array();
        foreach($groupList as &$v){
//            $res = $this->getRegisterCode($v['uid']);
//            if(!$res){
//                $newGroupList[] = $v;
//            }
            $newGroupList[] = $v;
        }
        unset($groupList);
        //用户详情查询
        foreach($newGroupList as &$v){
            $v['uname'] = getUserName($v['uid']);
        }
        $this->assign('group',$newGroupList);

        //生成邀请码
        $code = '';
        for($i=1;$i<=2;$i++){
            $code .= chr(rand(65,90));
        }
        $num = rand(1000,9999);
        $this->assign('code',$code.$num);
        //$this->displayConfig();
        $this->display();
    }


    /**
     *
     * 用于AJAX请求邀请码
     *
     */
    public function getRandCode(){
        $code = '';
        for($i=1;$i<=2;$i++){
            $code .= chr(rand(65,90));
        }
        $num = rand(1000,9999);
        $this->ajaxReturn($code.$num);
    }


    /**
     * 添加新用户操作.
     */
    public function doAddCode()
    {
        $code = $_POST['code'];
        $uid = $_POST['uid'];
        if(!$code){
            $this->error('邀请码参数有误');
        }else{
            //判断邀请码是否重复
            $map = array();
            $map['code'] = $code;
            $check_code = model('RegisterCode')->field('code')->where($map)->find();
            if($check_code){
                $this->error('该邀请码已经存在，请重新更换一个');
            }
        }
        if(!$uid){
            $this->error('请选择用户');
        }

        $map = array();
        $map['code'] = $code;
        $map['create_time'] = time();
        $map['create_uid'] = $GLOBALS['ts']['mid'];
        if($uid){
            $map['uid'] = $uid;
        }
        $res = model('RegisterCode')->add($map);
        if($res){
            $this->assign('jumpUrl', U('admin/RegisterCode/index'));
            $this->success(L('PUBLIC_ADD_SUCCESS'));
        }else{
            $this->error('添加失败，请重试');
        }
    }


    /**
     * @param $uid
     * @return bool
     *
     * 判断用户ID是否已存在
     */
    private function getRegisterCode($uid){
        $map = array();
        $map['is_audit'] = 1;
        $map['is_del'] = 0;
        $codeList = model('RegisterCode')->field('uid')->where($map)->select();
        foreach($codeList as $v){
            if($v['uid'] == $uid){
                return true;
            }
        }
        return false;
    }


    /**
     *
     * 删除一条邀请码
     *
     */
    public function delCode(){
        $id = intval($_GET['id']);
        $map = array();
        $map['id'] = $id;
        $map['is_del'] = 1;
        $res = model('RegisterCode')->save($map);
        if($res===false){
            $this->error('删除失败，请重试');
        }else{
            $this->assign('jumpUrl', U('admin/RegisterCode/index'));
            $this->success('删除成功');
        }
    }


    /**
     *
     * 邀请码绑定列表
     *
     */
    public function getCodeBindList(){
        $_REQUEST['tabHash'] = 'getCodeBindList';
        // tab选项
        $this->_initPageTab();
        $this->pageKeyList = array('id', 'code_id', 'uid', 'bind_time', 'DOACTION');
        $this->searchKey = array('id', 'code', 'uid',array('bind_time','bind_time1'));
        $this->pageButton[] = array('title' => '搜索绑定', 'onclick' => "admin.fold('search_form')");
        $this->pageButton[] = array('title' => '导出Excel', 'onclick' => "admin.codeExport()");

        $map = array();
        !empty($_POST['id']) && $map['b.id'] = intval($_POST['id']);
        !empty($_POST['code']) && $map['c.code'] = t($_POST['code']);
        !empty($_POST['uid']) && $map['b.uid'] = intval($_POST['uid']);
        if(!empty($_POST['bind_time'])){
            if(!empty($_POST['bind_time']['0'])&&!empty($_POST['bind_time']['1'])){
                $map['b.bind_time'] = array(
                    'BETWEEN',
                    array(
                        strtotime($_POST['bind_time'][0]),
                        strtotime($_POST['bind_time'][1]),
                    ),
                );
            }elseif (!empty($_POST['bind_time'][0])) {
                // 时间大于条件
                $map['b.bind_time'] = array(
                    'GT',
                    strtotime($_POST['bind_time'][0]),
                );
            } elseif (!empty($_POST['bind_time'][1])) {
                // 时间小于条件
                $map['b.bind_time'] = array(
                    'LT',
                    strtotime($_POST['bind_time'][1]),
                );
            }
        }
        $list = model('RegisterCode')->getBindList($map,20);
        foreach ($list['data'] as &$value) {
            $value['uid'] = getUserName($value['uid'])."(ID:".$value['uid'].")";
            $value['bind_time'] = date('Y-m-d H:i:s',$value['bind_time']);
            $value['DOACTION'] = "<a href='".U('admin/RegisterCode/delBindCode', array('id' => $value['id']))."'>删除".'</a>';
            //$value['DOACTION'] = "<a href='".U('admin/UserGroup/addUsergroup', array('user_group_id' => $value['user_group_id']))."'>".L('PUBLIC_EDIT').'</a>&nbsp;-&nbsp;';
            //$value['DOACTION'] .= "<a href='".U('admin/Config/permissionset', array('gid' => $value['user_group_id']))."'>".L('PUBLIC_PERMISSION_GROUP_CONFIGURATION').'</a>&nbsp;';
        }

        $this->_listpk = 'id';
        $this->allSelected = true;
        $this->displayList($list);
    }


    /**
     *
     * 删除邀请码绑定
     *
     */
    public function delBindCode(){
        $id = intval($_GET['id']);
        $map = array();
        $map['id'] = $id;
        $res = model('RegisterCode')->delBindCode($map);
        if($res==false){
            $this->error('删除失败，请重试');
        }else{
            $this->assign('jumpUrl', U('admin/RegisterCode/getCodeBindList'));
            $this->success('删除成功');
        }
    }


    public function report(){
        // tab选项
        $this->_initPageTab();
        $this->pageKeyList = array('id', 'code', 'uid', 'bindCount');
        $this->pageButton[] = array('title' => '导出Excel', 'onclick' => 'admin.addUserGroup()');

        $map = array();

        $this->displayList();
    }
    /**
     *
     * 导出邀请码绑定
     *
     */
    public function exportCode(){
        $map = array();
        if($_GET['form_id']){
            $map['b.id'] = intval($_GET['form_id']);
        }
        if($_GET['form_code']){
            $map['c.code'] = t($_GET['form_code']);
        }
        if($_GET['form_uid']){
            $map['b.uid'] = intval($_GET['form_uid']);
        }
        if(!empty($_GET['bind_time_0'])&&!empty($_GET['bind_time_1'])){
            $map['b.bind_time'] = array(
                'BETWEEN',
                array(
                    strtotime($_GET['bind_time_0']),
                    strtotime($_GET['bind_time_1']),
                ),
            );
        }elseif (!empty($_GET['bind_time_0'])) {
            // 时间大于条件
            $map['b.bind_time'] = array(
                'GT',
                strtotime($_GET['bind_time_0']),
            );
        } elseif (!empty($_GET['bind_time_1'])) {
            // 时间小于条件
            $map['b.bind_time'] = array(
                'LT',
                strtotime($_GET['bind_time_1']),
            );
        }
//       dump($map);die;
        $list = model('RegisterCode')->getBindList($map,'all');
//       dump($list);die;
        $data =array();
        foreach ($list as &$value){
            //提现账号查询
            $new_val = array();
            $new_val['id'] = $value['id'];
            $new_val['code_id'] = $value['code_id'];
            $new_val['uid'] = getUserName($value['uid'])."(ID:".$value['uid'].")";
            $new_val['bind_time'] = date('Y-m-d H:i:s',$value['bind_time']);
            $data[] = $new_val;
        }
//        dump($data);die;
        //创建对象
        $excel = new PHPExcel();
        $letter = array('A','B','C','D');
        //表头数组
        $tableheader = array('编号','邀请码','绑定人','绑定时间');
        //工作表设置
        $excel->setActiveSheetIndex( 0 );
        $excel->getActiveSheet()->freezePane('A2');
        $excel->getActiveSheet()->getStyle( 'A1:D1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $excel->getActiveSheet()->getStyle( 'A1:D1')->getFill()->getStartColor()->setARGB('#0096e5');
        $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
        $objActSheet = $excel->getActiveSheet ();
        $objActSheet->getColumnDimension('A')->setAutoSize(true);  //设置单元格宽度自适应
        $objActSheet->getColumnDimension('B')->setAutoSize(true);
        //设置列格式为，文本格式
        $excel->getActiveSheet()->setTitle('Simple');
        $excel->getActiveSheet()->getStyle('C')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        // $objActSheet->mergeCells('C:D');  //合并单元格
        //填充表头信息
        for($i = 0;$i < count($tableheader);$i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
        }

        //填充表格信息
        for ($i = 2;$i <= count($data) + 1;$i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key=>$value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
                $j++;
            }
        }
        $check_time = '邀请码绑定导出表.xls';
        //IE浏览器下面中文乱码解决办法
        $userBrowser = $_SERVER['HTTP_USER_AGENT'];
        if ( preg_match( '/MSIE/i', $userBrowser ) ) {
            $check_time = urlencode($check_time);
        }
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header("Content-Disposition:attachment;filename=$check_time");
        header("Content-Transfer-Encoding:binary");

        $write->save('php://output');
    }


}

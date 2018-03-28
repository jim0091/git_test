<?php
/**
 * 后台，抽奖管理控制器.
 *
 * @author zhl
 *
 * @version TS3.0
 */
// 加载后台控制器
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
tsload(SRC_PATH.'/vendor/PHPExcel.php');
class LuckDrawAction extends AdministratorAction
{

    private function _initPageTab(){
        $this->pageTab[] = array('title' => '抽奖管理', 'tabHash' => 'index', 'url' => U('admin/LuckDraw/index'));
        $this->pageTab[] = array('title' => '提现管理', 'tabHash' => 'takeList', 'url' => U('admin/LuckDraw/takeList'));
        $this->pageTab[] = array('title' => '添加抽奖', 'tabHash' => 'add', 'url' => U('admin/LuckDraw/add'));
        //$this->pageTab[] = array('title' => '统计报表', 'tabHash' => 'report', 'url' => U('admin/RegisterCode/report'));
    }

    /**
     * 抽奖管理
     */
    public function index()
    {
        $_REQUEST['tabHash'] = 'index';
        // tab选项
        $this->_initPageTab();
        $this->pageKeyList = array('id', 'title', 'start_time', 'end_time','planNum','hasNum','planMoney','useMoney','status','DOACTION');
        $this->searchKey = array('id', 'title');

        $this->pageButton[] = array('title' => '搜索抽奖', 'onclick' => "admin.fold('search_form')");
        //$this->pageButton[] = array('title' => '删除邀请码', 'onclick' => 'admin.addUserGroup()');

        $map = array();
        !empty($_POST['id']) && $map['id'] = intval($_POST['id']);
        !empty($_POST['title']) && $map['title'] = t($_POST['title']);
        $map['is_del'] = 0;
        $list = model('LuckDraw')->getList($map,20);
        foreach ($list['data'] as &$value) {
            $now = time();
            if($value['end_time']<$now&&$value['status']=='1'){
                $value['status'] = '2';
                $map = array();
                $map['id'] = $value['id'];
                $map['status'] = 2;
                model('LuckDraw')->save($map);
            }
            $value['start_time'] = date('Y-m-d H:i:s',$value['start_time']);
            $value['end_time'] = date('Y-m-d H:i:s',$value['end_time']);

            if($value['status']=='1'){
                $value['status'] = '进行中';
            }elseif($value['status']=='2'){
                $value['status'] = '已结束';
            }else{
                $value['status'] = '未开始';
            }

            $value['DOACTION'] = "<a href='".U('admin/LuckDraw/detail', array('id' => $value['id']))."'>查看详情".'</a>&nbsp;-&nbsp;';
            $value['DOACTION'] .= "<a href='".U('admin/Config/permissionset', array('gid' => $value['user_group_id']))."'>删除".'</a>&nbsp;';
        }

        $this->_listpk = 'id';
        $this->allSelected = true;
        $this->displayList($list);
    }


    /**
     *
     * 抽奖详情
     *
     */
    public function detail(){
        // tab选项
        $this->_initPageTab();
        $this->pageKeyList = array('id', 'uid', 'money','out_trade_no', 'create_time');
        $this->searchKey = array('id', 'uid', 'money');
        $this->pageButton[] = array('title' => '抽奖搜索', 'onclick' => "admin.fold('search_form')");

//        if($_POST['uname']){
//            $map = array();
//            $map['uname'] = $_POST['uname'];
//            $info = model('User')->field('uid')->where($map)->find();
//            $uid = $info['uid'];
//        }
        $map = array();
        $map['luck_draw_id'] = '1';
        $map['type'] = 0;
        !empty($_POST['id']) && $map['id'] = intval($_POST['id']);
        !empty($_POST['money']) && $map['money'] = t($_POST['money']);
        !empty($_POST['uid']) && $map['uid'] = intval($_POST['uid']);
        $list = model('IncomeMny')->getList($map,20);
        foreach ($list['data'] as &$value) {
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $value['uid'] = getUserName($value['uid'])."(ID:".$value['uid'].")";

           // $value['DOACTION'] = "<a href='".U('admin/LuckDraw/detail', array('id' => $value['id']))."'>查看详情".'</a>&nbsp;-&nbsp;';
           // $value['DOACTION'] .= "<a href='".U('admin/Config/permissionset', array('gid' => $value['user_group_id']))."'>删除".'</a>&nbsp;';
        }

        $this->_listpk = 'id';
        $this->allSelected = true;
        $this->displayList($list);
    }


    /**
     *
     *
     * 准备添加抽奖
     *
     */
    public function add(){
        // tab选项
        $this->_initPageTab();
        $this->pageKeyList = array('title', 'start_time', 'end_time','planNum','planMoney','status');

        // 字段选项配置
        $this->opt['status'] = array('0' => '未开始', '1' => '进行中', '2' => '已结束');

        // 表单URL设置
        $this->savePostUrl = U('admin/LuckDraw/doAdd');
        $this->displayConfig();
    }


    /**
     *
     * 添加抽奖
     *
     */
    public function doAdd(){
        $luckDraw = model('LuckDraw');
        $map = $luckDraw->create();
        $result = $luckDraw->addLuckDraw($map);
        if ($result) {
            $this->assign('jumpUrl', U('admin/LuckDraw/index'));
            $this->success(L('PUBLIC_ADD_SUCCESS'));
        } else {
            $this->error($luckDraw->getLastError());
        }
    }


    /**
     *
     *
     * 提现管理
     *
     */
    public function takeList(){
        $_REQUEST['tabHash'] = 'takeList';
        // tab选项
        $this->_initPageTab();
        $this->pageKeyList = array('id', 'uid', 'money', 'create_time','ali_account','take_state','DOACTION');
        $this->searchKey = array('id', 'uid', 'money','type');
        $this->opt['type'] = array('1' => '提现中',  '2' => '已提现');

        $this->pageButton[] = array('title' => '搜索提现', 'onclick' => "admin.fold('search_form')");
        //$this->pageButton[] = array('title' => '导出Excel', 'onclick' => 'admin.addUserGroup()');
        $this->pageButton[] = array('title' => '导出Excel', 'onclick' => "location.href='".U('admin/LuckDraw/report', array('id' => $_POST['id'],'uid'=>$_POST['uid'],'type'=>$_POST['type'],'money'=>$_POST['money'],'p'=>$_GET['p']))."'");
        $this->pageButton[] = array('title' => '确认提现', 'onclick' => "admin.doToCash('','doToCash','".'提现'."','".'申请'."')");

        $map = array();
        !empty($_POST['id']) && $map['id'] = intval($_POST['id']);
        !empty($_POST['uid']) && $map['uid'] = intval($_POST['uid']);
        !empty($_POST['money']) && $map['money'] = $_POST['money'];
        !empty($_POST['type']) && $map['take_state'] = intval($_POST['type']);
        $map['is_del'] = 0;
        $map['type'] = 1;
        $list = model('IncomeMny')->getList($map,20);
        foreach ($list['data'] as &$value) {
            //提现账号查询
            $ali_account = model('User')->field('ali_account')->where('uid='.$value['uid'])->find();
            $value['ali_account'] = $ali_account['ali_account'];
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $value['uid'] = getUserName($value['uid'])."(ID:".$value['uid'].")";
            if($value['take_state']=='1'){
               // $value['DOACTION'] = "<a href='".U('admin/LuckDraw/detail', array('id' => $value['id']))."'>确认提现".'</a>&nbsp;-&nbsp;';
                $value['DOACTION'] = "<a href='javascript:void(0)' onclick='admin.doToCash({$value['id']},\"doToCash\",\"".'提现'.'","'.'申请'."\")'>".'确认提现'.'</a>&nbsp;-&nbsp;';
            }else{
                ///$value['DOACTION'] = "<a href='javascript:void(0)'>已提现".'</a>&nbsp;-&nbsp;';
            }
            if($value['take_state']=='1'){
                $value['take_state'] = '提现中';
            }elseif($value['take_state']=='2'){
                $value['take_state'] = '已提现';
            }else{
                $value['take_state'] = '未提现';
            }

            $value['DOACTION'] .= "<a href='".U('admin/Config/permissionset', array('gid' => $value['user_group_id']))."'>删除".'</a>&nbsp;';
        }

        $this->_listpk = 'id';
        $this->allSelected = true;
        $this->displayList($list);
    }


    /**
     *
     * 确认提现
     *
     */
    public function doToCash(){
        $return = model('IncomeMny')->doToCash($_POST['id']);
        if ($return['status'] == 0) {
            $return['data'] = L('PUBLIC_ADMIN_OPRETING_ERROR');
        } else {
            $return['data'] = L('PUBLIC_ADMIN_OPRETING_SUCCESS');
        }
        echo json_encode($return);
        exit();
    }



    /**
     *
     * 提现导出
     *
     */
    public function report(){
        $map = array();
//        !empty($_REQUEST['id']) && $map['id'] = intval($_REQUEST['id']);
//        !empty($_REQUEST['uid']) && $map['uid'] = intval($_REQUEST['uid']);
//        !empty($_REQUEST['money']) && $map['money'] = $_REQUEST['money'];
//        !empty($_REQUEST['type']) && $map['take_state'] = intval($_REQUEST['type']);
//        $p = $_GET['p'];
//        if(empty($p)){
//            $p = 1;
//        }
        $map['is_del'] = 0;
        $map['type'] = 1;
        $list = model('IncomeMny')->reportAll($map);
        $data = array();
        foreach ($list as &$value) {
            //提现账号查询
            $new_val = array();
            $new_val['id'] = $value['id'];
            $new_val['uid'] = getUserName($value['uid'])."(ID:".$value['uid'].")";
            $new_val['money'] = $value['money'];
            $new_val['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $ali_account = model('User')->field('ali_account')->where('uid='.$value['uid'])->find();
            $new_val['ali_account'] = $ali_account['ali_account']." ";
            if($value['take_state']=='1'){
                $new_val['take_state'] = '提现中';
            }elseif($value['take_state']=='2'){
                $new_val['take_state'] = '已提现';
            }else{
                $new_val['take_state'] = '未提现';
            }
            $data[] = $new_val;

        }
        //创建对象
        $excel = new PHPExcel();
        $letter = array('A','B','C','D','E','F','G','H','I','J');
        //表头数组
        $tableheader = array('编号','提现人','提现金额','提现时间','支付宝账号','提现状态');
        //工作表设置
        $excel->setActiveSheetIndex( 0 );
        $excel->getActiveSheet()->freezePane('A2');
        $excel->getActiveSheet()->getStyle( 'A1:J1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $excel->getActiveSheet()->getStyle( 'A1:J1')->getFill()->getStartColor()->setARGB('#0096e5');
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
        $check_time = '提现统计-全部.xls';
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

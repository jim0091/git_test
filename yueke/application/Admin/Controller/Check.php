<?php
namespace app\Admin\Controller;
use app\Admin\Model\User;
use think\Session;
/**
 * 审核控制器
 * Class Check
 * @package app\Member\Controller
 */
class Check extends Base
{
    /**
     * @name  后台审核首页
     * @return string
     * @data 查找所有账号
     * @throws \think\Exception
     */
    public function index(){
        $user = new User;
        $data = $user->findAll(['a.state' => 1]);
        //查找所有申请账号
        if(!$data){
            $this->error('暂无申请内容','/admin');
        }
        $i = 0;
        foreach($data as $key){
            $data[$i]['create_time'] = date('Y-m-d',$key['create_time']);
            switch ($key['state']){
                case '1':
                    $data[$i]['state'] = '待审核';
                    break;
                case '2':
                    $data[$i]['state'] = '审核通过';
                    break;
                case '3':
                    $data[$i]['state'] = '审核失败';
                    break;
                case '4':
                    $data[$i]['state'] = '账号异常';
                    break;
            }
            $i++;
        }
        return $this->fetch('index',['data'=>$data]);
    }

    /**
     * @name  后台审核状态分类
     * @state 状态1=待审核，2=审核通过
     * @return string
     * @throws \think\Exception
     */
    public function check($state){
        if(empty($state)){
            return false;
        }
        $user = new User;
        //实例化用户模型
        if ($state==3){
            $data = $user->findRefuseState(['a.state' => $state]);
            //查询已拒绝的审核
        }else{
            $data = $user->findAll(['a.state' => $state]);
            //查询待审核或已审核
        }
        if(!$data) {
            $this->error('暂无内容');
        }
        $i = 0;
        foreach($data as $key){
            $data[$i]['create_time'] = date('Y-m-d',$key['create_time']);
            switch ($key['state']){
                case '1':
                    $data[$i]['state'] = '待审核';
                    break;
                case '2':
                    $data[$i]['state'] = '审核通过';
                    break;
                case '3':
                    $data[$i]['state'] = '审核失败';
                    break;
                case '4':
                    $data[$i]['state'] = '账号异常';
                    break;
            }
            $i++;
        }
        return $this->fetch('check', ['data' => $data]);


    }

    /**
     * @name  后台审核操作
     * @state 状态1=待审核，2=审核通过
     * @return string
     * @throws \think\Exception
     */
    public function operate($id){
        $user = new User;
        //实例化用户模型
        $data = $user->findById(['a.user_id' => $id]);
        //通过ID查找相关信息
        $data['create_time']=date('Y-m-d H:i:s',$data['create_time']);

        //时间戳转换
        switch ($data['state']){
            case '1':
                $data['state'] = '待审核';
                break;
            case '2':
                $data['state'] = '审核通过';
                break;
            case '3':
                $data['state'] = '审核失败';
                break;
            case '4':
                $data['state'] = '账号异常';
                break;
        }
        return $this->fetch('operate',['data'=>$data]);




    }

    /**
     * @name  后台审核同意
     * @return string
     * @throws \think\Exception
     */
    public function adopt($id){
        if(empty($id)){
            return false;
        }else{
            $user = new User;
            //实例化用户模型
            $data = $user->updateUser(['user_id' => $id],['state' =>2,'admin_id' =>Session::get('id')]);
            //根据ID同意完成审核
            $this->success('操作成功', 'admin/check/index');

        }
    }

    /**
     * @name  后台审核拒绝
     * @return string
     * @throws \think\Exception
     */
    public function lost($id){
        if(empty($id)){
            return false;
        }else{
            $user = new User;
            $reason = $_POST['slt'];
            if(!$reason){
                $this->error('请输入拒绝理由');
            }else{
                $data = $user->updateUser(['user_id' => $id],['state' => 3 ,'reason' =>$reason,'admin_id' =>Session::get('id')]);
                //根据ID完成拒绝审核，并添加拒绝理由
                $this->success('操作成功', 'admin/check/index');
            }

        }
    }

}

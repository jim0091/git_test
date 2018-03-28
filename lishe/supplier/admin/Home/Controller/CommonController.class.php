<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
    public function __construct(){
        parent::__construct();

        $accountCookie=cookie('adminAccount');
        $accountSession=session('adminAccount');
        if(!empty($accountCookie)){
            $account=json_decode($accountCookie,true);
        }
        if(!empty($accountSession)){
            $account=json_decode($accountSession,true);
        }

        if(!empty($account['uid']) and !empty($account['username']) and !empty($account['companyName'])){
            $this->adminId=$account['uid'];
            $this->adminName=$account['username'];
            $this->realName=$account['companyName'];
        }else{
            session(null);
            cookie(null);
            $this->adminId=0;
            $this->adminName='';
            $this->realName='';
        }

        $action=strtolower(ACTION_NAME);
        $control=strtolower(CONTROLLER_NAME);
        //if((empty($this->adminId) or empty($this->adminName)) and $control!='login'){
           // header("Location:".__APP__."/Login");
            //exit;
      //  }
    }
    //�ӿڷ��ش�����Ϣ
    public function retError($errCode=1,$msg='����ʧ��'){
        $ret=array(
            'result'=>100,
            'errcode'=>$errCode,
            'msg'=>$msg
        );
        echo json_encode($ret);
        exit;
    }

    //�ӿڷ��ؽ��
    public function retSuccess($data=array(),$msg='�����ɹ�'){
        $ret=array(
            'result'=>100,
            'errcode'=>0,
            'msg'=>$msg,
            'data'=>$data
        );
        echo json_encode($ret);
        exit;
    }

    //�ļ���־
    public function makeLog($type='',$data=''){
        if(!empty($type)){
            @file_put_contents(C('DIR_LOG').$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
        }
    }

}
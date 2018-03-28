<?php
namespace app\Member\Controller;
use think\Controller;
use think\Db;
use  think\Session;
use think\Request;

class Common extends Controller
{
    public function _initialize()
    {
        $action = Request::instance()->action();
        if (!session('alogin') && strtolower($action) != 'login' && strtolower($action) != 'register' && strtolower($action) != 'loginaction' && strtolower($action) != 'registeraction' ) {
            $this->error('请先登录', 'index/login');
        }
    }

    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:判断一串数字是不是手机号
     * @param: int $PhoneNumber
     * @return:boolean
     * @example:
     * 移动：134、135、136、137、138、139、150、151、152、157、158、159、182、183、184、187、188、178(4G)、147(上网卡)；
     * 联通：130、131、132、155、156、185、186、176(4G)、145(上网卡)；
     * 电信：133、153、180、181、189 、177(4G)；
     * 卫星通信：1349
     * 虚拟运营商：170
     */
    public function isPhoneNumber($PhoneNumber)
    {
        if (!is_numeric($PhoneNumber)) {
            return false;
        } else {
            if (preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $PhoneNumber)) {
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * @name 酒店最高管理员判断
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function roleCheck(){
        $alogin = seesion::get('alogin');
        if(empty($alogin)){
            return false;
        }else{
            $phone = $alogin['phone'];
            $res = Db::table('user')->where('phone',$phone)->find();
            if($res['state'] == '2'){
                return true;
            }else{
                return false;
            }
        }
    }

    /**
     * @name 图片上传
     * @param $file
     * @param string $path
     * @return bool
     */
    public function upload($file, $path=''){
        if ($file) {
            if ($path) {
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static' . DS . 'uploads/' . $path);
            } else {
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static' . DS . 'uploads');
            }
            if ($info) {
                return $info->getsaveName();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


}

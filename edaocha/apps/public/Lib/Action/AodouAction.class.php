<?php
/**
 * 首页控制器.
 *
 * @author jason <yangjs17@yeah.net>
 *
 * @version TS3.0
 */
class AodouAction extends Action
{
    public function zeroOfTheWorld()
    {
        $_SESSION['aodou_admin'] = true;
        $this->assign('jumpUrl', U('admin/index/index'));
        $this->success("验证成功");
    }  
}
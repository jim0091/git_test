<?php
/**
 * 分享控制器.
 *
 * @author liuxiaoqing <liuxiaoqing@zhishisoft.com>
 *
 * @version TS3.0
 */
class WaterMarkAction extends Action
{
    /**
     * 获取表情操作.
     *
     * @return json 表情相关的JSON数据
     */
    public function getWaterMark(){
        $id = intval($_GET['id']);
        $water = D('watermark')->where(array('wm_id'=>$id))->find();
        $water['image'] = getImageUrlByAttachId($water['image']);
        $water['image'] = TS_ROOT.substr($water['image'],strpos($water['image'],'/data'));
        exit(json_encode($water));
    }
}

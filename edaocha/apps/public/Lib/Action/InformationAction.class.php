<?php
/**
 * 分享控制器.
 *
 * @author liuxiaoqing <liuxiaoqing@zhishisoft.com>
 *
 * @version TS3.0
 */
use Apps\Information\Model\Subject;
class InformationAction extends Action
{


    public function search()
    {
        $key = $_GET['key'];
        $this->assign('search_key', $key);
        $informationData = Subject::getInstance()->searchInformation($key);
        foreach ($informationData['data'] as &$value) {
            $value['logo'] = getImageUrlByAttachId($value['logo']) ?: '';
        }

        $this->assign('informationData', $informationData);
        $this->display();
    }
}

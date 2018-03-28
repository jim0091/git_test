<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syslogistics_view_helper {

    /**
     * @brief 根据corpId获取到对于的物流名称
     *
     * @param $corpId
     */
    public function modifier_corpname($corpId)
    {
        if($corpId)
        {
            $params['corp_id'] = $corpId;
            $params['fields'] = "corp_name";
            $data = app::get('syslogistics')->rpcCall('logistics.dlycorp.get',$params);
            return $data['corp_name'];
        }
    }

    public function modifier_areaNameById($areaIds)
    {
        $areaIdArr = explode(',',$areaIds);
        $file = app::get('ectools')->res_dir.'/scripts/areas.json';
        $areas=json_decode(file_get_contents($file),TRUE);
        foreach( $areaIdArr as $id ){
        	$area[$areas[$id]['jd_pid']][]=$areas[$id]['name'];
        }
        foreach( $area as $parent=>$list){
            $areaNameArr[] = $areas[$parent]['name'].'<em class="text-muted">('.implode(',', $list).')</em>';
        }
        return implode(',',$areaNameArr);
    }
}


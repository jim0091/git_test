<?php
/**
 * 首页控制器.
 *
 * @author jason <yangjs17@yeah.net>
 *
 * @version TS3.0
 */
use Apps\Event\Model\Cate;
use Apps\Event\Model\Event;
use Apps\Information\Model\Subject;
use Apps\Information\Model\Cate    as CateModel;

class XmlAction extends Action
{

    public function feed(){
        $map = array();
        $map['is_del'] = 0;
        $map['is_audit'] = 1;
        $str = '<url> 
                    <loc>'.U('public/Index/index').'</loc> 
                    <lastmod>'.date('Y-m-d').'</lastmod> 
                    <changefreq>daily</changefreq> 
                    <priority>1.0</priority> 
                </url>';
        $category = model('CategoryTree')->setTable('channel_category')->order('sort ASC')->getCategoryList();
        foreach ($category as $key => $value) {
            $str .= '<url> 
                        <loc>'.U('public/Index/index',array('category'=>$value['channel_category_id'])).'</loc> 
                        <lastmod>'.date('Y-m-d').'</lastmod> 
                        <changefreq>daily</changefreq> 
                        <priority>0.9</priority> 
                    </url>';
        }
        $this->ec($str);
    }
    public function hd(){
        $data = Event::getInstance()->getList(null,null,null,null,9999);
        $str = '<url> 
                    <loc>'.U('public/Index/event_list').'</loc> 
                    <lastmod>'.date('Y-m-d').'</lastmod> 
                    <changefreq>daily</changefreq> 
                    <priority>1.0</priority> 
                </url>';
        foreach ($data['data'] as $key => &$v) {
            $str .= '<url> 
                        <loc>'.U('public/Index/event_info',array('eid'=>$v['eid'])).'</loc> 
                        <lastmod>'.date('Y-m-d',$v['stime']).'</lastmod> 
                        <changefreq>daily</changefreq> 
                        <priority>0.9</priority> 
                    </url>';
        }
        $this->ec($str);
    }
    public function wz(){
        $str = '<url> 
                    <loc>'.U('public/Index/information_list').'</loc> 
                    <lastmod>'.date('Y-m-d').'</lastmod> 
                    <changefreq>daily</changefreq> 
                    <priority>1.0</priority> 
                </url>';
        $cate_data = CateModel::getInstance()->get4Rank();
        foreach ($cate_data as $key => $value) {
            $str .= '<url> 
                        <loc>'.U('public/Index/information_list',array('cate'=>$value['id'])).'</loc> 
                        <lastmod>'.date('Y-m-d').'</lastmod> 
                        <changefreq>daily</changefreq> 
                        <priority>0.9</priority> 
                    </url>';
        }
        $informationData = Subject::getInstance()->setCate($cate)->setStatus(1)->getList(9999);
        foreach ($informationData['data'] as $key => $value) {
            $str .= '<url> 
                        <loc>'.U('public/Index/information_info',array('id'=>$value['id'])).'</loc> 
                        <lastmod>'.date('Y-m-d',$value['ctime']).'</lastmod> 
                        <changefreq>daily</changefreq> 
                        <priority>0.8</priority> 
                    </url>';
        }
        $this->ec($str);
    }

    public function ec($str){        
        header("Content-type: text/xml");
        echo '<?xml version="1.0"  encoding="UTF-8" ?><urlset>';        
        echo $str;
        echo '</urlset>';
        exit();
    }
}

<?php

use Apps\Information\Model\Cate;
use Apps\Information\Model\Subject;
use Ts\Models as Model;

/**
 * 资讯接口api类
 * Wayne qiaobinloverabbi@gmail.com.
 */
class InformationApi extends Api
{
    /**
     * 阅读资讯详情.
     *
     * @author Seven Du <lovevipdsw@outlook.com>
     * @datetime 2016-05-08T11:37:33+0800
     * @homepage http://medz.cn
     */
    public function reader()
    {
        $id = intval($_REQUEST['id']);
        $info = Model\InformationList::find($id);
        $info->increment('hits', 1);

        if (!$info) {
            return array(
                'status'  => 0,
                'message' => '访问的资讯不存在！',
            );
        }

        echo '<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui">
  <title>'.htmlspecialchars($info->subject, ENT_QUOTES, 'UTF-8').'</title>
  <style type="text/css">
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    html, body {
      font-family: -apple-system-font,"Helvetica Neue","PingFang SC","Hiragino Sans GB","Microsoft YaHei",sans-serif;
    }
    .wrap {
      width: 100%;
      height: auto;
      padding: 20px 15px 15px;
      background-color: #fff;
    }

    .wrap .title {
      margin-bottom: 10px;
      line-height: 1.4;
      font-weight: 400;
      font-size: 25px;
    }

    .wrap .date {
      position: relative;
      width: 100%;
      margin-bottom: 18px;
      line-height: 20px;
      font-size: 17px;
      font-style: normal;
      color: #8c8c8c;
    }

    .wrap .date .right {
      position: absolute;
      right: 0;
    }

    .wrap .abstract {
      width: 100%;
      height: auto;
      margin-bottom: 18px;
      padding: 10px;
      background: #edeeef;
    }

    .wrap .content {
      width: 100%;
      max-width: 100%;
      height: auto;
      overflow-x: hidden;
      color: #3e3e3e;
    }
    .content img{max-width:100%!important;}
  </style>
</head>
<body>
<div class="wrap">
  <h2 class="title">'.htmlspecialchars($info->subject, ENT_QUOTES, 'UTF-8').'</h2>
  <div class="date">
    '.date('Y-m-d', $info->rtime).'
    <span class="right">浏览：'.intval($info->hits).'</span>
  </div>
  <div class="abstract"><strong>[摘要]&nbsp;</strong>'.htmlspecialchars($info->abstract, ENT_QUOTES, 'UTF-8').'</div>
  <div class="content">
  	'.$info->content.'
  </div>
</div>
</body>
</html>
';
        exit;
    }

    /**
     * 新闻列表接口.
     *
     * @ wayne qiaobinloverabbi@gmail.com
     * @DateTime  2016-04-27T09:26:55+0800
     */
    public function NewsList()
    {
        $catid = intval($_REQUEST['cid']);
        $newsModel = Subject::getInstance();
        $catid && $map['cid'] = $catid;
        $map['isPre'] = 0;
        $map['isDel'] = 0;
        $this->data['max_id'] && $map['id'] = array('lt', $this->data['max_id']);
        $this->data['limit'] && $limit = $this->data['limit'] ? $this->data['limit'] : 10;
        $newsList = $newsModel->where($map)->field('id,cid,subject,abstract,author,ctime,hits,content')->limit($limit)->order('id desc')->select();
        if (!empty($newsList)) {
            foreach ($newsList as &$subject) {
                // $subject['url'] = 'http://www.baidu.com';
                $subject['url'] = sprintf('%s/api.php?mod=Information&act=reader&id=%d', SITE_URL, intval($subject['id']));
                preg_match_all('/\<img(.*?)src\=\"(.*?)\"(.*?)\/?\>/is', $subject['content'], $image);
                $image = $image[2];
                if ($image && is_array($image) && count($image) >= 1) {
                    $image = $image[0];
                    if (!preg_match('/https?\:\/\//is', $image)) {
                        $image = parse_url(SITE_URL, PHP_URL_SCHEME).'://'.parse_url(SITE_URL, PHP_URL_HOST).'/'.$image;
                    }
                }
                // $subject['commentNum'] = $this->_getComentNum($subject['id']);
                $subject['image'] = $image;
                unset($subject['content']);
            }
            $this->success(array('data' => $newsList));
        } else {
            $this->error('暂时没有资讯');
        }
    }

    /**
     * 咨询分类.
     *
     * @Author Wayne qiaobinloverabbi@gmail.com
     * @DateTime  2016-04-27T09:49:19+0800
     */
    public function NewsCate()
    {
        $cateModel = Cate::getInstance();
        $cates = $cateModel->where(['isDel' => 0])->order('rank asc')->select();
        if (!empty($cates)) {
            array_unshift($cates, array(
                'id'    => 0,
                'name'  => '全部',
                'isDel' => 0,
                'rank'  => 0,
            ));
            $return['msg'] = '获取分类成功';
            $return['status'] = 1;
            $return['data'] = $cates;

            return $return;
        } else {
            $return['msg'] = '没有找到分类';
            $return['status'] = 0;
            $return['data'] = '';

            return $return;
        }
    }

    /**
     * 获取评论数.
     *
     * @param int $sid 主题ID
     *
     * @return int 评论数
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    private function _getComentNum($sid)
    {
        $where = '`is_del` = 0 AND `app` = \'Information\' AND `table` = \'%s\' AND `row_id` = %d';
        $where = sprintf($where, 'information_list', intval($sid));

        return model('Comment')->where($where)->field('comment_id')->count();
    }


    /**
     * @return array
     *
     * 文章搜索接口
     *
     * 根据关键词匹配标题和标签
     */
    public function search_news(){
        $key = trim(t($this->data['key']));
        if ($key) {
            $newsModel = Subject::getInstance();
            $map['isPre'] = 0;
            $map['isDel'] = 0;
            $map['subject|tag'] = array('like','%'.$key.'%');
            $this->data['max_id'] && $map['id'] = array('lt', $this->data['max_id']);
            $this->data['limit'] && $limit = $this->data['limit'] ? $this->data['limit'] : 10;
            $newsList = $newsModel->where($map)->field('id,cid,subject,abstract,author,ctime,hits,content')->limit($limit)->order('id desc')->select();
            if (!empty($newsList)) {
                foreach ($newsList as &$subject) {
                    // $subject['url'] = 'http://www.baidu.com';
                    $subject['url'] = sprintf('%s/api.php?mod=Information&act=reader&id=%d', SITE_URL, intval($subject['id']));
                    preg_match_all('/\<img(.*?)src\=\"(.*?)\"(.*?)\/?\>/is', $subject['content'], $image);
                    $image = $image[2];
                    if ($image && is_array($image) && count($image) >= 1) {
                        $image = $image[0];
                        if (!preg_match('/https?\:\/\//is', $image)) {
                            $image = parse_url(SITE_URL, PHP_URL_SCHEME).'://'.parse_url(SITE_URL, PHP_URL_HOST).'/'.$image;
                        }
                    }
                    // $subject['commentNum'] = $this->_getComentNum($subject['id']);
                    $subject['image'] = $image;
                    unset($subject['content']);
                }
                $this->success(array('data' => $newsList));
            } else {
                $this->error('暂时没有资讯');
            }
        }else{
            return array(
                'status' => 0,
                'msg'    => '请输入关键词',
            );
        }
    }


    /**
     * @return mixed
     *
     * 赞文章
     */
    public function addInformationDigg()
    {
        $map['information_id'] = intval($_REQUEST['information_id']);
        $map['uid'] = empty($this->user_id) ? $this->mid : $this->user_id;
        $hasdigg = M('information_digg')->where($map)->find();
        $map['cTime'] = time();
        if (!$hasdigg) {
            $result = M('information_digg')->add($map);
            if ($result) {
                $res['status'] = 1;
                $res['msg'] = '赞成功';
            } else {
                $res['status'] = 0;
                $res['msg'] = '赞失败';
            }
        } else {
            $res['status'] = 0;
            $res['msg'] = '您已经赞过';
        }

        return $res;
    }

    /**
     * @return mixed
     *
     * 取消赞文章
     */
    public function delInformationDigg()
    {
        $map['information_id'] = intval($_REQUEST['information_id']);
        $map['uid'] = empty($this->user_id) ? $this->mid : $this->user_id;
        $hasdigg = M('information_digg')->where($map)->find();
        if ($hasdigg) {
            if (M('information_digg')->where($map)->delete()) {
                $res['status'] = 1;
                $res['msg'] = '取消赞成功';
            } else {
                $res['status'] = 0;
                $res['msg'] = '取消赞失败';
            }
        } else {
            $res['status'] = 0;
            $res['msg'] = '您还没赞过';
        }

        return $res;
    }


    /**
     * 收藏文章 --using.
     *
     * @param
     *        	integer information_id 资源ID
     *
     * @return array 状态+提示
     */
    public function favorite_information()
    {
        $data['information_id'] = intval($_REQUEST['information_id']);
        $data['uid'] = empty($this->user_id) ? $this->mid : $this->user_id;
        $resault = M('information_favorite')->where($data)->find();
        $data['favorite_time'] = time();
        if (!$resault) {
            if (M('information_favorite')->add($data)) {

                $res['status'] = 1;
                $res['msg'] = '收藏成功';
            } else {
                $res['status'] = 0;
                $res['msg'] = '收藏失败';
            }
        } else {
            $res['status'] = 0;
            $res['msg'] = '您已经收藏过';
        }

        return $res;
    }

    /**
     * 取消文章收藏 --using.
     *
     * @param
     *        	integer information_id 资源ID
     *
     * @return array 状态+提示
     */
    public function unfavorite_information()
    {
        $map['information_id'] = intval($_REQUEST['information_id']);
        $map['uid'] = empty($this->user_id) ? $this->mid : $this->user_id;
        $resault = M('information_favorite')->where($map)->find();
        if ($resault) {
            if (M('information_favorite')->where($map)->delete()) {

                $res['status'] = 1;
                $res['msg'] = '取消收藏成功';
            } else {
                $res['status'] = 0;
                $res['msg'] = '取消收藏失败';
            }
        } else {
            $res['status'] = 0;
            $res['msg'] = '你还没有收藏';
        }

        return $res;
    }


    /**
     * 用户收藏的文章 --using.
     *
     * @param
     *        	integer user_id 用户UID
     * @param
     *        	integer max_id 上次返回的最后一条收藏ID
     * @param
     *        	integer count 文章条数
     * @param
     *
     *
     * @return array 文章列表
     */
    public function getUserFavorite()
    {
        $user_id = $this->user_id ? intval($this->user_id) : $this->mid;
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;

        $map['c.uid'] = $user_id;
        // $map ['f.app'] = 'public';
        $map['f.isPre'] = 0;
        $map['f.isDel'] = 0;
        !empty($max_id) && $map['c.id'] = array(
            'lt',
            $max_id,
        );
        $list = D()->table('`'.C('DB_PREFIX').'information_list` AS f LEFT JOIN `'.C('DB_PREFIX').'information_favorite` AS c ON f.`id` = c.`information_id`')->field('c.`information_id`,c.id')->where($map)->order('c.id DESC')->limit($count)->findAll();
        $collection_list = array();
        foreach ($list as $k => $v) {
            // 微博信息
            $information_info = model('Cache')->get('information_api_'.$v['information_id']);
            if ($information_info) {
                $r[$k] = $information_info;
            } else {
                $r[$k] = $this->get_information_info($v['information_id']);

                model('Cache')->set('information_api_'.$v['information_id'], $r[$k]);
            }

            $collection_list[] = $r[$k];
        }

        return array(
            'max_id' => $this->max_id,
            'data'   => $collection_list,
        );
    }


    /**
     * @param $information_id
     * @return array
     *
     *
     * 文章详情
     */
    public function get_information_info($information_id){
        $map['id'] = $information_id;
        $data = model('InformationList')->where($map)->field('id,cid,subject,abstract,author,ctime,hits,logo')->find();
        if (!empty($data)) {
            $data['url'] = sprintf('%s/api.php?mod=Information&act=reader&id=%d', SITE_URL, intval($data['id']));
            //查询封面图完整路径
            $attach = model('Attach')->getAttachById($data['logo']);

            $data['image'] = getImageUrl($attach['save_path'].$attach['save_name'], $attach['width'], $attach['height'], true);
            return $data;
        } else {
            return array();
        }
    }
}

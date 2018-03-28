<?php

namespace App\H5\Controller;

use App\H5\Common;
use App\H5\Base\Controller;
use App\H5\Model;

/**
 * 分享相关控制器.
 *
 * @author Seven Du <lovveipdsw@outlook.com>
 **/
class Video extends Controller
{
    /**
     * 查询奥豆视频单集详情
     *
     * @return array
     *
     * @author zhl
     **/
    public function getVideoDataAction()
    {

//        $info = M('aodou_video_data')->find(2);
//        dump($info);exit;
        list($video_id, $parts) = Common::getInput(array('video_id', 'parts'));
        S('api_video_data_'.$video_id.'_'.$parts,null);
        $video_id = intval($video_id); //视频id
        if(!$video_id){
            return array('status' => 0, 'msg' => '参数错误');
        }
        $parts = intval($parts)==''?1:intval($parts); //第几集，默认第一集

        $video_info = S('api_video_data_'.$video_id.'_'.$parts);

        if(!$video_info){
            //视频详情
            $map = array();
            $map['a.video_id'] = $video_id;
            $map['a.parts'] = $parts;
            $map['a.is_del'] = 0;
            $tablePrefix = C('DB_PREFIX');
            echo $tablePrefix;
            $video_info = M('aodou_video_data')
                ->field('a.id as video_data_id,a.data_title,a.image_path,a.image_width,a.image_height,a.video_path,
            b.title,b.play_type,b.update_type,b.abstract,b.part_count')
                ->where($map)
                ->table("{$tablePrefix}aodou_video_data AS a LEFT JOIN {$tablePrefix}aodou_video AS b ON a.video_id = b.id")
                ->find();
            if(!$video_info){
                return array('status' => 0, 'msg' => '视频不存在或已删除');
            }
            $video_info['video_info'] = array(
                'image' => "https://v1.edaocha.net".$video_info['image_path'],
                'src' => "https://v1.edaocha.net".$video_info['video_path'],
                'type' => 'ts',
            );
            $video_info['parts'] = $parts;
            if($video_info['data_title']){
                $video_info['title'] = $video_info['title'].'-'.$video_info['data_title'];
            }
            $video_info['shortabstract'] = mb_substr($video_info['abstract'],0,58,'utf-8').'...';
            S('api_video_data_'.$video_id.'_'.$parts,$video_info);
        }

        //播放次数累加
        $save = array();
        $save['id'] = $video_info['video_data_id'];
        $save['pv'] = array('exp','pv+1');
        D('aodou_video_data')->save($save);

        //当前用户是否已点赞标识
        $map = array();
        $map['video_data_id'] = $video_info['video_data_id'];
        $map['uid'] = $this->mid;
        $digg_info = D('aodou_video_data_digg')->where($map)->count();
        if($digg_info){
            $video_info['digg'] = 1;
        }else{
            $video_info['digg'] = 0;
        }

        //点赞总数
        $map = array();
        $map['video_data_id'] = $video_info['video_data_id'];
        $video_digg_count = D('aodou_video_data_digg')->where($map)->count();
        $video_info['video_digg_count'] = $video_digg_count;

        //评论总数查询
        $map = array();
        $map['table'] = 'aodou_video_data';
        $map['is_del'] = 0;
        $map['to_comment_id'] = 0;
        $map['row_id'] = $video_info['video_data_id'];
        $count = model('Comment')->where($map)->count();
        $video_info['comment_count'] = $count;

        //评论查询
        $comment_list = array();
        $where = "app='public' and `table`='aodou_video_data' and to_comment_id=0 and is_del=0 and row_id=".$video_info['video_data_id']; //查询一级评论
        $count = 3;
        !empty($this->max_id) && $where .= " AND comment_id < {$this->max_id}";
        $comments = model('Comment')->where($where)->order('comment_id DESC')->limit($count)->findAll();

        foreach ($comments as $v) {
            switch ($v['type']) {
                case '2':
                    $type = '转发了此剧集';
                    break;
                case '3':
                    $type = '分享了此剧集';
                    break;
                case '4':
                    $type = '赞了此剧集';
                    break;
                default:
                    $type = '评论了此剧集';
                    break;
            }
            $comment_info['type'] = $type;
            $comment_info['user_info'] = $this->get_user_info($v['uid']);

            $comment_info['comment_id'] = $v['comment_id'];
            $comment_info['content'] = parse_remark($v['content']);
            $comment_info['ctime'] = $v['ctime'];
            $comment_info['digg_count'] = $v['digg_count'];
            $diggarr = model('CommentDigg')->checkIsDigg($v['comment_id'], $GLOBALS['ts']['mid']);
            $comment_info['is_digg'] = t($diggarr[$v['comment_id']] ? 1 : 0);

            /* # 将评论里面的emoji解析 */
            $comment_info['content'] = formatEmoji(false, $comment_info['content']);

            $comment_list[] = $comment_info;
        }

        $video_info['comment_list'] = $comment_list;

        $video_info['status'] = 1;
        $this->__json__($video_info);
    }


    private function get_user_info($uid)
    {
        $user_info_whole = model('User')->getUserInfo($uid);
        $user_info['uid'] = $user_info_whole['uid'];
        $user_info['uname'] = $user_info_whole['uname'];
        $user_info['remark'] = $user_info_whole['remark'];
        $user_info['avatar']['avatar_middle'] = $user_info_whole['avatar']['avatar_small'];


        return $user_info;
    }


} // END class Feed extends Controller

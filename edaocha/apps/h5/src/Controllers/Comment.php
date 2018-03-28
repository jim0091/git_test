<?php

namespace App\H5\Controller;

use App\H5\Base\Controller;
use App\H5\Common;
use App\H5\Model;
use App\H5\Model\Comment as CommentModel;

/**
 * 评论相关控制器.
 *
 * @author Seven Du <lovevipdsw@outlook.com>
 **/
class Comment extends Controller
{
    public function addByFeedAction()
    {
        $feedId = Common::getInput('feed_id');

        $feed = Model\Feed::existent()->find($feedId);

        if (!$feed) {
            $this->error('该分享不存在！');
        }

        $this
            ->setTitle('评论')
            ->trace('feed', $feed)
            ->display('add')
        ;
    }

    public function postFeedCommentAction()
    {
        list($feedId, $content, $toCid) = Common::getInput(array('feed_id', 'content', 'to_cid'));

        $feed = Model\Feed::existent()->find($feedId);

        if (!$feed) {
            $this->error('评论的分享已经被删除或者不存在！', true);
        }

        $comment = new Model\Comment();
        $comment->type = 1;
        $comment->app = 'public';
        $comment->table = 'feed';
        $comment->app_uid = $feed->uid;
        $comment->uid = $this->mid;
        $comment->content = $content;
        $comment->ctime = time();
        $comment->is_audit = 1;
        $comment->data = '';
        $comment->client_type = 4;
        $comment->to_comment_id = 0;
        $comment->to_uid = 0;

        $toComment = Model\Comment::find($toCid);
        if ($toComment) {
            $comment->to_comment_id = $toComment->comment_id;
            $comment->to_uid = $toComment->uid;
        }

        $feed->comments()->save($comment);
        $feed->increment('comment_count', 1);
        $feed->save();

        $unread_comment = $feed
            ->user
            ->datas()
            ->key('unread_comment')
            ->first()
            // ->increment('value', 1)
        ;

        if (!$unread_comment) {
            $unread_comment = new Model\UserData();
            $unread_comment->key = 'unread_comment';
            $unread_comment->value = 0;
            $unread_comment->mtime = date('Y-m-d H:i:s', time());
            $feed->user->datas()->save($unread_comment);
        }

        if ($feed->app == 'weiba' && $feed->app_row_id && ($post = Model\WeibaPost::find($feed->app_row_id))) {
            $reply = new Model\PostReply;
            $reply->weiba_id = $post->weiba_id;
            $reply->post_id = $post->post_id;
            $reply->post_uid = $post->post_uid;
            $reply->uid = $this->mid;
            $reply->to_reply_id = 0;
            $reply->to_uid = 0;
            $reply->ctime = time();
            $reply->content = $content;
            $reply->is_del = 0;
            $reply->comment_id = $comment->comment_id;
            $reply->storey = 0;
            $reply->attach_id = 0;
            $reply->digg_count = 0;

            if ($toComment) {
                $upReply = Model\PostReply::where('post_id', '=', $post->post_id)
                    ->where('comment_id', '=', $toComment->comment_id)
                    ->first()
                ;
                if ($upReply) {
                    $reply->to_reply_id = $upReply->reply_id;
                    $reply->to_uid = $upReply->uid;
                }
            }

            $post->replys()->save($reply);
            $post->increment('reply_count', 1);
        }

        if ($feed->user->uid != $this->mid) {
            $unread_comment->increment('value', 1);
        }

        $this->success('评论成功', true);
    }

    public function getListAction()
    {
        list($table, $rowId, $minId, $num) = Common::getInput(array('table', 'row_id', 'min_id', 'num'));
        ($num <= 0 || !$num) &&
        $num = 30;
        $table = empty($table) ? 'feed' : $table;
        $list = CommentModel::where('is_del', '=', 0)
            ->where('is_audit', '=', 1)
            ->where('row_id', '=', $rowId)
            ->where('table', '=', $table)
            ->where(function ($query) use ($minId) {
                if ($minId) {
                    $query->where('comment_id', '<', $minId);
                }
            })
            ->take($num)
            ->orderBy('comment_id', 'desc')
            ->limit(3)
            ->get();

        $list->load('user');

        $comments = array();
        foreach ($list as $comment) {
            $data = array();
            $data['comment_id'] = $comment->comment_id;
            $data['user'] = array(
                'uid' => $comment->uid,
                'face' => $comment->user->face->avatar_middle,
                'username' => $comment->user->uname,
            );
            //匿名图像和名称
            $uid =  $data['user']['uid'];
            $map['a.uid'] = $uid;
            $tablePrefix = C('DB_PREFIX');
            $user_anonymous = D('user_anonymous')
                ->field('a.anonymous_id,a.bd_time,b.nickname,c.icon')
                ->where($map)
                ->table("{$tablePrefix}user_anonymous AS a LEFT JOIN {$tablePrefix}anonymous AS b ON b.id=a.anonymous_id LEFT JOIN {$tablePrefix}anonymous AS c ON c.id=b.pid")
                ->find();
            $user_info['anonymous_name'] = $user_anonymous['nickname']; //匿名名称
            $data['user']['anonymous_name'] = $user_info['anonymous_name'];
            if($user_anonymous['icon']){
                $pic = D('attach')->where('attach_id='.$user_anonymous['icon'])->find();
                $pic_url = $pic['save_path'].$pic['save_name'];
                $url = getImageUrl($pic_url);
            }else{
                $url = '';
            }
            $user_info['anonymous_icon'] = $url; //匿名头像
            $data['user']['anonymous_icon'] = $user_info['anonymous_icon'];//匿名头像

            $data['time'] = friendlyDate($comment->ctime);
            $data['content'] = $comment->content;
            $data['client_type'] = getFromClient($comment->client_type,$comment->app);

            array_push($comments, $data);
        }

        $this->__json__($comments);
    }
} // END class Comment extends Controller

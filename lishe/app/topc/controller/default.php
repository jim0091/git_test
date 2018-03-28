<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_ctl_default extends topc_controller
{
	public function index()
    {
        $file=file_get_contents('site/index.html');
        $account=$_COOKIE['account'];
        if(empty($account)){
			$userMsg='<li><a href="/shop.php/Sign/index">会员登录</a></li>';
		}else{
			$accountCookie=json_decode($account,TRUE);
			$userName=$accountCookie['userName'];
			if(empty($userName)){
				$userName=$accountCookie['account'];
			}
			$userMsg='<li><a href="/user.php/Info/index">'.$userName.'</a>&nbsp;<a href="/shop.php/Sign/logout" style="font-size:14px">[退出]</a></li>';
		}
		echo str_replace('{loginStatus}',$userMsg,$file);
    }
    public function shop()
    {

    	$this->setLayoutFlag('activity_index');
        $post = input::get();
        $params = array(
            'release_time' => "sthan",
            'end_time' => "bthan",
            'order_by' => 'mainpush desc',
            'fields' => 'activity_name,activity_desc,activity_id,mainpush,slide_images,release_time,end_time,start_time,discount_max,discount_min',
        );
        $activitys = app::get('topc')->rpcCall('promotion.activity.list',$params);
        $now = time();
        $nostartCount = 0;
        $startCount = 0;
        foreach($data as $key=>$val)
        {
            if($now >= $val['release_time'] && $now < $val['start_time'] )
            {
                $pagedata['activity_list_nostart'][] = $val;
                $nostartCount += 1;
            }
            elseif($now >= $val['start_time'] && $now < $val['end_time'] )
            {
                $pagedata['activity_list_start'][] = $val;
                $startCount += 1;
            }
        }
        $pagedata['nostart_count'] = $nostartCount ;
        $pagedata['start_count'] = $startCount ;
        $pagedata['now_time'] = time();
        // var_dump($pagedata);

        $GLOBALS['runtime']['path'][] = array('title'=>app::get('topc')->_('首页'),'link'=>kernel::base_url(1));
        $this->setLayoutFlag('index');
        return $this->page();
    }
    
    public function theme_index()
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/theme/index.html');
    }
    
    public function theme_user()
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/theme/user.html');
    }
    
    public function theme_hr()
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/theme/hr.html');
    }
    
    public function theme_boss()
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/theme/boss.html');
    }
    
    public function theme_member()
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/theme/member.html');
    }
    
    public function theme_new_hand()
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/theme/new_hand.html');
    }

    public function theme_evaluate() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/theme/evaluate/index.html');
    }

    public function theme_evaluate_detail() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/theme/evaluate/detail.html');
    }

    public function theme_help() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/theme/help.html');
    }

    public function topic01() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/topic/01.html');
    }

    public function topic02() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/topic/02.html');
    }

    public function topic03() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/topic/03.html');
    }

    public function topic04() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/topic/04.html');
    }

    public function topic05() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/topic/05.html');
    }

    public function topic06() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/topic/06.html');
    }

    public function topic07() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/topic/07.html');
    }

    public function topic08() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/topic/08.html');
    }

    public function topic09() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/topic/09.html');
    }

    public function updateIntegral() 
    {

        $postdata = input::get();

        $pagedata['user_id'] = $params['user_id'];
        $pagedata['amount'] = $params['amount'];

        $this->setLayoutFlag('activity_index');
        return $this->page('topc/user/update-integral.html', $pagedata);
    }

    public function updateIntegral2() 
    {

        $postdata = input::get();

        $pagedata['user_id'] = $params['user_id'];
        $pagedata['amount'] = $params['amount'];

        $this->setLayoutFlag('activity_index');
        return $this->page('topc/user/update-integral2.html', $pagedata);
    }

    public function givenObject() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/given-object.html');
    }

    public function givenScene() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/given-scene.html');
    }

    public function TALike() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/TA-like.html');
    }

    public function help01() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/help/help_index.html');
    }

    public function help02() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/help/help_summary.html');
    }

    public function help03() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/help/help_resume.html');
    }

    public function help04() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/help/help_problem.html');
    }

    public function help05() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/help/help_recruitment.html');
    }

    public function help06() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/help/help_partner.html');
    }

    public function help07() 
    {
        $this->setLayoutFlag('activity_index');
        return $this->page('topc/help/help_we.html');
    }
}
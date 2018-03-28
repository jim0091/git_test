import React from 'react';
import { Route, Link, Redirect, IndexRedirect } from 'react-router';
import WeibaPostReader from './WeibaPostReader';
import ChannelPager from './channel-pager';
import ChannelReader from './channel-reader';
import TopicPager from './topic-pager';
import TopicReader from './topic-reader';
import UserSeting from './user-seting';
import UserShowList from './user-show-list';
import UserPhotoList from './user-photo-list';
import UserFeedList from './user-feed-list';
import UserInfo from './user-info';
import UserSignIn from './UserSignIn';

import Home from './feed/home';
import Find from './find';
import User from './user';
import Message from './message/message';
import Weiba from './weiba/weiba';

import FeedReader from './feed/reader';
import FeedDiggList from './feed/digg-list';
import UserShow from './user/show';
import UserSignUp from './sign/up';
import FeedSend from './feed/send';
import MessageChat from './message/chat';
import WeibaIndex from './weiba/index';
import WeibaTops from './weiba/tops';
import WeibaAllBox from './weiba/weiba-all-box';
import WeibaReader from './weiba/weiba-reader';

import FeedAll from './feed/feed-all';
import FeedStart from './feed/feed-start';
import FeedChannel from './feed/feed-channel';
import FeedRecommend from './feed/feed-recommend';
import FeedComment from './feed/comment';

import EventReader from './event/reader';
import EventAll from './event/event-all';

import InformationHome from './information/information-home';
import InformationReader from './information/reader';
import VideoReader from './video/reader';
import AnonyReader from './anony/reader';
import history from '../app/src/util/history';

import DownApp from './downapp';

class NoMatch extends React.Component
{
	render() {
		return (
			<Link to="/">Go to home page!</Link>
		);
	}
}

window.router = history;
window.goBack = history.goBack;

const App = (
	<Route path="/">
		{/* 根 */}
		<IndexRedirect to="/home/all" />
		{/* 首页 */}
		<Route path={'/home'} component={Home}>
			<IndexRedirect to={'all'} />
			<Route path={'all'} component={FeedAll} />
			<Route path={'event'} component={EventAll} />
			<Route path={'information(/:cid)'} component={InformationHome} />
		</Route>
		{/* 微吧 */}
		<Route path={'/weiba/post/:postId'} component={WeibaPostReader} />
		<Route path={'/weiba/all'} component={WeibaAllBox} />
		<Route path={'/weiba/reader/:weibaId'} component={WeibaReader} />
		<Route path={'/weiba'} component={Weiba} >
			<IndexRedirect to={'join'} />
			<Route path={'join'} component={WeibaIndex} />
			<Route path={'tops'} component={WeibaTops} />
		</Route>
		{/* 频道 */}
		<Route path={'/channel'} component={ChannelPager} />
		<Route path={'/channel/reader/:id'} component={ChannelReader} />
		{/* 话题 */}
		<Route path={'/topic'} component={TopicPager} />
		<Route path={'/topic/reader/:name'} component={TopicReader} />
		{/* 用户 */}
		<Route path={'/user'} component={User} />
		<Route path={'/user/seting'} component={UserSeting} />
		<Route path={'/user/photo/:uid'} component={UserPhotoList} />
		<Route path={'/user/feed'} component={UserFeedList} />
		<Route path={'/user/info/:uid'} component={UserInfo} />
		<Route path={'/user/more/:controller/:action/:userId(/:title)'} component={UserShowList} />
		<Route path={'/user/:uid(/:type)'} component={UserShow} />
		{/* 分享 */}
		<Route path={'/feed/digglist/:feedId'} component={FeedDiggList} />
		<Route path={'/feed/reader/:feedId'} component={FeedReader} />
		<Route path={'/feed/comment/:feedId(/:cid)'} component={FeedComment} />
		{/* 发现 */}
		<Route path={'/find'} component={Find} />
		{/* 消息 */}
		<Route path={'/message'} component={Message} />
		<Route path={'/chat/:roomId'} component={MessageChat} />
		{/* sign */}
		<Route path={'/sign/in'} component={UserSignIn} />
		<Route path={'/sign/up'} component={UserSignUp} />
		{/* 发布分享 */}
		<Route path={'/send(/:type/:data)'} component={FeedSend} />
		{/* 活动 */}		
		<Route path={'/event/reader/:eventId'} component={EventReader}></Route>
		{/* 文章 */}	
		<Route path={'/information/reader/:id/:cid'} component={InformationReader}></Route>
		{/*视频*/}
		<Route path={'/video/reader/:id/:parts'} component={VideoReader}></Route>
        {/*匿名动态*/}
        <Route path={'/anony/reader/:feedId'} component={AnonyReader}></Route>
		{/* 下载 */}
		<Route path={'/downapp'} component={DownApp} />
		{/* 没有路由匹配页面 */}
		<Route path="*" component={NoMatch} />
	</Route>
);

export default App;
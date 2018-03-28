import React, {Component} from 'react';
import CommonFeedList from './common-feed-list';


import ShareBottom from '../share-bottom';

class FeedAll extends Component
{

  render() {
    return (
    	<div>
		    <CommonFeedList  uri={buildURL('feed', 'getFeedListToAll')}  cacheKeyName={'home-all-init'}  emptyMessage={'暂时没有分享内容哦！'}   />
		    <div style={{borderTop:'solid 1px #e5e5e5'}}>
	    	<ShareBottom />
	    	</div>
    	</div>
    );
  }
}

export default FeedAll;


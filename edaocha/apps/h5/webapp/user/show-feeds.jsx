import React, {Component} from 'react';
import CommonFeedList from '../feed/common-feed-list';

import ShareBottom from '../share-bottom';

class ShowFeeds extends Component
{

  render() {
    if (this.props.uid <= 0) {
      return (<TipsEmpty message={this.props.emptyMessage} />);
    }
    return (
      <div style={{padding:'0 12px',backgroundColor: '#fff'}}>
        <CommonFeedList
          uri={buildURL('user', 'feeds', {uid: this.props.uid})}
          cacheKeyName={'show-user-feeds-' + this.props.uid}
          emptyMessage={'暂时没有分享内容哦！'}
        />
        <ShareBottom />
      </div>
    );
  }
}

export default ShowFeeds;
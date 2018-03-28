import React, {Component} from 'react';
import FlatButton from 'material-ui/FlatButton';
import Avatar from 'material-ui/Avatar';
import Snackbar from 'material-ui/Snackbar';

// 图标
import NavigationMoreHoriz from 'material-ui/svg-icons/navigation/more-horiz';
import ActionFavoriteBorder from 'material-ui/svg-icons/action/favorite-border';
import CommunicationComment from 'material-ui/svg-icons/communication/comment';
import AVRepeat from 'material-ui/svg-icons/av/repeat';

// 自有组件
import Cache from '../util/cache';
import FeedContentText from './content/text';
import FeedContentImage from './content/image';
import FeedContentVideo from './content/video';
import FormatClientName from '../util/FormatClientName';
import checkLoginStatus from '../util/check-login-status.jsx';

import reportImg from '../../app/images/icons/report.png';
import commentImg from '../../app/images/icons/comment.png';
import zanImg from '../../app/images/icons/zan.png';

class FeedItem extends Component
{
  constructor(props) {
    super(props);
    this.state = {
      data: props.data,
      Snackbar: {
        open: false,
        message: '',
      },
    };
  }

  render() {
    return (
      <div style={styles.root}>
        <div style={styles.header}>
          <div
            style={{
              width: 'auto',
              height: 'auto',
              position: 'relative',
            }}
          >
            <Avatar
              size={40}
              src={this.state.data.user.face}
            />
          </div>
          <div style={styles.headerContent} onTouchTap={() => {
              window.router.push(`/feed/reader/${this.state.data.feed.id}`);
            }}>
            <span style={styles.headerUsername}>{this.state.data.user.username}</span>
            <span style={styles.headerMore}>{this.state.data.date}  来自{FormatClientName(this.state.data.feed.from)}</span>
          </div>
        </div>
        <div style={styles.body}>
          <FeedContentText content={this.state.data.feed.content} feedId={this.state.data.feed.id} />
          <FeedContentImage images={this.state.data.images} />
          <FeedContentVideo video={this.state.data.video} />
        </div>
        <div style={styles.footer}>
            <div style={styles.commentbox} onTouchTap={this.gotoItunesApp.bind(this)}>
              <Avatar
                size={15}
                src={reportImg}
                style={{
                  borderRadius:'0',
                  marginRight:15,
                }}
              />
              转发
            </div>
            <div style={styles.commentBor}></div>
            <div style={styles.commentbox} onTouchTap={this.gotoItunesApp.bind(this)}>
            <Avatar
                size={15}
                src={commentImg}
                style={{
                  borderRadius:'0',
                  marginRight:15,
                }}
              />

            评论</div>
            <div style={styles.commentBor}></div>
            <div style={styles.commentbox} onTouchTap={this.gotoItunesApp.bind(this)}>
            <Avatar
                size={15}
                src={zanImg}
                style={{
                  borderRadius:'0',
                  marginRight:15,
                }}
              />

            赞</div>
        </div>
        <Snackbar
          open={this.state.Snackbar.open}
          message={this.state.Snackbar.message}
          autoHideDuration={1500}
          onRequestClose={() => {
            this.state.Snackbar.open = false;
            this.setState(this.state);
          }}
        />
      </div>
    );
  }
  gotoItunesApp() {
     window.location.href = 'https://www.aodou.com/index.php?app=h5#/downapp';
  }
  getStarDOM() {
    if (this.state.data.feed.starStatus == true) {
      return (
        <FlatButton
          style={{
            color: '#ff5b36',
            minWidth: 70,
            width: 70,
          }}
          labelStyle={{
            display: 'inline-block',
            boxSizing: 'border-box',
            minWidth: 33,
            paddingRight: 0,
          }}
          icon={<ActionFavoriteBorder />}
          label={this.state.data.feed.starNum > 99 ? '99+' : this.state.data.feed.starNum + ''}
          onTouchTap={this.handleUnStar.bind(this)}
        />
      );
    }
    return (
      <FlatButton
        style={{
          color: '#b2b2b2',
          minWidth: 70,
          width: 70,
        }}
        labelStyle={{
          display: 'inline-block',
          boxSizing: 'border-box',
          minWidth: 33,
          paddingRight: 0,
        }}
        icon={<ActionFavoriteBorder />}
        label={this.state.data.feed.starNum > 99 ? '99+' : this.state.data.feed.starNum + ''}
        onTouchTap={this.handleStar.bind(this)}
      />
    );
  }

  handleStar() {
    if (!checkLoginStatus()) {
      window.router.push('/sign/up');
    } else {
      // let load = loadTips('执行中...');
      $.ajax({
        url: buildURL('feed', 'digg'),
        type: 'POST',
        dataType: 'json',
        data: {
          feed_id: this.state.data.feed.id,
        },
        timeout: 5000,
      })
      .done(function(data) {
        if (data.status == true) {
          this.state.data.feed.starStatus = true;
          this.state.data.feed.starNum += 1;
        }
        // this.state.Snackbar.open = true;
        // this.state.Snackbar.message = data.message;
      }.bind(this))
      .fail(function() {
        this.state.Snackbar.open = true;
        this.state.Snackbar.message = '请求超时，请检查网络状态!';
      }.bind(this))
      .always(function() {
        // load.hide();
        this.setState(this.state);
      }.bind(this));
    }
  }

  handleUnStar() {
    if (!checkLoginStatus()) {
      window.router.push('/sign/up');
    } else {
      // let load = loadTips('执行中...');
      $.ajax({
        url: buildURL('feed', 'unDigg'),
        type: 'POST',
        dataType: 'json',
        data: {
          feed_id: this.state.data.feed.id,
        },
        timeout: 5000,
      })
      .done(function(data) {
        if (data.status == true) {
          this.state.data.feed.starStatus = false;
          this.state.data.feed.starNum -= 1;
        }
        // this.state.Snackbar.open = true;
        // this.state.Snackbar.message = data.message;
      }.bind(this))
      .fail(function() {
        this.state.Snackbar.open = true;
        this.state.Snackbar.message = '请求超时，请检查网络状态!';
      }.bind(this))
      .always(function() {
        // load.hide();
        this.setState(this.state);
      }.bind(this));
    }
  }

}

const styles = {
  root: {
    boxSizing: 'border-box',
    width: '100%',
    padding: 12,
    paddingBottom: 0,
    marginBottom:9,
    borderBottom: 'solid 1px #e5e5e5',
    borderTop: 'solid 1px #e5e5e5',
    backgroundColor: '#fff',
  },
  header: {
    width: '100%',
    display: 'flex',
    justifyContent: 'flex-start',
    alignItems: 'center',
    flexDirection: 'row',
    paddingBottom: 6,
    paddingLeft: 12,
    paddingRight: 12,
    boxSizing: 'border-box',
  },
  headerContent: {
    boxSizing: 'border-box',
    display: 'flex',
    flexGrow: 1,
    flexDirection: 'column',
    paddingLeft: 16,
  },
  headerUsername: {
    width: '100%',
    fontSize: 14,
    color: '#ff7300',
  },
  headerMore: {
    width: '100%',
    fontSize: 12,
    color: '#999999',
  },
  body: {
    width: '100%',
    height: 'auto',
  },
  footer: {
    display:'flex',
    height:35,
    borderTop:'solid 1px #e5e5e5',
  },
  commentbox:{
    width:'33%',
    textAlign:'center',
    display:'flex',
    justifyContent:'center',
    alignItems:'center',
    cursor:'pointer',
    fontSize:14,
    color:'#999999',
  },
  commentBor:{
    backgroundColor: '#d7d7d7',
    height: 15,
    width: 1,
    display: 'flex',
    marginTop:10,
  }
};

FeedItem.defaultProps = {
  data: {
    user: {
      uid: 0,
      username: '',
      face: '',
      groupicon: ''
    },
    feed: {
      id: 0,
      content: '',
      from: 0,
      starNum: 0,
      commentNum: 0,
      starStatus: false,
    },
    date: 'new',
    type: 'post',
    images: [],
  }
}

export default FeedItem;

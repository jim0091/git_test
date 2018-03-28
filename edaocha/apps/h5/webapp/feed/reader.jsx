import React, {Component} from 'react';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import IconButton from 'material-ui/IconButton';
import FlatButton from 'material-ui/FlatButton';
import {ListItem} from 'material-ui/List';
import Avatar from 'material-ui/Avatar';
import Snackbar from 'material-ui/Snackbar';
import NavigationChevronLeft from 'material-ui/svg-icons/navigation/chevron-left';
import NavigationChevronRight from 'material-ui/svg-icons/navigation/chevron-right';
import ActionFavorite from 'material-ui/svg-icons/action/favorite';
import ActionFavoriteBorder from 'material-ui/svg-icons/action/favorite-border';
import ToggleStar from 'material-ui/svg-icons/toggle/star';
import ToggleStarBorder from 'material-ui/svg-icons/toggle/star-border';
import MapsRateReview from 'material-ui/svg-icons/maps/rate-review';
import NavigationClose from 'material-ui/svg-icons/navigation/close';

import {Link} from 'react-router';

import AppBar from '../AppBar.jsx';
import CommentBox from '../comment-box.jsx';
import FormatClientName from '../util/FormatClientName.jsx';
import Expression from '../util/expression.jsx';
import guid from '../util/guid.jsx';
import AtUser from '../util/at-user.jsx';
import checkLoginStatus from '../util/check-login-status';
import ShareTop from '../share-top';
import ShareBottom from '../share-bottom';
import ScrollTop from '../scroll-top';

import FeedContentText from './content/text';
import FeedContentImage from './content/image';
import FeedContentVideo from './content/video';

import reader_logo from '../../app/images/icons/reader-logo.png';
import reader_slogan from '../../app/images/icons/reader-slogan.png';
import reader_qcode from '../../app/images/icons/reader-qcode.png';
import reader_icon from '../../app/images/icons/reader-icon.png';

import pathImg from '../../app/images/icons/path.png';

class Reader extends Component
{
  constructor(props) {
    super(props);
    this.state = {
      feed:{
        feedId: props.params.feedId,
        content: '',
        diggCount: 0,
        type: 'post',
        date: 'now',
        from: 0,
        users: [],
        user:{
          username: '',
          face: ''
        },
        followStatus: false,
        starStatus: false,
        images: [],
        video: null,
      },
      Snackbar: {
        open: false,
        message: '',
      },
      isCache: true,
      isInstall:false,
    };

    if (props.location.search.indexOf('comit') >= 0) {
      this.state.isCache = false;
    }
    //判断是否安装APP
    //this.isInstalled();
  }

  componentDidMount() {
    let load = loadTips('加载中...');
    $.ajax({
      url: buildURL('feed', 'getFeedInfo'),
      type: 'POST',
      dataType: 'json',
      data: {feed_id: this.state.feed.feedId},
    })
    .done(function(data) {
      if (typeof data.status != undefined && data.status == false) {
        this.state.Snackbar.open = true;
        this.state.Snackbar.message = data.message;
      } else {
        this.state.feed = data;
      }
    }.bind(this))
    .fail(function() {
      this.state.Snackbar.open = true;
      this.state.Snackbar.message = '请检查网络～';
    }.bind(this))
    .always(function() {
      load.hide();
      this.setState(this.state);
    }.bind(this));
  }

  render() {
    const {images, ...feed} = this.state.feed;
    return (
      <MuiThemeProvider muiTheme={muiTheme}>
        <div style={styles.root}>
          {/*<div style={styles.hide}>
              {images.map((image, key) => {
                if (key < 1) {
                  return (<img src={image.src} />);
                }
              })}
          </div>*/}
          <div style={{width:'100%',height:'45px',fontSize:18,color:'#333333',textAlign:'center',position:'relative',backgroundColor:'#ffffff',lineHeight:'45px',borderBottom:'solid 1px #e5e5e5'}}>
          <Avatar
            size={20}
            src={pathImg}
            style={{
              backgroundColor:'#ffffff',
              width:'10px',
              height: '17px',
              position:'absolute',
              zIndex:10,
              top: '15px',
              left: '15px',
            }}
            onTouchTap={() => {
              window.router.push(`/home/all`);
            }}
          />
          动态
          </div>
          <div style={styles.contentBox}>
              <div style={{backgroundColor: '#fff', paddingBottom: 5,}}>
                <ListItem
                  disabled={true}
                  leftAvatar={<Avatar src={this.state.feed.user.face} />}
                  primaryText={
                    <span style={styles.username}>{this.state.feed.user.username}</span>
                  }
                  secondaryText={
                    <div style={styles.comment}>
                      {this.state.feed.date}&nbsp;&nbsp;来自{FormatClientName(this.state.feed.from)} 
                    </div>
                  }
                  style={{
                    paddingTop: 17,
                    paddingLeft: 50,
                    paddingBottom: 12,
                  }}
                />
                <FeedContentText content={this.state.feed.content} feedId={this.state.feed.feedId} />
                <FeedContentImage images={this.state.feed.images} />
                <FeedContentVideo video={this.state.feed.video} />
              </div>
          </div>
          { /* 点赞的人列表 */
            this.getDiggsDOM()
          }
          <div style={styles.commentBox}>
              {
                /* 根据评论数量是否显示评论 */
                this.getComment()
              }
              <ShareBottom />
          </div>
          <ScrollTop />
          <div style={{position:'fixed',bottom:0,left:0,zIndex:999,width:'100%'}}>
            <ShareTop  isInstall={this.state.isInstall} botn={true}/></div>
          <Snackbar
            open={this.state.Snackbar.open}
            message={this.state.Snackbar.message}
            autoHideDuration={1500}
            onRequestClose={() => {
              this.state.Snackbar.open = false;
              this.setState(this.state);
            }}
          />
          {/*             
          <div htmlFor={'tool-bar'} style={styles.toolBar}>
            <div style={styles.toolItem}>
              {this.getFollowDOM()} //喜欢／取消喜欢
            </div>
            <div style={styles.toolItem}>
              {this.getStarDOM()}  //收藏／取消收藏
            </div>
            <div style={styles.toolItem}>
              <FlatButton
                label={'评论'}
                icon={<MapsRateReview color={'#b2b2b2'} />}
                onTouchTap={() => {
                  this.context.router.push(`/feed/comment/${this.state.feed.feedId}`);
                }}
              />
            </div>
          </div>
           */}
        </div>
      </MuiThemeProvider>
    );
  }

  /*isInstalled() {
    let timeout, t = 1000, hasApp = true;
    setTimeout(() => {
      if (hasApp) {
          this.state.isInstall = true;
      } else {
          this.state.isInstall = false;
      }
      document.body.removeChild(ifr);
      this.setState(this.state);
    }, 2000);

    let t1 = Date.now();
    let ifr = document.createElement("iframe");    
    document.body.appendChild(ifr);    
    ifr.style.display = 'none';
    ifr.setAttribute('src', 'com.edaocha.www://');
    setTimeout(() => {
       var t2 = Date.now();
       if (!t1 || t2 - t1 < 600) {
           hasApp = false;
       }
    }, t);
  }*/

  getComment(){
    if(this.state.feed.commentNum > 0){
      return (<CommentBox feedId={this.state.feed.feedId} isCache={this.state.isCache}/>);
    }
  }

  getContentDOM() {
    let content = this.state.feed.content;

    // 表情
    content = Expression.buildDOM(content, (path) => {
      return (
        <img
          className={'ts-emotion'}
          key={guid()}
          src={path}
        />
      );
    });

    // At用户
    content = AtUser(content, (username) => {
      username = username.substr(1);
      return (<Link key={guid()} to={'/user/' + username}>@{username}</Link>);
    });

    return content;
  }

  getFollowDOM() {
    if (this.state.feed.followStatus) {
      return (
        <FlatButton
          label={'取消喜欢'}
          icon={<ActionFavorite />}
          style={{
            color: 'red',
          }}
          labelStyle={{
            color: '#333',
          }}
          onTouchTap={this.handleUnFollow.bind(this)}
        />
      );
    }
    return (
      <FlatButton
        label={'喜欢'}
        icon={<ActionFavoriteBorder />}
        style={{
          color: '#b1b1b1',
        }}
        labelStyle={{
          color: '#333',
        }}
        onTouchTap={this.handleFollow.bind(this)}
      />
    );
  }

  getDiggsDOM() {
    if (!this.state.feed.diggCount) {
      return null;
    }
    return (
      <div style={styles.diggsBox}>
        <div style={styles.diggsLeftAvatars}>
          {this.state.feed.users.map((user, key) => (<Avatar style={styles.diggAvatar} key={key} src={user.face} size={30} />))}
        </div>
        <div
          style={{
            display: 'inline-flex',
            alignItems: 'center',
            color: '#999',
          }}
          onTouchTap={() => {
            this.context.router.push(`/feed/digglist/${this.state.feed.feedId}`);
          }}
        >
          {this.state.feed.diggCount}人喜欢
          <NavigationChevronRight style={{marginTop: -2}} color={'#999'} />
        </div>
      </div>
    );
  }

  getStarDOM() {
    if (this.state.feed.starStatus) {
      return (
        <FlatButton
          label={'取消收藏'}
          icon={<ToggleStar />}
          style={{
            color: '#ff5b36'
          }}
          labelStyle={{
            color: '#333',
          }}
          onTouchTap={this.handleUnStar.bind(this)}
        />
      );
    }
    return (
      <FlatButton
        label={'收藏'}
        icon={<ToggleStarBorder />}
        style={{
          color: '#b1b1b1'
        }}
        labelStyle={{
          color: '#333',
        }}
        onTouchTap={this.handleStar.bind(this)}
      />
    );
  }

  handleStar() {
    if (checkLoginStatus()) {
      let load = loadTips('收藏中...');
      $.ajax({
        url: buildURL('feed', 'star'),
        type: 'POST',
        dataType: 'json',
        data: {feed_id: this.state.feed.feedId},
        timeout: 5000,
      })
      .done(function(data) {
        if (data.status == true) {
          this.state.feed.starStatus = true;
        } else {
          this.state.Snackbar.open = true;
          this.state.Snackbar.message = data.message;
        }
      }.bind(this))
      .fail(function() {
        this.state.Snackbar.open = true;
        this.state.Snackbar.message = '请求超时，请检查网络状态！';
      }.bind(this))
      .always(function() {
        load.hide();
        this.setState(this.state);
      }.bind(this));
    } else {
      this.context.router.push('/sign/up');
    }
  }

  handleUnStar() {
    if (checkLoginStatus()) {
      let load = loadTips('取消中...');
      $.ajax({
        url: buildURL('feed', 'unStar'),
        type: 'POST',
        dataType: 'json',
        data: {feed_id: this.state.feed.feedId},
      })
      .done(function(data) {
        if (data.status) {
          this.state.feed.starStatus = false;
        } else {
          this.state.Snackbar.open = true;
          this.state.Snackbar.message = data.message;
        }
      }.bind(this))
      .fail(function() {
        this.state.Snackbar.open = true;
        this.state.Snackbar.message = '请求超时，请检查网络状态！';
      }.bind(this))
      .always(function() {
        load.hide();
        this.setState(this.state);
      }.bind(this, load));
    } else {
      this.context.router.push('/sign/up');
    }
  }

  handleFollow() {
    if (checkLoginStatus()) {
      let load = loadTips('执行中...');
      $.ajax({
        url: buildURL('feed', 'digg'),
        type: 'POST',
        dataType: 'json',
        data: {feed_id: this.state.feed.feedId},
        timeout: 5000,
      })
      .done(function(data) {
        if (data.status == true) {
          this.state.feed.diggCount += 1;
          this.state.feed.followStatus = true;
        } else {
          this.state.Snackbar.open = true;
          this.state.Snackbar.message = data.message;
        }
      }.bind(this))
      .fail(function() {
        this.state.Snackbar.open = true;
        this.state.Snackbar.message = '请求超时，请检查网络状态！';
      }.bind(this))
      .always(function() {
        load.hide();
        this.setState(this.state);
      }.bind(this));
    } else {
      this.context.router.push('/sign/up');
    }
  }

  handleUnFollow() {
    if (checkLoginStatus()) {
      let load = loadTips('取消中...');
      $.ajax({
        url: buildURL('feed', 'unDigg'),
        type: 'POST',
        dataType: 'json',
        data: {feed_id: this.state.feed.feedId},
        timeout: 5000,
      })
      .done(function(data) {
        if (data.status == true) {
          this.state.feed.diggCount -= 1;
          this.state.feed.followStatus = false;
        } else {
          this.state.Snackbar.open = true;
          this.state.Snackbar.message = data.message;
        }
      }.bind(this))
      .fail(function() {
        this.state.Snackbar.open = true;
        this.state.Snackbar.message = '请求超时，请检查网络状态！';
      }.bind(this))
      .always(function() {
        load.hide();
        this.setState(this.state);
      }.bind(this));
    } else {
      this.context.router.push('/sign/up');
    }
  }

  gotoItunesApp() {
    window.location.href = 'https://www.aodou.com/index.php?app=h5#/downapp';
  }

  handleCloseBar() {
    this.state.isInstall = true;
    this.setState(this.state);
  }
}

const styles = {
  root: {
    paddingTop: 0,
    paddingBottom: 60,
  },
  feedBar: {
    boxSizing: 'border-box',
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    width: '100%',
    height: 60,
    padding: '0 12px',
    backgroundColor: '#fff',
    borderBottom: '1px solid #e5e5e5',
    overflow: 'hidden',
  },
  feedBarLogo: {
    width: 101,
    minWidth: 101,
    height: 40,
    overflow: 'hidden',
  },
  feedBarLogoImg: {
    width: '100%',
  },
  feedBarSlogan: {
    width: 107,
    minWidth: 107,
    height: 40,
    overflow: 'hidden',
  },
  feedBarSloganImg: {
    width: '100%',
  },
  username: {
    color: '#ff7300',
  },
  comment: {
    color: '#999999',
    marginTop: 9,
  },
  feedBarApp: {
    boxSizing: 'border-box',
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    width: '100%',
    height: 60,
    padding: 0,
    backgroundColor: '#666',
    overflow: 'hidden',
  },
  feedBarAppLogo: {
    color: '#fff',
  },
  feedBarAppLogoName: {
    display: 'block',
    fontSize: 16,
  },
  feedBarAppLogoInfo: {
    display: 'block',
    paddingTop: 2,
    fontSize: 12,
  },
  feedBarAppButton: {
    width: '87px',
    height: '34px',
    borderRadius: '4px',
  },

  feedContentBox: {
    paddingRight: 16,
    paddingLeft: 16,
  },
  diggsBox: {
    boxSizing: 'border-box',
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingRight: 16,
    paddingLeft: 16,
    width: '100%',
    height: 50,
    alignItems: 'center',
    borderTop: '1px solid #e5e5e5',
    borderBottom: '1px solid #e5e5e5',
    margin: '10px 0',
    backgroundColor: '#fff',
  },
  diggsLeftAvatars: {
    display: 'inline-flex',
    flexDirection: 'row',
    flexWrap: 'nowrap',
    flexGrow: 1,
    overflow: 'hidden',
  },
  diggAvatar: {
    margin: 4,
  },
  contentBox: {
    boxSizing: 'border-box',
    paddingRight: 12,
    paddingLeft: 12,
    width: '100%',
    borderBottom: '1px solid #e5e5e5',
    backgroundColor: '#fff',
    marginBottom: 10,
  },
  commentBox: {
    boxSizing: 'border-box',
    paddingRight: 12,
    paddingLeft: 12,
    paddingBottom: 60,
    width: '100%',
    borderTop: '1px solid #e5e5e5',
    backgroundColor: '#fff',
    position:'relative',
  },
  downAppBox: {
    clear: 'both',
    width: '100%',
    padding: '20px 0',
  },
  downAppButton: {
    width: '100%',
    height: 35,
    color: '#fff',
    borderRadius: '4px',
  },
  subHeader: {
    boxSizing: 'border-box',
    width: '100%',
    marginTop: 20,
    borderTop: '1px solid #e5e5e5',
    position: 'relative',
  },
  subHeaderSpan: {
    position: 'absolute',
    textAlign: 'center',
    fontSize: 14,
    width: '60px',
    height: '16px',
    lineHeight: '16px',
    top: -8,
    left: '50%',
    marginLeft: -30,
    color: '#999',
    backgroundColor: '#fff',
  },
  qCode: {
    width: '100%',
    boxSizing: 'border-box',
    textAlign: 'center',
    paddingTop: 35,
  },
  qCodeImg: {
    width: 125,
  },
  qCodeTitle: {
    paddingTop: 5,
    fontSize: 16,
    color: '#333',
  },
  qCodeInfo: {
    paddingTop: 5,
    fontSize: 12,
    color: '#999',
  },
  toolBar: {
    boxSizing: 'border-box',
    display: 'flex',
    flexDirection: 'row',
    alignItems: 'center',
    width: '100%',
    height: 50,
    position: 'fixed',
    bottom: 0,
    boxShadow: '0 0 1px 1px #ebebeb',
    zIndex: 9,
    backgroundColor: '#fff',
  },
  toolItem: {
    flexGrow: 1,
    textAlign: 'center',
  },
  hide: {
    display: 'none',
  },
}

Reader.contextTypes = {
    router: React.PropTypes.object.isRequired
};

export default Reader;

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
import CommentVideo from '../comment-video.jsx';
import FormatClientName from '../util/FormatClientName.jsx';
import Expression from '../util/expression.jsx';
import guid from '../util/guid.jsx';
import AtUser from '../util/at-user.jsx';
import checkLoginStatus from '../util/check-login-status';
import ShareTop from '../share-top';
import ShareBottom from '../share-bottom';
import ScrollTop from '../scroll-top';

import FeedContentText from '../feed/content/text';
import FeedContentImage from '../feed/content/image';
import FeedContentVideo from '../feed/content/video';//引进的位置修改了

import reader_logo from '../../app/images/icons/reader-logo.png';
import reader_slogan from '../../app/images/icons/reader-slogan.png';
import reader_qcode from '../../app/images/icons/reader-qcode.png';
import reader_icon from '../../app/images/icons/reader-icon.png';

import pathImg from '../../app/images/icons/path.png';

import duboImg from '../../app/images/icons/dubo.png';
import downImg from '../../app/images/icons/down.png';
import upImg from '../../app/images/icons/up.png';
class Video extends Component {
  constructor(props) {
    super(props);
    this.state = {
      video:{
        videoId: props.params.id,//视频ID
        parts:props.params.parts ? props.params.parts:1,//单集ID
        video_data_id:'',
        image_path:'',
        title: '',
        update_type:'',
        abstract:'',
        shortabstract:'',
        comment_count:'',
        users: [],
        user:{
          username: '',
          face: ''
        },
        comment_list:[],
        comment:[],
        followStatus: false,
        starStatus: false,
        images: [],
        video_info: [],
        video: null,
      },
      Snackbar: {
        open: false,
        message: '',
      },
      isCache: true,
      isInstall:false,
      isMore:true,
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
          url: buildURL('video', 'getVideoData'),//请求的地址
          type: 'POST',
          dataType: 'json',
          data: {video_id: this.state.video.videoId,
            parts:this.state.video.parts},
        })
        .done(function(data) {
          if (typeof data.status != undefined && data.status == false) {
            this.state.Snackbar.open = true;
            this.state.Snackbar.message = data.message;
          } else {
            this.state.video = data;
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
    const {images, ...video} = this.state.video;
    return (
        <MuiThemeProvider muiTheme={muiTheme}>
          <div style={styles.root}>
            <ShareTop isInstall={this.state.isInstall} />

            <div style={styles.contentBox}>
              <div style={{backgroundColor: '#fff', }}>

                <div style={{width:'100%',height: 'auto',}}>
                  <FeedContentVideo video={this.state.video.video_info} />
                </div>

                <div style={{paddingLeft:12,paddingRight:12,}}>
                  <div style={{marginTop:15,fontSize:20,fontWeight:'bold',}}>{this.state.video.title}</div>
                  <div style={{fontSize:12,color:"#666666",marginTop:15}}>
                    <Avatar
                        size={11}
                        src={duboImg}
                        style={{
                          width:30,
                          height:15,
                          borderRadius:'0',
                          marginRight:5,
                          backgroundColor:'#ffffff',
                          position: 'relative',
                          top: 1,
                        }}
                    />
                    {this.state.video.update_type}
                  </div>
                  <div style={{fontSize:16,color:'#333333',marginBottom:24,marginTop:15}}>
                    <div >
                      {this.state.video.abstract}

                    </div>

                  </div>
                </div>

              </div>
            </div>

            <div style={styles.commentBox}>
              {
                  this.getComment()
                  // this.state.video.comment_list.map((list,key)=>(
                  //     <div>
                  //         {list.type}
                  //         <div>
                  //             {list.ctime}
                  //         </div>
                  //         <div>{list.content}</div>
                  //         <div>{list.user_info.uname}</div>
                  //         <div>{list.user_info.remark}123</div>
                  //     </div>
                  //     ))
              }
              <ShareBottom />
            </div>
            <ScrollTop />
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
        </MuiThemeProvider>
    );
  }

  getComment(){
    if(this.state.video.comment_count > 0){
      return (<CommentVideo videoId={this.state.video.video_data_id} isCache={this.state.isCache}/>);
    }
  }


  gotoItunesApp() {
    window.location.href = 'https://www.aodou.com/index.php?app=h5#/downapp';
  }

  handleCloseBar() {
    this.state.isInstall = true;
    this.setState(this.state);
  }

  handleMoreInfo(flag) {
    this.state.isMore = flag;
    this.setState(this.state);
  }
}

const styles = {
  root: {
    width: '100%',
    minHeight: '100%',
    // height: '100%',
    boxSizing: 'border-box',
    // position: 'absolute',
    // display: 'flex',
    // flexDirection: 'column',
    // overflow: 'hidden',
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
    paddingRight: 0,
    paddingLeft: 0,
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
  show: {
    display: 'block',
  },
  hide: {
    display: 'none',
  },
}

Video.contextTypes = {
  router: React.PropTypes.object.isRequired
};

export default Video;

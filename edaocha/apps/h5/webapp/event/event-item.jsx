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
import FormatClientName from '../util/FormatClientName';
import timeImg from '../../app/images/icons/time.png';


class EventItem extends Component
{
  constructor(props) {
    super(props);
    this.state = {
      data: props.data,
      Snackbar: {
        open: false,
        message: '',
      },
      status:[{
        class:{
          backgroundColor:'#ff7300',
          color:'#ffffff',
          borderTopRightRadius: '15px',
          borderBottomRightRadius: '15px',
          height:30,
          fontSize:14,
          lineHeight:'30px',
          width:65,
          left: 0,
          top: 15,
          position:'absolute',
          zIndex:9,
          textIndent:'11px',
        },
        text:'未开始',
      },{
        class:{
          backgroundColor:'#ff5b36',
          color:'#ffffff',
          borderTopRightRadius: '15px',
          borderBottomRightRadius: '15px',
          height:30,
          fontSize:14,
          lineHeight:'30px',
          width:65,
          left: 0,
          top: 15,
          position:'absolute',
          zIndex:9,
          textIndent:'11px',
        },
        text:'进行中',
      },{
        class:{
          backgroundColor:'#cccccc',
          color:'#ffffff',
          borderTopRightRadius: '15px',
          borderBottomRightRadius: '15px',
          height:30,
          fontSize:14,
          lineHeight:'30px',
          width:65,
          left: 0,
          top: 15,
          position:'absolute',
          zIndex:9,
          textIndent:'11px',
        },
        text:'已结束',
      }],
    };
  }

  render() {
    return (
      <div style={styles.root} onTouchTap={() => {
            window.router.push(`/event/reader/${this.state.data.eid}`);
          }}>
        <div style={styles.eventbox}>
            <div style={this.state.status[this.state.data.status].class}>{this.state.status[this.state.data.status].text}</div>
            <div><img src={this.state.data.image} style={{width:'100%'}} /></div>
            <div style={styles.title}>{this.state.data.name}</div>
            <div style={styles.desc}>
                 <Avatar
                    size={11}
                    src={timeImg}
                    style={{
                      borderRadius:'0',
                      marginRight:4,
                      backgroundColor:'#ffffff',
                    }}
                  />
                  {this.state.data.stime} 至 {this.state.data.etime}
            </div>
        </div>
      </div>
    );
  }
  gotoItunesApp() {
    window.location.href = 'https://www.aodou.com/index.php?app=h5#/downapp';
  }
}

const styles = {
  root: {
    boxSizing: 'border-box',
    padding: '18px 0',
    margin: '0 12px',
    borderTop: 'solid 1px #e5e5e5',
  },
  eventbox : {
    clear: 'both',
    position: 'relative',
  },
  title:{

    fontWeight: 700,
    fontSize:18,
    marginTop:8,
  },
  desc:{
    color:'#999999',
    fontSize:12,
    marginTop:5,
  },
};

EventItem.defaultProps = {
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

export default EventItem;

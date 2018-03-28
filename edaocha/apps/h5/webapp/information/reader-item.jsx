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


class ReaderItem extends Component
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
      <div style={styles.root} onTouchTap={() => {
            window.router.push(`/information/reader/${this.state.data.id}/${this.state.data.cid}`);
            window.location.reload();
          }}>
          <div style={styles.ibox}>
              <div style = {styles.img}><img style={{width:'100%',height:80}} src={this.state.data.logo}/></div>
              <div style = {styles.info}>
                    <div style = {{fontSize:16,color:'#333333'}}>{this.state.data.subject}</div>
                    <div style = {styles.desc}>
                      <Avatar
                        size={11}
                        src={timeImg}
                        style={{
                          borderRadius:'0',
                          marginRight:4,
                          backgroundColor:'#ffffff',
                          position: 'relative',
                          top: 3,
                        }}
                      />
                      {this.state.data.ctime}
                    </div>
              </div>
          </div>
      </div>
    );
  }
}

const styles = {
  root: {
    boxSizing: 'border-box',
    padding: '15px 0',
    margin: '0 11px',
    borderBottom: 'solid 1px #e5e5e5',
  },
  ibox:{
    clear: 'both',
    height: '80px',
    width: '100%',
  },
  img:{
    width : '106px',
    height : '80px',
    float : 'left',
    overflow: 'hidden',
  },
  info :{
    paddingLeft: 125,

  },
  desc:{
    display: 'flex',
    fontSize:12,
    color: '#999999',
    marginTop: 15,
  }
};

export default ReaderItem;

import React, { Component } from 'react';
import {ListItem} from 'material-ui/List';
import Avatar from 'material-ui/Avatar';
import IconButton from 'material-ui/IconButton';
import FlatButton from 'material-ui/FlatButton';
import NavigationClose from 'material-ui/svg-icons/navigation/close';

import reader_logo from '../app/images/icons/reader-logo.png';
import reader_slogan from '../app/images/icons/reader-slogan.png';
import reader_qcode from '../app/images/icons/reader-qcode.png';
import reader_icon from '../app/images/icons/reader-icon.png';

class ShareTop extends Component
{
  constructor(props) {
    super(props);
    this.state = {
      isInstall: props.isInstall,
      botn : props.botn ? false : true,
    };
  }
  render() {
    return(
        <div>
          { this.bbDom()}
          <div style={!this.state.isInstall ? styles.feedBarApp : styles.hide}>
            <ListItem
              disabled={true}
              leftAvatar={
                <Avatar 
                  src={reader_icon}
                  size={40}
                  style={{
                    borderRadius: 'none',
                    backgroundColor: 'transparent',
                    top: '10px',
                    left: '12px',
                  }}
                />
              }
              primaryText={
                <div style={styles.feedBarAppLogo}>
                  <span style={styles.feedBarAppLogoName}>奥豆</span>
                  <span style={styles.feedBarAppLogoInfo}>一个娱乐社交APP</span>
                </div>
              }
              style={{
                padding: '12px 0px 12px 62px',
              }}
            />
            <div>
              <FlatButton
                style={styles.feedBarAppButton}
                label="立即下载"
                backgroundColor="#ff7300"
                hoverColor="#ff7300"
                rippleColor="#fff"
                labelStyle={{
                  color: '#fff',
                  fontSize: 14,
                }}
                onTouchTap={this.gotoItunesApp.bind(this)}
              />
              <IconButton
                iconStyle={{
                  width: 15,
                  height: 15,
                  backgroundColor: '#a0a1a7',
                  borderRadius: '50%',
                }}
                style={{
                  padding: 0,
                }}
                onTouchTap={this.handleCloseBar.bind(this)}
              >
                <NavigationClose color={'#fff'} />
              </IconButton>
            </div>
          </div>
        </div>
    );
  }
  gotoItunesApp() {
    window.location.href = 'https://www.aodou.com/index.php?app=h5#/downapp';
  }
  bbDom(){
    if(this.state.botn){
      return(<div 
          style={this.state.isInstall ? styles.feedBar : styles.hide}
          onTouchTap={() => {
            window.location.href = 'com.edaocha.www://';
          }}
        >
        <div style={styles.feedBarLogo}>
          <img style={styles.feedBarLogoImg} src={reader_logo} />
        </div>
        <div style={styles.feedBarSlogan}>
          <img style={styles.feedBarSloganImg} src={reader_slogan} />
        </div>
      </div>);
    }
  }
  handleCloseBar() {
    this.state.isInstall = true;
    this.setState(this.state);
  }
}

const styles = {
  feedBar: {
    boxSizing: 'border-box',
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    width: '100%',
    height: 60,
    padding: '0 12px',
    backgroundImage:'linear-gradient(315deg,#ff5b36,#ff9000)',
    overflow: 'hidden',
  },
  feedBarLogo: {
    width: 75,
    minWidth: 75,
    height: 20,
    overflow: 'hidden',
  },
  feedBarLogoImg: {
    width: '100%',
  },
  feedBarSlogan: {
    width: 124,
    minWidth: 124,
    height: 32,
    overflow: 'hidden',
  },
  feedBarSloganImg: {
    width: '100%',
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
  hide: {
    display: 'none',
  },
}

export default ShareTop;

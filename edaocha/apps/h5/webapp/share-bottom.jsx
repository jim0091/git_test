import React, { Component } from 'react';
import {ListItem} from 'material-ui/List';
import Avatar from 'material-ui/Avatar';
import IconButton from 'material-ui/IconButton';
import FlatButton from 'material-ui/FlatButton';
import NavigationChevronRight from 'material-ui/svg-icons/navigation/chevron-right';

import reader_logo from '../app/images/icons/reader-logo.png';
import reader_slogan from '../app/images/icons/reader-slogan.png';
import reader_qcode from '../app/images/icons/reader-qcode.png';
import reader_icon from '../app/images/icons/reader-icon.png';

class ShareBottom extends Component
{
  render() {
    return(
        <div>
          <div style={styles.downAppBox}>
              <FlatButton
                  style={styles.downAppButton} 
                  label="下载奥豆，看更多趣味内容"
                  labelPosition="before"
                  backgroundColor="#ff7300"
                  hoverColor="#ff7300"
                  rippleColor="#fff"
                  labelStyle={{
                    paddingRight: 0,
                  }}
                  icon={<NavigationChevronRight />}
                  onTouchTap={this.gotoItunesApp.bind(this)}
              />
          </div>
        </div>
    );
  }
  gotoItunesApp() {
    window.location.href = 'https://www.aodou.com/index.php?app=h5#/downapp';
  }
}

const styles = {
  downAppBox: {
    clear: 'both',
    padding: '20px 12px',
    backgroundColor: '#ffffff',
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
}

export default ShareBottom;

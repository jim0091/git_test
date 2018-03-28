import React, {Component} from 'react';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import NavigationChevronLeft from 'material-ui/svg-icons/navigation/chevron-left';
import {List, ListItem} from 'material-ui/List';
import Subheader from 'material-ui/Subheader';
import Divider from 'material-ui/Divider';
import IconButton from 'material-ui/IconButton';
import Avatar from 'material-ui/Avatar';
import Snackbar from 'material-ui/Snackbar';

import AppBar from '../AppBar.jsx';


class DiggList extends Component
{
  constructor(props) {
    super(props);
    this.state = {
      feed:{
        feedId: props.params.feedId,
        diggCount: 0,
        users: [],
      }
    };
  }

  componentDidMount() {
    let load = loadTips('加载中...');
    $.ajax({
      url: buildURL('feed', 'feedDiggList'),
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
    return (
      <MuiThemeProvider muiTheme={muiTheme}>
        <div style={styles.root}>
          <AppBar
            title={'点赞列表'}
            iconElementLeft={
              <IconButton onTouchTap={goBack}>
                <NavigationChevronLeft />
              </IconButton>
            }
          />
        { /* 点赞的人列表 */
          this.getDiggsDOM()
        }
        </div>
      </MuiThemeProvider>
    );
  }

  getDiggsDOM() {
    if (!this.state.feed.diggCount) {
      return null;
    }
    const lenth = this.state.feed.users.length-1;
    return (
      <div style={styles.diggsBox}>
        <List style={styles.list}>
          {this.state.feed.users.map((user, key) => {
            return ([
              <ListItem
                style={(lenth!=key)?styles.item:{}}
                primaryText={user.username}
                leftAvatar={<Avatar src={user.face} />}
                onTouchTap={() => {
                  window.router.push(`/user/${user.uid}`);
                }}
              />
            ]);
          })}
        </List>
      </div>
    );
  }
}

const styles = {
  root: {
    paddingTop: 50,
    backgroundColor: '#fff',
  },
  list: {
    width: '100%',
  },
  item: {
    boxShadow: '0 1px 0 #ebebeb',
  },
  diggsBox: {
    boxSizing: 'border-box',
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingRight: 16,
    paddingLeft: 16,
    width: '100%',
    alignItems: 'center',
  },
}

export default DiggList;
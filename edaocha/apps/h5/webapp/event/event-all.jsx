import React, {Component} from 'react';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import ReactDOM from 'react-dom';

import ScrollTop from '../scroll-top';

import guid from '../util/guid';
import EventItem from './event-item';
import request from 'superagent';

class EventAll extends Component
{
  constructor(props) {
    super(props);
    this.state = {
      eventList : [],
      Snackbar: {
        open: false,
        message: '',
      },
      isCache: true,
    };    
    this.max = 0;
    this.min = 0;
    this.scroll = true;
  }

  componentDidMount() {
    let load = loadTips('加载中...');
    $.ajax({
      url: buildURL('event', 'getEventList'),
      type: 'POST',
      dataType: 'json',
      data: {
        max: this.max
      },
    })
    .done(function(data) {
      if (typeof data.status != undefined && data.status == false) {
        this.state.Snackbar.open = true;
        this.state.Snackbar.message = data.message;
      } else {
        this.state.eventList = data;
        if (data.length >= 1) {
          this.min = data[data.length - 1].eid;
        }
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

    document.body.scrollTop = 0;
    this.scrollHandle = this.handleScroll.bind(this);
    // 滚动事件
    document.addEventListener('scroll', this.scrollHandle);
  }

  render() {
    return (
    	<div ref={'box'} style={{backgroundColor:'#ffffff'}}>
          {this.state.eventList.map((data) => <EventItem key={guid()} data={data} />)}
          <ScrollTop />
      </div>
    );
  }
  gotoItunesApp() {
    window.location.href = 'https://www.aodou.com/index.php?app=h5#/downapp';
  }

  handleAppendEventItem(data) {
    let divDOM = document.createElement('div');
    this.refs.box.appendChild(divDOM);
    ReactDOM.render(
      (<MuiThemeProvider muiTheme={muiTheme}>
        <EventItem data={data} />
      </MuiThemeProvider>),
      divDOM
    );
  }

  handleScroll() {
    if (this.scroll == true) {
      let top = document.body.scrollTop + document.body.clientHeight;
      let height = document.body.scrollHeight;
      let data = [];
      data['min'] = this.min;
      if (height - top < 500) {
        this.scroll = false;
        request
          .post(buildURL('event', 'getEventList'))
          .field(data)
          .end((error, ret) => {
            if (!error) {
              ret.body.forEach((data) => {
                this.handleAppendEventItem(data);
                this.min = data.eid;
              });
            }
            setTimeout(function() {
              this.scroll = true;
            }.bind(this), 1000);
          })
        ;
      }
    }
  }
}
export default EventAll;


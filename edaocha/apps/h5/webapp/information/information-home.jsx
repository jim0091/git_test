import React, {Component} from 'react';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import {Tabs, Tab} from 'material-ui/Tabs';
import ReactDOM from 'react-dom';

import ShareTop from '../share-top';
import guid from '../util/guid';
import InformationItem from './information-item';
import request from 'superagent';
import ScrollTop from '../scroll-top';

class InformationHome extends Component
{
  constructor(props) {
    super(props);
    this.state = {
      nav : [{
        name : '全部',
        url : '/home/information',
      }],
      informationList : [],
      cid : props.params.cid ? props.params.cid : 0,
    };
    this.min = 0;
    this.scroll = true;
  }

  componentDidMount() {
    let load = loadTips('加载中...');
    $.ajax({
      url: buildURL('information', 'getInformationNav'),
      type: 'POST',
      dataType: 'json',
    })
    .done(function(data) {
      if (typeof data.status != undefined && data.status == false) {
        this.state.Snackbar.open = true;
        this.state.Snackbar.message = data.message;
      } else {
        this.state.nav = data;
      }
    }.bind(this))
    .fail(function() {
      this.state.Snackbar.open = true;
      this.state.Snackbar.message = '请检查网络～';
    }.bind(this))
    .always(function() {
      load.hide();
      this.setState(this.state);
      this.refetch();
    }.bind(this));

    document.body.scrollTop = 0;
    this.scrollHandle = this.handleScroll.bind(this);
    // 滚动事件
    document.addEventListener('scroll', this.scrollHandle);
  }

  refetch(){
      let load = loadTips('加载中...');
      let field = [];
      field['max'] = 0;
      field['cid'] = this.state.cid;
      request
        .post(buildURL('information', 'getInformationList'))
        .field(field)
        .end((error, ret) => {
          if (!error) {
            if (ret.body.length >= 1) {
              this.state.informationList = ret.body;
              this.min = ret.body[ret.body.length - 1].id;
              this.setState(this.state);
            }           
          }
          setTimeout(function() {
            this.scroll = true;
            load.hide();
          }.bind(this), 1000);
        })
      ; 
  }
  
  handleScroll() {
    if (this.scroll == true) {
      let top = document.body.scrollTop + document.body.clientHeight;
      let height = document.body.scrollHeight;
      let data = [];
      data['min'] = this.min;
      data['cid'] = this.state.cid;
      if (height - top < 500) {
        this.scroll = false;
        request
          .post(buildURL('information', 'getInformationList'))
          .field(data)
          .end((error, ret) => {
            if (!error && ret.body.length >= 1) {
              this.min = ret.body[ret.body.length - 1].id;
              this.state.informationList =  this.state.informationList.concat(ret.body);
              this.setState(this.state);
            }
            setTimeout(function() {
              this.scroll = true;
            }.bind(this), 1000);
          })
        ;
      }
    }
  }


  navDom(data){    
      const { children, location, ...props } = this.props;
      const { pathname } = location;
      return(
        <div style={styles.navroot}>
          <div style={data.id == this.state.cid ? styles.on : styles.out} onTouchTap={() => {
            if(pathname != ('/home/information/'+data.id)){
              this.context.router.replace('/home/information/'+data.id);              
              this.state.cid = data.id;              
              this.refetch(data.id);
              this.setState(this.state);
            }
          }}>{data.name}</div>
        </div>
      );
  }
  render() {
    return (
      <MuiThemeProvider muiTheme={muiTheme}>
        <div style={styles.root}>
            <div style={styles.navs} id="rows">
               {this.state.nav.map((data) => {return this.navDom(data)})} 
            </div>
            <div ref={'box'} style={{backgroundColor:'#ffffff'}}>
                {
                    this.state.informationList.map((data) => <InformationItem key={guid()} data={data} />)
                }
            </div>
            <ScrollTop />
        </div>
      </MuiThemeProvider>
    );
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
  navroot: {
    boxSizing: 'border-box',
    padding: '10px 12px',
  },
  navs:{
    display:'flex',
    height:'40px',
    width: '100%',
    boxSizing: 'border-box',
    backgroundColor: '#ffffff',
    borderBottom: 'solid 1px #e5e5e5',
    borderTop: 'solid 1px #e5e5e5',
    overflowY:'auto',
  },  
  on:{
    backgroundColor:'#ff5b36',
    fontSize:14,
    width:'45px',
    height : '20px',
    textAlign: 'center',
    color: '#fff',
    borderRadius: '10px',
  },
  out:{
    fontSize:14,
    width:'45px',
    height : '20px',
    textAlign: 'center',
    color:'#666666',
  }
};

InformationHome.contextTypes = {
  router: React.PropTypes.object.isRequired
};
export default InformationHome;

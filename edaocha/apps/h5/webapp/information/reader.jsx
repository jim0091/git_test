import React, {Component} from 'react';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import {ListItem} from 'material-ui/List';
import Avatar from 'material-ui/Avatar';
import IconButton from 'material-ui/IconButton';
import FlatButton from 'material-ui/FlatButton';
import NavigationClose from 'material-ui/svg-icons/navigation/close';
import NavigationChevronLeft from 'material-ui/svg-icons/navigation/chevron-left';
import NavigationChevronRight from 'material-ui/svg-icons/navigation/chevron-right';
import Snackbar from 'material-ui/Snackbar';

import AppBar from '../AppBar.jsx';

import InformationContent from './content/content';
import ShareTop from '../share-top';
import ShareBottom from '../share-bottom';
import ScrollTop from '../scroll-top';

import reader_logo from '../../app/images/icons/reader-logo.png';
import reader_slogan from '../../app/images/icons/reader-slogan.png';
import reader_qcode from '../../app/images/icons/reader-qcode.png';
import reader_icon from '../../app/images/icons/reader-icon.png';

import weixinImg from '../../app/images/icons/weixin.png';
import weiboImg from '../../app/images/icons/weibo.png';
import qqImg from '../../app/images/icons/qq.png';
import kongjianImg from '../../app/images/icons/kongjian.png';

import pathImg from '../../app/images/icons/path.png';
import timeImg from '../../app/images/icons/time.png';
import ReaderItem from './reader-item';
import guid from '../util/guid';
import request from 'superagent';

class Event extends Component
{
  constructor(props) {
    super(props);
    this.state = {
      information:{
        id: props.params.id,
        subject : '',
      },
      Snackbar: {
        open: false,
        message: '',
      },
        cid : props.params.cid ? props.params.cid : 0,
        list:[],//
      isCache: true,
    };
      this.min = 0;//
  }

  componentDidMount() {
    let load = loadTips('加载中...');
    $.ajax({
      url: buildURL('information', 'getInformationInfo'),
      type: 'POST',
      dataType: 'json',
      data: {id: this.state.information.id},
    })
    .done(function(data) {
      if (typeof data.status != undefined && data.status == false) {
        this.state.Snackbar.open = true;
        this.state.Snackbar.message = data.message;
      } else {
        this.state.information = data;
      }
    }.bind(this))
    .fail(function() {
      this.state.Snackbar.open = true;
      this.state.Snackbar.message = '请检查网络～';
    }.bind(this))
    .always(function() {
      load.hide();
      this.setState(this.state);
        this.refetch();//
    }.bind(this));

    // let script = document.createElement('script');
    // script.src = 'http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion=1560244125';
    // document.body.appendChild(script);
  }

    refetch(){
        let load = loadTips('加载中...');
        let field = [];
        field['max'] = 0;
        field['cid'] = this.state.cid;
        field['limit'] = 4;
        request
            .post(buildURL('information', 'getInformationList'))
            .field(field)
            .end((error, ret) => {
                if (!error) {
                    if (ret.body.length >= 1) {
                        this.state.list = ret.body;
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

  render() {
    return (
    	<MuiThemeProvider muiTheme={muiTheme}>    	
    	    <div style={styles.root}>
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
                      window.router.push(`/home/information/0`);
                    }}
                  />
                  正文
                </div>
                <div style={styles.context}>
                    <div style={{fontSize:22,color:'#333333',lineHeight:'32px'}}>{this.state.information.subject}</div>
                    <div style={{fontSize:12,color:'#999999',marginTop:20}}>作者:{this.state.information.name}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;来源:{this.state.information.copyfrom}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;发布于{this.state.information.ctime}</div>
                    <InformationContent content={this.state.information.content} />
                    <div style={{position:'relative',height:'24px',width:'100%',textAlign:'center'}}>
                        <div style={{width:'100%',height:'1px',backgroundColor:'#e5e5e5',position:'absolute',top:11,left:0,zIndex:10,}}></div>
                        <div style={{width:'70px',height:'12px',backgroundColor:'#ffffff',position:'absolute',zIndex:20,left:'50%',marginLeft:'-35px',color:'#999999'}}>END</div>
                    </div>
                    {/*<div className="bdsharebuttonbox" style={{marginTop:30,marginBottom:30,textAlign:'center',lineHeight:'40px',display:'flex',justifyContent:'center'}}>*/}
                      {/*<div style={{height:'40px',display:'inline-block'}}>分享到</div>*/}
                      {/*<a style={styles.weixin} href="javascript:;" className="bds_weixin" data-cmd="weixin" title="分享到微信"></a>*/}
                      {/*<a style={styles.sina} href="javascript:;" className="bds_tsina" data-cmd="tsina" title="分享到新浪微博"></a>*/}
                      {/*<a style={styles.qq} href="javascript:;" className="bds_sqq" data-cmd="sqq" title="分享到QQ好友"></a>*/}
                      {/*<a style={styles.kongjian} href="javascript:;" className="bds_qzone" data-cmd="qzone" title="分享到QQ空间"></a>*/}
                    {/*</div>*/}
                    <div id="initShare"></div>
                    <div style={{backgroundColor:'#f9f9f9',border:'solid #f2f2f2 1px',borderRadius:'2.5px',padding:10,lineHeight:'18px',fontSize:12,marginBottom:10,textAlign:'justify',color:'#666666'}}>
                       <div>版权申明：本文章部分来自网络，如有侵权，请联系我们（service#aodou.com，发送时把#改成@），我们收到后立即删除，谢谢！</div>
                       <div>特别注意：本站转载文章言论不代表本站观点，本站所提供的摄影照片，插画，设计作品，如需使用，请与原作者联系，版权归原作者所有。</div>
                    </div>
                    <div style={{marginBottom:15, }}>
                        <div style={styles.relation}>相关文章</div>
                        <div>
                            {
                                this.state.list.map((data) => <ReaderItem key={guid()} data={data} />)
                            }
                        </div>
                    </div>
                    <div style={{padding:'0 10px 50px',backgroundColor: '#fff',}}>
                        <ShareBottom />
                    </div>
                </div>
                <div style={{position:'fixed',bottom:0,left:0,zIndex:999,width:'100%'}}>
                  <ShareTop  isInstall={this.state.isInstall} botn={true}/>
                </div>
                <ScrollTop />
          </div>
    	</MuiThemeProvider>
    );
  }
  gotoItunesApp() {
	   window.location.href = 'https://www.aodou.com/index.php?app=h5#/downapp';
  }
}

const styles = {
	root: {
	    paddingTop: 0,
      backgroundColor: '#ffffff',
	},
  context:{
      padding:'20px 12px',
  },
  weixin:{
    width:'40px',
    height:'40px',
    margin: '0px',
    marginLeft : '20px',
    backgroundPosition : '0px 0px',
    float: 'none',
    display: 'inline-block',
    paddingLeft : '0px',
    backgroundImage : 'url('+weixinImg+')',
    backgroundSize:'40px 40px',
  },
  qq:{
    width:'40px',
    height:'40px',
    margin: '0px',
    marginLeft : '20px',
    backgroundPosition : '0px 0px',
    float: 'none',
    display: 'inline-block',
    paddingLeft : '0px',
    backgroundImage : 'url('+qqImg+')',
    backgroundSize:'40px 40px',
  },
  sina:{
    width:'40px',
    height:'40px',
    margin: '0px',
    marginLeft : '20px',
    backgroundPosition : '0px 0px',
    float: 'none',
    display: 'inline-block',
    paddingLeft : '0px',
    backgroundImage : 'url('+weiboImg+')',
    backgroundSize:'40px 40px',
  },
  kongjian:{
    width:'40px',
    height:'40px',
    margin: '0px',
    marginLeft : '20px',
    backgroundPosition : '0px 0px',
    float: 'none',
    display: 'inline-block',
    paddingLeft : '0px',
    backgroundImage : 'url('+kongjianImg+')',
    backgroundSize:'40px 40px',
  },
    relation:{
        fontSize:18,
        color:'#333333',
        marginTop:30,
        paddingBottom:15,
        marginBottom:4,
        borderBottom:'1px solid #e5e5e5',
    }
};
export default Event;


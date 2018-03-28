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

import EventContent from './content/content';
import ShareTop from '../share-top';
import ShareBottom from '../share-bottom';
import ScrollTop from '../scroll-top';

import reader_logo from '../../app/images/icons/reader-logo.png';
import reader_slogan from '../../app/images/icons/reader-slogan.png';
import reader_qcode from '../../app/images/icons/reader-qcode.png';
import reader_icon from '../../app/images/icons/reader-icon.png';

import event_icon1 from '../../app/images/icons/event-icon1.png';
import event_icon2 from '../../app/images/icons/event-icon2.png';
import event_icon3 from '../../app/images/icons/event-icon3.png';
import event_icon4 from '../../app/images/icons/event-icon4.png';
import zan from '../../app/images/icons/zan.png';
import tc_icon from '../../app/images/icons/tabcomment.png';

import pathImg from '../../app/images/icons/epath.png';

import girlImg from '../../app/images/icons/girl_01.png';
import boyImg from '../../app/images/icons/boy_01.png';

class Event extends Component
{
  constructor(props) {
    super(props);
    this.state = {
      event:{
        EventId: props.params.eventId,
        image: '',
        name: '',
        location: '',
        time: '',
        price: '',
        sponsor: '',
        starcount: 0,
        staruser: [],
      },
      Snackbar: {
        open: false,
        message: '',
      },
      isCache: true,
    };
  }

  componentDidMount() {
    let load = loadTips('加载中...');
    $.ajax({
      url: buildURL('event', 'getEventInfo'),
      type: 'POST',
      dataType: 'json',
      data: {event_id: this.state.event.EventId},
    })
    .done(function(data) {
      if (typeof data.status != undefined && data.status == false) {
        this.state.Snackbar.open = true;
        this.state.Snackbar.message = data.message;
      } else {
        this.state.event = data;
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
              <Avatar
                size={20}
                src={pathImg}
                style={{
                  backgroundColor:'transparent',
                  width:'10px',
                  height: '17px',
                  position:'absolute',
                  zIndex:10,
                  top: '15px',
                  left: '15px',
                }}
                onTouchTap={() => {
                  window.router.push(`/home/event`);
                }}
              />
            	<div style={styles.imagebox} onTouchTap={this.handleShowImages.bind(this, 0)}>
            		<img style={styles.img} src={this.state.event.image}  />
            	</div>
            	<div style={styles.eventBox}>
            		<h1 style={styles.eventH1}>{this.state.event.name}</h1>
            		<div style={styles.btnbox}>
            			<FlatButton
			                style={styles.eventBtn}
			                label="收藏活动"
			                backgroundColor="#fff"
			                hoverColor="#fff"
			                rippleColor="#ff5b36"
			                labelStyle={{
			                  color: '#ff5b36',
			                  fontSize: 14,
			                }}
			                onTouchTap={this.gotoItunesApp.bind(this)}
			              />
			            <FlatButton
			                style={styles.eventBtnR}
			                label="立即报名"
			                backgroundColor="#fff"
			                hoverColor="#fff"
			                rippleColor="#ff5b36"
			                labelStyle={{
			                  color: '#ff5b36',
			                  fontSize: 14,
			                }}
			                onTouchTap={this.gotoItunesApp.bind(this)}
			              />
            		</div>
            		<div style={styles.connerBox}>
            			<div style={styles.item}>
            				<div style={styles.itemLeft}>
	            				<ListItem
					              disabled={true}
					              leftAvatar={
					                <Avatar 
					                  src={event_icon1}
					                  style={{
					                    borderRadius: 'none',
					                    backgroundColor: 'transparent',
					                    top: '16px',
					                    left: '12px',
					                    width:12,
					                    height:14,
					                  }}
					                />
					              }
					              primaryText={
					                <div style={{lineHeight: '24px'}}>地点</div>
					              }
					              style={{
					                padding: '12px 0px 12px 40px',
					              }}
					            />
				            </div>
				            <div style={styles.itemRight}>{this.state.event.location}</div>
            			</div>
            			<div style={styles.item}>
            				<div style={styles.itemLeft}>
	            				<ListItem
					              disabled={true}
					              leftAvatar={
					                <Avatar 
					                  src={event_icon2}
					                  style={{
					                    borderRadius: 'none',
					                    backgroundColor: 'transparent',
					                    top: '17px',
					                    left: '12px',
					                    width:14,
					                    height:14,
					                  }}
					                />
					              }
					              primaryText={
					                <div style={{lineHeight: '24px'}}>时间</div>
					              }
					              style={{
					                padding: '12px 0px 12px 40px',
					              }}
					            />
				            </div>
				            <div style={styles.itemRight}>{this.state.event.time}</div>
            			</div>
            			<div style={styles.item}>
            				<div style={styles.itemLeft}>
	            				<ListItem
					              disabled={true}
					              leftAvatar={
					                <Avatar 
					                  src={event_icon3}
					                  style={{
					                    borderRadius: 'none',
					                    backgroundColor: 'transparent',
					                    top: '17px',
					                    left: '12px',
					                    width:14,
					                    height:14,
					                  }}
					                />
					              }
					              primaryText={
					                <div style={{lineHeight: '24px'}}>费用</div>
					              }
					              style={{
					                padding: '12px 0px 12px 40px',
					              }}
					            />
				            </div>
				            <div style={styles.itemRight}>{this.state.event.price}</div>
            			</div>
            			<div style={styles.itemLast}>
            				<div style={styles.itemLeft}>
	            				<ListItem
					              disabled={true}
					              leftAvatar={
					                <Avatar 
					                  src={event_icon4}
					                  style={{
					                    borderRadius: 'none',
					                    backgroundColor: 'transparent',
					                    top: '17px',
					                    left: '12px',
					                    width:14,
					                    height:14,
					                  }}
					                />
					              }
					              primaryText={
					                <div style={{lineHeight: '24px'}}>主办方</div>
					              }
					              style={{
					                padding: '12px 0px 12px 40px',
					              }}
					            />
				            </div>
				            <div style={styles.itemRight}>{this.state.event.sponsor}</div>
            			</div>
            		</div>
            	</div>
            	{ /* 点赞的人列表 */
	              this.getStarDOM()
	            }
	            <EventContent content={this.state.event.content} />
              <ScrollTop />
	            { /* 评论列表 */
	            	this.getCommentDOM()
	            }
	            <div style={{padding:'0 10px 75px',backgroundColor: '#fff',}}>
	              	<ShareBottom />
	            </div>
              <div style={{position:'fixed',bottom:0,left:0,zIndex:999,width:'100%'}}>
                <ShareTop  isInstall={this.state.isInstall} botn={true}/>
              </div>
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
  gotoItunesApp() {
	 window.location.href = 'https://www.aodou.com/index.php?app=h5#/downapp';
  }
  handleCloseBar() {
    this.state.isInstall = true;
    this.setState(this.state);
  }

  getStarDOM() {
    return (
      <div style={styles.starBox}>
      	<div style={{fontSize:16,textIndent:'12px',height:'50px',lineHeight:'50px',borderBottom:'solid 1px #e5e5e5'}}>感兴趣的小伙伴</div>
        <div style={styles.starB}>
          <div style={styles.starC}>
              <Avatar 
                src={girlImg}
                style={{
                  borderRadius: '50%',
                  backgroundColor: 'transparent',
                  width: 60,
                  height: 60,
                }}
              />
              <div style={{color:'#999999',fontSize:14}}>{this.state.event.starcount.girl}个美女陪你玩</div>
          </div>
          <div style={styles.starC}>
              <Avatar 
                src={boyImg}
                style={{
                  borderRadius: '50%',
                  backgroundColor: 'transparent',
                  width: 60,
                  height: 60,
                }}
              />
              <div style={{color:'#999999',fontSize:14}}>{this.state.event.starcount.boy}个帅哥陪你玩</div>
          </div>
        </div>
    </div>
    );
  }

  handleShowImages(index) {
    if (this.state.show == true) {
      this.state.show = false;
    } else {
      this.state.show = true;
      this.state.index = index ? index : 0
    }
    this.setState(this.state);
  }

  getCommentDOM() {
  	if (!this.state.event.commentNum) {
      return null;
    }
    return (
    	<div style={styles.commentBox}>
    		<div style={styles.CommentTitle}>评论
    			<div style={styles.CommentNum}>共<span style={styles.count}>{this.state.event.commentNum}</span>条评论</div>
    		</div>
    		<div style={styles.commentMap}>
    		{this.state.event.comment.map((comment,key) => (
    			 <div style={styles.commentlist}>
    			 	<Avatar 
	                  src={comment.userface}
	                  style={{
	                    borderRadius: '50%',
	                    backgroundColor: 'transparent',
	                    width: 40,
	                    height: 40,
	                    float: 'left',
	                    marginRight: 12,
	                  }}
	                />
	                <div style={styles.comment}>
						<p style={styles.username}>{comment.name}</p>
                        <p style={styles.date}>{comment.time}&nbsp;&nbsp;&nbsp;&nbsp;{comment.from}</p>
                        <p style={styles.content}>{comment.content}</p>
                        <div style={comment.tabcomment.length ? styles.tabcomment : styles.hide}>
                        	<Avatar 
			                  src={tc_icon}
			                  style={{
			                    borderRadius: 'none',
			                    backgroundColor: 'transparent',
			                    width: 22,
			                    height: 11,
			                    top: -11,
			                    left: 10,
			                    position: 'absolute',
			                  }}
			                />
							{comment.tabcomment.map((tabcomment, key) => (
								<p style={styles.tbc}>
										<span style={{color: '#4986d2'}}>{tabcomment.uname}</span>&nbsp;
										回复&nbsp;<span style={{color: '#4986d2'}}>{tabcomment.toname}</span>
										：{tabcomment.content}
								</p>
							))}
							<div style={comment.tabcomment.length == 2 ? { color : '#999', textAlign : 'center', fontSize: 14} : styles.hide}> 
								 <FlatButton
								 	  style={{color:'#999999'}}
				                      label="展开所有评论"
				                      labelPosition="before"
				                      backgroundColor="#f9f9f9"
				                      hoverColor="#999999"
				                      rippleColor="#999999"
				                      labelStyle={{
				                        paddingRight: 0,
				                      }}
				                      icon={<NavigationChevronRight style={{transform: 'rotate(90deg)'}} />}
				                      onTouchTap={this.gotoItunesApp.bind(this)}
				                  />
             				</div>
                        </div>
                        <div style={styles.zan}>
                        	{this.state.event.diggCount ? this.state.event.diggCount : '赞'}
                        	&nbsp;
                        	<Avatar 
			                  src={zan}
			                  style={{
			                    borderRadius: 'none',
			                    backgroundColor: 'transparent',
			                    width: 15,
			                    height: 15,
			                    marginLeft: 5,
			                  }}
			                />
                        </div>
					</div>
    			 </div>
	         ))}
    		</div>
    	</div>
    );
  }
}


const styles = {
	root: {
	    paddingTop: 0,
	},
	imagebox: {
    width: '100%',
    height: 0,
    overflow: 'hidden',
    paddingBottom: '56.25%',
  },
  img: {
    width: '100%',
  },
  eventBox: {
  	paddingLeft: 12,
  	paddingRight: 12,
  	paddingTop: 15,
  	backgroundColor: '#fff',
  	borderBottom: '1px solid #e5e5e5',
  	marginButtom: 9,
  },
  eventH1: {
  	fontSize: 24,
  	fontWeight: 700,
  },
  btnbox: {
  	marginTop: 22,
  	marginBottom: 20,
  },
  eventBtn: {
  	width: '47%',
  	borderRadius: '4px',
  	border: '1px solid #ff7300',
  },
  eventBtnR: {
  	width: '47%',
  	borderRadius: '4px',
  	border: '1px solid #ff7300',
  	marginLeft: '6%',
  },
  connerBox: {
  	clear: 'both',
  },
  item: {
  	clear: 'both',
  	borderBottom: '1px solid #e5e5e5',
  	overflow: 'hidden',
  },
  itemLast: {
  	clear: 'both',
  	overflow: 'hidden',
  },
  itemLeft: {
  	float : 'left',
  	width: 100,
  },
  itemRight: {
  	color: '#999',
  	float: 'left',
  	paddingTop: 12,
  	paddingBottom: 12,
  	lineHeight: '24px',
  	width: 'calc(100% - 100px)',
  },
  hide: {
    display: 'none',
  },
  starBox: {
    width: '100%',
    borderTop: '1px solid #e5e5e5',
    borderBottom: '1px solid #e5e5e5',
    marginTop: 10,
    backgroundColor: '#fff',
  },
  starB:{      
    boxSizing: 'border-box',
    display: 'flex',    
    alignItems: 'center',
  },
  starC:{
    width:'50%',
    textAlign:'center',
    padding:'20px 0',
  },
  commentBox: {
  	marginTop: 10,
  	backgroundColor: '#fff',
  },
  CommentTitle: {
  	borderBottom: '1px solid  #e5e5e5',
    width: '100%',
    lineHeight: '50px',
    height: 50,
    textIndent: '1em',
  },
  CommentNum: {
  	float: 'right',
  	paddingRight: 13,
  	fontSize: 14,
  	color: '#999',
  },
  count: {
  	color: '#ff5b36',
  },
  comment: {
  	paddingLeft: 50,
  	paddingTop: 2,
  	flexDirection: 'row',
  	justifyContent: 'space-between',
  	color: '#333',
  },
  username: {
    boxSizing: 'border-box',
    flexGrow: 1,
    paddingRight: 12,
    fontSize: 14,
    whiteSpace: 'nowrap',
    textOverflow: 'ellipsis',
    overflow: 'hidden',
    color: '#ff7300',
    marginBottom: 7,
  },
  date: {
    whiteSpace: 'nowrap',
    fontSize: 12,
    color: '#999',
  },
  commentMap: {
  	padding: '0 10px',
  },
  zan: {
  	position: 'absolute',
  	right: 10,
  	top: 28,
  	color: '#999',
  	fontSize: 14,
  },
  commentlist:{
  	fontSize: 16,
  	lineHeight: '16px',
  	clear: 'both',
  	paddingBottom: 17,
  	paddingTop: 20,
  	borderBottom: "#e5e5e5 1px dashed",
  	position: 'relative',
  },
  commentlistLast:{
  	fontSize: 16,
  	lineHeight: '16px',
  	clear: 'both',
  	paddingBottom: 17,
  	paddingTop: 20,
  	position: 'relative',
  },
  content: {
  	paddingTop: 10,
  },
  tabcomment: {
  	marginTop: 20,
  	backgroundColor: '#f9f9f9',
  	border: '1px solid #e5e5e5',
  	padding: '12px 11px 0',
  	position: 'relative',
  },
  tbc: {
  	fontSize: 14,
  	lineHeight: '24px',
  },
  tbcColor:{
  	color: '#4986d2',
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
};
export default Event;


import React from 'react';
import ReactDOM from 'react-dom';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import Divider from 'material-ui/Divider';
import {Link} from 'react-router';
import ListItem from 'material-ui/List/ListItem';
import Avatar from 'material-ui/Avatar';
import IconButton from 'material-ui/IconButton';
import FlatButton from 'material-ui/FlatButton';
import FontIcon from 'material-ui/FontIcon';
import Dialog from 'material-ui/Dialog';
import TextField from 'material-ui/TextField';
import Snackbar from 'material-ui/Snackbar';
import Expression from './util/expression';
import AtUser from './util/at-user';
import guid from './util/guid';

class CommentItem extends React.Component
{

	constructor(props) {
		super(props);
		this.state = {
			dialog: {
				open: false,
				floatingLabelText: '请输入评论内容！',
				error: '',
			},
			Snackbar: {
				open: false,
				message: '',
			}
		};
	}

	render() {
		let content = this.props.comment.content;
		content = this.formatExpression(content);
		content = this.formatAtUser(content);
		return (
			<ListItem
				leftAvatar={<Avatar src={this.props.comment.user.anonymous_icon} />}
				primaryText={
					<div style={styles.comment}>
						<p style={styles.username}>{this.props.comment.user.anonymous_name}</p>
                        {/*<p style={styles.date}>{this.props.comment.time}&nbsp;&nbsp;&nbsp;&nbsp;{this.props.comment.client_type}</p>*/}
					</div>
				}
				secondaryText={content}
				secondaryTextLines={2}
			>
			</ListItem>
		);
	}

	formatAtUser(content) {
    return AtUser(content, (anonymous_name) => {
        anonymous_name = anonymous_name.substr(1);
      return (<Link key={guid()} style={styles.link} to={'/user/' + anonymous_name}>@{anonymous_name}</Link>);
    });
  }

  formatExpression(content) {
    return Expression.buildDOM(content, (path) => (
      <img
        src={path}
        key={guid()}
        style={styles.expression}
      />
    ));
  }

	handleSendReply() {
		let content = this.refs.postCommentTextField.getValue();
    let defat = '回复@' + this.props.comment.user.anonymous_name + '：';
    let curMid = this.props.comment.user.uid;
    if (curMid == TS.MID) {
      this.state.dialog.error = '不能回复自己!';
    } else if (!content || (content == defat)) {
			this.state.dialog.error = '评论内容不能为空！';
		} else if (content.length > 140) {
			this.state.dialog.error = '评论内容字符不能超过140个！';
		} else {
			this.state.dialog.error = '';
			this.state.dialog.open = false;
			let load = loadTips('发送中...');
			$.ajax({
				url: buildURL('comment', 'postFeedComment'),
				type: 'POST',
				dataType: 'json',
				data: {
					feed_id: this.props.feedId,
					to_cid: this.props.comment.comment_id,
					content: content
				},
			})
			.done(function(data) {
				if (data.status == true) {
					this.state.Snackbar.open = true;
					this.state.Snackbar.message = '回复成功！';
                    this.handleAppendCommentItem(this.props);
				} else {
					this.state.Snackbar.open = true;
					this.state.Snackbar.message = data.message;
				}
			}.bind(this))
			.fail(function() {
				this.state.Snackbar.open = true;
				this.state.Snackbar.message = '哎哟，好像忘了不给力哦！😫';
			}.bind(this))
			.always(function() {
				load.hide();
				this.setState(this.state);
			}.bind(this));
		}
		this.setState(this.state);
	}

    handleAppendCommentItem(props) {
        this.props = props;
        let content = this.refs.postCommentTextField.getValue();
            content = this.formatExpression(content);
            content = this.formatAtUser(content);
        let divDOM = document.createElement('div');
            divDOM.style.width = '100%';
            divDOM.style.height = 'auto';
        $('#topcomment').next('div').prepend(divDOM);
        ReactDOM.render(
          (<MuiThemeProvider muiTheme={muiTheme}>
            <div>
            <ListItem
                leftAvatar={<Avatar src={this.props.comment.user.anonymous_icon} />}
                primaryText={
                    <div style={styles.comment}>
                        <span style={styles.username}>{this.props.comment.user.anonymous_name}</span>
                        {/*<span style={styles.date}>{this.props.comment.time}</span>*/}
                    </div>
                }
                secondaryText={content}
                secondaryTextLines={2}
                rightIconButton={
                    <IconButton onTouchTap={() => {
                        this.state.dialog.open = true;
                        this.setState(this.state);
                    }}>
                        <FontIcon className="material-icons" color={'#b2b2b2'}>reply</FontIcon>
                    </IconButton>
                }
            />
            <Divider inset={true} />
            </div> 
            </MuiThemeProvider>),
          divDOM
        );
  }
}

CommentItem.defaultProps = {
	comment: {
		user: {
			uid: 0,
			username: '系统',
			face: 'http://thinksns.io/data/upload/2016/0512/12/5734012da6b354328c95_200_200.jpg'
		},
		time: '2016-05-21 12:00',
		comment_id: 0,
		content: '内容'
	},
	feedId: 0,
};

const styles = {
	expression: {
    width: 14,
    height: 14,
  },
  link: {
    color: '#ff5b36',
    textDecoration: 'blink',
  },
  comment: {
  	width: '100%',
  	flexDirection: 'row',
  	justifyContent: 'space-between',
  	color: '#333',
  },
  username: {
    boxSizing: 'border-box',
    flexGrow: 1,
    paddingRight: 12,
    fontSize: 16,
    whiteSpace: 'nowrap',
    textOverflow: 'ellipsis',
    overflow: 'hidden',
    color: '#ff7300',
    marginBottom: 7,
	marginTop:14,
  },
  date: {
    whiteSpace: 'nowrap',
    fontSize: 14,
    color: '#999',
  }
};

export default CommentItem;

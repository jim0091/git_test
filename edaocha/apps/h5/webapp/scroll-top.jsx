import React, {Component} from 'react';
import Avatar from 'material-ui/Avatar';

import topImg from '../app/images/icons/top.png';

class ScrollTop extends Component
{
	constructor(props) {
		super(props);
		this.state = {
			scroll : false,
		};
	}
	componentDidMount() {
	    document.body.scrollTop = 0;
	    this.scrollHandle = this.handleScroll.bind(this);
	    // 滚动事件
	    document.addEventListener('scroll', this.scrollHandle);
	}

	handleScroll() {
		if (document.body.scrollTop > 200) {
		  	this.state.scroll = true;
		}else{
			this.state.scroll = false;
		}
		this.setState(this.state);
	}
	render() {
	    return (
			<div style={this.state.scroll ?  styles.show : styles.hide} 
	                onTouchTap={() => {
	                  document.body.scrollTop = 0;
	                }}>
	          	<Avatar
	                size={20}
	                src={topImg}
	                style={{
	                  backgroundColor:'transparent',
	                  borderRadius:'unset',
	                  margin: '10px 0 0 10px',
	                }}
	            />
	    	</div>
	    );
	}
}

const styles = {
	hide : {
		display: 'none',
	},
	show : {
		display: 'block',
		width:'40px',
		height: '40px',
		position: 'fixed',
		zIndex: 999,
		right: '12px',
		bottom: '72px',
		backgroundColor: 'rgba(255,91,54,0.9)',
		borderRadius:'50%',
	}
};

export default ScrollTop;
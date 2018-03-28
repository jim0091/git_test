import React, {Component} from 'react';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import {Tabs, Tab} from 'material-ui/Tabs';

import ShareTop from '../share-top';
class Home extends Component
{
  constructor(props) {
    super(props);
    this.state = {
      isInstall:false,
      fixed : false,
    };
    this.fixed = {
      value : false
    };
  }

  componentDidMount() {
    document.body.scrollTop = 0;
    this.scrollHandle = this.handleScroll.bind(this);
    // 滚动事件
    document.addEventListener('scroll', this.scrollHandle);
  }

  handleScroll() {
    if (document.body.scrollTop > 108) {
        this.fixed.value = true;
    }else{
        this.fixed.value = false;
    }
  }

  render() {
    const { children, location, ...props } = this.props;
    const { pathname } = location;
    return (
      <MuiThemeProvider muiTheme={muiTheme}>
        <div style={styles.root}>
          <ShareTop  isInstall={this.state.isInstall}/>
          <Tabs
            style={styles.Tabs}
            tabItemContainerStyle={{
              
            }}
            textColor={'#333333'}
            selectedTextColor={'#ff7300'}
            inkBarStyle={{
              backgroundColor: '#ff5b36',
            }}
            value={this.props.children.props.route.path}
          >
            <Tab
              label={'推荐'}
              value={'all'}
              onActive={() => {
                if (pathname != '/home/all') {
                  this.context.router.replace('/home/all');
                }
              }}
              textColor={'#333333'}
              selectedTextColor={'#ff7300'}
              style={{
                fontSize:18,
              }}
            />
            <Tab
              label={'活动'}
              value={'event'}
              onActive={() => {
                if (pathname != '/home/event') {
                  this.context.router.replace('/home/event');
                }
              }}
              textColor={'#333333'}
              selectedTextColor={'#ff7300'}
              style={{
                fontSize:18,
              }}
            />
            <Tab
              label={'文章'}
              value={'information(/:cid)'}
              onActive={() => {
                if (pathname != '/home/information') {
                  this.context.router.replace('/home/information');
                }
              }}
              textColor={'#333333'}
              selectedTextColor={'#ff7300'}
              style={{
                fontSize:18,
              }}
            />
          </Tabs>
          {this.props.children}
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
  Tabsfixed:{
    position: 'fixed',
    zIndex:999,
    top: 0,
    left: 0,
    width:'100%'
  },
  Tabs: {
    boxShadow: '#ebebeb 0 1px 4px 0',
    overflow: 'hidden',
    display: 'fiex',
    top: 60,
    left: 0,
    width: '80%',
    padding:'0 10%',
    maxHeight: 50,
    zIndex: 10,
    backgroundColor: '#ffffff',
  },
  hide:{
    display:'none',
  }
};

Home.contextTypes = {
  router: React.PropTypes.object.isRequired
};

export default Home;

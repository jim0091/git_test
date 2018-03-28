import React, {Component} from 'react';

class InformationNav extends Component
{
  constructor(props) {
    super(props);
    this.state = {
      data: props.data,
      cid : props.cid,
    };
  }

  render() {
    return (
      
    );
  }
  gotoItunesApp() {
    window.location.href = 'https://www.aodou.com/index.php?app=h5#/downapp';
  }
}

const styles = {
  root: {
    boxSizing: 'border-box',
    padding: '10px 15px',
  },
};

export default InformationNav;

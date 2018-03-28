import React, { Component } from 'react';


class DownApp extends Component
{
  constructor(props) {
    super(props);
  }
  componentDidMount() {
    let script = document.createElement('script');
    script.src = "https://www.aodou.com/apps/h5/app/src/util/downapp.js";
    document.body.appendChild(script);
  }

  render() {
    return (<div>正在跳转中...</div>);
  }
}

export default DownApp;
import wxApi from '/static/library/wx-api'
import config from '/config/config'
import wxRequest from '/static/library/wx-request'
App({
  globalData: {
    openId: null,
    userInfo: null
  },
  onLaunch() {
    let query = {
      appkey: 'b1d2c31cd603a8766a20cafac642639b',
      appsecret: '307d97687b99a5724085fe3911856221',
      // limit: 1,
      lang: 'zh-cn'
      // cached: -1
    }
    wxRequest.post(
      config.urls.getSession,
      query
    ).then(res => {
      if (res.code === 100) {
        const token = res.response.token
        wx.setStorageSync('token', token)
      }
    })
    let login = wxApi.login();
    let getUserInfo = wxApi.getUserInfo()
    // 登录并进行openId的获取
    login().then(res => {
      // if (res.code) {
      //   console.log('res.code:', res.code)
      //   wxRequest.post(
      //     config.urls.getOpenId, {
      //       appid: 'wx15a3b07359b8ab28',
      //       secret: '3ad332abd5909344b8f15985930177d8',
      //       // secret: '888949428bf47ded5ac91a2799c1d3c4',
      //       js_code: res.code,
      //       grant_type: 'authorization_code'
      //     }
      //   ).then(res => {
      //     console.log('获取openid', res)
      //     wx.setStorageSync('openId', res.openid);
      //   })
      // }
    })
    // 获取用户信息并进行获取证件信息
    getUserInfo().then(res => {
      let that = this;
      // let userInfo = JSON.parse(res.rawData);
      // let openId = wx.getStorageSync('openId');
      // console.log('[成功] openId', openId)
      // wx.setStorageSync('userInfo', userInfo);
      // if (openId) {
      //   wxRequest.post(
      //     config.urls.getPaperwork, {
      //       openid: openId,
      //     }
      //   ).then(res => {
      //     console.log('[成功] 获取证件信息', res)
      //     let paperwork = Object.assign({}, res.data.success)
      //     wx.setStorageSync('paperwork', paperwork)
      //   })
      // }
    })

  },
});
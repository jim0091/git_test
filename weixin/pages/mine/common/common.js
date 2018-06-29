import wxApi from '../../../static/library/wx-api'
import config from '../../../config/config'
import wxRequest from '../../../static/library/wx-request'
Page({
  data: {
    target: '',
    targetTitle: '',
  },
  onLoad (option) {
    // console.log('option:', option)
    this.setTargetTitle(option.target)
    this.setData({
      target: option.target
    })
  },
  onShow () {

  },
  setTargetTitle (e) {
    console.log('e:', e)
    let targetTitle = ''
    if (e == 'feekback') {
      wx.setNavigationBarTitle({
        title: '帮助反馈'
      })
      // targetTitle = '帮助反馈'
    } else if (e == 'contack') {
      wx.setNavigationBarTitle({
        title: '联系我们'
      })
      // targetTitle = '联系我们'
    } else if (e == 'agreement') {
      wx.setNavigationBarTitle({
        title: '使用协议'
      })
      // targetTitle = '使用协议'
    }
    // this.setData({
    //   targetTitle: targetTitle
    // })
  },
  backHandler() {
    wxApi.switchTab('../../mine/index/index')
  },

});

import wxApi from '../../../static/library/wx-api'
import config from '../../../config/config'
import wxRequest from '../../../static/library/wx-request'

Page({
  data: {
    token: '',
    stopicList: [],
    inputValue: '',
    historyCompanyList: []
  },
  onLoad () {
  },
  onShow () {
    wx.setStorageSync('searchType', 'company')
    let token = wx.getStorageSync('token')
    let historyCompanyList = []
    if (wx.getStorageSync('historyCompanyList')) {
      historyCompanyList = wx.getStorageSync('historyCompanyList')
    }
    this.setData({
      token: token,
      historyCompanyList: historyCompanyList
    })
    this.getStopicList()
  },
  //请求热门舆情接口
  getStopicList () {
    const token = this.data.token
    const query = {
      token,
      type: 'new',
      page: 1,
      rows: 20,
      top: 'day',
      lang: 'zh-cn',
      cached: 0
    }
    wxRequest.get(
      config.urls.getStopic,
      query
    ).then(res => {
      if (res.code == 100) {
        let stopicList = res.response.list,
          topThreeStopList = []
        wx.setStorageSync('stopicList', stopicList)
        for (let index = 0; index < stopicList.length; index++) {
          if (index < 3) {
            topThreeStopList.push(stopicList[index])
          }
        }
        this.setData({
          stopicList: topThreeStopList
        })
      }
    })
  },
  //多关系查询
  searchRelationHandler () {
    wx.showToast({
      title: '该功能未开发，敬请期待',
      icon: 'none',
      duration: 2000
    })
  },
  //输入
  inputHandle (e) {
    this.setData({
      inputValue: e.detail.value
    })
    // console.log('inputValue:', this.data.inputValue)
    this.searchHandle()
  },
  inputIngHandle(e) {
    this.setData({
      inputValue: e.detail.value
    })
  },
  //搜索
  searchHandle () {
    if (this.data.inputValue) {
      let historyCompanyList = this.data.historyCompanyList
      historyCompanyList.unshift(this.data.inputValue)
      for (let index = 1; index < historyCompanyList.length; index++) {
        if (historyCompanyList[index] == this.data.inputValue) {
          historyCompanyList.splice(index, 1)
        }
      }
      wx.setStorageSync('historyCompanyList', historyCompanyList)
      wx.navigateTo({
        url: '../secondSearch/secondSearch?input=' + this.data.inputValue
      })
    } else {
      wx.showToast({
        title: '输入搜索框不能为空',
        icon: 'none',
        duration: 800
      })
    }
  }
});

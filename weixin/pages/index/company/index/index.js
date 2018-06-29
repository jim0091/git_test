import wxApi from '../../../../static/library/wx-api'
import config from '../../../../config/config'
import wxRequest from '../../../../static/library/wx-request'
Page({
  data: {
    token: '',
    // company: '小米有限公司',
    companyId: null,
    companyDetail: {},
    isFocus: false,
    focusCompanyList: [], //公司关注列表
  },
  onLoad(option) {
    console.log('option:', option)
    if (option.companyId) {
      this.setData({
        token: wx.getStorageSync('token'),
        companyId: option.companyId
      })
      this.getCompanyInfo()
    }
  },
  onShow() {
    let focusCompanyList
    if (wx.getStorageSync('focusCompanyList')) {
      focusCompanyList = wx.getStorageSync('focusCompanyList')
    }
    this.setData({
      focusCompanyList: focusCompanyList
    })
  },
  //公司基本信息数据
  getCompanyInfo() {
    const cid = this.data.companyId
    let query = {
      token: this.data.token,
      cid: cid,
      lang: '',
      cached: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getCompanyInfo,
      query
    ).then(res => {
      if (res.code === 100) {
        let targetCompany = res.response
        targetCompany.tag = targetCompany.tag[0]
        this.setData({
          companyDetail: res.response
        })
        wx.setStorageSync('companyDetail', res.response)
        wx.hideLoading()
      }
    }).catch(err => {
      console.log('err')
      wx.showToast({
        title: err,
        icon: 'none',
        duration: 2000
      })
    })
  },
  backHandler() {
    wxApi.switchTab('../../../index/index/index')
  },
  //关注公司
  focusCompanyHandle(e) {
    const fullName = e.currentTarget.dataset.fullname
    const companyId = e.currentTarget.dataset.companyid
    let focusCompanyList = this.data.focusCompanyList
    let targetObject = { companyId, fullName }
    const isFocus = !this.data.isFocus
    this.setData({
      isFocus: isFocus
    })
    if (isFocus) {
      focusCompanyList.unshift(targetObject)
      for (let index = 1; index < focusCompanyList.length; index++) {
        if (focusCompanyList[index].fullName == targetObject.fullName) {
          focusCompanyList.splice(index, 1)
        }
      }
      wx.setStorageSync('focusCompanyList', focusCompanyList)
      wx.showToast({
        title: '关注成功',
        icon: 'success',
        duration: 800
      })
    } else {
      for (let index = 0; index < focusCompanyList.length; index++) {
        if (focusCompanyList[index].fullName == targetObject.fullName) {
          focusCompanyList.splice(index, 1)
        }
      }
      wx.setStorageSync('focusCompanyList', focusCompanyList)
      wx.showToast({
        title: '已取消关注',
        icon: 'success',
        duration: 800
      })
    }
  }
});

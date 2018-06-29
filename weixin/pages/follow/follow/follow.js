import wxApi from '../../../static/library/wx-api'
import config from '../../../config/config'
import wxRequest from '../../../static/library/wx-request'
Page({
  data: {
    target: null,   // 1=>公司 2=>舆情
    companyList: [
      { name: '小米科技有限公司' },
      { name: '小米保险有限公司' },
      { name: '万科企业有限公司' }
    ],
    sensationList: [
      { name: '中华名族' },
      { name: '政治教育' },
      { name: '爱国情操' }
    ],
    //删除图标
    showDelete: false,
    focusCompanyList: [], //公司关注列表
    focuSentimentList: [],   //关注舆情
  },
  onLoad (option) {
    console.log('target:', option.target)
    console.log('company:', option.company)
    if(option.company) {
      let targetCompanyList = this.data.companyList
      targetCompanyList.push({name: option.company})
      this.setData({
        companyList: targetCompanyList
      })
    }
    this.setData({
      target: option.target
    })

  },
  onShow() {
    let focusCompanyList = []
    let focuSentimentList = []
    if (wx.getStorageSync('focusCompanyList')) {
      focusCompanyList = wx.getStorageSync('focusCompanyList')
    }
    if (wx.getStorageSync('focuSentimentList')) {
      focuSentimentList = wx.getStorageSync('focuSentimentList')
    }
    this.setData({
      focusCompanyList: focusCompanyList,
      focuSentimentList: focuSentimentList
    })
  },
  //根据浏览记录进入公司
  chooseCompanyHandle (e) {
    const companyId = e.currentTarget.dataset.companyid
    wx.navigateTo({
      url: '../../index/company/index/index?companyId=' + companyId
    })
  },
  //根据关注记录进入舆情
  chooseSentitmentHandle (e) {
    let target
    if (e.currentTarget.dataset.topicid) {
      target = e.currentTarget.dataset.topicid
      wx.navigateTo({
        url: '../../index/sentimentContent/sentimentContent?topic_id=' + target
      })
    } else {
      target = e.currentTarget.dataset.url
      console.log('url:', target)
      wx.navigateTo({
        url: '../../index/sentimentContent/sentimentContent?url=' + target
      })
    }
  },
  addTargetHandle () {
    if (this.data.target == 1) {
      wxApi.navigateTo('../../index/search/search?type=company')
    } else {
      wxApi.navigateTo('../../index/search/search?type=sentiment')
    }
  },
  deleteHandler () {
    this.setData({
      showDelete: !this.data.showDelete
    })
  },
  deleteTargetHandler (e) {
    console.log('index,target', e.currentTarget.dataset.index, e.currentTarget.dataset.target)
    let targetIndex = e.currentTarget.dataset.index,
      target = e.currentTarget.dataset.target,
      _this = this,
      targetList
    if (target == 1) {
      targetList = this.data.focusCompanyList
    } else {
      targetList = this.data.focuSentimentList
    }
    wx.showModal({
      title: '提示',
      content: '确定删除该选择吗？',
      confirmText: '删除',
      success: function (res) {
        if (res.confirm) {
          console.log('用户点击确定')
          for (let index = 0; index < targetList.length; index++) {
            if (index == targetIndex) {
              targetList.splice(index, 1)
              if (target == 1) {
                _this.setData({
                  focusCompanyList: targetList
                })
                wx.setStorageSync('focusCompanyList', targetList)
              } else {
                _this.setData({
                  focuSentimentList: targetList
                })
              }
            }
          }
          wx.showToast({
            title: '删除成功',
            icon: 'success',
            duration: 2000
          })
        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })
  }

});

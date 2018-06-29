import wxApi from '../../../static/library/wx-api'
import config from '../../../config/config'
import wxRequest from '../../../static/library/wx-request'
const wxParser = require('../../../wxParser/index')
Page({
  data: {
    token: '',
    palceholderValue: '',
    inputValue: '',
    latestList: [
      { name: '万科企业股份有限公司' },
      { name: '小米有限公司' }
    ],
    browseList: [
      { name: '小米有限公司' },
      { name: '北京小米保险经纪有限公司' }
    ],
    historyList: [
      { name: '万科' },
      { name: '英国' }
    ],
    target: null,
    type: '',
    page: 1,
    row: 20,
    total_page: null,
    stopicList: [], //舆情列表
    historyCompanyList: [], //公司搜索历史
    browseCompanyList: [], //公司浏览历史
    historySentimentList: [], //舆情搜索历史
    isNoSearchHistory: false,   //显示无公司搜索记录
    isNoBrowseHistory: false,   //显示无公司浏览记录
    isNoSentimentHistory: false,   //显示无舆情搜索记录
  },
  onLoad(option) {
    if (option.type == 'company') {
      this.setData({
        palceholderValue: '请输入企业名、注册号、品牌等关键词'
      })
      wx.setNavigationBarTitle({
        title: '公司搜索'
      })
    } else {
      this.setData({
        palceholderValue: '请输入舆情关键词'
      })
      wx.setNavigationBarTitle({
        title: '舆情搜索'
      })
    }
    this.setData({
      // type: 'sentiment'
      type: option.type
    })
    wx.setStorageSync('searchType', option.type)
  },

  onShow() {
    let historyCompanyList = []
    let browseCompanyList = []
    let historySentimentList = []
    if (wx.getStorageSync('historyCompanyList') && wx.getStorageSync('historyCompanyList').length > 0) {
      historyCompanyList = wx.getStorageSync('historyCompanyList')
      this.setData({
        isNoSearchHistory: false,
        historyCompanyList: historyCompanyList
      })
    } else {
      this.setData({
        isNoSearchHistory: true
      })
    }
    if (wx.getStorageSync('browseCompanyList') && wx.getStorageSync('browseCompanyList').length > 0) {
      browseCompanyList = wx.getStorageSync('browseCompanyList')
      this.setData({
        isNoBrowseHistory: false,
        browseCompanyList: browseCompanyList
      })
      this.add()
      console.log('浏览记录：', browseCompanyList)
    } else {
      this.setData({
        isNoBrowseHistory: true
      })
    }
    if (wx.getStorageSync('historySentimentList') && wx.getStorageSync('historySentimentList').length > 0) {
      console.log('historySentimentList:', wx.getStorageSync('historySentimentList'))
      historySentimentList = wx.getStorageSync('historySentimentList')
      this.setData({
        isNoSentimentHistory: false,
        historySentimentList: historySentimentList
      })
    } else {
      this.setData({
        isNoSentimentHistory: true
      })
    }
    this.setData({
      token: wx.getStorageSync('token')
    })
    this.getStopicList(this.data.page)
  },
  add() {
    let browseCompanyList = this.data.browseCompanyList;
    // console.log('list', browseCompanyList)
    let richText = [];
    for (let num = 0; num < browseCompanyList.length; num++) {
      wxParser.parse({
        bind: 'richText[' + num + ']',
        html: browseCompanyList[num].fullName,
        target: this,
      })
    }
    // console.loge('list', browseCompanyList)
  },
  deleteRecordHandler(e) {
    console.log('target:', e.currentTarget.dataset.target)
    const target = e.currentTarget.dataset.target
    let _this = this,
      targetList
    if (target == 'latest') {
      targetList = _this.data.historyCompanyList
    } else {
      targetList = _this.data.browseCompanyList
    }
    wx.showModal({
      title: '提示',
      content: '确定清空该记录吗？',
      confirmText: '删除',
      success: function (res) {
        if (res.confirm) {
          console.log('用户点击确定')
          targetList.splice(0, targetList.length)
          if (target == 'latest') {
            _this.setData({
              historyCompanyList: targetList
            })
            wx.setStorageSync('historyCompanyList', [])
          } else {
            _this.setData({
              browseCompanyList: targetList
            })
            wx.setStorageSync('browseCompanyList', [])
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
  },
  //根据浏览记录进入公司
  chooseCompanyHandle(e) {
    const companyId = e.currentTarget.dataset.companyid
    wx.navigateTo({
      url: '../company/index/index?companyId=' + companyId
    })
  },
  //点击搜索历史记录
  chooseSearchHandle(e) {
    console.log('e:', e)
    let target = e.currentTarget.dataset.target
    this.setData({
      inputValue: target
    })
    this.searchHandle()
  },
  //获取实时舆情
  getStopicList(page) {
    const token = this.data.token
    const query = {
      token,
      type: 'new',
      page,
      rows: this.data.row,
      top: 'day',
      lang: 'zh-cn',
      cached: 0
    }
    wxRequest.get(
      config.urls.getStopic,
      query
    ).then(res => {
      if (res.code == 100) {
        let stopicList = res.response.list
        for (let stopic of stopicList) {
          stopic.create_time = stopic.create_time.slice(5, 16)
        }
        this.setData({
          stopicList: this.data.stopicList.concat(res.response.list),
          page: res.response.page,
          total_page: res.response.total_page
        })
      }
    })
  },
  //上拉加载
  onReachBottom: function () {
    console.log('上拉加载')
    let _this = this
    const page = _this.data.page + 1
    if (page <= _this.data.total_page) {
      _this.setData({
        page: _this.data.page + 1
      })
      _this.getStopicList(page)
    }
  },
  //删除舆情搜索历史
  deleteSentimentHandler() {
    let _this = this
    wx.showModal({
      title: '提示',
      content: '确定清空该记录吗？',
      confirmText: '删除',
      success: function (res) {
        if (res.confirm) {
          console.log('用户点击确定')
          _this.setData({
            historySentimentList: []
          })
          wx.setStorageSync('historySentimentList', [])
          wx.showToast({
            title: '清空成功',
            icon: 'success',
            duration: 2000
          })
        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })
  },
  //输入
  inputHandle(e) {
    this.setData({
      inputValue: e.detail.value
    })
    this.searchHandle()
  },
  inputIngHandle(e) {
    this.setData({
      inputValue: e.detail.value
    })
  },
  //搜索
  searchHandle() {
    console.log('搜索')
    if (this.data.inputValue) {
      if (this.data.type === 'company') {
        let historyCompanyList = this.data.historyCompanyList
        historyCompanyList.unshift(this.data.inputValue)
        for (let index = 1; index < historyCompanyList.length; index++) {
          if (historyCompanyList[index] == this.data.inputValue) {
            historyCompanyList.splice(index, 1)
          }
        }
        wx.setStorageSync('historyCompanyList', historyCompanyList)
      } else {
        let historySentimentList = this.data.historySentimentList
        historySentimentList.unshift(this.data.inputValue)
        for (let index = 1; index < historySentimentList.length; index++) {
          if (historySentimentList[index] == this.data.inputValue) {
            historySentimentList.splice(index, 1)
          }
        }
        wx.setStorageSync('historySentimentList', historySentimentList)
      }
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

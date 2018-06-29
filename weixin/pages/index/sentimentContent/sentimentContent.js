import wxApi from '../../../static/library/wx-api'
import config from '../../../config/config'
import wxRequest from '../../../static/library/wx-request'
const wxParser = require('../../../wxParser/index')
Page({
  data: {
    topicId: null,
    token: '',
    topicView: {},
    showAll: false,
    showTitle: '查看更多',
    spreadList: [
      {
        title: '新浪网',
        time: '2017-06-16  04:20:00',
      },
      {
        title: '搜索网',
        time: '2017-06-16  04:20:00',
      },
      {
        title: '网易网',
        time: '2017-06-16  04:20:00',
      }
    ],
    url: '', //舆情详情链接
    focuSentimentList: [],   //关注舆情
    isFocus: false
  },
  onLoad (option) {
    console.log('option:', option)
    // const topicId = option.topic_id
    if (option && option.topic_id) {
      this.setData({
        // topicId: 91471812
        topicId: option.topic_id
      })
    } else if (option && option.url) {
      this.setData({
        url: option.url
      })
    }
  },
  onShow () {
    let focuSentimentList = []
    if (wx.getStorageSync('focuSentimentList') && wx.getStorageSync('focuSentimentList').length > 0) {
      focuSentimentList = wx.getStorageSync('focuSentimentList')
    }
    this.setData({
      token: wx.getStorageSync('token'),
      focuSentimentList: focuSentimentList
    })
    if (this.data.topicId) {
      this.getTopicView()
    } else if (this.data.url) {
      this.getSearchStopic()
    }
  },
  getTopicView () {
    let query = {
      token: this.data.token,
      id: this.data.topicId,
      lang: 'zh-cn',
      cache: 0
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getStopicview,
      query
    ).then(res => {
      if (res.code == 100) {
        console.log('富文本：')
        this.setData({
          topicView: res.response
        })
        wxParser.parse({
          bind: 'richText',
          html: res.response.content,
          target: this,
        })
        wx.hideLoading()
      }
    })
  },
  //通过搜索接口的舆情详情
  getSearchStopic () {
    let query = {
      token: this.data.token,
      id: '',
      url: this.data.url,
      type: 'News',
      lang: 'zh-cn',
      cache: 0
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getSearchStopic,
      query
    ).then(res => {
      if (res.code == 100) {
        let result = res.response
        console.log('result', result)
        let topicView = {}
        topicView.topic_title = result.title
        topicView.create_time = result.create_time
        topicView.content = result.content
        topicView.source_name = result.source_name
        topicView.url = result.url
        console.log('topicView:', topicView)
        this.setData({
          topicView: topicView
        })
        wxParser.parse({
          bind: 'richText',
          html: result.content,
          target: this,
        })
        wx.hideLoading()
      }
    })
  },
  seeMoreHandler () {
    if (this.data.showAll) {
      this.setData({
        showTitle: '查看更多'
      })
    } else {
      this.setData({
        showTitle: '收起'
      })
    }
    this.setData({
      showAll: !this.data.showAll
    })
  },
  //关注舆情
  focusHandle (e) {
    console.log('e:', e)
    const title = e.currentTarget.dataset.title
    const topicId = e.currentTarget.dataset.topicid
    const url = e.currentTarget.dataset.url
    let targetObject = {}
    if (topicId) {
      targetObject = { title, topicId }
    } else {
      targetObject = { title, url }
    }
    console.log('targetObject:', targetObject)
    let focuSentimentList = this.data.focuSentimentList
    const isFocus = !this.data.isFocus
    this.setData({
      isFocus: isFocus
    })
    if (isFocus) {
      focuSentimentList.unshift(targetObject)
      for (let index = 1; index < focuSentimentList.length; index++) {
        if (focuSentimentList[index].title == targetObject.title) {
          focuSentimentList.splice(index, 1)
        }
      }
      wx.setStorageSync('focuSentimentList', focuSentimentList)
      wx.showToast({
        title: '关注成功',
        icon: 'success',
        duration: 800
      })
    } else {
      for (let index = 0; index < focuSentimentList.length; index++) {
        if (focuSentimentList[index].title == targetObject.title) {
          focuSentimentList.splice(index, 1)
        }
      }
      wx.setStorageSync('focuSentimentList', focuSentimentList)
      wx.showToast({
        title: '已取消关注',
        icon: 'success',
        duration: 800
      })
    }
  }
});

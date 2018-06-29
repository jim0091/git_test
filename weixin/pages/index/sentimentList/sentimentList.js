import wxApi from '../../../static/library/wx-api'
import config from '../../../config/config'
import wxRequest from '../../../static/library/wx-request'
Page({
  data: {
    page: 1,
    row: 20,
    total_page: null,
    stopicList: []
  },
  onShow() {
    const stopicList = wx.getStorageSync('stopicList')
    this.setData({
      stopicList: stopicList
    })
    this.getStopicList(this.data.page)
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
});

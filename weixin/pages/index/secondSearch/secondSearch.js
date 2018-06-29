import wxApi from '../../../static/library/wx-api'
import config from '../../../config/config'
import wxRequest from '../../../static/library/wx-request'
const wxParser = require('../../../wxParser/index')
Page({
  data: {
    token: '',
    type: '',
    // type: 'company',
    choose: false,
    choosetype: '',  //条件筛选类型：全国、行业、更多
    inputValue: '',
    page: 1,
    row: 20,
    total_page: null,
    gameover: false,
    categoryList: [
      {
        name: '万科',
        selected: true
      },
      {
        name: '小米',
        selected: false
      },
      {
        name: '优酷',
        selected: false
      },
    ],
    resultList: [
      {
        title: '搜狐网',
        subTitle: '宝能发布声明',
        time: '2018-01-09'
      },
      {
        title: '搜狐网',
        subTitle: '销售压力',
        time: '2018-01-09'
      },
      {
        title: '搜狐网',
        subTitle: '宝能发布声明',
        time: '2018-01-09'
      },
    ],
    targetList: [
      { name: '全国' },
      { name: '北京市' },
      { name: '天津市' },
      { name: '河北省' },
      { name: '山西省' },
      { name: '内蒙古自治区' },
      { name: '辽宁省' },
      { name: '吉林省' },
      { name: '黑龙江省' },
      { name: '上海市' },
      { name: '江苏省' },
      { name: '浙江省' },
      { name: '安徽省' },
      { name: '福建省' },
      { name: '江西省' },
      { name: '山东省' },
      { name: '河南省' },
      { name: '湖北省' },
      { name: '湖南省' },
      { name: '广东省' },
      { name: '广西壮族自治区' },
      { name: '海南省' },
      { name: '重庆市' },
      { name: '四川省' },
      { name: '贵州省' },
      { name: '云南省' },
      { name: '西藏自治区' },
      { name: '陕西省' },
      { name: '甘肃省' },
      { name: '青海省' },
      { name: '宁夏回族自治区' },
      { name: '新疆维吾尔自治区' }
    ],
    moreList: [
      // {
      //   title: '搜索范围',
      //   list: [
      //     { name: '按名称查询', selected: false },
      //     { name: '按地址查询', selected: false },
      //     { name: '按经营范围查询', selected: false },
      //     { name: '按品牌/产品查询', selected: false },
      //     { name: '按法定代表人查询', selected: false },
      //     { name: '按股东/高管查询', selected: false },
      //   ]
      // },
      // {
      //   title: '成立年限',
      //   list: [
      //     { name: '不限', selected: false },
      //     { name: '1年内', selected: false },
      //     { name: '1-3年', selected: false },
      //     { name: '2-3年', selected: false },
      //     { name: '3-5年', selected: false },
      //     { name: '5-10年', selected: false },
      //   ]
      // },
      {
        title: '注册资本',
        list: [
          { name: '不限', value: '', selected: false },
          { name: '100万以内', value: '1~100', selected: false },
          { name: '100万-200万', value: '100~200', selected: false },
          { name: '200万-500万', value: '200~500', selected: false },
          { name: '500万-1000万', value: '500~1000', selected: false },
          { name: '1000万以上', value: '1000', selected: false },
        ]
      }

    ],
    companyList: [], //公司列表
    total: 0,
    province: '全国',
    establishTime: '',  //成立日期
    registerCapital: '', //注册资本
    searchArea: '',      //搜索范围
    establishTimeList: [], //成立日期列表
    registerList: [],    //注册资本列表    
    searchList: [],    //搜索范围列表    
    stopicList: [], //舆情列表
    isReLoad: true,  //确定是否onShow()
    historyCompanyList: [], //公司搜索历史    
    browseCompanyList: [],  //公司浏览历史
    historySentimentList: [], //舆情搜索历史
    focusCompanyList: [], //公司关注列表
  },
  onLoad (option) {
    // console.log('option', option)
    this.setData({
      inputValue: option.input
    })
  },
  onShow () {
    const type = wx.getStorageSync('searchType')
    if (type === 'company') {
      wx.setNavigationBarTitle({
        title: '公司搜索'
      })
    } else {
      wx.setNavigationBarTitle({
        title: '舆情搜索'
      })
    }
    let historyCompanyList = []
    let browseCompanyList = []
    let historySentimentList = []
    if (wx.getStorageSync('historyCompanyList') && wx.getStorageSync('historyCompanyList').length > 0) {
      historyCompanyList = wx.getStorageSync('historyCompanyList')
    }
    if (wx.getStorageSync('browseCompanyList') && wx.getStorageSync('browseCompanyList').length > 0) {
      browseCompanyList = wx.getStorageSync('browseCompanyList')
    }
    if (wx.getStorageSync('historySentimentList') && wx.getStorageSync('historySentimentList').length > 0) {
      historySentimentList = wx.getStorageSync('historySentimentList')
    }
    this.setData({
      type: type,
      token: wx.getStorageSync('token'),
      historyCompanyList: historyCompanyList,
      browseCompanyList: browseCompanyList,
      historySentimentList: historySentimentList
    })
    if (this.data.isReLoad) {
      if (type == 'company') {
        this.companySearch(this.data.inputValue)
      } else {
        this.stopicSearch(this.data.inputValue)
        // this.getStopicList(this.data.page)
      }
    } else {
      // console.log('companyList:', this.data.companyList)
      this.setData({
        isReLoad: true
      })
      return
    }
  },

  goCompanyIndexHandle (e) {
    const companyId = e.currentTarget.dataset.companyid
    let fullName = e.currentTarget.dataset.fullname
    let browseCompanyList = this.data.browseCompanyList
    let targetObject = { companyId, fullName }
    browseCompanyList.unshift(targetObject)
    for (let index = 1; index < browseCompanyList.length; index++) {
      if (browseCompanyList[index].fullName == targetObject.fullName) {
        browseCompanyList.splice(index, 1)
      }
    }
    wx.setStorageSync('browseCompanyList', browseCompanyList)
    console.log('browseCompanyList:', browseCompanyList)
    this.setData({
      isReLoad: false
    })
    wx.navigateTo({
      url: '../company/index/index?companyId=' + companyId
    })
  },
  chooseHandler (e) {
    const target = e.currentTarget.dataset.index
    let categoryList = this.data.categoryList
    for (let index = 0; index < categoryList.length; index++) {
      if (index === target) {
        categoryList[index].selected = true
      } else {
        categoryList[index].selected = false
      }
    }
    this.setData({
      categoryList: categoryList
    })
  },
  //条件筛选
  chooseConditionHandle (e) {
    const target = e.currentTarget.dataset.choosetype
    // console.log('target:', target)
    this.setData({
      choose: !this.data.choose,
      choosetype: target
    })
  },
  //点击遮罩
  closeZhezhaoHandle () {
    this.setData({
      choose: !this.data.choose
    })
  },
  //公司搜索
  companySearch (inputValue) {
    let province = ''
    if (this.data.province !== '全国') {
      province = this.data.province
    }
    let historyCompanyList = this.data.historyCompanyList
    historyCompanyList.unshift(inputValue)
    for (let index = 1; index < historyCompanyList.length; index++) {
      if (historyCompanyList[index] == inputValue) {
        historyCompanyList.splice(index, 1)
      }
    }
    wx.setStorageSync('historyCompanyList', historyCompanyList)
    let query = {
      token: this.data.token,
      key: inputValue,
      page: this.data.page,
      rows: this.data.row,
      type: 'company',
      // type: this.data.type,
      company_type: 'all',
      province,
      industry: '',
      tag: '',
      clrq: '',
      // clrq: this.data.establishTime,
      finance: this.data.registerCapital,
      financing_time: '',
      financing_amount: '',
      financing_institutions: '',
      companybackground: '',
      schoolbackground: '',
      trading_market: '',
      market_value: '',
      round: '',
      total_company_num: '',
      all_amount: '',
      company_num: '',
      lang: '',
      cached: ''
    }
    wx.showLoading({
      title: '搜索中',
    })
    wxRequest.get(
      config.urls.companySearch,
      query
    ).then(res => {
      // wx.showLoading({
      //   title: '搜索中',
      // })
      if (res.code == 100) {
        let companyList = res.response.companys
        for (const company of companyList) {
          company.focuStatus = false
        }
        this.setData({
          companyList: this.data.companyList.concat(companyList),
          page: res.response.page,
          total_page: res.response.total_page,
          total: res.response.total
        })      
        wx.hideLoading()
        // wx.showToast({
        //   title: '搜索成功',
        //   icon: 'success',
        //   duration: 800
        // })
        this.resetHandle()
        this.add()
      }
    }).catch((err) => {
      console.log('err:', err)
    })
  },
  add(){
    let companyList = this.data.companyList;
    let richText = [];
    for (let num = 0; num < companyList.length; num++) {
      wxParser.parse({ 
        bind: 'richText['+num+']' ,
        html: companyList[num].fullname,
        target: this,
      })
    }
  },
  //舆情搜索
  stopicSearch (inputValue) {
    let historySentimentList = this.data.historySentimentList
    historySentimentList.unshift(inputValue)
    for (let index = 1; index < historySentimentList.length; index++) {
      if (historySentimentList[index] == inputValue) {
        historySentimentList.splice(index, 1)
      }
    }
    wx.setStorageSync('historySentimentList', historySentimentList)
    let query = {
      token: this.data.token,
      key: inputValue,
      page: this.data.page,
      rows: this.data.row,
      type: 'News', //暂时写死
      sortType: 0,
      sortOrder: 1,
      lang: '',
      cached: ''
    }
    wx.showLoading({
      title: '搜索中',
    })
    wxRequest.get(
      config.urls.stopicSearch,
      query
    ).then(res => {
      // wx.showLoading({
      //   title: '加载中',
      // })
      if (res.code == 100) {
        // let stopicList = res.response.list
        // for (let stopic of stopicList) {
        //   stopic.create_time = stopic.create_time.slice(5,16)
        // }
        this.setData({
          stopicList: this.data.stopicList.concat(res.response.list),
          page: res.response.page,
          total_page: res.response.total_page
        })
        wx.hideLoading()
        // wx.showToast({
        //   title: '搜索成功',
        //   icon: 'success',
        //   duration: 800
        // })
      }
    })
  },
  //获取实时舆情
  getStopicList (page) {
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
      wx.showLoading({
        title: '加载中',
      })
      if (res.code == 100) {
        // let stopicList = res.response.list
        // for (let stopic of stopicList) {
        //   stopic.create_time = stopic.create_time.slice(5,16)
        // }
        this.setData({
          stopicList: this.data.stopicList.concat(res.response.list),
          page: res.response.page,
          total_page: res.response.total_page
        })
        wx.hideLoading()
        // wx.showToast({
        //   title: '加载成功',
        //   icon: 'success',
        //   duration: 800
        // })
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
      if (_this.data.type == 'company') {
        _this.companySearch(this.data.inputValue)
      } else if (_this.data.type == 'sentiment') {
        _this.stopicSearch(this.data.inputValue)
      }
    } else {
      _this.setData({
        gameover: true
      })
    }
  },
  //输入
  inputHandle (e) {
    this.setData({
      inputValue: e.detail.value
    })
    this.searchHandle()
  },
  inputIngHandle (e) {
    this.setData({
      inputValue: e.detail.value
    })
  },
  //搜索
  searchHandle () {
    this.resetHandle()
    if (this.data.inputValue) {
      this.setData({
        choose: false
      })
      if (this.data.type == 'company') {
        this.setData({
          companyList: []
        })

        this.companySearch(this.data.inputValue)
      } else if (this.data.type == 'sentiment') {
        this.setData({
          stopicList: []
        })
        this.stopicSearch(this.data.inputValue)
      }
    } else {
      wx.showToast({
        title: '输入搜索框不能为空',
        icon: 'none',
        duration: 800
      })
    }
  },
  //选择省份
  chooseProvince (e) {
    const province = e.currentTarget.dataset.province
    this.setData({
      province: province,
      companyList: [],
      choose: !this.data.choose
    })
    this.companySearch(this.data.inputValue)
  },
  //更多筛选
  chooseMore (e) {
    const targetName = e.currentTarget.dataset.name
    const targetTitle = e.currentTarget.dataset.title
    let moreList = this.data.moreList,
      establishTimeList = this.data.establishTimeList,
      registerList = this.data.registerList,
      searchList = this.data.searchList
    for (const more of moreList) {
      if (more.title == targetTitle) {
        for (const target of more.list) {
          if (target.name == targetName) {
            target.selected = !target.selected
            if (target.selected) {
              console.log('title:', more.title)
              if (more.title == '注册资本') {
                registerList.push(target.value)
              } else if (more.title == '成立年限') {
                establishTimeList.push(targetName)
              } else {
                searchList.push(targetName)
              }
            } else {
              if (more.title == '注册资本') {
                for (let index = 0; index < registerList.length; index++) {
                  if (registerList[index] == target.value) {
                    registerList.splice(index, 1)
                  }
                }
              } else if (more.title == '成立年限') {
                for (let index = 0; index < establishTimeList.length; index++) {
                  if (establishTimeList[index] == targetName) {
                    establishTimeList.splice(index, 1)
                  }
                }
              } else {
                for (let index = 0; index < searchList.length; index++) {
                  if (searchList[index] == targetName) {
                    searchList.splice(index, 1)
                  }
                }
              }
            }
          }
        }
      }
    }
    // console.log('establishTimeList:', establishTimeList)
    // console.log('registerList:', registerList)
    // console.log('searchList:', searchList)
    const targetTime = establishTimeList.join('~')
    const targetRegister = registerList.join('~')
    const targetSearch = searchList.join('~')
    // console.log('targetTime:', targetTime)
    // console.log('targetRegister:', targetRegister)
    this.setData({
      moreList: moreList,
      establishTime: targetTime,
      registerCapital: targetRegister,
      searchArea: targetSearch,
      establishTimeList: establishTimeList,
      registerList: registerList
    })
  },
  //提交条件筛选
  submitHandle () {
    if (this.data.establishTime || this.data.registerCapital || this.data.searchArea) {
      this.setData({
        choose: !this.data.choose,
        companyList: [],
      })
      this.companySearch(this.data.inputValue)
    } else {
      wx.showToast({
        title: '您还未选择筛选条件~',
        icon: 'none',
        duration: 1000
      })
    }
  },
  //重置筛选条件
  resetHandle (e) {
    console.log('e:', e)
    let targetMoreList = this.data.moreList
    for (const more of targetMoreList) {
      for (const list of more.list) {
        list.selected = false
      }
    }
    this.setData({
      moreList: targetMoreList,
      establishTime: '',  //成立日期
      registerCapital: '', //注册资本
      searchArea: '',      //搜索范围
      establishTimeList: [], //成立日期列表
      registerList: [],    //注册资本列表    
      searchList: []     //搜索范围列表    
    })
    if (e && e.currentTarget.dataset.reset) {
      wx.showToast({
        title: '重置完成',
        icon: 'success',
        duration: 800
      })
    }
  },
  //关注
  focusHandle (e) {
    const fullName = e.currentTarget.dataset.fullname
    const companyId = e.currentTarget.dataset.companyid
    let focusCompanyList = this.data.focusCompanyList
    let targetObject = { companyId, fullName }
    let companyList = this.data.companyList
    for (const company of companyList) {
      if (company.fullname == fullName) {
        company.focuStatus = !company.focuStatus
        if (company.focuStatus) {
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
    }
    this.setData({
      companyList: companyList
    })
  },
  //关注舆情
  focusSentimentHandle (e) {

  },

})
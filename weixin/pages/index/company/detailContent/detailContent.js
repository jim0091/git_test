import wxApi from '../../../../static/library/wx-api'
import config from '../../../../config/config'
import wxRequest from '../../../../static/library/wx-request'
const wxParser = require('../../../../wxParser/index')
Page({
  data: {
    token: '',
    companyDetail: {},
    target: '',
    name: '',
    regNo: '',
    md5: '',
    managerId: null,
    hasShow: false,
    //导航列表
    navList: [
      {
        id: 0,
        name: '相关新闻'
      },
      {
        id: 1,
        name: '相关公司'
      }
    ],
    currentChooseNav: 0,
    page: 1,
    row: 20,
    total_page: null,
    gameover: false,
    managerInfo: {},   //董监高个人信息
    companys: [],      //相关公司
    brandList: [],     //商标
    brand: {},
    managerNewsList: [], //董监高新闻信息
    wenshuInfo: '',   //文书详情
    isNomanagerNewsList: null
  },
  onLoad(option) {
    console.log('option:', option)
    this.setData({
      // target: 'judicialvalue',
      target: option.target
    })
    if (option.name) {
      this.setData({
        name: option.name,
        managerId: option.id
      })
    } else if (option.regNo) {
      this.setData({
        regNo: option.regNo
      })
    } else if (option.md5) {
      console.log('md5:', option.md5)
      this.setData({
        md5: option.md5
      })
    }
  },
  onShow() {
    this.setData({
      token: wx.getStorageSync('token'),
      companyDetail: wx.getStorageSync('companyDetail'),
      brandList: wx.getStorageSync('brandList')
    })
    this.setTargetTitle(this.data.target)
  },
  setTargetTitle(e) {
    console.log('e:', e)
    let targetTitle = ''
    if (e == 'resume') {
      wx.setNavigationBarTitle({
        title: '个人简历'
      })
      this.getManagerInfo()
    } else if (e == 'brandvalue') {
      wx.setNavigationBarTitle({
        title: '商标'
      })
      this.setBrand(this.data.regNo)
    } else if (e == 'judicialvalue') {
      wx.setNavigationBarTitle({
        title: '司法信息'
      })
      this.getWenshuInfo()
    } else if (e == 'tendervalue') {
      wx.setNavigationBarTitle({
        title: '招投标信息'
      })
    }
  },
  //获取董监高具体信息
  getManagerInfo() {
    const query = {
      token: this.data.token,
      // id: 87620,
      id: this.data.managerId,
      // company_id: 3167,
      company_id: this.data.companyDetail.company_id,
      // name: '郁亮',
      name: this.data.name,
      lang: '',
      cache: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getManagerInfo,
      query
    ).then(res => {
      if (res.code === 100) {
        const managerId = res.response.manager.person_id - 100000000
        this.setData({
          managerId: managerId
          // managerInfo: res.response.manager,
          // companys: res.response.companys
        })
        this.getManagerNews(managerId)
        let workList = res.response.manager.work
        let re = /[\u4e00-\u9fa5,，。]/
        for (const work of workList) {
          let company = ''
          for (let i = 0; i < work.company.length; i++) {
            if (re.test(work.company.charAt(i))) {
              company = company + work.company.charAt(i)
            }
          }
          work.company = company
        }
        // let resumes = res.response.manager.resumes
        // let resume = ''
        // console.log('resumes:', resumes)
        // for (let j = 0; j < resumes.length; j++) {
        //   if (re.test(resumes.charAt(j))) {
        //     resume = resume + resumes.charAt(j)
        //   }
        // }
        // res.response.manager.resumes = resume
        // console.log('resume:', resume)
        this.setData({
          managerInfo: res.response.manager,
          companys: res.response.companys
        })
        wxParser.parse({
          bind: 'richText',
          html: res.response.manager.resumes,
          target: this,
        })
        // wx.hideLoading()        
      }
    }).catch(err => {
      console.log('err:', err)
      wx.showToast({
        title: err,
        icon: 'none',
        duration: 2000
      })
    })
  },
  //获取董监高新闻信息
  getManagerNews(managerId) {
    const query = {
      token: this.data.token,
      id: managerId,
      page: this.data.page,
      rows: this.data.row,
      s_date: '',
      e_date: '',
      keyword: '',
      polarity: '',
      lang: '',
      cache: ''
    }
    // wx.showLoading({
    //   title: '加载中',
    // })
    wxRequest.get(
      config.urls.getManagerNews,
      query
    ).then(res => {
      wx.hideLoading()      
      if (res.response.list && res.response.list.length > 0) {
        let re = /[\u4e00-\u9fa50-9,，。-]/
        for (const news of res.response.list) {
          let summary = ''
          for (let i = 0; i < news.summary.length; i++) {
            if (re.test(news.summary.charAt(i))) {
              summary = summary + news.summary.charAt(i)
            }
          }
          news.summary = summary
        }
        this.setData({
          isNomanagerNewsList: false,
          managerNewsList: this.data.managerNewsList.concat(res.response.list),
          page: res.response.page,
          total_page: res.response.total_page
        })
      } else {
        this.setData({
          isNomanagerNewsList: true          
        })
      }
    })
  },
  //获取商标信息
  setBrand(regNo) {
    let brandData = {}
    let brandList = this.data.brandList
    for (const brand of brandList) {
      if (parseInt(brand.regNo) === parseInt(regNo)) {
        brandData = Object.assign({}, brand)
      }
    }
    this.setData({
      brand: brandData
    })
  },
  //获取司法信息
  getWenshuInfo() {
    const query = {
      token: this.data.token,
      md5: this.data.md5,
      cid: 3167,
      // cid: this.data.companyDetail.cid,
      lang: '',
      cache: ''
    }
    wxRequest.get(
      config.urls.getWenshuInfo,
      query
    ).then(res => {
      if (res.code === 100) {
        this.setData({
          wenshuInfo: res.response,
        })
        wxParser.parse({
          bind: 'richText',
          html: res.response.content,
          target: this,
        })
      }
    }).catch(err => {
      console.log('err:', err)
      wx.showToast({
        title: err,
        icon: 'none',
        duration: 2000
      })
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
      console.log('target:', _this.data.target)
      if (_this.data.target == 'resume') {
        _this.getManagerNews(this.data.managerId)
      }
    } else {
      _this.setData({
        gameover: true
      })
    }
  },
  backIndexHandler() {
    wxApi.navigateBack()
  },
  //展示简历详情
  showMoreHandler() {
    this.setData({
      hasShow: !this.data.hasShow
    })
  },
  //动态切换导航栏
  chooseHandler(e) {
    this.setData({
      currentChooseNav: e.currentTarget.dataset.id
    })
  },
  //人物图谱
  relationHandler() {
    wx.showToast({
      title: '请联系管理员',
      icon: 'none',
      duration: 2000
    })
  }
});

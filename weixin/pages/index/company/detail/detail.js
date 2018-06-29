import wxApi from '../../../../static/library/wx-api'
import config from '../../../../config/config'
import wxRequest from '../../../../static/library/wx-request'
Page({
  data: {
    token: '',
    target: '',
    // targetTitle: '',
    stop: null,  //控制返回键是否停留此页
    parentTarget: '',
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
    sort: 'desc',
    order: 'createTime',
    total_page: null,
    companyDetail: {},   //工商信息
    logRecordList: [],   //变更记录
    holderList: [],      //股东
    managerList: [],     //董监高
    investList: [],      //对外投资
    branchesList: [],     //分支机构
    patentList: [],      //专利
    softList: [],      //软著
    netWorkList: [],      //网络备案
    jobList: [],      //招聘信息
    brandList: [],      //商标
    wenshuList: [],      //司法信息
    bidsList: [],      //招标信息
    noticeList:[],     //公告信息
    gameover: false,   //加载完成标识
    isShow: false      //展示没有信息标识
  },
  onLoad(option) {
    console.log('option:', option)
    this.setData({
      // target: 'tender',
      token: wx.getStorageSync('token'),
      companyDetail: wx.getStorageSync('companyDetail'),
      target: option.target,
      stop: false
    })
    this.setTargetTitle(option.target)
    
  },
  onShow() {
    this.setData({
    })
    // this.setTargetTitle(this.data.target)
  },
  setTargetTitle(e) {
    console.log('e:', e)
    let targetTitle = ''
    if (e == 'business') {
      wx.setNavigationBarTitle({
        title: '工商信息'
      })
    } else if (e == 'log') {
      wx.setNavigationBarTitle({
        title: '变更记录'
      })
      this.getLogRecordList(this.data.page)
    } else if (e == 'shareholder') {
      wx.setNavigationBarTitle({
        title: '股东'
      })
      this.getHolder(this.data.page)
    } else if (e == 'dongjiangao') {
      wx.setNavigationBarTitle({
        title: '董监高'
      })
      this.getManagers(this.data.page)
    } else if (e == 'investment') {
      wx.setNavigationBarTitle({
        title: '对外投资'
      })
      this.getInvest(this.data.page)
    } else if (e == 'branches') {
      wx.setNavigationBarTitle({
        title: '分支机构'
      })
      this.getBranches(this.data.page)
    } else if (e == 'patent') {
      wx.setNavigationBarTitle({
        title: '专利'
      })
      this.getPatent(this.data.page)
    } else if (e == 'software') {
      wx.setNavigationBarTitle({
        title: '软著'
      })
      this.getSofts(this.data.page)
    } else if (e == 'network') {
      wx.setNavigationBarTitle({
        title: '网络备案'
      })
      this.getWebsites(this.data.page)
    } else if (e == 'brand') {
      wx.setNavigationBarTitle({
        title: '商标'
      })
      this.getBrand(this.data.page)
    } else if (e == 'judicial') {
      wx.setNavigationBarTitle({
        title: '司法信息'
      })
      this.getWenshuList(this.data.page)
    } else if (e == 'recruitment') {
      wx.setNavigationBarTitle({
        title: '招聘信息'
      })
      this.getJobs(this.data.page)
    } else if (e == 'tender') {
      wx.setNavigationBarTitle({
        title: '招投标信息'
      })
      this.getBids(this.data.page)
    } else if (e == 'notice') {
      wx.setNavigationBarTitle({
        title: '公告信息'
      })
      this.getNotice(this.data.page)
    }

    // this.setData({
    //   targetTitle: targetTitle
    // })
  },
  //获取变更记录
  getLogRecordList(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      lang: '',
      cache: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getLogRecord,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            logRecordList: this.data.logRecordList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }

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
  //获取股东信息
  getHolder(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      lang: '',
      cache: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getHolders,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            holderList: this.data.holderList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.hideLoading()
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
  //获取董监高信息
  getManagers(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      lang: '',
      cache: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getManagers,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            managerList: this.data.managerList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
  //获取对外投资
  getInvest(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      lang: '',
      cache: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getInvest,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            investList: this.data.investList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
  //获取分支机构
  getBranches(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      lang: '',
      cache: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getBranches,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            branchesList: this.data.branchesList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
  //获取专利
  getPatent(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      lang: '',
      cache: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getPatent,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            patentList: this.data.patentList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
  //获取软著
  getSofts(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      lang: '',
      cache: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getSofts,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            softList: this.data.softList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
  //获取网络备案
  getWebsites(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      lang: '',
      cache: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getWebsites,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            netWorkList: this.data.netWorkList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
  //获取招聘信息
  getJobs(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      // sort: 'desc',
      sort: this.data.sort,
      // order: 'createTime',
      order: this.data.order,
      years: '',
      s_date: '',
      e_date: '',
      jobtype: '',
      title: '',
      location: '',
      lang: '',
      cached: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getJobs,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            jobList: this.data.jobList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
  //获取商标
  getBrand(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      lang: '',
      cache: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getBrand,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            brandList: this.data.brandList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
          wx.setStorageSync('brandList', this.data.brandList)
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
  //获取司法信息
  getWenshuList(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      s_date: '',
      e_data: '',
      lang: '',
      cache: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getWenshuList,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            wenshuList: this.data.wenshuList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // console.log('wenshu', this.data.wenshuList);
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
  //获取招标信息
  getBids(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      page,
      rows: this.data.row,
      lang: '',
      cached: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getBids,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            bidsList: this.data.bidsList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
  //获取公告信息
  getNotice(page) {
    const query = {
      token: this.data.token,
      // cid: 3167,
      cid: this.data.companyDetail.company_id,
      type:'notice',
      page,
      rows: this.data.row,
      lang: '',
      cached: ''
    }
    wx.showLoading({
      title: '加载中',
    })
    wxRequest.get(
      config.urls.getNotice,
      query
    ).then(res => {
      if (res.code === 100) {
        wx.hideLoading()
        if (res.response.total_page > 0) {
          if (res.response.total_page === res.response.page) {
            this.setData({
              gameover: true
            })
          }
          this.setData({
            noticeList: this.data.noticeList.concat(res.response.list),
            page: res.response.page,
            total_page: res.response.total_page
          })
          // wx.showToast({
          //   title: '加载成功',
          //   icon: 'success',
          //   duration: 800
          // })
        } else if (res.response.total_page === 0) {
          this.setData({
            isShow: true
          })
        }
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
      if (_this.data.target == 'log') {
        _this.getLogRecordList(page)
      } else if (_this.data.target == 'shareholder') {
        _this.getHolder(page)
      } else if (_this.data.target == 'dongjiangao') {
        _this.getManagers(page)
      } else if (_this.data.target == 'investment') {
        _this.getInvest(page)
      } else if (_this.data.target == 'branches') {
        _this.getBranches(page)
      } else if (_this.data.target == 'patent') {
        _this.getPatent(page)
      } else if (_this.data.target == 'software') {
        _this.getSofts(page)
      } else if (_this.data.target == 'network') {
        _this.getWebsites(page)
      } else if (_this.data.target == 'recruitment') {
        _this.getJobs(page)
      } else if (_this.data.target == 'brand') {
        _this.getBrand(page)
      } else if (_this.data.target == 'judicial') {
        _this.getWenshuList(page)
      } else if (_this.data.target == 'tender') {
        _this.getBids(page)
      }else if (_this.data.target == 'notice') {
        _this.getNotice(page)
      }
    } else {
      _this.setData({
        gameover: true
      })
    }
  },
  //选择股东
  choosePartnerHandle(e) {
    const personId = e.currentTarget.dataset.personid
    const name = e.currentTarget.dataset.name
    const companyId = e.currentTarget.dataset.companyid
    if (personId) {
      wx.navigateTo({
        url: '../detailContent/detailContent?target=resume&name=' + name + '&id=' + personId
      })
    } else {
      wx.navigateTo({
        url: '../index/index?companyId=' + companyId
      })
    }
  }
});

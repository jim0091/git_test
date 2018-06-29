import wxApi from '../../static/library/wx-api'
import wxRequest from '../../static/library/wx-request'
Page({
  data: {
    username:'',
    password:'',
  },
  formSubmit: function (e) { 
    const username = e.detail.value.username;
    const password = e.detail.value.password;
    // console.log(username);
    if(username.length==0||password.length==0){
      wx.showToast({
        title: '账号或密码为空!',
        icon: 'none',
        duration: 1500
      })
      setTimeout(function () {
        wx.hideToast()
      }, 2000)
    }else{
      // let url = "https://wx.jdeasy.cn/api/login"; 
      let url = "http://www.jdeasy.cn/api/login"; 
      let query = {
        username: username,
        password: password,
      };
      wxRequest.post(
        url,
        query
      ).then(res => {
        if (res.code === 200) {
          let data = res.data;
          let authKey = data.authKey;
          let sessionId = data.sessionId;
          let uid = data.userInfo.id;
          wx.setStorageSync('authKey', authKey);
          wx.setStorageSync('sessionId', sessionId);
          wx.setStorageSync('uid', uid);
          wxApi.switchTab('../index/index/index');
        }else{
          wx.showToast({
            title:res.error,
            icon: 'none',
            duration: 2000
          })
        }
      }).catch(err => {
        console.log(err)
        wx.showToast({
          title: err.errMsg,
          icon: 'none',
          duration: 2000
        })
      }) 
      // wx.request({
      //   url: 'https://www.jdeasy.cn/api/login',
      //   header: {
      //     "Content-Type": "application/x-www-form-urlencoded"
      //   },
      //   method: "POST",
      //   data: { username: username, password: password },
      //   success: function (res) {
      //     console.log("res",res);
      //   },
      //   error:function(err){
      //     console.log("err",err);
      //     console.log(1);
      //   }
      // })
    }
  },
  change(){
    console.log(1111);
    wxApi.switchTab('../index/index/index');
  },
  // phone(){
  //   wx.makePhoneCall({
  //     phoneNumber: '15818271323' //仅为示例，并非真实的电话号码
  //   })
  // }        
})
/**
 * 通用函数
 * */

// 拨打电话
function callCompany(e) {
    wx.makePhoneCall({phoneNumber: '12580'});
}

export {
    callCompany
}
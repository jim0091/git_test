import Promise from "./es6-promise";

function wxPromisify (fn) {
    return function (obj = {}) {
        return new Promise((resolve, reject) => {
            obj.success = function (res) {
                resolve(res)
            }
            obj.fail = function (res) {
                reject(res)
            }
            fn(obj);
        })
    }
}

//无论promise对象最后状态如何都会执行
Promise.prototype.finally = function (callback) {
    let P = this.constructor;
    return this.then(
        value => P.resolve(callback()).then(() => value),
        reason => P.resolve(callback()).then(() => {
            throw reason
        })
    );
};

/*****************************
 ********** 开放接口 ***********
 /****************************/

// 登录
function login () {
    return wxPromisify(wx.login);
}

// 检查登录状态
function checkSession () {
    return wxPromisify(wx.checkSession);
}

// 调起用户编辑收货地址原生界面
function chooseAddress () {
    return wxPromisify(wx.chooseAddress);
}

/*****************************
 ********** 用户信息 ***********
 /****************************/

// 查询用户信息
function getUserInfo () {
    return wxPromisify(wx.getUserInfo);
}

/*****************************
 ************ 媒体 ************
 /****************************/

// 选择图片
function chooseImage (count) {
    let chooseImage = wxPromisify(wx.chooseImage);
    return chooseImage({
        count
    });
}

// 预览图片
function previewImage (urls, current) {
    let previewImage = wxPromisify(wx.previewImage);
    current = current ? current : 1;
    return previewImage({
        current,
        urls
    });
}

// 开始录音
function startRecord (filePath) {
    let startRecord = wxPromisify(wx.startRecord);
    return startRecord({
        filePath
    });
}

/*****************************
 ********** 数据缓存 ***********
 /****************************/

// 设置缓存
function setStorage (key, data) {
    let setStorage = wxPromisify(wx.setStorage);
    return setStorage({
        key,
        data
    });
}

// 查询缓存
function getStorage (key) {
    let getStorage = wxPromisify(wx.getStorage);
    return getStorage({
        key
    });
}

// 删除缓存
function removeStorage (key) {
    let removeStorage = wxPromisify(wx.removeStorage);
    return removeStorage({
        key
    });
}

/*****************************
 ************ 位置 ************
 /****************************/

// 查询当前位置
function getLocation (type) {
    let getLocation = wxPromisify(wx.getLocation);
    type = type ? type : 'wgs84';
    return getLocation({
        type
    });
}

// 选择位置
function chooseLocation (cb) {
    wx.chooseLocation({
        success (res) {
            cb(res);
        }
    });
}

// 查看位置
function openLocation (latitude, longitude, name, address, scale) {
    let openLocation = wxPromisify(wx.openLocation);
    return openLocation({
        latitude,
        longitude,
        name,
        address,
        scale
    });
}

/*****************************
 ************ 设备 ************
 /****************************/

// 查询系统信息
function getSystemInfo () {
    return wxPromisify(wx.getSystemInfo);
}

// 查询系统信息 [同步]
function getSystemInfoSync () {
    return wxPromisify(wx.getSystemInfoSync);
}

// 查询网络状态
function getNetworkType () {
    return wxPromisify(wx.getNetworkType);
}

// 拨打电话
function makePhoneCall (phoneNumber) {
    let makePhoneCall = wxPromisify(wx.makePhoneCall);
    phoneNumber = phoneNumber ? phoneNumber : '';
    return makePhoneCall({
        phoneNumber
    });
}

// 扫码
function scanCode (onlyFromCamera) {
    let scanCode = wxPromisify(wx.scanCode);
    onlyFromCamera = onlyFromCamera ? onlyFromCamera : false;
    return scanCode({
        onlyFromCamera
    });
}

// 设置系统剪贴板的内容
function setClipboardData (data) {
    let setClipboardData = wxPromisify(wx.setClipboardData);
    return setClipboardData({
        data
    });
}

/*****************************
 ************ 界面 ************
 /****************************/

// ​显示模态弹窗
function showModal (content, showCancel, title) {
    let showModal = wxPromisify(wx.showModal);
    title = title ? title : '提示';
    showCancel = showCancel ? showCancel : false;
    return showModal({
        title,
        content,
        showCancel
    });
}

// ​显示消息提示框 [success]
function showSuccess (title) {
    let showToast = wxPromisify(wx.showToast);
    return showToast({
        title,
        icon: 'success',
        mask: true
    });
}

// 显示 loading 提示
function showLoading (title, mask) {
    let showLoading = wxPromisify(wx.showLoading);
    title = title ? title : '加载数据中';
    mask = mask ? mask : true;
    return showLoading({
        title,
        mask
    });
}

// 动态设置当前页面的标题
function setNavigationBarTitle (title) {
    let setNavigationBarTitle = wxPromisify(wx.setNavigationBarTitle);
    return setNavigationBarTitle({
        title
    });
}

// 保留当前页面，跳转到应用内的某个页面，使用wx.navigateBack可以返回到原页面
function navigateTo (url) {
    let navigateTo = wxPromisify(wx.navigateTo);
    return navigateTo({
        url
    });
}

// 关闭当前页面，跳转到应用内的某个页面
function redirectTo (url) {
    let redirectTo = wxPromisify(wx.redirectTo);
    return redirectTo({
        url
    });
}

// 跳转到 tabBar 页面，并关闭其他所有非 tabBar 页面
function switchTab (url) {
    let switchTab = wxPromisify(wx.switchTab);
    return switchTab({
        url
    });
}

// 关闭当前页面，返回上一页面或多级页面
function navigateBack (delta) {
    let navigateBack = wxPromisify(wx.navigateBack);
    delta = delta ? delta : 1;
    return navigateBack({
        delta
    });
}

// 关闭所有页面，打开到应用内的某个页面
function reLaunch (url) {
    let reLaunch = wxPromisify(wx.reLaunch);
    return reLaunch({
        url
    });
}

module.exports = {
    login,
    checkSession,
    chooseAddress,
    getUserInfo,
    chooseImage,
    previewImage,
    startRecord,
    setStorage,
    getStorage,
    removeStorage,
    getLocation,
    openLocation,
    getSystemInfo,
    getSystemInfoSync,
    getNetworkType,
    makePhoneCall,
    chooseLocation,
    scanCode,
    setClipboardData,
    showModal,
    showSuccess,
    showLoading,
    setNavigationBarTitle,
    navigateTo,
    redirectTo,
    switchTab,
    navigateBack,
    reLaunch,
    reLaunch,
}

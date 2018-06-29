import Promise from './es6-promise';

function wxPromisify(fn) {
    return function (obj = {}) {
        return new Promise((resolve, reject) => {
            obj.success = function (res) {
                if (res.statusCode == 200)
                    resolve(res.data)
                else
                    reject(res)
            }
            obj.fail = function (res) {
                reject(res)
            }
            fn(obj);
        });
    }
}

// 无论Promise对象最后状态如何都会执行
Promise.prototype.finally = function (callback) {
    let P = this.constructor;
    return this.then(
        value => P.resolve(callback()).then(() => value),
        reason => P.resolve(callback()).then(() => {
            throw reason
        })
    );
};

// 获取json [get]
function getRequest(url, data) {
    var getRequest = wxPromisify(wx.request);
    return getRequest({
        url: url,
        method: 'GET',
        data: data,
        header: {
            'Content-Type': 'application/json'
        }
    })
}

// 不编码提交 [post]
function postRequest(url, data) {
    var postRequest = wxPromisify(wx.request);
    return postRequest({
        url: url,
        data: data,
        header: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        method: 'POST',
    });
}

// 传统不编码提交 [post]
function postRequest2(url, data) {
    var postRequest = wxPromisify(wx.request);
    return postRequest({
        url: url,
        data: data,
        traditional: true,
        header: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        method: 'POST',
    })
}

// 提交表单 [post]
function postForm(url, data) {
    var postRequest = wxPromisify(wx.request);
    return postRequest({
        url: url,
        data: data,
        header: {
            "Content-Type": "multipart/form-data"
        },
        method: 'POST',
    });
}

// 提交json [post]
function postJson(url, data) {
    var postRequest = wxPromisify(wx.request);
    return postRequest({
        url: url,
        data: data,
        header: {
            "Content-Type": 'application/json'
        },
        method: 'POST'
    });
}

// [上传文件] [post]
function uploadFile(url, filePath, name, formData) {
    var uploadFile = wxPromisify(wx.uploadFile);
    return uploadFile({
        url,
        filePath,
        name,
        formData,
        header: {
            "Content-Type": 'multipart/form-data'
        },
        method: 'POST'
    });
}

// 发送模板 [post]
function postTemplate(url, data) {
    var postRequest = wxPromisify(wx.request);
    return postRequest({
        url: url,
        data: data,
        method: 'POST',
    });
}

module.exports = {
    get: getRequest,
    post: postRequest,
    post2: postRequest2,
    upload: uploadFile,
    postForm,
    postJson,
    postTemplate
}
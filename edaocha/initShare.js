var initShare = function (elementNode, config) {

    if (!document.getElementById(elementNode)) {
        return false;
    }

    var qApiSrc = {
        lower: "http://3gimg.qq.com/html5/js/qb.js",
        higher: "http://jsapi.qq.com/get?api=app.share"
    };
    var bLevel = {
        qq: {forbid: 0, lower: 1, higher: 2},
        uc: {forbid: 0, allow: 1}
    };
    var UA = navigator.appVersion;
    var isqqBrowser = (UA.split("MQQBrowser/").length > 1) ? bLevel.qq.higher : bLevel.qq.forbid;
    var isucBrowser = (UA.split("UCBrowser/").length > 1) ? bLevel.uc.allow : bLevel.uc.forbid;
    var version = {
        uc: "",
        qq: ""
    };
    var isWeixin = false;

    config = config || {};
    this.elementNode = elementNode;
    this.url = config.url || document.location.href || '';
    this.title = config.title || document.title || '';
    this.desc = config.desc || document.title || '';
    this.img = config.img || document.getElementsByTagName('img').length > 0 && document.getElementsByTagName('img')[1].src || '';
    this.img_title = config.img_title || document.title || '';
    this.from = config.from || window.location.host || '';
    this.ucAppList = {
        sinaWeibo: ['kSinaWeibo', 'SinaWeibo', 11, '新浪微博'],
        weixin: ['kWeixin', 'WechatFriends', 1, '微信好友'],
        weixinFriend: ['kWeixinFriend', 'WechatTimeline', '8', '微信朋友圈'],
        QQ: ['kQQ', 'QQ', '4', 'QQ好友'],
        QZone: ['kQZone', 'QZone', '3', 'QQ空间']
    };

    this.share = function (to_app) {
        var title = this.title, url = this.url, desc = this.desc, img = this.img, img_title = this.img_title, from = this.from;
        if (isucBrowser) {
            to_app = to_app == '' ? '' : (platform_os == 'iPhone' ? this.ucAppList[to_app][0] : this.ucAppList[to_app][1]);
            if (to_app == 'QZone') {
                B = "mqqapi://share/to_qzone?src_type=web&version=1&file_type=news&req_type=1&image_url="+img+"&title="+title+"&description="+desc+"&url="+url+"&app_name="+from;
                k = document.createElement("div"), k.style.visibility = "hidden", k.innerHTML = '<iframe src="' + B + '" scrolling="no" width="1" height="1"></iframe>', document.body.appendChild(k), setTimeout(function () {
                    k && k.parentNode && k.parentNode.removeChild(k)
                }, 5E3);
            }
            if (typeof(ucweb) != "undefined") {
                ucweb.startRequest("shell.page_share", [title, title, url, to_app, "", "@" + from, ""])
            } else {
                if (typeof(ucbrowser) != "undefined") {
                    ucbrowser.web_share(title, title, url, to_app, "", "@" + from, '')
                } else {
                }
            }
        } else {
            if (isqqBrowser && !isWeixin) {
                to_app = to_app == '' ? '' : this.ucAppList[to_app][2];
                var ah = {
                    url: url,
                    title: title,
                    description: desc,
                    img_url: img,
                    img_title: img_title,
                    to_app: to_app,//微信好友1,腾讯微博2,QQ空间3,QQ好友4,生成二维码7,微信朋友圈8,啾啾分享9,复制网址10,分享到微博11,创意分享13
                    cus_txt: "请输入此时此刻想要分享的内容"
                };
                ah = to_app == '' ? '' : ah;
                if (typeof(browser) != "undefined") {
                    if (typeof(browser.app) != "undefined" && isqqBrowser == bLevel.qq.higher) {
                        browser.app.share(ah)
                    }
                } else {
                    if (typeof(window.qb) != "undefined" && isqqBrowser == bLevel.qq.lower) {
                        window.qb.share(ah)
                    } else {
                    }
                }
            } else {
            }
        }
    };

    this.isloadApi = function (b) {
        var d = document.createElement("script");
        var a = document.getElementsByTagName("head")[0]||document.body;
        d.setAttribute("src", b);
        a.appendChild(d)
    };

    this.getPlantform = function () {
        ua = navigator.userAgent;
        if ((ua.indexOf("iPhone") > -1 || ua.indexOf("iPod") > -1)) {
            return "iPhone"
        }
        return "Android"
    };

    this.is_weixin = function () {
        var a = UA.toLowerCase();
        if (a.match(/MicroMessenger/i) == "micromessenger") {
            return true
        } else {
            return false
        }
    };

    this.getVersion = function (c) {
        var a = c.split("."), b = parseFloat(a[0] + "." + a[1]);
        return b
    };

    this.init = function () {
        platform_os = this.getPlantform();
        version.qq = isqqBrowser ? this.getVersion(UA.split("MQQBrowser/")[1]) : 0;
        version.uc = isucBrowser ? this.getVersion(UA.split("UCBrowser/")[1]) : 0;
        isWeixin = this.is_weixin();
        if ((isqqBrowser && version.qq < 5.4 && platform_os == "iPhone") || (isqqBrowser && version.qq < 5.3 && platform_os == "Android")) {
            isqqBrowser = bLevel.qq.forbid
        } else {
            if (isqqBrowser && version.qq < 5.4 && platform_os == "Android") {
                isqqBrowser = bLevel.qq.lower
            } else {
                if (isucBrowser && ((version.uc < 10.2 && platform_os == "iPhone") || (version.uc < 9.7 && platform_os == "Android"))) {
                    isucBrowser = bLevel.uc.forbid
                }
            }
        }
        var position = document.getElementById(this.elementNode);
        if (isqqBrowser || isucBrowser) {
            if (isqqBrowser) {
                var b = (version.qq < 5.4) ? qApiSrc.lower : qApiSrc.higher;
                this.isloadApi(b);
            }
            var html = '<div class="ui-sharing">'+
                '<h3>分享</h3>'+
                '<ul class="share-w4 clearfix">'+
                '<li><a class="initShare weixin" rel="nofollow" data-app="weixin">微信好友</a></li>'+
                '<li><a class="initShare friend" rel="nofollow" data-app="weixinFriend">朋友圈</a></li>'+
                '<li><a class="initShare sina" rel="nofollow" data-app="sinaWeibo">新浪微博</a></li>'+
                '<li><a class="initShare qq" rel="nofollow" data-app="QQ">QQ好友</a></li>'+
                '</ul>'+
                '</div>';
            position.innerHTML = html;
            var share = this;
            var items = document.getElementsByClassName('initShare');
            for (var i=0;i<items.length;i++) {
                items[i].onclick = function(e){
                    e.preventDefault();
                    share.share(this.getAttribute('data-app'));
                }
            }
        } else if (isWeixin) {
            var html = '<div class="bdsharebuttonbox ui-sharing" data-tag="share_1">'+
                '<h3>分享</h3>'+
                '<ul class="share-w2 clearfix">'+
                '<li><a class="initShare weixin" rel="nofollow">发送给好友</a></li>'+
                '<li><a class="initShare friend" rel="nofollow">分享到朋友圈</a></li>'+
                '</ul>'+
                '</div>';
            position.innerHTML = html;
            var wx = document.createElement("div");
            var html = '<div id="weixinGuide" class="ui-weixin-guide">'+
                '<div class="guide-bg"></div>'+
                '<a href="javascript:;" class="close-mask">关闭提示</a>'+
                '</div>'+
                '<div id="weixinMask" class="ui-weixin-mask" id="weixin-mask"></div>';
            wx.innerHTML = html;
            document.body.appendChild(wx);
            var share = this;
            var items = document.getElementsByClassName('initShare');
            for (var i=0;i<items.length;i++) {
                items[i].onclick = function(e){
                    e.preventDefault();
                    var a = document.getElementById('weixinGuide');
                    var b = document.getElementById('weixinMask');
                    a.style.display = 'block';
                    b.style.display = 'block';
                    a.onclick = function(){
                        a.style.display = 'none';
                        b.style.display = 'none';
                    };
                }
            }
        } else {
            var html = '<div class="bdsharebuttonbox ui-sharing" data-tag="share_1">'+
                '<h3>分享</h3>'+
                '<ul class="share-w3 clearfix">'+
                '<li><a class="bds_weixin weixin" rel="nofollow" data-cmd="weixin">微信</a></li>'+
                '<li><a class="bds_tsina sina" rel="nofollow" data-cmd="tsina">新浪微博</a></li>'+
                '<li><a class="bds_sqq qq" rel="nofollow" data-cmd="sqq">QQ好友</a></li>'+
                '</ul>'+
                '</div>';
            position.innerHTML = html;
            window._bd_share_config={
                "common":{
                    "bdSnsKey":{},
                    "bdText":this.title,
                    "bdDesc":this.desc,
                    "bdUrl":this.url,
                    "bdPic":this.img,
                    "bdMini":"1",
                    "bdMiniList":false,
                    "bdStyle":"1",
                    "bdSize":"24"
                },"share":{}};
            var b = 'https://v3.edaocha.net/static/api/js/share.js?cdnversion='+~(-new Date()/36e5);
            this.isloadApi(b);
        }
    };

    this.init();

    return this;
};

setTimeout(function() {
    var share_obj = new initShare('initShare',{});
}, 3000);
// 是否将当前网页翻译成中文?关闭翻译网页

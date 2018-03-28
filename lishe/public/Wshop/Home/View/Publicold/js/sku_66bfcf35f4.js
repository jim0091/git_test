define("zenjs/backbone/quantity", ["require", "backbone"],
function(t) {
    var e = t("backbone"),
    i = function() {},
    n = e.View.extend({
        template: _.template('<div class="quantity">            <button class="minus" type="button" <% if (data.readonly){ %> disabled <% } %> ></button>            <input type="text" class="txt" value="<%= data.num %>" <% if (data.readonly){ %> readonly <% } %>/>            <button class="plus" type="button" <% if (data.readonly){ %> disabled <% } %>></button>            <div class="response-area response-area-minus"></div>            <div class="response-area response-area-plus"></div>        </div>'),
        initialize: function() {
            var t = function(t, e, i) {
                var n;
                return i > e ? (n = e, 0 !== e && (this.disabled = !0)) : n = i > t ? i: t > e ? e: t,
                n
            };
            return function(e) {
                this.onNumChange = e.onNumChange || i,
                this.onOverLimit = e.onOverLimit || i,
                this.limitNum = parseInt(e.limitNum),
                this.minimalNum = parseInt(e.minimalNum),
                this.minimalNum = 0 == this.minimalNum ? 0 : this.minimalNum || 1,
                this.onBelowLeast = e.onBelowLeast || i,
                this.disabled = e.disabled,
                this.num = t.call(this, e.num, this.limitNum, this.minimalNum),
                this.readonly = e.readonly,
                this.num != e.num && this.onNumChange(this.num)
            }
        } (),
        events: {
            "click .response-area-minus": "onSubClicked",
            "click .response-area-plus": "onAddClicked",
            "blur .txt": "onTxtBlur"
        },
        onSubClicked: function(t) {
            this.changeNum(this.num - 1)
        },
        onAddClicked: function() {
            this.changeNum(this.num + 1)
        },
        onTxtBlur: function(t) {
            var e = t.target.value;
            this.changeNum(isNaN(e) ? 0 : e)
        },
        changeNum: function(t) {
            this.readonly || this.disabled || (t > this.limitNum && (this.onOverLimit(t, this.limitNum), t = this.limitNum), t < this.minimalNum && (this.onBelowLeast(t, this.minimalNum), t = this.minimalNum), this.updateBtnStatus(t), this.updateNum(t))
        },
        updateBtnStatus: function() {
            var t = function(t) {
                t.addClass("disabled"),
                t.attr("disabled", "true")
            },
            e = function(t) {
                t.removeClass("disabled"),
                t.removeAttr("disabled")
            };
            return function(i) {
                return this.readonly ? (t(this.nMinus), void t(this.nPlus)) : (i > this.minimalNum ? e(this.nMinus) : t(this.nMinus), void(this.limitNum > 0 && i >= this.limitNum ? t(this.nPlus) : e(this.nPlus)))
            }
        } (),
        updateNum: function(t) {
            this.disabled || (this.num = +t, this.$("input").val(this.num), this.onNumChange(this.num))
        },
        refreshNum: function() {
            var t = parseInt(this.$("input").val());
            this.num !== t && (t > 0 ? this.limitNum > 0 && t > this.limitNum ? this.num = this.limitNum: this.num = t: (this.num = this.minimalNum, this.updateNum(this.num)), this.changeNum(this.num))
        },
        setLimitNum: function(t) {
            this.disabled || 1 > t || (this.limitNum = +t, this.limitNum < this.num && this.changeNum(this.limitNum))
        },
        setMinimalNum: function(t) {
            this.disabled || 1 > t || (this.minimalNum = +t, this.num < this.minimalNum && this.changeNum(this.minimalNum))
        },
        validateNum: function(t) {
            var e = parseInt(this.$("input").val());
            return this.$("input").val(e),
            e > this.limitNum || 0 === this.limitNum ? (this.onOverLimit(e, this.limitNum), !1) : e < this.minimalNum ? (this.onBelowLeast(e, this.minimalNum), !1) : !0
        },
        getNum: function() {
            return this.validateNum() ? (this.refreshNum(), this.num) : null
        },
        render: function() {
            return this.$el.html(this.template({
                data: {
                    num: this.num,
                    readonly: this.readonly
                }
            })),
            this.nMinus = this.$(".minus"),
            this.nPlus = this.$(".plus"),
            this.updateBtnStatus(this.num),
            this
        }
    });
    return n
}),
define("zenjs/uploader/photo_uploader", ["require", "bower_components/ajax/ajax", "../util/ua"],
function(t) {
    t("bower_components/ajax/ajax");
    var e = t("../util/ua"),
    i = function() {},
    n = window._global,
    o = function() {
        if (navigator.userAgent.match(/(Android (1.0|1.1|1.5|1.6|2.0|2.1))|(Windows Phone (OS 7|8.0))|(XBLWP)|(ZuneWP)|(w(eb)?OSBrowser)|(webOS)|(Kindle\/(1.0|2.0|2.5|3.0))/)) return ! 1;
        var t = document.createElement("input");
        return t.type = "file",
        !t.disabled
    },
    s = function() {
        var t = "youzanwsc" === n.platform && n.platform_version <= "3.0.0",
        i = e.getIOSVersion() >= 9;
        return ! (i && t)
    },
    a = Backbone.View.extend({
        initialize: function(t) {
            this.nInput = this.$("input"),
            this.nUploader = this.$("button"),
            this.save_to_shop = t.save_to_shop || !1,
            this.onValidUpload = t.onValidUpload ||
            function() {
                return ! 0
            },
            this.onStartReadFile = t.onStartReadFile || i,
            this.onFinishReadFile = t.onFinishReadFile || i,
            this.onBeforeUpload = t.onBeforeUpload || i,
            this.onUploadSuccess = t.onUploadSuccess || i,
            this.onUploadError = t.onUploadError || i
        },
        events: {
            "click input": "onInputClicked",
            "change input": "onFileChanged"
        },
        render: function(t) {
            o() || (this.nInput.attr("disabled", "disabled"), this.nUploader.css("padding-left", "10px").html("您的浏览器不支持图片上传").attr("disabled", "disabled")),
            s() || this.nInput.on("click",
            function(t) {
                t.preventDefault(),
                motify.log("暂不支持图片上传功能")
            })
        },
        onInputClicked: function(t) {
            t.target.value = ""
        },
        onFileChanged: function(t) {
            var e = this,
            i = t.target.files;
            _.map(i,
            function(t, i, n) {
                if (t.size > 5242880) return void motify.log("图片太大啦~");
                if (e.onValidUpload({
                    file: t
                })) {
                    e.onStartReadFile({
                        file: t
                    });
                    var o = new FileReader;
                    o.onload = function(i) {
                        e.onFinishReadFile({
                            src: i.target.result,
                            file: t
                        })
                    },
                    o.readAsDataURL(t),
                    e.getUploadToken(t)
                }
            })
        },
        getUploadToken: function(t) {
            var e = this;
            if (this.uptoken) return void this.doUploadPhoto(t);
            var i = window._global.url.materials + "/dock/token.json";
            "https:" === location.protocol && (i = i.replace("http://", "https://")),
            $._ajax({
                url: i,
                type: "post",
                dataType: "json",
                timeout: 5e3,
                cache: !1,
                data: {
                    scope: n.js.qn_public,
                    kdt_id: n.kdt_id || ""
                },
                xhrFields: {
                    withCredentials: !0
                },
                beforeSend: function() {},
                success: function(i) {
                    e.uptoken = i.data.uptoken,
                    e.doUploadPhoto(t)
                },
                error: function(t, e, i) {},
                complete: function(t, e) {}
            })
        },
        doUploadPhoto: function(t) {
            var e = this,
            i = new FormData;
            i.append("token", this.uptoken),
            i.append("file", t),
            this.save_to_shop === !1 && i.append("x:skip_save", 1);
            var o = t.name.split("."),
            s = "";
            o.length > 1 && (s = "." + o[o.length - 1]),
            i.append("x:ext", s),
            $._ajax({
                url: "//up.qbox.me",
                type: "post",
                data: i,
                dataType: "json",
                processData: !1,
                contentType: !1,
                timeout: 8e4,
                beforeSend: function() {
                    e.onBeforeUpload({
                        file: t
                    }),
                    e.nInput.data("uploaded", "false")
                },
                success: function(i) {
                    var o = n.url.imgqn + "/" + i.data.attachment_file;
                    e.onUploadSuccess({
                        url: o,
                        file: t,
                        data: i.data
                    }),
                    e.nInput.data("value", o),
                    e.nInput.data("uploaded", "true")
                },
                error: function(i, n, o) {
                    e.onUploadError({
                        file: t
                    }),
                    "timeout" === n && motify.log("网络环境不佳<br/>请稍后重试")
                },
                complete: function(t, e) {}
            })
        }
    });
    return a
}),
window.Utils = window.Utils || {},
$.extend(window.Utils, {
    validMobile: function(t) {
        return t = "" + t,
        /^((\+86)|(86))?(1)\d{10}$/.test(t)
    },
    validPhone: function(t) {
        return t = "" + t,
        /^0[0-9\-]{10,13}$/.test(t)
    },
    validNumber: function(t) {
        return /^\d+$/.test(t)
    },
    validEmail: function(t) {
        return /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(t)
    },
    validPostalCode: function(t) {
        return t = "" + t,
        /^\d{6}$/.test(t)
    }
}),
define("wap/components/util/valid",
function() {}),
define("text!wap/showcase/sku/templates/message.html", [],
function() {
    return '<%\n    var PLACEHOLDER_MAP = {\n        id_no: \'填写中国大陆18位身份证号\',\n        text: \'\'\n    };\n%>\n\n<% if(messages.length !== 0) {%>\n    <div class=\'sku-message\'>\n        <% for (var j = 0, len = messages.length; j < len; j++) { %>\n        <dl class="clearfix">\n            <dt class="pull-left">\n                <label for="ipt-<%=j %>"><% if (messages[j].required == \'1\') { %><sup class="required">*</sup><% } %><%=messages[j].name %></label>\n            </dt>\n            <dd class="comment-wrapper clearfix">\n                <% if (messages[j].multiple == \'0\') { %>\n                    <% if (messages[j].type === \'image\') { %>\n                        <input data-valid-type="<%=messages[j].type %>"\n                            <% if (isKdtApp) { %>multiple="multiple"<% } %>\n                            <% if (messages[j].required == \'1\') { %>required<% } %>\n                            tabindex="<%=j + 1 %>" id="ipt-<%=j %>"\n                            name="message_<%=j %>"\n                            type="file"\n                            capture="camera"\n                            accept="image/*"\n                            class="js-message photo-input"\n                        >\n                        <button class="btn btn-white image-input-trigger"><span class="take-photo">拍照</span>或<span class="select-photo">选择照片</span></button>\n                        <div class=\'image-input-show hide\'>\n                            <img />\n                            <span class="img-tip">（仅限一张）</span>\n                        </div>\n                    <% } else { %>\n                        <input placeholder="<%= PLACEHOLDER_MAP[messages[j].type] || \'\' %>"\n                            data-valid-type="<%=messages[j].type %>"\n                            <% if (messages[j].required == \'1\') { %>required<% } %>\n                            tabindex="<%=j + 1 %>"\n                            id="ipt-<%=j %>"\n                            name="message_<%=j %>"\n                            type="<%= (\'id_no\' === messages[j].type) ? \'text\' : messages[j].type %>" data-type="<%=messages[j].type %>"\n                            class="txt js-message font-size-14"\n                            <% if (\'id_no\' === messages[j].type && id_no.length > 0) { %>value="<%=id_no %>"<% } %>\n                        >\n                    <% } %>\n                <% } else { %>\n                    <textarea data-valid-type="<%=messages[j].type %>"\n                        <% if (messages[j].required == \'1\') { %>required<% } %>\n                        tabindex="<%=j + 1 %>"\n                        id="ipt-<%=j %>"\n                        name="message_<%=j %>"\n                        cols="32"\n                        rows="1"\n                        class="txta js-message font-size-14"></textarea>\n                <% } %>\n            </dd>\n        </dl>\n        <% } %>\n    </div>\n<% } %>\n'
}),
function(t) {
    var e;
    t.localStorage;
    try {
        var i = new Date;
        try {
            localStorage.setItem(i, i)
        } catch(n) {
            if (22 === n.code) throw "localstorage define error"
        }
        var o = localStorage.getItem(i) == i;
        if (localStorage.removeItem(i), !o) throw "localstorage define error";
        if ("FUNCTION" != (typeof localStorage.clear).toUpperCase()) throw "localstorage define error";
        e = localStorage
    } catch(n) {
        var s = function() {
            return null
        };
        e = {
            getItem: s,
            setItem: s,
            removeItem: s,
            clear: s
        }
    }
    "function" == typeof define && define.amd && define("zenjs/util/local_storage", [],
    function() {
        return e
    }),
    t.YZLocalStorage = e
} (window),
define("wap/showcase/sku/views/message", ["require", "zenjs/uploader/photo_uploader", "wap/components/util/valid", "text!wap/showcase/sku/templates/message.html", "zenjs/util/ua", "zenjs/util/local_storage"],
function(t) {
    var e = t("zenjs/uploader/photo_uploader"),
    i = (t("wap/components/util/valid"), t("text!wap/showcase/sku/templates/message.html")),
    n = t("zenjs/util/ua"),
    o = t("zenjs/util/local_storage"),
    s = n.isIOS(),
    a = "youzanwsc" === window._global.platform || "youzanwxd" === window._global.platform,
    r = Backbone.View.extend({
        template: _.template(i),
        initialize: function(t) {
            this.messages = this.options.messages || []
        },
        render: function() {
            this.$el.html(this.template({
                messages: this.messages,
                id_no: o.getItem("id_no") || "",
                isIOS: s,
                isKdtApp: a
            })),
            0 === this.messages.length && this.$el.hide();
            var t = this.$(".photo-input");
            return this.photoUploaders = [],
            t.each(_(function(t, i) {
                var n = $(i).parent(),
                o = n.find("img"),
                s = n.find(".image-input-show"),
                a = n.find("button"),
                r = new e({
                    el: n,
                    onFinishReadFile: function(t) {
                        s.removeClass("hide"),
                        o.attr("src", t.src)
                    },
                    onBeforeUpload: function() {
                        a.html("正在上传...")
                    },
                    onUploadSuccess: function(t) {
                        motify.log("上传成功"),
                        o.attr("src", t.url + "!100x100.jpg"),
                        a.html('<span class="take-photo">重拍</span>或<span class="select-photo">重新选择照片</span>')
                    },
                    onUploadError: function() {
                        a.html("重新上传")
                    }
                }).render();
                this.photoUploaders.push(r)
            }).bind(this)),
            s || this.$el.find("input[type=date], input[type=time]").on("mouseover mousedown mouseup select click dblclick touchstart touchmove touchend touchcancel",
            function(t) {
                t.stopPropagation()
            }),
            this
        },
        validate: function(t) {
            for (var e, i, n, o, s = this,
            a = this.messages,
            r = 0,
            l = a.length; l > r; r++) {
                if (e = "message_" + r, i = $.trim(t[e]), n = a[r], _.isEmpty(i)) {
                    if ("1" == n.required) return s.$el.find("#ipt-" + r).focus(),
                    "image" == n.type ? motify.log("请上传 " + n.name + "。") : motify.log("请填写 " + n.name + "。"),
                    !1
                } else {
                    if ("image" == n.type && (o = s.$el.find("#ipt-" + r).data("uploaded"), "false" == o || !o)) return motify.log("图片还在上传中，请稍等。。"),
                    !1;
                    if ("tel" == n.type && !Utils.validNumber(i)) return s.$el.find("#ipt-" + r).focus(),
                    motify.log("请填写正确的" + n.name + "。"),
                    !1;
                    if ("email" == n.type && !Utils.validEmail(i)) return s.$el.find("#ipt-" + r).focus(),
                    motify.log("请填写正确的" + n.name + "。"),
                    !1;
                    if ("id_no" == n.type && (i.length < 15 || i.length > 18)) return s.$el.find("#ipt-" + r).focus(),
                    motify.log("请填写正确的" + n.name + "。"),
                    !1
                }
                if (i.length > 200) return motify.log(n.name + " 写的太多了<br/>不要超过200字"),
                !1
            }
            return ! 0
        },
        getData: function() {
            var t = {};
            return this.$("dl .js-message").each(function(e, i) {
                if ("file" == i.type) var n = $(i).data("value");
                t[i.name] = n || i.value || "",
                "id_no" === $(i).data("type") && o.setItem("id_no", $(i).val())
            }),
            this.validate(t) ? t: null
        }
    });
    return r
}),
define("zenjs/backbone/base_view", ["require", "backbone", "../core/trigger_method"],
function(t) {
    var e = t("backbone"),
    i = t("../core/trigger_method");
    return e.View.extend({
        clean: function() {
            return this.stopListening(),
            this
        },
        triggerMethod: i
    })
}),
define("bower_components/zenlist/list", ["require", "exports", "module", "zenjs/backbone/base_view"],
function(t, e, i) {
    var n = function() {},
    o = t("zenjs/backbone/base_view");
    i.exports = o.extend({
        initialize: function(t) {
            return this.options = t = t || {},
            this.items = [],
            this.itemView = t.itemView,
            this.itemOptions = t.itemOptions || {},
            this.collection = t.collection,
            this.onAfterListChange = t.onAfterListChange || n,
            this.onAfterListLoad = t.onAfterListLoad || n,
            this.onAfterListDisplay = t.onAfterListDisplay || n,
            this.onListEmpty = t.onListEmpty || t.onEmptyList || this._onListEmpty,
            this.onItemClick = t.onItemClick || n,
            this.onViewItemAdded = t.onViewItemAdded || n,
            this.displaySize = t.displaySize || -1,
            this.emptyHTML = t.emptyHTML || "",
            this.emptyText = t.emptyText || "列表为空",
            this
        },
        render: function(t) {
            return this.displaySize = -1 == (t || {}).displaySize ? -1 : this.displaySize,
            this.clean(),
            this._setupListeners(),
            this.addAll(),
            this.onAfterListDisplay({
                list: this.collection
            }),
            this
        },
        fetchRender: function(t) {
            return this.collection.fetch({
                data: t,
                success: _(function(t, e) {
                    this.render(),
                    this.onAfterListLoad(this.collection, e),
                    this.onFetchSuccess && this.onFetchSuccess()
                }).bind(this),
                error: _.bind(function() {
                    this.onAfterListLoad(this.collection, response)
                },
                this)
            }),
            this
        },
        _setupListeners: function() {
            this.collection && (this.stopListening(this.collection), this.listenTo(this.collection, "add", this.addItem, this), this.listenTo(this.collection, "reset sort", this.render, this), this.listenTo(this.collection, "remove", this.onItemRemoved, this))
        },
        addItemListeners: function(t) {
            var e = this;
            this.listenTo(t, "all",
            function() {
                var e = "item:" + arguments[0],
                i = _.toArray(arguments);
                i.splice(0, 1),
                i.unshift(e, t),
                this.trigger.apply(this, i),
                "item:click" == e && this.onItemClick()
            }),
            this.listenTo(t.model, "change",
            function() {
                e.onAfterListChange({
                    list: this.collection
                })
            })
        },
        addAll: function() {
            0 === this.collection.length ? this.fetching || this.triggerMethod("list:empty") : this.collection.each(function(t) {
                this.addItem(t)
            },
            this)
        },
        removeAll: function() {
            for (var t = this.items.length - 1; t >= 0; t--) this.removeView(this.items[t]);
            this.onAfterListChange({
                list: this.collection
            })
        },
        addItem: function(t, e, i) {
            if (! (this.displaySize >= 0 && this.items.length >= this.displaySize)) {
                1 == this.collection.length && (this.listEl || this.$el).html("");
                var n = new this.itemView(_.extend({},
                this.options.itemOptions, {
                    model: t,
                    index: this.collection.indexOf(t)
                }));
                this.items.push(n),
                this.addItemListeners(n),
                n.render(),
                this.onViewItemAdded({
                    list: this.items,
                    viewItem: n,
                    model: t
                });
                var o = (i || {}).at;
                return 0 === o ? (this.listEl || this.$el).prepend(n.el) : (this.listEl || this.$el).append(n.el),
                n
            }
        },
        removeItem: function(t) {
            var e = this.getViewByModel(t);
            e && this.removeView(e)
        },
        removeView: function(t) {
            var e;
            this.stopListening(t),
            t && (this.stopListening(t.model), t.remove(), e = this.items.indexOf(t), this.items.splice(e, 1)),
            0 === this.collection.length && (this.fetching || this.triggerMethod("list:empty"))
        },
        onItemRemoved: function(t) {
            this.onAfterListChange({
                list: this.collection,
                action: "remove",
                model: t
            }),
            this.removeItem(t)
        },
        getViewByModel: function(t) {
            return _.find(this.items,
            function(e, i) {
                return e.model === t
            })
        },
        dispatchEventToAllViews: function(t, e) {
            for (var i = this.items.length - 1; i >= 0; i--) this.items[i].trigger(t, e)
        },
        remove: function() {
            o.prototype.remove.call(this, arguments),
            this.removeAll(),
            this.collection.reset(),
            delete this.collection
        },
        clean: function() {
            o.prototype.clean.call(this, arguments),
            this.removeAll(),
            (this.listEl || this.$el).html(""),
            this.stopListening(this.collection)
        },
        _onListEmpty: function() {
            this.$el.html(this.emptyHTML || (this.emptyText ? '<p style="text-align:center;line-height:60px;">' + this.emptyText + "</p>": ""))
        }
    })
}),
define("zenjs/util/url_helper", [],
function() {
    var t = {
        site: function(t, e) {
            var i = window._global.env,
            n = window._global.url;
            "static" === e && "online" === i && (e = "cdn_static");
            var o = t;
            return e = e ? n[e] || "": "",
            -1 == t.search(/^http[s]?\:\/\//) && ("/" !== t[0] && (t = "/" + t), o = e + t),
            o
        },
        getCdnImageUrl: function(t, e) {
            if (!t) return window._global.url.cdn_static + "/image/wap/no_pic.png";
            if (e = t.match(/.+\!\d+x\d+.+/) ? "": e && e.length > 0 ? e: "!100x100.jpg", t.match(/^(https?:)?\/\//i)) {
                for (var i = [/^(https?:)?\/\/imgqn.koudaitong.com/, /^(https?:)?\/\/kdt-img.koudaitong.com/, /^(https?:)?\/\/img.yzcdn.cn/, /^(https?:)?\/\/dn-kdt-img.qbox.me/], n = 0; n < i.length; n++) t = t.replace(i[n], window._global.url.imgqn);
                return t + e
            }
            return window._global.url.imgqn + "/" + t + e
        }
    };
    return t
}),
define("text!wap/showcase/sku/templates/skuList.html", [],
function() {
    return '<dt class="model-title sku-sel-title">\n    <label><%= skuCollection.k %>：</label>\n</dt>\n<dd>\n    <ul class="model-list sku-sel-list"></ul>\n</dd>'
}),
define("wap/showcase/sku/views/sku_list", ["bower_components/zenlist/list", "zenjs/util/url_helper", "text!wap/showcase/sku/templates/skuList.html"],
function(t, e, i) {
    var n = Backbone.View.extend({
        tagName: "li",
        className: "tag sku-tag pull-left ellipsis",
        template: _.template("<%= data.name %>"),
        initialize: function(t) {
            this.onItemClick = t.onItemClick ||
            function() {},
            this.listenTo(this, "active", this.onActive),
            this.listenTo(this, "enable", this.enableView),
            this.listenTo(this, "disable", this.disableView)
        },
        events: {
            click: "onClick"
        },
        onClick: function(t) {
            return this.$el.hasClass("unavailable") ? !1 : (this.toggle(), void this.onItemClick({
                v_id: this.model.id
            }))
        },
        onActive: function(t) {
            t.v_id !== this.model.id && this.deActive()
        },
        toggle: function() {
            this.$el.toggleClass("active"),
            this.isActived = !this.isActived
        },
        active: function() {
            this.$el.addClass("active"),
            this.isActived = !0
        },
        deActive: function() {
            this.isActived && (this.$el.removeClass("active"), this.isActived = !1)
        },
        disableView: function(t) {
            parseInt(this.model.get("id"), 10) == parseInt(t.value, 10) && (this.$el.addClass("unavailable"), this.enabled = !1)
        },
        enableView: function(t) {
            this.enabled || !t || "all" !== t.value && parseInt(t.value, 10) !== parseInt(this.model.get("id"), 10) || (this.$el.removeClass("unavailable"), this.enabled = !0)
        },
        isEnabled: function() {
            return this.enabled
        },
        render: function() {
            return this.$el.html(this.template(_.extend({
                data: this.model.toJSON()
            },
            {}))),
            this.enabled = !0,
            this
        }
    }),
    o = Backbone.View.extend({
        tagName: "dl",
        className: "clearfix block-item sku-list-container",
        template: _.template(i),
        initialize: function(t) {
            this.skuCollection = t.skuCollection,
            this.onSkuActived = t.onSkuActived ||
            function() {}
        },
        onItemClick: function(t) {
            this.skuListView.dispatchEventToAllViews("active", t),
            this.onSkuActived(this.getActivedSku())
        },
        getActivedSku: function() {
            var t = this.skuListView.items,
            i = _.find(t, _(function(t) {
                return t.isActived
            }).bind(this));
            if (i) {
                var n = i.model.get("imgUrl");
                return {
                    thumb: n && e.getCdnImageUrl(n),
                    k_id: this.skuCollection.k_id,
                    k_s: this.skuCollection.k_s,
                    v_id: i.model.id
                }
            }
            return null
        },
        activeFirstSku: function() {
            if (this.skuListView.items) for (var t in this.skuListView.items) {
                var e = this.skuListView.items[t];
                if (e.isEnabled()) return void e.onClick()
            }
        },
        disableSkuItem: function(t) {
            this.skuListView.dispatchEventToAllViews("disable", {
                value: t
            })
        },
        enableSkuItem: function(t) {
            this.skuListView.dispatchEventToAllViews("enable", {
                value: t
            })
        },
        enalbeAllSkuItem: function() {
            this.skuListView.dispatchEventToAllViews("enable", {
                value: "all"
            })
        },
        render: function(e) {
            return this.$el.html(this.template({
                skuCollection: this.skuCollection
            })),
            this.skuListView = new t({
                collection: this.skuCollection,
                el: this.$(".model-list"),
                itemView: n,
                itemOptions: {
                    onItemClick: _(this.onItemClick).bind(this)
                }
            }).render(),
            this
        }
    });
    return o
}),
define("wap/showcase/sku/views/sku_brain", [],
function() {
    var t = Backbone.View.extend({
        initialize: function(t) {
            this.collection = t.collection,
            this.skuTrees = t.skuTrees,
            Backbone.EventCenter.on("active", _(this.onSkuActived).bind(this))
        },
        onSkuActived: function(t) {
            var e = this,
            i = t.activedSkus,
            n = (t.clickedSku, ["s1", "s2", "s3"]);
            _.each(n, _(function(t) {
                var n = _.find(this.skuTrees,
                function(e) {
                    return e.k_s == t
                });
                if (n) {
                    var o = _.filter(i,
                    function(e) {
                        return e.k_s !== t
                    });
                    if (0 === o.length) return void n.each(function(i) {
                        e.trigger("sku-comb:hasstock", {
                            v_id: i.get("id"),
                            k_s: t
                        })
                    });
                    var s = this.collection.filter(function(t) {
                        return _.every(o,
                        function(e) {
                            return t.get(e.k_s) === e.v_id
                        })
                    }),
                    a = _.groupBy(s,
                    function(e) {
                        return e.get(t)
                    });
                    _.each(a,
                    function(i, n) {
                        var o = _.every(i,
                        function(t) {
                            return 0 == t.get("stock_num")
                        });
                        o ? e.trigger("sku-comb:nostock", {
                            v_id: n,
                            k_s: t
                        }) : e.trigger("sku-comb:hasstock", {
                            v_id: n,
                            k_s: t
                        })
                    })
                }
            }).bind(this))
        }
    });
    return t
}),
define("wap/showcase/sku/model", [],
function(t, e) {
    var i, n;
    return e = e || {},
    e.SkuModel = i = Backbone.Model,
    e.SkuCollection = Backbone.Collection.extend({
        model: i
    }),
    e.SkuStockModel = n = Backbone.Model,
    e.SkuStockCollection = Backbone.Collection.extend({
        model: n
    }),
    e
}),
define("wap/showcase/sku/views/sku_selector", ["./sku_list", "./sku_brain", "../model"],
function(t, e, i) {
    var n = i.SkuStockCollection,
    o = i.SkuCollection,
    s = Backbone.View.extend({
        initialize: function(t) {
            var e = this;
            t = t || {},
            this.goods_info = t.goods_info || {},
            this.skuCollectionArray = [],
            this.sku = (t || {}).sku,
            this.skuStockCollection = new n(this.sku.list),
            e.sku.none_sku ? this.selectedSkuComb = {
                id: e.sku.collection_id,
                get: function(t) {
                    return "price" === t ? e.sku.collection_price || "": "points_price" === t ? e.sku.collection_points_price || "": void 0
                }
            }: _.each(this.sku.tree, _(function(t) {
                var e = new o(t.v);
                e.count = t.count,
                e.k = t.k,
                e.k_id = t.k_id,
                e.k_s = t.k_s,
                this.skuCollectionArray.push(e)
            }).bind(this)),
            Backbone.EventCenter.on("enable", _(this.enalbeAllSkuItem).bind(this))
        },
        events: {},
        onSkuActived: function(t) {
            var e = [];
            _.each(this.skuListViews,
            function(t) {
                var i = t.getActivedSku();
                i && e.push(i)
            }),
            this.selectedSkuComb = this.getSelectedSkuComb(e),
            this.selectedSkuThumb = this.getSelectedSkuThumb(e),
            Backbone.EventCenter.trigger("sku:selected", {
                skuComb: this.selectedSkuComb,
                skuThumb: this.selectedSkuThumb
            }),
            Backbone.EventCenter.trigger("active", {
                activedSkus: e,
                clickedSku: t
            })
        },
        getSelectedSkuComb: function(t) {
            this.activedSkus = t;
            var e = {},
            i = null;
            return _.each(t,
            function(t) {
                e[t.k_s] = t.v_id
            }),
            t.length === _.size(this.skuListViews) && (i = this.skuStockCollection.find(function(t) {
                for (var i in e) if (t.get(i) !== e[i]) return ! 1;
                return ! 0
            })),
            i
        },
        getSelectedSkuThumb: function(t) {
            var e = (this.goods_info.picture || [])[0];
            return _.each(t,
            function(t) {
                return t.thumb ? (e = t.thumb, !1) : void 0
            }),
            e
        },
        diableSkuItem: function(t) {
            this.skuListViews[t.k_s].disableSkuItem(t.v_id)
        },
        enableSkuItem: function(t) {
            this.skuListViews[t.k_s].enableSkuItem(t.v_id)
        },
        enalbeAllSkuItem: function(t) { !! this.skuListViews[t.k_s] && this.skuListViews[t.k_s].enalbeAllSkuItem()
        },
        autoSelect: function() {
            this.skuListViews && _.each(this.skuListViews,
            function(t) {
                1 === t.skuCollection.length && t.activeFirstSku()
            })
        },
        getSelectedSku: function() {
            var t = _.pluck(this.sku.tree, "k_id"),
            e = this.sku,
            i = [];
            return this.selectedSkuComb ? {
                status: !0,
                sku: this.selectedSkuComb
            }: (_.each(this.activedSkus,
            function(e) {
                t = _.without(t, "" + e.k_id)
            }), _.each(t,
            function(t) {
                i.push(_.find(e.tree,
                function(e) {
                    return e.k_id === t
                }).k)
            }), {
                status: !1,
                errMsg: i.join(" 和 ")
            })
        },
        render: function(i) {
            return this.skuListViews = {},
            _.each(this.skuCollectionArray, _(function(e) {
                var i = new t({
                    skuCollection: e,
                    onSkuActived: _(this.onSkuActived).bind(this)
                });
                this.$el.append(i.render().el),
                this.skuListViews[e.k_s] = i
            }).bind(this)),
            this.skuBrain = new e({
                collection: this.skuStockCollection,
                skuTrees: this.skuCollectionArray
            }),
            this.listenTo(this.skuBrain, "sku-comb:nostock", _(this.diableSkuItem).bind(this)),
            this.listenTo(this.skuBrain, "sku-comb:hasstock", _(this.enableSkuItem).bind(this)),
            this
        },
        clear: function() {
            return this.stopListening(),
            Backbone.EventCenter.off("enable"),
            this.remove(),
            null
        }
    });
    return s
}),
define("text!wap/showcase/sku/templates/stock.html", [],
function() {
    return "剩余<%= data.stock %>件\n"
}),
define("wap/showcase/sku/views/sku_stock", ["text!wap/showcase/sku/templates/stock.html"],
function(t) {
    var e = Backbone.View.extend({
        template: _.template(t),
        initialize: function(t) {
            this.hide_stock = t.hide_stock
        },
        events: {},
        onClick: function(t) {},
        render: function(t) {
            return this.stock = this.stock || t.stock,
            !this.hide_stock && this.stock && this.$el.html(this.template({
                data: {
                    stock: this.stock
                }
            })),
            this
        },
        setNum: function(t) {
            this.stock = t,
            this.render({})
        }
    });
    return e
}),
define("text!wap/showcase/sku/templates/title.html", [],
function() {
    return "<% if (goods_info['picture'].length > 0){ %>\n    <div class=\"thumb\"><img class=\"js-goods-thumb goods-thumb\" src=\"<%=goods_info['picture'][0] %>\" alt=\"\"></div>\n<% } %>\n<div class=\"detail goods-base-info clearfix\">\n    <p class=\"title c-black ellipsis\"><%=goods_info['title'] %></p>\n    <div class=\"goods-price clearfix\">\n    <% if (isPoints) { %>\n        <div class=\"current-price c-black\">\n            <span class='price-name pull-left font-size-14 c-orange'>积分价：</span><i class=\"js-points-price price font-size-16 vertical-middle c-orange\"><%= sku['points_price'] || points_goods['points_price'] %></i>\n        </div>\n    <% } else if(activity){ %>\n        <div class=\"current-price c-black activity-price\">\n            <span class='price-name pull-left font-size-14 c-orange'>￥</span><i class=\"js-goods-price price font-size-16 vertical-middle c-orange\"><%=activity['price'] %></i>\n            <span class=\"price-tag vertical-middle\"><%=activity['price_title'] %></span>\n        </div>\n        <div class=\"old-price line-through\">原价：￥<%=sku['old_price'] && sku['old_price'] != '0.00' && sku['old_price'] != 0.00 ? sku['old_price'] : goods_info['price'] %></div>\n    <% }else{ %>\n        <div class=\"current-price pull-left c-black\">\n            <span class='price-name pull-left font-size-14 c-orange'>￥</span><i class=\"js-goods-price price font-size-16 vertical-middle c-orange\"><%=sku['price'] && sku['price'] != '0.00' && sku['price'] != 0.00 ? sku['price'] : goods_info['price'] %></i>\n        </div>\n    <% } %>\n    <% if(goods_info['origin'] && goods_info['origin'] !== '淘价：'){ %>\n        <div class=\"original-price vertical-middle font-size-14 line-through\"><%=goods_info['origin'] %></div>\n    <% } %>\n    </div>\n</div>\n<div class=\"js-cancel sku-cancel\">\n    <div class=\"cancel-img\"></div>\n</div>\n"
}),
define("wap/showcase/sku/views/sku_title", ["zenjs/util/image", "text!wap/showcase/sku/templates/title.html"],
function(t, e) {
    var i = Backbone.View.extend({
        initialize: function(t) {
            this.difTitle = (t || {}).difTitle
        },
        template: _.template(e),
        changeSkuThumb: function(e) {
            this.skuThumb = e,
            this.$(".js-goods-thumb").attr("src", t.toWebp(this.skuThumb + "!100x100.jpg"))
        },
        changePrice: function(t) {
            this.price = t,
            this.$(".js-goods-price").html((t / 100).toFixed(2))
        },
        changePointsPrice: function(t) {
            this.points_price = t,
            this.$(".js-points-price").html(t)
        },
        getPrice: function() {
            return this.price
        },
        getPointsPrice: function() {
            return this.points_price
        },
        resetPrice: function() {
            this.$(".js-goods-price").html(this.priceScope),
            this.$(".js-points-price").html(this.pointsPriceScope)
        },
        render: function(t) {
            return this.$el.html(this.template(t)),
            this.priceScope = this.$el.find(".js-goods-price").text(),
            this.pointsPriceScope = this.$el.find(".js-points-price").text(),
            this.isPoints = t.isPoints,
            this
        }
    });
    return i
}),
define("bower_components/aes/aes", ["require", "exports", "module"],
function(t, e, i) {
    var n = n ||
    function(t, e) {
        var i = {},
        n = i.lib = {},
        o = function() {},
        s = n.Base = {
            extend: function(t) {
                o.prototype = this;
                var e = new o;
                return t && e.mixIn(t),
                e.hasOwnProperty("init") || (e.init = function() {
                    e.$super.init.apply(this, arguments)
                }),
                e.init.prototype = e,
                e.$super = this,
                e
            },
            create: function() {
                var t = this.extend();
                return t.init.apply(t, arguments),
                t
            },
            init: function() {},
            mixIn: function(t) {
                for (var e in t) t.hasOwnProperty(e) && (this[e] = t[e]);
                t.hasOwnProperty("toString") && (this.toString = t.toString)
            },
            clone: function() {
                return this.init.prototype.extend(this)
            }
        },
        a = n.WordArray = s.extend({
            init: function(t, i) {
                t = this.words = t || [],
                this.sigBytes = i != e ? i: 4 * t.length
            },
            toString: function(t) {
                return (t || l).stringify(this)
            },
            concat: function(t) {
                var e = this.words,
                i = t.words,
                n = this.sigBytes;
                if (t = t.sigBytes, this.clamp(), n % 4) for (var o = 0; t > o; o++) e[n + o >>> 2] |= (i[o >>> 2] >>> 24 - 8 * (o % 4) & 255) << 24 - 8 * ((n + o) % 4);
                else if (65535 < i.length) for (o = 0; t > o; o += 4) e[n + o >>> 2] = i[o >>> 2];
                else e.push.apply(e, i);
                return this.sigBytes += t,
                this
            },
            clamp: function() {
                var e = this.words,
                i = this.sigBytes;
                e[i >>> 2] &= 4294967295 << 32 - 8 * (i % 4),
                e.length = t.ceil(i / 4)
            },
            clone: function() {
                var t = s.clone.call(this);
                return t.words = this.words.slice(0),
                t
            },
            random: function(e) {
                for (var i = [], n = 0; e > n; n += 4) i.push(4294967296 * t.random() | 0);
                return new a.init(i, e)
            }
        }),
        r = i.enc = {},
        l = r.Hex = {
            stringify: function(t) {
                var e = t.words;
                t = t.sigBytes;
                for (var i = [], n = 0; t > n; n++) {
                    var o = e[n >>> 2] >>> 24 - 8 * (n % 4) & 255;
                    i.push((o >>> 4).toString(16)),
                    i.push((15 & o).toString(16))
                }
                return i.join("")
            },
            parse: function(t) {
                for (var e = t.length,
                i = [], n = 0; e > n; n += 2) i[n >>> 3] |= parseInt(t.substr(n, 2), 16) << 24 - 4 * (n % 8);
                return new a.init(i, e / 2)
            }
        },
        c = r.Latin1 = {
            stringify: function(t) {
                var e = t.words;
                t = t.sigBytes;
                for (var i = [], n = 0; t > n; n++) i.push(String.fromCharCode(e[n >>> 2] >>> 24 - 8 * (n % 4) & 255));
                return i.join("")
            },
            parse: function(t) {
                for (var e = t.length,
                i = [], n = 0; e > n; n++) i[n >>> 2] |= (255 & t.charCodeAt(n)) << 24 - 8 * (n % 4);
                return new a.init(i, e)
            }
        },
        u = r.Utf8 = {
            stringify: function(t) {
                try {
                    return decodeURIComponent(escape(c.stringify(t)))
                } catch(e) {
                    throw Error("Malformed UTF-8 data")
                }
            },
            parse: function(t) {
                return c.parse(unescape(encodeURIComponent(t)))
            }
        },
        d = n.BufferedBlockAlgorithm = s.extend({
            reset: function() {
                this._data = new a.init,
                this._nDataBytes = 0
            },
            _append: function(t) {
                "string" == typeof t && (t = u.parse(t)),
                this._data.concat(t),
                this._nDataBytes += t.sigBytes
            },
            _process: function(e) {
                var i = this._data,
                n = i.words,
                o = i.sigBytes,
                s = this.blockSize,
                r = o / (4 * s),
                r = e ? t.ceil(r) : t.max((0 | r) - this._minBufferSize, 0);
                if (e = r * s, o = t.min(4 * e, o), e) {
                    for (var l = 0; e > l; l += s) this._doProcessBlock(n, l);
                    l = n.splice(0, e),
                    i.sigBytes -= o
                }
                return new a.init(l, o)
            },
            clone: function() {
                var t = s.clone.call(this);
                return t._data = this._data.clone(),
                t
            },
            _minBufferSize: 0
        });
        n.Hasher = d.extend({
            cfg: s.extend(),
            init: function(t) {
                this.cfg = this.cfg.extend(t),
                this.reset()
            },
            reset: function() {
                d.reset.call(this),
                this._doReset()
            },
            update: function(t) {
                return this._append(t),
                this._process(),
                this
            },
            finalize: function(t) {
                return t && this._append(t),
                this._doFinalize()
            },
            blockSize: 16,
            _createHelper: function(t) {
                return function(e, i) {
                    return new t.init(i).finalize(e)
                }
            },
            _createHmacHelper: function(t) {
                return function(e, i) {
                    return new h.HMAC.init(t, i).finalize(e)
                }
            }
        });
        var h = i.algo = {};
        return i
    } (Math); !
    function() {
        var t = n,
        e = t.lib.WordArray;
        t.enc.Base64 = {
            stringify: function(t) {
                var e = t.words,
                i = t.sigBytes,
                n = this._map;
                t.clamp(),
                t = [];
                for (var o = 0; i > o; o += 3) for (var s = (e[o >>> 2] >>> 24 - 8 * (o % 4) & 255) << 16 | (e[o + 1 >>> 2] >>> 24 - 8 * ((o + 1) % 4) & 255) << 8 | e[o + 2 >>> 2] >>> 24 - 8 * ((o + 2) % 4) & 255, a = 0; 4 > a && i > o + .75 * a; a++) t.push(n.charAt(s >>> 6 * (3 - a) & 63));
                if (e = n.charAt(64)) for (; t.length % 4;) t.push(e);
                return t.join("")
            },
            parse: function(t) {
                var i = t.length,
                n = this._map,
                o = n.charAt(64);
                o && (o = t.indexOf(o), -1 != o && (i = o));
                for (var o = [], s = 0, a = 0; i > a; a++) if (a % 4) {
                    var r = n.indexOf(t.charAt(a - 1)) << 2 * (a % 4),
                    l = n.indexOf(t.charAt(a)) >>> 6 - 2 * (a % 4);
                    o[s >>> 2] |= (r | l) << 24 - 8 * (s % 4),
                    s++
                }
                return e.create(o, s)
            },
            _map: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/="
        }
    } (),
    function(t) {
        function e(t, e, i, n, o, s, a) {
            return t = t + (e & i | ~e & n) + o + a,
            (t << s | t >>> 32 - s) + e
        }
        function i(t, e, i, n, o, s, a) {
            return t = t + (e & n | i & ~n) + o + a,
            (t << s | t >>> 32 - s) + e
        }
        function o(t, e, i, n, o, s, a) {
            return t = t + (e ^ i ^ n) + o + a,
            (t << s | t >>> 32 - s) + e
        }
        function s(t, e, i, n, o, s, a) {
            return t = t + (i ^ (e | ~n)) + o + a,
            (t << s | t >>> 32 - s) + e
        }
        for (var a = n,
        r = a.lib,
        l = r.WordArray,
        c = r.Hasher,
        r = a.algo,
        u = [], d = 0; 64 > d; d++) u[d] = 4294967296 * t.abs(t.sin(d + 1)) | 0;
        r = r.MD5 = c.extend({
            _doReset: function() {
                this._hash = new l.init([1732584193, 4023233417, 2562383102, 271733878])
            },
            _doProcessBlock: function(t, n) {
                for (var a = 0; 16 > a; a++) {
                    var r = n + a,
                    l = t[r];
                    t[r] = 16711935 & (l << 8 | l >>> 24) | 4278255360 & (l << 24 | l >>> 8)
                }
                var a = this._hash.words,
                r = t[n + 0],
                l = t[n + 1],
                c = t[n + 2],
                d = t[n + 3],
                h = t[n + 4],
                p = t[n + 5],
                m = t[n + 6],
                f = t[n + 7],
                g = t[n + 8],
                b = t[n + 9],
                k = t[n + 10],
                v = t[n + 11],
                w = t[n + 12],
                y = t[n + 13],
                x = t[n + 14],
                _ = t[n + 15],
                C = a[0],
                F = a[1],
                z = a[2],
                j = a[3],
                C = e(C, F, z, j, r, 7, u[0]),
                j = e(j, C, F, z, l, 12, u[1]),
                z = e(z, j, C, F, c, 17, u[2]),
                F = e(F, z, j, C, d, 22, u[3]),
                C = e(C, F, z, j, h, 7, u[4]),
                j = e(j, C, F, z, p, 12, u[5]),
                z = e(z, j, C, F, m, 17, u[6]),
                F = e(F, z, j, C, f, 22, u[7]),
                C = e(C, F, z, j, g, 7, u[8]),
                j = e(j, C, F, z, b, 12, u[9]),
                z = e(z, j, C, F, k, 17, u[10]),
                F = e(F, z, j, C, v, 22, u[11]),
                C = e(C, F, z, j, w, 7, u[12]),
                j = e(j, C, F, z, y, 12, u[13]),
                z = e(z, j, C, F, x, 17, u[14]),
                F = e(F, z, j, C, _, 22, u[15]),
                C = i(C, F, z, j, l, 5, u[16]),
                j = i(j, C, F, z, m, 9, u[17]),
                z = i(z, j, C, F, v, 14, u[18]),
                F = i(F, z, j, C, r, 20, u[19]),
                C = i(C, F, z, j, p, 5, u[20]),
                j = i(j, C, F, z, k, 9, u[21]),
                z = i(z, j, C, F, _, 14, u[22]),
                F = i(F, z, j, C, h, 20, u[23]),
                C = i(C, F, z, j, b, 5, u[24]),
                j = i(j, C, F, z, x, 9, u[25]),
                z = i(z, j, C, F, d, 14, u[26]),
                F = i(F, z, j, C, g, 20, u[27]),
                C = i(C, F, z, j, y, 5, u[28]),
                j = i(j, C, F, z, c, 9, u[29]),
                z = i(z, j, C, F, f, 14, u[30]),
                F = i(F, z, j, C, w, 20, u[31]),
                C = o(C, F, z, j, p, 4, u[32]),
                j = o(j, C, F, z, g, 11, u[33]),
                z = o(z, j, C, F, v, 16, u[34]),
                F = o(F, z, j, C, x, 23, u[35]),
                C = o(C, F, z, j, l, 4, u[36]),
                j = o(j, C, F, z, h, 11, u[37]),
                z = o(z, j, C, F, f, 16, u[38]),
                F = o(F, z, j, C, k, 23, u[39]),
                C = o(C, F, z, j, y, 4, u[40]),
                j = o(j, C, F, z, r, 11, u[41]),
                z = o(z, j, C, F, d, 16, u[42]),
                F = o(F, z, j, C, m, 23, u[43]),
                C = o(C, F, z, j, b, 4, u[44]),
                j = o(j, C, F, z, w, 11, u[45]),
                z = o(z, j, C, F, _, 16, u[46]),
                F = o(F, z, j, C, c, 23, u[47]),
                C = s(C, F, z, j, r, 6, u[48]),
                j = s(j, C, F, z, f, 10, u[49]),
                z = s(z, j, C, F, x, 15, u[50]),
                F = s(F, z, j, C, p, 21, u[51]),
                C = s(C, F, z, j, w, 6, u[52]),
                j = s(j, C, F, z, d, 10, u[53]),
                z = s(z, j, C, F, k, 15, u[54]),
                F = s(F, z, j, C, l, 21, u[55]),
                C = s(C, F, z, j, g, 6, u[56]),
                j = s(j, C, F, z, _, 10, u[57]),
                z = s(z, j, C, F, m, 15, u[58]),
                F = s(F, z, j, C, y, 21, u[59]),
                C = s(C, F, z, j, h, 6, u[60]),
                j = s(j, C, F, z, v, 10, u[61]),
                z = s(z, j, C, F, c, 15, u[62]),
                F = s(F, z, j, C, b, 21, u[63]);
                a[0] = a[0] + C | 0,
                a[1] = a[1] + F | 0,
                a[2] = a[2] + z | 0,
                a[3] = a[3] + j | 0
            },
            _doFinalize: function() {
                var e = this._data,
                i = e.words,
                n = 8 * this._nDataBytes,
                o = 8 * e.sigBytes;
                i[o >>> 5] |= 128 << 24 - o % 32;
                var s = t.floor(n / 4294967296);
                for (i[(o + 64 >>> 9 << 4) + 15] = 16711935 & (s << 8 | s >>> 24) | 4278255360 & (s << 24 | s >>> 8), i[(o + 64 >>> 9 << 4) + 14] = 16711935 & (n << 8 | n >>> 24) | 4278255360 & (n << 24 | n >>> 8), e.sigBytes = 4 * (i.length + 1), this._process(), e = this._hash, i = e.words, n = 0; 4 > n; n++) o = i[n],
                i[n] = 16711935 & (o << 8 | o >>> 24) | 4278255360 & (o << 24 | o >>> 8);
                return e
            },
            clone: function() {
                var t = c.clone.call(this);
                return t._hash = this._hash.clone(),
                t
            }
        }),
        a.MD5 = c._createHelper(r),
        a.HmacMD5 = c._createHmacHelper(r)
    } (Math),
    function() {
        var t = n,
        e = t.lib,
        i = e.Base,
        o = e.WordArray,
        e = t.algo,
        s = e.EvpKDF = i.extend({
            cfg: i.extend({
                keySize: 4,
                hasher: e.MD5,
                iterations: 1
            }),
            init: function(t) {
                this.cfg = this.cfg.extend(t)
            },
            compute: function(t, e) {
                for (var i = this.cfg,
                n = i.hasher.create(), s = o.create(), a = s.words, r = i.keySize, i = i.iterations; a.length < r;) {
                    l && n.update(l);
                    var l = n.update(t).finalize(e);
                    n.reset();
                    for (var c = 1; i > c; c++) l = n.finalize(l),
                    n.reset();
                    s.concat(l)
                }
                return s.sigBytes = 4 * r,
                s
            }
        });
        t.EvpKDF = function(t, e, i) {
            return s.create(i).compute(t, e)
        }
    } (),
    n.lib.Cipher ||
    function(t) {
        var e = n,
        i = e.lib,
        o = i.Base,
        s = i.WordArray,
        a = i.BufferedBlockAlgorithm,
        r = e.enc.Base64,
        l = e.algo.EvpKDF,
        c = i.Cipher = a.extend({
            cfg: o.extend(),
            createEncryptor: function(t, e) {
                return this.create(this._ENC_XFORM_MODE, t, e)
            },
            createDecryptor: function(t, e) {
                return this.create(this._DEC_XFORM_MODE, t, e)
            },
            init: function(t, e, i) {
                this.cfg = this.cfg.extend(i),
                this._xformMode = t,
                this._key = e,
                this.reset()
            },
            reset: function() {
                a.reset.call(this),
                this._doReset()
            },
            process: function(t) {
                return this._append(t),
                this._process()
            },
            finalize: function(t) {
                return t && this._append(t),
                this._doFinalize()
            },
            keySize: 4,
            ivSize: 4,
            _ENC_XFORM_MODE: 1,
            _DEC_XFORM_MODE: 2,
            _createHelper: function(t) {
                return {
                    encrypt: function(e, i, n) {
                        return ("string" == typeof i ? f: m).encrypt(t, e, i, n)
                    },
                    decrypt: function(e, i, n) {
                        return ("string" == typeof i ? f: m).decrypt(t, e, i, n)
                    }
                }
            }
        });
        i.StreamCipher = c.extend({
            _doFinalize: function() {
                return this._process(!0)
            },
            blockSize: 1
        });
        var u = e.mode = {},
        d = function(e, i, n) {
            var o = this._iv;
            o ? this._iv = t: o = this._prevBlock;
            for (var s = 0; n > s; s++) e[i + s] ^= o[s]
        },
        h = (i.BlockCipherMode = o.extend({
            createEncryptor: function(t, e) {
                return this.Encryptor.create(t, e)
            },
            createDecryptor: function(t, e) {
                return this.Decryptor.create(t, e)
            },
            init: function(t, e) {
                this._cipher = t,
                this._iv = e
            }
        })).extend();
        h.Encryptor = h.extend({
            processBlock: function(t, e) {
                var i = this._cipher,
                n = i.blockSize;
                d.call(this, t, e, n),
                i.encryptBlock(t, e),
                this._prevBlock = t.slice(e, e + n)
            }
        }),
        h.Decryptor = h.extend({
            processBlock: function(t, e) {
                var i = this._cipher,
                n = i.blockSize,
                o = t.slice(e, e + n);
                i.decryptBlock(t, e),
                d.call(this, t, e, n),
                this._prevBlock = o
            }
        }),
        u = u.CBC = h,
        h = (e.pad = {}).Pkcs7 = {
            pad: function(t, e) {
                for (var i = 4 * e,
                i = i - t.sigBytes % i,
                n = i << 24 | i << 16 | i << 8 | i,
                o = [], a = 0; i > a; a += 4) o.push(n);
                i = s.create(o, i),
                t.concat(i)
            },
            unpad: function(t) {
                t.sigBytes -= 255 & t.words[t.sigBytes - 1 >>> 2]
            }
        },
        i.BlockCipher = c.extend({
            cfg: c.cfg.extend({
                mode: u,
                padding: h
            }),
            reset: function() {
                c.reset.call(this);
                var t = this.cfg,
                e = t.iv,
                t = t.mode;
                if (this._xformMode == this._ENC_XFORM_MODE) var i = t.createEncryptor;
                else i = t.createDecryptor,
                this._minBufferSize = 1;
                this._mode = i.call(t, this, e && e.words)
            },
            _doProcessBlock: function(t, e) {
                this._mode.processBlock(t, e)
            },
            _doFinalize: function() {
                var t = this.cfg.padding;
                if (this._xformMode == this._ENC_XFORM_MODE) {
                    t.pad(this._data, this.blockSize);
                    var e = this._process(!0)
                } else e = this._process(!0),
                t.unpad(e);
                return e
            },
            blockSize: 4
        });
        var p = i.CipherParams = o.extend({
            init: function(t) {
                this.mixIn(t)
            },
            toString: function(t) {
                return (t || this.formatter).stringify(this)
            }
        }),
        u = (e.format = {}).OpenSSL = {
            stringify: function(t) {
                var e = t.ciphertext;
                return t = t.salt,
                (t ? s.create([1398893684, 1701076831]).concat(t).concat(e) : e).toString(r)
            },
            parse: function(t) {
                t = r.parse(t);
                var e = t.words;
                if (1398893684 == e[0] && 1701076831 == e[1]) {
                    var i = s.create(e.slice(2, 4));
                    e.splice(0, 4),
                    t.sigBytes -= 16
                }
                return p.create({
                    ciphertext: t,
                    salt: i
                })
            }
        },
        m = i.SerializableCipher = o.extend({
            cfg: o.extend({
                format: u
            }),
            encrypt: function(t, e, i, n) {
                n = this.cfg.extend(n);
                var o = t.createEncryptor(i, n);
                return e = o.finalize(e),
                o = o.cfg,
                p.create({
                    ciphertext: e,
                    key: i,
                    iv: o.iv,
                    algorithm: t,
                    mode: o.mode,
                    padding: o.padding,
                    blockSize: t.blockSize,
                    formatter: n.format
                })
            },
            decrypt: function(t, e, i, n) {
                return n = this.cfg.extend(n),
                e = this._parse(e, n.format),
                t.createDecryptor(i, n).finalize(e.ciphertext)
            },
            _parse: function(t, e) {
                return "string" == typeof t ? e.parse(t, this) : t
            }
        }),
        e = (e.kdf = {}).OpenSSL = {
            execute: function(t, e, i, n) {
                return n || (n = s.random(8)),
                t = l.create({
                    keySize: e + i
                }).compute(t, n),
                i = s.create(t.words.slice(e), 4 * i),
                t.sigBytes = 4 * e,
                p.create({
                    key: t,
                    iv: i,
                    salt: n
                })
            }
        },
        f = i.PasswordBasedCipher = m.extend({
            cfg: m.cfg.extend({
                kdf: e
            }),
            encrypt: function(t, e, i, n) {
                return n = this.cfg.extend(n),
                i = n.kdf.execute(i, t.keySize, t.ivSize),
                n.iv = i.iv,
                t = m.encrypt.call(this, t, e, i.key, n),
                t.mixIn(i),
                t
            },
            decrypt: function(t, e, i, n) {
                return n = this.cfg.extend(n),
                e = this._parse(e, n.format),
                i = n.kdf.execute(i, t.keySize, t.ivSize, e.salt),
                n.iv = i.iv,
                m.decrypt.call(this, t, e, i.key, n)
            }
        })
    } (),
    function() {
        for (var t = n,
        e = t.lib.BlockCipher,
        i = t.algo,
        o = [], s = [], a = [], r = [], l = [], c = [], u = [], d = [], h = [], p = [], m = [], f = 0; 256 > f; f++) m[f] = 128 > f ? f << 1 : f << 1 ^ 283;
        for (var g = 0,
        b = 0,
        f = 0; 256 > f; f++) {
            var k = b ^ b << 1 ^ b << 2 ^ b << 3 ^ b << 4,
            k = k >>> 8 ^ 255 & k ^ 99;
            o[g] = k,
            s[k] = g;
            var v = m[g],
            w = m[v],
            y = m[w],
            x = 257 * m[k] ^ 16843008 * k;
            a[g] = x << 24 | x >>> 8,
            r[g] = x << 16 | x >>> 16,
            l[g] = x << 8 | x >>> 24,
            c[g] = x,
            x = 16843009 * y ^ 65537 * w ^ 257 * v ^ 16843008 * g,
            u[k] = x << 24 | x >>> 8,
            d[k] = x << 16 | x >>> 16,
            h[k] = x << 8 | x >>> 24,
            p[k] = x,
            g ? (g = v ^ m[m[m[y ^ v]]], b ^= m[m[b]]) : g = b = 1
        }
        var _ = [0, 1, 2, 4, 8, 16, 32, 64, 128, 27, 54],
        i = i.AES = e.extend({
            _doReset: function() {
                for (var t = this._key,
                e = t.words,
                i = t.sigBytes / 4,
                t = 4 * ((this._nRounds = i + 6) + 1), n = this._keySchedule = [], s = 0; t > s; s++) if (i > s) n[s] = e[s];
                else {
                    var a = n[s - 1];
                    s % i ? i > 6 && 4 == s % i && (a = o[a >>> 24] << 24 | o[a >>> 16 & 255] << 16 | o[a >>> 8 & 255] << 8 | o[255 & a]) : (a = a << 8 | a >>> 24, a = o[a >>> 24] << 24 | o[a >>> 16 & 255] << 16 | o[a >>> 8 & 255] << 8 | o[255 & a], a ^= _[s / i | 0] << 24),
                    n[s] = n[s - i] ^ a
                }
                for (e = this._invKeySchedule = [], i = 0; t > i; i++) s = t - i,
                a = i % 4 ? n[s] : n[s - 4],
                e[i] = 4 > i || 4 >= s ? a: u[o[a >>> 24]] ^ d[o[a >>> 16 & 255]] ^ h[o[a >>> 8 & 255]] ^ p[o[255 & a]]
            },
            encryptBlock: function(t, e) {
                this._doCryptBlock(t, e, this._keySchedule, a, r, l, c, o)
            },
            decryptBlock: function(t, e) {
                var i = t[e + 1];
                t[e + 1] = t[e + 3],
                t[e + 3] = i,
                this._doCryptBlock(t, e, this._invKeySchedule, u, d, h, p, s),
                i = t[e + 1],
                t[e + 1] = t[e + 3],
                t[e + 3] = i
            },
            _doCryptBlock: function(t, e, i, n, o, s, a, r) {
                for (var l = this._nRounds,
                c = t[e] ^ i[0], u = t[e + 1] ^ i[1], d = t[e + 2] ^ i[2], h = t[e + 3] ^ i[3], p = 4, m = 1; l > m; m++) var f = n[c >>> 24] ^ o[u >>> 16 & 255] ^ s[d >>> 8 & 255] ^ a[255 & h] ^ i[p++],
                g = n[u >>> 24] ^ o[d >>> 16 & 255] ^ s[h >>> 8 & 255] ^ a[255 & c] ^ i[p++],
                b = n[d >>> 24] ^ o[h >>> 16 & 255] ^ s[c >>> 8 & 255] ^ a[255 & u] ^ i[p++],
                h = n[h >>> 24] ^ o[c >>> 16 & 255] ^ s[u >>> 8 & 255] ^ a[255 & d] ^ i[p++],
                c = f,
                u = g,
                d = b;
                f = (r[c >>> 24] << 24 | r[u >>> 16 & 255] << 16 | r[d >>> 8 & 255] << 8 | r[255 & h]) ^ i[p++],
                g = (r[u >>> 24] << 24 | r[d >>> 16 & 255] << 16 | r[h >>> 8 & 255] << 8 | r[255 & c]) ^ i[p++],
                b = (r[d >>> 24] << 24 | r[h >>> 16 & 255] << 16 | r[c >>> 8 & 255] << 8 | r[255 & u]) ^ i[p++],
                h = (r[h >>> 24] << 24 | r[c >>> 16 & 255] << 16 | r[u >>> 8 & 255] << 8 | r[255 & d]) ^ i[p++],
                t[e] = f,
                t[e + 1] = g,
                t[e + 2] = b,
                t[e + 3] = h
            },
            keySize: 8
        });
        t.AES = e._createHelper(i)
    } (),
    n.pad.Iso10126 = {
        pad: function(t, e) {
            var i = 4 * e,
            i = i - t.sigBytes % i;
            t.concat(n.lib.WordArray.random(i - 1)).concat(n.lib.WordArray.create([i << 24], 1))
        },
        unpad: function(t) {
            t.sigBytes -= 255 & t.words[t.sigBytes - 1 >>> 2]
        }
    },
    i.exports = n
}),
define("bower_components/aes/main", ["require", "./aes"],
function(t) {
    var e = t("./aes"),
    i = e.enc.Utf8.parse("youzan.com.aesiv"),
    n = e.enc.Utf8.parse("youzan.com._key_");
    return {
        encrypt: function(t) {
            return t = e.enc.Utf8.parse(t),
            e.AES.encrypt(t, n, {
                mode: e.mode.CBC,
                padding: e.pad.Iso10126,
                iv: i
            }).toString()
        }
    }
}),
define("text!bower_components/login/templates/init.html", [],
function() {
    return '<form class="js-login-form popout-login" method="GET" action="">\n    <div class="header c-green center">\n        <h2>请填写您的手机号码</h2>\n    </div>\n    <fieldset class="wrapper-form font-size-14">\n        <div class="form-item">\n            <label for="phone">手机号</label>\n            <input id="phone" name="phone" type="tel" maxlength="11" autocomplete="off" placeholder="" value="<%= phone %>">\n        </div>\n        <div class="js-help-info font-size-12 error c-orange"></div>\n    </fieldset>\n    <div class="action-container">\n        <input type="submit" class="js-confirm btn btn-green btn-block font-size-14" value="确认手机号码" />\n    </div>\n</form>\n'
}),
define("text!bower_components/login/templates/login.html", [],
function() {
    return '<form class="js-login-form popout-login" method="GET" action="">\n    <div class="header c-green center">\n        <h2>该号码注册过，请直接登录</h2>\n    </div>\n    <fieldset class="wrapper-form font-size-14">\n        <div class="form-item">\n            <label for="phone">手机号</label>\n            <input id="phone" name="phone" type="tel" maxlength="11" autocomplete="off" placeholder="请输入你的手机号" disabled="disabled" value="<%= phone %>">\n        </div>\n        <div class="form-item">\n            <label for="password">密码</label>\n            <input id="passsword" name="password"  type="password" autocomplete="off" placeholder="请输入登录密码">\n        </div>\n        <div class="js-help-info font-size-12 error c-orange"></div>\n    </fieldset>\n    <div class="action-container">\n        <button type="button" class="js-confirm btn btn-green btn-block font-size-14">\n            <%= showBindText ? \'登录并绑定\' : \'确认\' %>\n        </button>\n    </div>\n    <div class="bottom-tips font-size-12">\n        <span class="c-orange">如果您忘了密码，请</span><a href="javascript:;" class="js-change-pwd c-blue">点此找回密码</a>\n        <a href="javascript:;" class="js-change-phone c-blue pull-right">更换手机号</a>\n    </div>\n</form>\n'
}),
define("text!bower_components/login/templates/register.html", [],
function() {
    return '<form class="js-login-form popout-login" method="GET" action="">\n    <div class="header c-green center">\n        <h2>注册有赞帐号</h2>\n    </div>\n    <fieldset class="wrapper-form font-size-14">\n        <div class="form-item">\n            <label for="phone">手机号</label>\n            <input id="phone" name="phone" type="tel" maxlength="11" autocomplete="off" placeholder="请输入你的手机号" disabled="disabled" value="<%= phone %>">\n        </div>\n        <div class="form-item js-image-verify hide">\n            <label for="verifycode">身份校验</label>\n            <input id="verifycode" name="verifycode" class="js-verify-code item-input"  type="tel" style="width:178px" maxlength="6" autocomplete="off" placeholder="输入右侧数字">\n            <img class="js-verify-image verify-image" src="">\n        </div>\n        <div class="form-item">\n            <label for="code">验证码</label>\n            <input id="code" name="code"  type="text" style="width:178px" maxlength="6" autocomplete="off" placeholder="输入短信验证码">\n            <button type="button" class="js-auth-code tag btn-auth-code tag-green font-size-12" data-text="获取验证码">\n                获取验证码\n            </button>\n        </div>\n        <div class="form-item">\n            <label for="password">密码</label>\n            <input id="passsword" name="password"  type="password" autocomplete="off" maxlength="20" placeholder="请输入8-20位数字和字母组合">\n        </div>\n        <div class="js-help-info font-size-12 error c-orange"></div>\n    </fieldset>\n    <div class="action-container">\n        <button type="button" class="js-confirm btn btn-green btn-block font-size-14">\n            <%= showBindText ? \'注册并绑定\' : \'确认\' %>\n        </button>\n    </div>\n    <div class="bottom-tips font-size-12">\n        <span class="c-orange">如果您忘了密码，请</span><a href="javascript:;" class="js-change-pwd c-blue">点此找回密码</a>\n        <a href="javascript:;" class="js-change-phone c-blue pull-right">更换手机号</a>\n    </div>\n</form>\n'
}),
define("text!bower_components/login/templates/change_pwd.html", [],
function() {
    return '<form class="js-login-form popout-login" method="GET" action="">\n    <div class="header c-green center">\n        <h2><%if(isSetting){%>设定<%}else{%>找回<%}%>帐号密码</h2>\n    </div>\n    <fieldset class="wrapper-form font-size-14">\n        <div class="form-item">\n            <label for="phone">手机号</label>\n            <input id="phone" name="phone" type="tel" maxlength="11" autocomplete="off" placeholder="请输入你的手机号" disabled="disabled" value="<%= phone %>">\n        </div>\n        <div class="form-item js-image-verify hide">\n            <label for="verifycode">身份校验</label>\n            <input id="verifycode" name="verifycode" class="js-verify-code item-input"  type="tel" style="width:178px" maxlength="6" autocomplete="off" placeholder="输入右侧数字">\n            <img class="js-verify-image verify-image" src="">\n        </div>\n        <div class="form-item">\n            <label for="code">验证码</label>\n            <input id="code" name="code"  type="text" style="width:178px" maxlength="6" autocomplete="off" placeholder="输入短信验证码">\n            <button type="button" class="js-auth-code tag btn-auth-code font-size-12 tag-green" data-text="获取验证码">\n                获取验证码\n            </button>\n        </div>\n        <div class="form-item">\n            <label for="password">密码</label>\n            <input id="passsword" name="password"  type="password" autocomplete="off" placeholder="设置新的8-20位数字和字母组合密码">\n        </div>\n        <div class="js-help-info font-size-12 error c-orange"></div>\n    </fieldset>\n    <div class="action-container">\n        <button type="button" class="js-confirm btn btn-green btn-block font-size-14">确定</button>\n    </div>\n    <div class="bottom-tips pull-right">\n        <a href="javascript:;" class="js-login inline-item c-blue">已有帐号登录</a>\n        <a href="javascript:;" class="js-register inline-item c-blue">注册新帐号</a>\n    </div>\n</form>\n'
}),
define("zenjs/util/valid", [],
function() {
    window.zenjs = window.zenjs || {};
    var t = {
        validMobile: function(t) {
            return t = "" + t,
            /^((\+86)|(86))?(1)\d{10}$/.test(t)
        },
        validPhone: function(t) {
            return t = "" + t,
            /^0[0-9\-]{10,13}$/.test(t)
        },
        validNumber: function(t) {
            return /^\d+$/.test(t)
        },
        validEmail: function(t) {
            return /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(t)
        },
        validPostalCode: function(t) {
            return t = "" + t,
            /^\d{6}$/.test(t)
        }
    };
    return window.zenjs.Valid = t,
    t
}),
window.Zepto &&
function(t) {
    t.fn.serializeArray = function() {
        var e, i, n = [],
        o = function(t) {
            return t.forEach ? t.forEach(o) : void n.push({
                name: e,
                value: t
            })
        };
        return this[0] && t.each(this[0].elements,
        function(n, s) {
            i = s.type,
            e = s.name,
            e && "fieldset" != s.nodeName.toLowerCase() && !s.disabled && "submit" != i && "reset" != i && "button" != i && "file" != i && ("radio" != i && "checkbox" != i || s.checked) && o(t(s).val())
        }),
        n
    },
    t.fn.serialize = function() {
        var t = [];
        return this.serializeArray().forEach(function(e) {
            t.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value))
        }),
        t.join("&")
    },
    t.fn.submit = function(e) {
        if (0 in arguments) this.bind("submit", e);
        else if (this.length) {
            var i = t.Event("submit");
            this.eq(0).trigger(i),
            i.isDefaultPrevented() || this.get(0).submit()
        }
        return this
    }
} (Zepto),
define("vendor/zepto/form",
function() {}),
define("zenjs/util/form", ["require", "exports", "module", "vendor/zepto/form", "jquery"],
function(t, e, i) {
    t("vendor/zepto/form");
    var n = t("jquery");
    window.zenjs = window.zenjs || {};
    var o = {
        getFormData: function(t) {
            var e = t.serializeArray(),
            i = {};
            return n.map(e,
            function(t) {
                i[t.name] = t.value
            }),
            i
        }
    };
    window.zenjs.Form = o,
    i.exports = o
}),
define("zenjs/sms_fetch/main", ["require", "exports", "module", "bower_components/ajax/ajax", "jquery"],
function(t, e, i) {
    function n() {
        this.loadingLock = !1,
        this.isUsed = void 0
    }
    function o(t) {
        t = t || {},
        this.$el = s(t.el || t.$el || "<div></div>"),
        this.el = this.$el[0],
        this.$ = function(t) {
            return this.$el.find(t)
        },
        this.initialize && this.initialize(t)
    }
    t("bower_components/ajax/ajax");
    var s = t("jquery"),
    a = function() {};
    n.prototype = {
        fetch: function() {
            if (this.isUsed !== !1) {
                var t = this;
                this.loadingLock = !0,
                s._ajax({
                    url: window._global.url.www.replace(/^https?:\/\//, "//") + "/common/token/token.jsonp",
                    type: "get",
                    dataType: "jsonp"
                }).done(function(e) {
                    0 == e.code ? (t.token = e.data, t.loadingLock = !1, t.isUsed = !1) : motify.log(e.msg)
                }).fail(function() {
                    motify.log("token 获取失败")
                })
            }
        },
        get: function() {
            return this.isUsed = !0,
            this.token
        }
    };
    var r = new n;
    s.extend(o.prototype, {
        initialize: function(t) {
            this.duration = t.time || 60,
            this.step = t.step || 1100,
            this.codeVerifyClass = t.codeVerifyClass || "js-verify-code",
            this.verifyType = "smsFetch";
            var e = window._global.url.www.replace(/^https?:\/\//, "//");
            this.smsFetchUrl = e + "/common/sms/captcha.jsonp",
            this.imgUrl = e + "/common/sms/imgcaptcha",
            this.imgVerifyUrl = e + "/common/sms/imgcaptcha.jsonp",
            this.biz = t.biz || "kdt_account_captcha",
            this.onTimeChange = t.onTimeChange || a,
            this.onTimeEnd = t.onTimeEnd || a,
            this.onTimerStart = t.onTimerStart || a,
            this.onTimerClose = t.onTimerClose || a,
            this.onVerifyPictureShow = t.onVerifyPictureShow || a,
            this.onGetCodeError = t.onGetCodeError || a,
            this.onVerifyPictureSuccess = t.onVerifyPictureSuccess || a,
            this.onVerifyPictureError = t.onVerifyPictureError || a,
            this.platform = t.platform || "",
            this.subFrom = t.subFrom || "",
            r.fetch()
        },
        setMobile: function(t) {
            t && (this.mobile = t)
        },
        getImageCode: function() {
            return s.trim(this.$("." + this.codeVerifyClass).val())
        },
        getSms: function(t) {
            var e = this;
            if (r.loadingLock) return void motify.log("数据加载中，稍后再试");
            if (t = t || {},
            t.mobile && (this.mobile = t.mobile), !this.mobile) return ! 1;
            var i = {
                smsFetch: e.onSmsFetchHandler,
                image: e.onImageHandler
            };
            return e.startTimer.call(e),
            (i[e.verifyType] || a).call(e),
            this
        },
        startTimer: function() {
            this.onTimerStart(),
            this.btnCountdown(this.duration)
        },
        stopTimer: function() {
            clearTimeout(this.timer),
            this.onTimerClose()
        },
        btnCountdown: function(t) {
            var e = this;
            this.onTimeChange({
                second: t
            }),
            --t >= 0 ? this.timer = setTimeout(function() {
                e.btnCountdown(t)
            },
            this.step) : (this.onTimeEnd(), this.timer = "")
        },
        onVerifyImageShow: function(t) {
            this.$(".js-image-verify").removeClass("hide"),
            this.$(".js-verify-image").attr("src", t)
        },
        onVerifyImageHide: function() {
            this.$(".js-image-verify").addClass("hide")
        },
        onSmsFetchHandler: function() {
            var t = 1;
            return function() {
                var e = this,
                i = {
                    verifyTimes: t,
                    mobile: this.mobile,
                    biz: this.biz,
                    token: r.get()
                };
                this.platform && (i.platform = this.platform),
                this.subFrom && (i.sub_from = this.subFrom),
                s._ajax({
                    url: this.smsFetchUrl,
                    dataType: "jsonp",
                    data: i,
                    success: function(i) {
                        return 0 == i.code ? void t++:(e.stopTimer.call(e), e.onGetCodeError.call(e), void(10111 === i.code ? (e.verifyType = "image", e.onVerifyImageShow(e.imgUrl), e.onVerifyPictureShow()) : (t++, motify.log(i.msg))))
                    },
                    error: function(i, n, o) {
                        t++,
                        e.stopTimer.call(e),
                        e.onGetCodeError.call(e),
                        motify.log("获取验证码失败，请稍后再试")
                    },
                    complete: function(t, e) {}
                }).always(function() {
                    r.fetch()
                })
            }
        } (),
        onImageHandler: function() {
            var t = this,
            e = this.mobile;
            s._ajax({
                url: this.imgVerifyUrl,
                dataType: "jsonp",
                data: {
                    mobile: e,
                    captcha_code: this.getImageCode()
                },
                success: function(i) {
                    return 0 === i.code ? (t.verifyType = "smsFetch", t.mobile = e, t.onVerifyImageHide(), t.onVerifyPictureSuccess(), void t.onSmsFetchHandler()) : (t.stopTimer.call(t), t.onVerifyPictureError.call(t), void(10100 === i.code ? (motify.log(i.msg), t.$el.find(".js-verify-image").attr("src", t.imgUrl)) : motify.log(i.msg)))
                },
                error: function(e, i, n) {
                    t.stopTimer.call(t),
                    t.onVerifyPictureError.call(t),
                    motify.log("图形验证失败，重试一下吧~"),
                    t.$el.find(".js-verify-image").attr("src", t.imgUrl)
                },
                complete: function(t, e) {}
            })
        }
    }),
    i.exports = o
}),
define("bower_components/login/main", ["require", "bower_components/ajax/ajax", "jquery", "backbone", "bower_components/aes/main", "text!./templates/init.html", "text!./templates/login.html", "text!./templates/register.html", "text!./templates/change_pwd.html", "zenjs/util/valid", "zenjs/util/form", "zenjs/sms_fetch/main", "zenjs/util/url_helper", "zenjs/util/ua"],
function(t) {
    t("bower_components/ajax/ajax");
    var e = t("jquery"),
    i = t("backbone"),
    n = t("bower_components/aes/main"),
    o = t("text!./templates/init.html"),
    s = t("text!./templates/login.html"),
    a = t("text!./templates/register.html"),
    r = t("text!./templates/change_pwd.html"),
    l = t("zenjs/util/valid"),
    c = t("zenjs/util/form"),
    u = t("zenjs/sms_fetch/main"),
    d = t("zenjs/util/url_helper"),
    h = t("zenjs/util/ua"),
    p = ["youzanmars"],
    m = i.View.extend({
        events: {
            "click .js-confirm": "onConfirmClicked",
            "click .js-change-phone": "onChangePhoneClicked",
            "click .js-change-pwd": "onChangePwdClicked",
            "click .js-login": "onLoginClicked",
            "click .js-register": "onRegisterClicked",
            "click .js-auth-code": "onAuthcodeClicked",
            "submit .js-login-form": "onConfirmClicked"
        },
        initialize: function(t) {
            t = t || {};
            var i = h.getPlatform();
            if (p.indexOf(i) > -1) return void(window.location.href = d.site("/buyer/kdtunion?redirect_uri=" + encodeURIComponent(window.location.href), "wap"));
            var n = this,
            l = _global.url;
            this.tpl_map = {
                init: _.template(t.initTpl || o),
                login: _.template(t.loginTpl || s),
                register: _.template(t.registerTpl || a),
                changePwd: _.template(t.changePwdTpl || r)
            },
            this.valid_map = {
                checkPhone: _(this.checkPhone).bind(this),
                checkPwd: _(this.checkPwd).bind(this),
                checkCode: _(this.checkCode).bind(this)
            },
            this.renderOpt = t.renderOpt || {
                type: "init",
                phone: ""
            },
            this.renderOpt.showBindText = h.isWeixin() || h.isQQ(),
            this.urlMap = {
                login: t.loginUrl || l.wap + "/buyer/auth/authlogin.json",
                register: t.registerUrl || l.wap + "/buyer/auth/authRegister.json",
                changePwd: t.changePwdUrl || l.wap + "/buyer/auth/changePassword.json",
                confirm: t.confirmUrl || l.wap + "/buyer/auth/authConfirm.json"
            },
            this.source = t.source || 2,
            this.ajaxType = t.ajaxType || "POST",
            this.afterLogin = t.afterLogin ||
            function() {};
            var c, m;
            h.isMobile() ? (c = "app", m = "wsc", h.isWxd() ? m = "wxd": h.isWsc() ? m = "wsc": h.isPf() ? m = "pf": c = "wap") : c = "pc",
            this.sms = new u({
                el: this.$el,
                onTimeChange: function(t) {
                    var i = t.second;
                    e(n.nAuthCode).text("等待 " + i + "秒")
                },
                onTimeEnd: function() {
                    n.nAuthCode.text("再次获取"),
                    n.nAuthCode.prop("disabled", !1),
                    n.nAuthCode.removeClass("disabled"),
                    n.nCodeInput.attr("placeholder", "没有收到验证码？")
                },
                onTimerStart: function() {
                    n.nAuthCode.prop("disabled", !0),
                    n.nAuthCode.addClass("disabled")
                },
                onVerifyPictureError: function() {
                    n.nAuthCode.removeAttr("disabled"),
                    n.nAuthCode.removeClass("disabled"),
                    n.nAuthCode.text("再次获取")
                },
                onGetCodeError: function() {
                    n.nAuthCode.removeAttr("disabled"),
                    n.nAuthCode.removeClass("disabled"),
                    n.nAuthCode.text("再次获取")
                },
                onVerifyPictureShow: function() {
                    n.nHelpInfo.html("操作过于频繁，请先输入图像验证码再获取")
                },
                onVerifyPictureSuccess: function() {
                    n.nHelpInfo.html("")
                },
                platform: c,
                subFrom: m
            })
        },
        render: function() {
            return this.appLogined ? this: (this.$el.html(this.tpl_map[this.renderOpt.type](this.renderOpt)), this.nForm = this.$(".js-login-form"), this.nHelpInfo = this.$(".js-help-info"), this.nPhone = this.$('input[name="phone"]'), this.nPwd = this.$('input[name="password"]'), this.nCodeInput = this.$('input[name="authcode"]'), this.nAuthCode = this.$(".js-auth-code"), this)
        },
        show: function(t, e) {
            "changePwd" == t ? this.sms.biz = "reset_account_passwd": this.sms.biz = "kdt_account_captcha",
            _.extend(this.renderOpt, {
                type: t
            },
            e || {
                isSetting: !1
            }),
            this.render(this.renderOpt),
            this.$el.show(this.animationTime)
        },
        onConfirmClicked: function(t) {
            t.preventDefault();
            var i = this,
            o = e(t.target),
            s = c.getFormData(i.nForm);
            if (s = _.extend(i.renderOpt, s), !i.validate(s)) return ! 1;
            s.source = this.source,
            s.password && (s.password = n.encrypt(s.password));
            var a = o.html();
            if ("init" === i.renderOpt.type) i.renderOpt.phone = s.phone,
            e._ajax({
                url: this.urlMap.confirm,
                type: "POST",
                dataType: "json",
                timeout: 15e3,
                data: s,
                xhrFields: {
                    withCredentials: !0
                },
                beforeSend: function() {
                    o.html("确认中..."),
                    o.prop("disabled", !0)
                },
                success: function(t) {
                    switch ( + t.code) {
                    case 0:
                        i.show("login");
                        break;
                    case 200:
                        i.show("register");
                        break;
                    case 300:
                        i.show("changePwd", {
                            isSetting: !0
                        });
                    default:
                        i.nHelpInfo.html(t.msg)
                    }
                },
                error: function() {
                    i.nHelpInfo.html("出错啦，请重试")
                },
                complete: function() {
                    o.html(a),
                    o.prop("disabled", !1)
                }
            });
            else {
                var r = i.renderOpt.type;
                e._ajax({
                    url: this.urlMap[r],
                    type: this.ajaxType,
                    dataType: "json",
                    timeout: 15e3,
                    data: s,
                    xhrFields: {
                        withCredentials: !0
                    },
                    beforeSend: function() {
                        o.html("确认中..."),
                        o.prop("disabled", !0)
                    },
                    success: function(t) {
                        0 === t.code ? i.afterLogin(t, {
                            type: r
                        }) : i.nHelpInfo.html(t.msg)
                    },
                    error: function() {
                        i.nHelpInfo.html("出错啦，请重试")
                    },
                    complete: function() {
                        o.html(a),
                        o.prop("disabled", !1)
                    }
                })
            }
        },
        onAuthcodeClicked: function(t) {
            t.preventDefault();
            var e = this,
            i = c.getFormData(e.nForm);
            i = _.extend(e.renderOpt, i);
            var n = i.phone;
            this.sms.setMobile(n),
            this.sms.getSms()
        },
        onChangePhoneClicked: function(t) {
            t.preventDefault(),
            this.show("init")
        },
        onChangePwdClicked: function(t) {
            t.preventDefault(),
            this.sms.stopTimer(),
            this.show("changePwd")
        },
        onLoginClicked: function(t) {
            t.preventDefault(),
            this.show("init")
        },
        onRegisterClicked: function(t) {
            t.preventDefault(),
            this.show("init")
        },
        validate: function() {
            var t = {
                init: ["checkPhone"],
                login: ["checkPwd"],
                register: ["checkCode", "checkPwd"],
                changePwd: ["checkCode", "checkPwd"]
            };
            return function(e) {
                return _.every(t[e.type], _(function(t) {
                    return this.valid_map[t](e)
                }).bind(this))
            }
        } (),
        checkPhone: function(t) {
            return "" === t.phone ? (this.nPhone.focus(), this.nHelpInfo.html("请填写您的手机号码"), !1) : l.validMobile(t.phone) ? !0 : (this.nPhone.focus(), this.nHelpInfo.html("请填写11位手机号码"), !1)
        },
        checkPwd: function(t) {
            function e(t) {
                var e = /[a-zA-Z]/g,
                i = /[0-9]/g,
                n = 0,
                o = e.test(t),
                s = i.test(t);
                return o && s && (n = 1),
                n
            }
            if ("" === t.password) return this.nPwd.focus(),
            this.nHelpInfo.html("请输入您的密码"),
            !1;
            if ("login" !== this.renderOpt.type && t.password.length < 8) return this.nPwd.focus(),
            this.nHelpInfo.html("亲，密码最短为8位"),
            !1;
            if ("login" !== this.renderOpt.type && t.password.length > 20) return this.nPwd.focus(),
            this.nHelpInfo.html("亲，密码最长为20位"),
            !1;
            if ("login" !== this.renderOpt.type) {
                var i = e(t.password);
                if (!i) return this.nPwd.focus(),
                this.nHelpInfo.html("亲，密码为8-20位数字和字母组合"),
                !1
            }
            return ! 0
        },
        checkCode: function(t) {
            return l.validPostalCode(t.code) ? !0 : (this.nCodeInput.focus(), this.nHelpInfo.html("请填写6位短信验证码"), !1)
        }
    });
    return m
}),
define("zenjs/util/number", [],
function() {
    var t = {
        makeRandomString: function(t) {
            var e = "",
            i = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            t = t || 10;
            for (var n = 0; t > n; n++) e += i.charAt(Math.floor(Math.random() * i.length));
            return e
        }
    };
    return t
}),
define("bower_components/pop/pop", ["require", "zenjs/events", "zenjs/util/number"],
function(t) {
    var e = function() {},
    i = t("zenjs/events"),
    n = t("zenjs/util/number");
    window.zenjs = window.zenjs || {};
    var o = i.extend({
        init: function(t) {
            this._window = $(window);
            var i = n.makeRandomString();
            $("body").append('<div id="' + i + '"                 style="display:none; height: 100%;                 position: fixed; top: 0; left: 0; right: 0;                background-color: rgba(0, 0, 0, ' + (t.transparent || ".9") + ');z-index:1000;opacity:0;transition: opacity ease 0.2s;"></div>'),
            this.nBg = $("#" + i),
            this.nBg.on("click", $.proxy(function() {
                this.isCanNotHide || this.hide()
            },
            this));
            var o = n.makeRandomString();
            $("body").append('<div id="' + o + '" class="' + (t.className || "") + '" style="overflow:hidden;visibility: hidden;"></div>'),
            this.nPopContainer = $("#" + o),
            this.nPopContainer.hide(),
            this.nPopContainer.css({
                opacity: 0,
                position: "absolute",
                "z-index": 1e3
            }),
            t.contentViewClass && (this.contentViewClass = t.contentViewClass, this.contentViewOptions = $.extend({
                el: this.nPopContainer
            },
            t.contentViewOptions || {}), this.contentView = new this.contentViewClass($.extend({
                onHide: $.proxy(this.hide, this)
            },
            this.contentViewOptions)), this.contentView.onHide = $.proxy(this.hide, this)),
            this.animationTime = t.animationTime || 300,
            this.isCanNotHide = t.isCanNotHide,
            this.doNotRemoveOnHide = t.doNotRemoveOnHide || !1,
            this.onShow = t.onShow || e,
            this.onHide = t.onHide || e,
            this.onFinishHide = t.onFinishHide || e,
            this.html = t.html
        },
        render: function(t) {
            return this.renderOptions = t || {},
            this.contentViewClass ? this.contentView.render(this.renderOptions) : this.html && this.nPopContainer.html(this.html),
            this
        },
        show: function() {
            return this.nBg.show().css({
                opacity: "1",
                "transition-property": "none"
            }),
            this.nPopContainer.show(),
            this.trigger("pop:show:before"),
            setTimeout($.proxy(function() {
                this.trigger("pop:show:after"),
                this.nPopContainer.show().css("visibility", "visible"),
                this._doShow && this._doShow(),
                this.onShow()
            },
            this), 200),
            this
        },
        hide: function(t) {
            t = t || {};
            var e = t.doNotRemove || this.doNotRemoveOnHide || !1;
            this._doHide && this._doHide(),
            this.trigger("pop:hide:before"),
            setTimeout($.proxy(function() {
                this.nBg.css({
                    opacity: 0,
                    "transition-property": "opacity"
                }),
                this.trigger("pop:hide:after"),
                setTimeout($.proxy(function() {
                    this.nBg.hide(),
                    this.nPopContainer.hide(),
                    e || this.destroy()
                },
                this), 200)
            },
            this), this.animationTime),
            this.onHide()
        },
        destroy: function() {
            return this.nPopContainer.remove(),
            this.nBg.remove(),
            this.contentView && this.contentView.remove(),
            this
        }
    });
    return o
}),
define("bower_components/pop/pop_forbid_scroll", ["require", "./pop"],
function(t) {
    window.zenjs = window.zenjs || {};
    var e = t("./pop"),
    i = e.extend({
        init: function(t) {
            this._super(t),
            this.on("pop:show:before", $.proxy(this.onBeforePopShow, this)),
            this.on("pop:show:after", $.proxy(this.onAfterPopShow, this)),
            this.on("pop:hide:after", $.proxy(this.onAfterPopHide, this))
        },
        onBeforePopShow: function() {
            this.top = this._window.scrollTop()
        },
        onAfterPopShow: function() {
            this._window.scrollTop(0),
            this.startShow()
        },
        onAfterPopHide: function() {
            var t, e = function(i) {
                return t !== this._window.scrollTop() && i > 0 ? (this._window.scrollTop(t), void setTimeout($.proxy(e, this, i - 1))) : void setTimeout($.proxy(this.onFinishHide, this), 50)
            };
            return function() {
                this.startHide(),
                t = this.top,
                this._window.scrollTop(t),
                $.proxy(e, this)(2),
                setTimeout($.proxy(function() {
                    window.zenjs.popList.length < 1 && $("html").css("position", this.htmlPosition)
                },
                this), 200)
            }
        } (),
        startShow: function() {
            var t = window.zenjs.popList;
            if (t || (t = window.zenjs.popList = []), 0 === t.length) {
                var e = $("body"),
                i = $("html");
                this.htmlPosition = i.css("position"),
                i.css("position", "relative"),
                this.bodyCss = (e.prop("style") || {}).cssText,
                this.htmlCss = (i.prop("style") || {}).cssText,
                $("body,html").css({
                    overflow: "hidden",
                    height: this._window.height()
                })
            }
            t.indexOf(this) < 0 && t.push(this)
        },
        startHide: function() {
            var t = window.zenjs.popList,
            e = t.indexOf(this);
            e > -1 && t.splice(e, 1),
            t.length < 1 && ($("html").attr("style", this.htmlCss || ""), $("body").attr("style", this.bodyCss || ""))
        }
    });
    return i
}),
define("bower_components/pop/popout", ["require", "./pop_forbid_scroll"],
function(t) {
    var e = t("./pop_forbid_scroll"),
    i = e.extend({
        init: function(t) {
            t = t || {},
            this._super(t),
            this.css = $.extend({
                transition: "opacity ease " + this.animationTime + "ms",
                top: "50%",
                left: "50%",
                "-webkit-transform": "translate3d(-50%, -50%, 0)",
                transform: "translateY(-50%, -50%, 0)"
            },
            t.css || {}),
            this.nPopContainer.css(this.css)
        },
        _doShow: function() {
            $(".js-popout-close").click($.proxy(function(t) {
                this.hide()
            },
            this)),
            this.nPopContainer.css("opacity", 1),
            this.nPopContainer.show()
        },
        _doHide: function(t) {
            this.nPopContainer.css({
                opacity: 0
            })
        }
    });
    return i
}),
define("bower_components/pop/popout_box", ["require", "./popout"],
function(t) {
    var e = function() {},
    i = t("./popout"),
    n = i.extend({
        init: function(t) {
            this._super(t),
            this._onOKClicked = t.onOKClicked || e,
            this._onCancelClicked = t.onCancelClicked || e,
            this.preventHideOnOkClicked = t.preventHideOnOkClicked || !1,
            this.width = t.width,
            this.setEventListener()
        },
        setEventListener: function() {
            this.nPopContainer.on("click", ".js-ok", $.proxy(this.onOKClicked, this)),
            this.nPopContainer.on("click", ".js-cancel", $.proxy(this.onCancelClicked, this))
        },
        _doShow: function() {
            this.boxCss = {
                "border-radius": "4px",
                background: "white",
                width: this.width || "270px",
                padding: "15px"
            },
            this.nPopContainer.css(this.boxCss).addClass("popout-box"),
            this._super()
        },
        _doHide: function(t) {
            this._super()
        },
        onOKClicked: function(t) {
            this._onOKClicked(t),
            !this.preventHideOnOkClicked && this.hide()
        },
        onCancelClicked: function(t) {
            this._onCancelClicked(t),
            this.hide()
        }
    });
    return n
}),
window.Utils = window.Utils || {},
$.extend(window.Utils, {
    needConfirm: function(t, e, i) {
        var n = window.confirm(t);
        n ? e && "function" == typeof e && e.apply() : i && "function" == typeof i && i.apply()
    }
}),
define("wap/components/util/confirm",
function() {}),
define("zenjs/util/jump_to_url", ["require", "bower_components/youzanjsbridge/api"],
function(t) {
    var e = t("bower_components/youzanjsbridge/api"),
    i = function(t) {
        "youzanmars" == _global.platform ? e.gotoWebview({
            page: "web",
            url: t
        }) : location.href = t
    };
    return i
}),
define("text!wap/showcase/sku/templates/buyBtn.html", [],
function() {
    return '<% if(!isMultiBtn) {%>\n    <a href="javascript:;" class="js-confirm-it btn btn-block btn-orange-dark btn-next">下一步</a>\n<% } else {\n    if(!isCartBtnHide) {%>\n        <div class="half-button">\n            <a href="javascript:;" class="js-mutiBtn-confirm confirm btn btn-block btn-orange-dark btn-buy">立即购买</a>\n        </div>\n        <div class="half-button">\n            <a href="javascript:;" class="js-mutiBtn-confirm cart btn btn-block btn-white btn-cart">加入购物车</a>\n        </div>\n    <% } else {%>\n        <a href="javascript:;" class="js-mutiBtn-confirm confirm btn btn-block btn-orange-dark btn-next">下一步</a>\n    <%}\n}%>\n'
}),
define("text!wap/showcase/sku/templates/skuContainer.html", [],
function() {
    return '<div class="sku-layout-title name-card sku-name-card">\n</div>\n\n<div class="adv-opts layout-content">\n    <div class="goods-models js-sku-views block block-list border-top-0"></div>\n    <div class="confirm-action content-foot clearfix"></div>\n</div>\n'
}),
define("text!wap/showcase/sku/templates/quantity.html", [],
function() {
    return '<dl class="clearfix block-item">\n    <dt class="sku-num pull-left">\n        <label>购买数量：</label>\n    </dt>\n    <dd class="sku-quantity-contaienr"></dd>\n    <dt class="other-info"></dt>\n</dl>\n'
}),
define("text!wap/showcase/sku/templates/quota.html", [],
function() {
    return '<div class="quota">每人限购<%= quota %>件</div>\n'
}),
define("css", [],
function() {
    var t = {};
    return t.normalize = function(t, e) {
        return ".css" == t.substr(t.length - 4, 4) && (t = t.substr(0, t.length - 4)),
        e(t)
    },
    t
}),
define("css!components_css/sku_layout", [],
function() {}),
require(["bower_components/wap_common/base/make_url_log", "bower_components/youzanjsbridge/api", "zenjs/backbone/quantity", "bower_components/wap_common/base/page_type", "wap/showcase/sku/views/message", "wap/showcase/sku/views/sku_selector", "wap/showcase/sku/views/sku_stock", "wap/showcase/sku/views/sku_title", "bower_components/login/main", "bower_components/pop/popout_box", "wap/components/util/confirm", "zenjs/util/args", "vendor/zepto/form", "zenjs/util/jump_to_url", "text!wap/showcase/sku/templates/buyBtn.html", "text!wap/showcase/sku/templates/skuContainer.html", "text!wap/showcase/sku/templates/quantity.html", "text!wap/showcase/sku/templates/quota.html", "css!components_css/sku_layout"],
function(t, e, i, n, o, s, a, r, l, c, u, d, h, p, m, f, g, b) {
    var k = function() {};
    Backbone.EventCenter = _.extend({},
    Backbone.Events);
    var v = Backbone.View.extend({
        initialize: function(t) {
            t = t || {},
            this.skuViewConfig = _.extend({
                bottom: 0,
                left: 0,
                right: 0,
                top: 40
            },
            t.skuViewConfig || {}),
            this.baseUrl = t.baseUrl,
            this.need_ajax_login = t.need_ajax_login || !1,
            this.wxpay_env = t.wxpay_env,
            this.isCartBtnHide = t.isCartBtnHide,
            this.quantityReadOnly = t.quantityReadOnly,
            this.isPriceCanChanged = !0,
            this.isPointsPriceCanChanged = !0,
            this.onAddSuccess = t.onAddSuccess || k,
            this.inited = !1,
            this.SkuTitleView = t.SkuTitleView || r,
            this.quotaTpl = t.quotaTpl || b,
            this.onHide = t.onHide ||
            function() {},
            this.viewTop = this.skuViewConfig.top,
            delete this.skuViewConfig.top,
            this.deviceView = {
                width: $(document).width(),
                height: $(document).height()
            },
            this.bodyPos = $("html").css("position"),
            this.nActionBtnTemplate = _.template(m),
            this.nPrice = this.$(".js-goods-price"),
            this.nPointsPrice = this.$(".js-points-price"),
            Backbone.EventCenter = Backbone.EventCenter || _.extend({},
            Backbone.Events),
            Backbone.EventCenter.on("sku:selected", _(this.onSelectChange).bind(this))
        },
        render: function(t) {
            if (t = t || {},
            this.sku = t.sku || {},
            this.goods_id = t.goods_id, this.points_goods = t.points_goods, this.store_id = window._global.offline_id, this.kdt_id = t.kdt_id || window._global.kdt_id, this.postage = t.postage, this.activity = t.activity, this.activity_alias = t.activity_alias || "", this.activity_id = t.activity_id || 0, this.activity_type = t.activity_type || 0, this.quota = t.quota, this.quota_used = t.quota_used, this.stockNum = t.stock || this.sku.stock_num, this.isGift = t.isGift, this.is_virtual = t.is_virtual, this.isPoints = t.isPoints, this.isAddCart = t.isAddCart, this.isAddWish = t.isAddWish, this.groupBuyLimit = t.groupBuyLimit || 0, this.isMultiBtn = t.isMultiBtn || !1, this.onAfterHideFunc = t.onAfterHideFunc ||
            function() {},
            this.goods_info = t.goods_info || {
                title: "",
                picture: [],
                price: "",
                points_price: "",
                origin: ""
            },
            this.item_id = t.item_id || "", this.is_buy = t.is_buy || "", this.show_stock = t.show_stock !== !1, !this.inited) {
                this.inited = !0,
                this.$el.append(f);
                var e = this.$(".sku-layout-title");
                this.skuTitleView = new this.SkuTitleView({
                    el: e
                }).render({
                    goods_info: this.goods_info,
                    activity: this.activity,
                    sku: this.sku,
                    points_goods: this.points_goods,
                    isPoints: this.isPoints
                }),
                this.$(".js-sku-views").empty();
                var n = this.$(".js-sku-views");
                if (this.skuSelectorView = new s({
                    sku: this.sku,
                    goods_info: this.goods_info
                }).render(), n.append(this.skuSelectorView.$el.children()), !this.isAddWish) {
                    var r = this.quantityLimit = this.getLimitNum(this.quota, this.quota_used, this.stockNum),
                    l = _.template(g)(),
                    c = $(l);
                    this.quantityView = new i({
                        readonly: this.quantityReadOnly,
                        num: 1,
                        tagName: "dl",
                        className: "clearfix",
                        limitNum: +r.limitNum,
                        minimalNum: this.isAddWish ? 0 : 1,
                        onOverLimit: _(function(t) {
                            var e = this.quota_used > 0 ? "<br>您之前已经购买过" + this.quota_used + "件": "";
                            return r = this.quantityLimit,
                            "quota" === r.limitType ? void motify.log("该商品每人限购" + this.quota + "件" + e) : "stock" === r.limitType ? void motify.log("就这么几件啦～") : void 0
                        }).bind(this),
                        onNumChange: _(function() {
                            var t;
                            return _(function(e) {
                                this.isAddWish && (0 === e ? t ? t.show() : (t = $('<div class="c-red text-right">0表示不限制赠送数量</div>'), this.quantityView.$el.parent().append(t)) : t.hide())
                            }).bind(this)
                        }).bind(this)()
                    }).render(),
                    n.append(c),
                    this.quantityView.$el.appendTo(c.find(".sku-quantity-contaienr"));
                    var u = c.find(".other-info");
                    if (this.show_stock && !this.sku.hide_stock && (this.stockView = new a({
                        el: $('<div class="stock"></div>'),
                        hide_stock: this.sku.hide_stock
                    }).render({
                        stock: this.stockNum
                    }), this.stockView.$el.appendTo(u)), this.quota) {
                        var d = _.template(this.quotaTpl)({
                            quota: this.quota
                        });
                        $(d).appendTo(u)
                    }
                }
                this.messageView = new o({
                    messages: this.sku.messages,
                    className: "block-item block-item-messages"
                }),
                n.append(this.messageView.render().el),
                this.skuSelectorView.autoSelect()
            }
            if (this.$(".confirm-action").html(this.nActionBtnTemplate({
                isMultiBtn: this.isMultiBtn,
                isCartBtnHide: this.isCartBtnHide
            })), this.nConfirmBtn = 0 === this.$(".js-confirm-it").length ? this.$(".js-mutiBtn-confirm.confirm") : this.$(".js-confirm-it"), r && 0 === r.limitNum && this.nConfirmBtn.attr("disabled", !0), this.isMultiBtn) this.nConfirmBtn.data("text", "立即购买");
            else {
                var h = this.isAddCart ? "加入购物车": "下一步";
                this.nConfirmBtn.text(h),
                this.nConfirmBtn.data("text", h)
            }
            return this.$el.css(this.skuViewConfig),
            !this.isAddWish || this.sku.messages && 0 !== this.sku.messages.length || !this.sku.none_sku || n.hide(),
            this
        },
        events: {
            "click .js-confirm-it": "doConfirmClicked",
            "click .js-cancel": "onCancelClicked",
            "click .js-mutiBtn-confirm": "onMultiBtnClick"
        },
        onSelectChange: function(t) {
            var e = t.skuComb,
            i = t.skuThumb,
            n = this.stockNum;
            i && this.skuTitleView.changeSkuThumb(i),
            e ? (this.quantityView && (["19170526", "24413294", "24413268", "24413261"].indexOf(e.id) >= 0 && 54023 === +_global.kdt_id ? (this.quantityView && this.quantityView.setMinimalNum(10), this.quantityView && this.quantityView.updateNum(10), this.quantityView.updateBtnStatus()) : ["19170527", "24413295", "24413269", "24413262", "35907137"].indexOf(e.id) >= 0 && 54023 === +_global.kdt_id ? (this.quantityView && this.quantityView.setMinimalNum(50), this.quantityView && this.quantityView.updateNum(50), this.quantityView.updateBtnStatus()) : (this.quantityView && this.quantityView.setMinimalNum(1), this.quantityView && this.quantityView.updateNum(1), this.quantityView.updateBtnStatus()), ["35907136"].indexOf(e.id) >= 0 && 187661641 === parseInt(_global.goods_id, 10) && 54023 === +_global.kdt_id ? (this.quantityView && this.quantityView.updateNum(10), this.quantityView && this.quantityView.setMinimalNum(10), this.quantityView.updateBtnStatus()) : ["35907135"].indexOf(e.id) >= 0 && 187661641 === parseInt(_global.goods_id, 10) && 54023 === +_global.kdt_id && (this.quantityView && this.quantityView.updateNum(1), this.quantityView && this.quantityView.setMinimalNum(1), this.quantityView.updateBtnStatus())), n = parseInt(e.get("stock_num"), 10), this.stockView && this.stockView.setNum(n), this.setPrice(e.get("price")), this.setPointsPrice(e.get("points_price"))) : (this.stockView && this.stockView.setNum(this.stockNum), this.skuTitleView.resetPrice()),
            this.quantityView && (this.quantityLimit = this.getLimitNum(this.quota, this.quota_used, n), e && ["35907136"].indexOf(e.id) >= 0 && 187661641 === parseInt(_global.goods_id, 10) && 54023 === +_global.kdt_id ? this.quantityView.setLimitNum(this.quantityLimit.limitNum >= 49 ? 49 : this.quantityLimit.limitNum) : e && ["35907135"].indexOf(e.id) >= 0 && 187661641 === parseInt(_global.goods_id, 10) && 54023 === +_global.kdt_id ? this.quantityView.setLimitNum(this.quantityLimit.limitNum >= 9 ? 9 : this.quantityLimit.limitNum) : this.quantityView.setLimitNum(this.quantityLimit.limitNum))
        },
        setPrice: function(t) {
            this.isPriceCanChanged && this.skuTitleView.changePrice(t)
        },
        setPointsPrice: function(t) {
            this.isPointsPriceCanChanged && this.skuTitleView.changePointsPrice(t)
        },
        disablePriceChange: function() {
            this.isPriceCanChanged = !1
        },
        disablePointsPriceChange: function() {
            this.isPointsPriceCanChanged = !1
        },
        onMultiBtnClick: function(t) {
            t = t || window.event;
            var e = t.target || t.srcElement;
            this.isAddCart = $(e).hasClass("cart"),
            this.doConfirmClicked(t)
        },
        doConfirmClicked: function(t) {
            function e() {
                n.doSubmit({
                    isPoints: n.isPoints,
                    buyType: o
                })
            }
            var i = $(t.target);
            if (!i.attr("disabled")) {
                var n = this,
                o = this.getBuyType();
                t && window.Logger ? window.Logger && Logger.log({
                    fm: "click",
                    title: o
                }).then(e,
                function() {
                    motify.log("亲，请稍等。")
                }) : e()
            }
        },
        doSubmit: function() {
            var i, o = function(e) {
                var i = this,
                o = this.messageView.getData();
                if (o) {
                    var s = $.extend({
                        goods_id: e.goods_id,
                        num: e.num,
                        sku_id: e.sku_id,
                        price: e.price,
                        points_price: e.points_price
                    },
                    o),
                    a = [s],
                    r = {
                        is_buy: this.is_buy,
                        item_id: this.item_id
                    };
                    delete e.goods_id,
                    delete e.num,
                    delete e.sku_id,
                    delete e.price,
                    delete e.points_price,
                    e.order_from = "";
                    var l = {
                        goodsList: JSON.stringify(a),
                        common: JSON.stringify(e)
                    };
                    r.item_id && (l.pinjian = JSON.stringify(r)),
                    $._ajax({
                        url: "/v2/trade/common/cache.json",
                        type: "POST",
                        dataType: "json",
                        data: l,
                        timeout: 15e3,
                        beforeSend: function() {
                            i.ajaxing = !0,
                            i.nConfirmBtn.attr("disabled", "disabled"),
                            i.nConfirmBtn.html("数据提交中")
                        },
                        success: function(o) {
                            var s = +o.code,
                            a = o.data;
                            if (0 !== s) return motify.log(o.msg),
                            void i.submitError("buy");
                            var r = window._global.url.trade + a.url;
                            r = d.add(r, {
                                showwxpaytitle: 1,
                                kdt_id: i.kdt_id
                            }),
                            e.from && (r = d.add(r, {
                                from: e.from
                            })),
                            e.use_wxpay && (r = d.add(r, {
                                use_wxpay: e.use_wxpay
                            })),
                            r = t(r),
                            r = d.add(r, {
                                page_type: n.getPageType(),
                                page_version: _global.page_version || "unknown",
                                platform: _global.platform
                            });
                            var l = d.add(r, {
                                book_key: a.key
                            });
                            i.onHide(),
                            p(l)
                        },
                        error: function() {
                            motify.log("购买失败，请重试。"),
                            i.submitError("buy")
                        },
                        complete: function() {
                            i.nConfirmBtn.removeAttr("disabled"),
                            i.ajaxing = !1
                        }
                    })
                }
            },
            s = function(t) {
                var i = this,
                n = this.baseUrl;
                $._ajax({
                    url: n + "/trade/cart/goods.jsonp",
                    type: "GET",
                    dataType: "jsonp",
                    cache: !1,
                    timeout: 15e3,
                    data: t,
                    beforeSend: function() {
                        i.ajaxing = !0
                    },
                    success: function(o) {
                        var s = +o.code;
                        if (0 === s) motify.log("已成功添加到购物车"),
                        i.onAddSuccess({
                            wish: !1,
                            cart: !0,
                            buy: !1
                        },
                        t),
                        window.eventHandler && (window.eventHandler.trigger("cart:add", o, t), window.eventHandler.trigger("global_icon:new")),
                        e.doAction({
                            action: "addToCart"
                        }),
                        i.onHide();
                        else if (40015 === s) {
                            var a = o.data || {},
                            r = n + "/trade/wxlogin",
                            l = a.cart_key || "";
                            r = d.add(r, {
                                cart_key: l,
                                kdt_id: i.kdt_id,
                                redirect_uri: location.href
                            }),
                            i.onHide(),
                            p(r)
                        } else motify.log(o.msg)
                    },
                    error: function(t, e, n) {
                        i.ajaxing = !1,
                        i.submitError("add-cart")
                    },
                    complete: function() {
                        i.ajaxing = !1
                    }
                })
            },
            a = function(t) {
                var e = this;
                $._ajax({
                    url: "/v2/trade/wish/add.json?kdt_id=" + e.kdt_id,
                    type: "POST",
                    dataType: "json",
                    cache: !1,
                    timeout: 15e3,
                    data: t,
                    beforeSend: function() {
                        e.ajaxing = !0,
                        e.nConfirmBtn.html("提交中..."),
                        e.doDisabled(e.nConfirmBtn, !0)
                    },
                    success: function(i) {
                        e.ajaxing = !1,
                        e.submitSuccess(i, t)
                    },
                    error: function(t, i, n) {
                        e.ajaxing = !1,
                        e.submitError("add-wish")
                    }
                })
            },
            r = {
                "add-cart": s,
                buy: o,
                "add-wish": a,
                gift: o
            },
            u = function(t, e) { (e.buyType || "function" == typeof i) && i.call(this, t)
            },
            h = function() {
                var t, e, i = this.skuSelectorView.getSelectedSku(),
                n = this.messageView.getData();
                if (!i.status) return motify.log("请选择 " + i.errMsg),
                !1;
                if (t = i.sku, !n) return ! 1;
                if (e = this.quantityView ? this.quantityView.getNum() : 1, !e) return motify.log("亲，是不是数量不对？"),
                !1;
                var o = {
                    kdt_id: this.kdt_id,
                    goods_id: this.goods_id,
                    postage: this.postage || 0,
                    num: e,
                    activity_alias: this.activity_alias,
                    activity_id: this.activity_id,
                    activity_type: this.activity_type,
                    sku_id: t.id,
                    price: parseInt(this.skuTitleView.getPrice(), 10) || t.get("price"),
                    points_price: this.skuTitleView.getPointsPrice() || t.get("points_price")
                },
                s = (d.get ||
                function() {})("from");
                return s && s.length > 0 && (o.from = s),
                this.wxpay_env ? o.use_wxpay = 1 : o.use_wxpay = 0,
                _(o).extend(n),
                o
            };
            return function(t) {
                if (i = r[t.buyType], !t.notCheckBtnDisabled && this.isDisabled(this.nConfirmBtn)) return ! 1;
                if (this.ajaxing) motify.log("提交订单中，请勿重复提交。");
                else {
                    var e = h.call(this);
                    if (!e) return ! 1;
                    if (this.isPoints && (e.is_points = 1), this.store_id && (e.store_id = this.store_id), this.isGift && (e.order_type = 1), t.accept_price && (e.accept_price = t.accept_price), this.needLogin()) {
                        var n = new c({
                            contentViewClass: l,
                            contentViewOptions: {
                                afterLogin: _(function(i) {
                                    n.hide(),
                                    window.user_logined = !0,
                                    "pinjian" == window._global.goods_type ? window.location.reload() : u.call(this, e, t)
                                }).bind(this)
                            }
                        });
                        return void n.render().show()
                    }
                    u.call(this, e, t)
                }
            }
        } (),
        doWait: function(t) {
            t > 0 ? (this.nConfirmBtn.attr("disabled", !0), this.isMultiBtn ? this.nConfirmBtn.text(this.nConfirmBtn.data("text") + "(" + t + ")") : this.nConfirmBtn.text("正在排队购买，请等待 " + t + " 秒后再提交"), this.waitId = setTimeout(_(this.doWait).bind(this, t - 1), 1e3)) : (this.nConfirmBtn.removeAttr("disabled"), this.nConfirmBtn.text(this.nConfirmBtn.data("text")), this.waitId = !1)
        },
        submitSuccess: function() {
            var t = {
                11011 : !0,
                11014 : !0,
                11012 : !0,
                11013 : !0
            },
            e = function(t) {
                motify.log(t),
                this.nConfirmBtn.html(t),
                this.doDisabled(this.nConfirmBtn, !0)
            };
            return function(i, n) {
                var o = this,
                s = i.code;
                if (0 === s) {
                    if (o.onAddSuccess({
                        wish: o.isAddWish,
                        cart: !1,
                        buy: !o.isAddWish
                    },
                    n), o.isAddWish) return window.eventHandler && window.eventHandler.trigger("wish:add"),
                    window.eventHandler && window.eventHandler.trigger("global_icon:new"),
                    void o.onHide();
                    var a = i.data.order_no;
                    if (!a || 0 == a.length || !i.data.trade_confirm_url) return motify.log("订单生成失败，请联系管理员。"),
                    o.nConfirmBtn.html("确认提交"),
                    o.nConfirmBtn.removeAttr("disabled"),
                    !1;
                    p(i.data.trade_confirm_url)
                } else 12500 === s ? (this.isMultiBtn && motify.log("网络繁忙，请稍后再试～"), o.doWait( + i.data.wait)) : 11010 === s ? Utils.needConfirm(i.msg + "（新价格：" + (parseInt(i.data.cur_price, 10) / 100).toFixed(2) + "元）",
                function() {
                    var t = o.getBuyType();
                    o.doSubmit({
                        buyType: t,
                        accept_price: 1,
                        notCheckBtnDisabled: !0
                    })
                },
                function() {
                    window.location.reload()
                }) : 13021 === s ? p(i.data.redirectUrl) : t[s + ""] ? e.call(o, i.msg) : 11020 === s ? (motify.log(i.msg + "正在刷新页面..."), window.location.reload()) : 11021 === s ? (motify.log(i.msg), o.nConfirmBtn.html("确认提交"), o.nConfirmBtn.removeAttr("disabled"), o.quantityView.updateNum(1)) : (motify.log(i.msg), o.nConfirmBtn.html("再次提交"), o.nConfirmBtn.removeAttr("disabled"))
            }
        } (),
        submitError: function(t) {
            this.doDisabled(this.nConfirmBtn, !1),
            "buy" == t ? this.nConfirmBtn.html("立即购买") : "add-cart" == t ? motify.log("添加到购物车失败") : "add-wish" == t && (motify.log("添加到心愿单失败"), this.nConfirmBtn.html("下一步"))
        },
        onCancelClicked: function(t) {
            this.onHide()
        },
        hide: function(t) {
            this.waitId && (clearTimeout(this.waitId), this.waitId = !1, this.nConfirmBtn.removeAttr("disabled")),
            this.isMultiBtn && (this.$(".js-sku-views").empty(), this.$(".layout-content").height("auto"), this.skuSelectorView = this.skuSelectorView.clear())
        },
        height: function() {
            var t = $(window).height() - this.viewTop,
            e = this.$(".sku-layout-title").outerHeight(),
            i = t - e,
            n = this.$(".layout-content").height();
            this.skuConH = n,
            this.skuConWinH = i,
            this.skuConHeight = i > n ? n: i,
            this.skuViewHeight = this.skuConHeight + e,
            this.$(".layout-content").css("max-height", i)
        },
        onAfterPopupShow: function() {
            this.quantityView && this.quantityView.validateNum()
        },
        displaySku: function(t) {
            return this.render(t || {})
        },
        getLimitNum: function(t, e, i) {
            var n, o, s = t - e;
            return 0 > s && (s = 0),
            i > s && 0 !== t ? (n = +s, o = "quota") : (n = +i, o = "stock"),
            {
                limitNum: n,
                limitType: o
            }
        },
        doDisabled: function(t, e) {
            e ? this.nConfirmBtn.attr("disabled", !0) : this.nConfirmBtn.removeAttr("disabled")
        },
        isDisabled: function(t) {
            return t.attr("disabled")
        },
        getBuyType: function() {
            return this.isAddWish ? "add-wish": this.isAddCart ? "add-cart": this.isGift ? "gift": "buy"
        },
        needLogin: function() {
            return window.user_logined || !this.need_ajax_login ? !1 : this.isAddWish || this.isAddCart ? !1 : !0
        }
    });
    return window.BuyView = v
}),
define("main",
function() {}),
function(t) {
    var e = document,
    i = "appendChild",
    n = "styleSheet",
    o = e.createElement("style");
    o.type = "text/css",
    e.getElementsByTagName("head")[0][i](o),
    o[n] ? o[n].cssText = t: o[i](e.createTextNode(t))
} ("@charset \"UTF-8\";.block,.block-item{display:block;overflow:hidden;border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch}.block,.block p,.block-item{overflow:hidden}.quantity,.quantity .txt,.quantity button,.sku-layout .vertical-middle{vertical-align:middle}.block,.block-item,.sku-layout .block-item,.sku-layout-title,.sku-message dl{border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch}.block-item{position:relative;padding:10px;line-height:1.5;border:0;-webkit-border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch;-moz-border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch;border-top:2px solid #e5e5e5}@media only screen and (-webkit-min-device-pixel-ratio:1.5),only screen and (min--moz-device-pixel-ratio:1.5),only screen and (min-device-pixel-ratio:1.5){.block-item{border-top-width:1px}}.block-item .btn-auth-code{position:absolute;top:6px;right:0;height:30px;line-height:30px;padding-left:7px;padding-right:7px;font-weight:700}.block-item .verify-image{position:absolute;width:80px;height:30px;right:10px;top:7px}.block,.quantity,.quantity .minus{position:relative}.block-item.border-none{border-top:0}.block-item ul{padding-right:50px}.block-item ul em{color:#999}.block-item h4.block-item-title{line-height:22px;float:left;margin-right:10px}.block{-webkit-border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch;-moz-border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch;border-top:2px solid #e5e5e5;border-bottom:2px solid #e5e5e5;margin:10px 0;background-color:#fff;font-size:14px}.block.top-0,.block:first-child{margin-top:0}.quantity,.tag{display:inline-block}@media only screen and (-webkit-min-device-pixel-ratio:1.5),only screen and (min--moz-device-pixel-ratio:1.5),only screen and (min-device-pixel-ratio:1.5){.block{border-top-width:1px;border-bottom-width:1px}}.block.border-0,.block.border-top-0{border-top:0}.block.border-0,.block.border-bottom-0{border-bottom:0}.block.bottom-0{margin-bottom:0}.block .bottom{padding:10px;height:18px;line-height:18px}.block .bottom .price{float:right;color:#f60}.block.block-list{margin:0;padding:0 0 0 10px;list-style:none;font-size:14px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}.block.block-list.margin-top-normal{margin-top:20px}.block-list.wf{padding-left:0}.block-list.wf .b-list{padding-left:10px;background-color:#fff}.block-list>.block-item{padding:10px 10px 10px 0}.block-list>.block-item:first-child{border-top:0 none}.block.block-list+.block.block-list{margin-top:12px}.quantity .txt,.quantity button,.tag{margin:0;text-align:center}@media only screen and (-webkit-min-device-pixel-ratio:1.5),only screen and (min--moz-device-pixel-ratio:1.5),only screen and (-o-min-device-pixel-ratio:3/2),only screen and (min-device-pixel-ratio:1.5){.block.block-list li span.clear{background:url(https://b.yzcdn.cn/v2/image/wap/icon_clear@2x.png) center center no-repeat;background-size:100%}}.tag{background-color:transparent;border:1px solid #e5e5e5;border-radius:3px;color:#999;font-size:12px;line-height:12px;padding:4px}.tag-big{font-size:14px;line-height:18px}.tag.tag-green{color:#06bf04;border-color:#0c3}.tag.tag-orange,.tag.tag-orangef60{color:#f60;border-color:#f60}.tag.tag-white{color:#333;border-color:#bbb}.tag.tag-blue{color:#38f;border-color:#38f}.tag.tag-red{color:#ed5050;border-color:#ed5050}.tag.tag-pink{color:#ee614b;border-color:#ee614b}.tag.disabled{background-color:#ddd!important;background-image:none!important;border:1px solid transparent!important;color:#fff!important}.quantity{font-size:0}.quantity input[type=number]::-webkit-outer-spin-button{margin:0}.quantity button{border:2px solid #eee;font-size:16px;line-height:10px;font-weight:700;color:#666;padding:5px;outline:0!important;width:26px;height:30px;text-indent:-9999px;overflow:hidden}.quantity .txt{font-size:14px;width:24px;height:18px;-webkit-tap-highlight-color:transparent;border-radius:0}.quantity .minus::before,.quantity .plus::before{width:8px;height:2px;top:0;left:0;right:0;margin:auto;background-color:#6c6c6c;bottom:0;content:''}.quantity .txt:focus{border-color:#eee}.quantity .minus{border-radius:4px 0 0 4px;border-right:0 none}.quantity .minus::before{position:absolute}.quantity .plus{position:relative;border-left:0 none;border-radius:0 4px 4px 0}.quantity .plus::before{position:absolute}.quantity .plus::after{position:absolute;top:0;bottom:0;left:0;right:0;margin:auto;content:'';width:2px;height:8px;background-color:#6c6c6c}.quantity .minus.disabled::before,.quantity .plus.disabled::after,.quantity .plus.disabled::before{background-color:#ddd}.quantity .response-area{width:42px;height:42px;top:-7px;position:absolute}.name-card,.name-card .thumb{position:relative;overflow:hidden}.quantity .response-area-plus{right:-5px}.quantity .response-area-minus{left:-5px}.name-card{margin-left:0;width:auto;padding:5px 0}.name-card .thumb{width:60px;height:60px;float:left;margin-left:auto;margin-right:auto;background-size:cover}.name-card .thumb img{position:absolute;margin:auto;top:0;left:0;right:0;bottom:0;width:auto;height:auto;max-width:100%;max-height:100%}.name-card a:active,.name-card a:hover{text-decoration:none}.name-card .detail{margin-left:68px;width:auto;position:relative}.name-card .detail h3{margin-top:1px;color:#333;font-size:12px;line-height:16px;width:100%}.name-card .detail p{position:relative;font-size:12px;line-height:16px;white-space:nowrap;margin:0 0 2px;color:#ccc}.name-card .detail a{display:block}.name-card .detail .l2-ellipsis{max-height:34px;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}.name-card .btn-goods-link{bottom:8px;right:10px;position:absolute;line-height:14px;font-size:12px}.name-card.name-card-3col{padding:8px 85px 8px 0}.name-card.name-card-3col .right-col{position:absolute;right:0;top:8px;width:78px;padding-right:10px;font-size:12px}.name-card.name-card-3col .right-col .price{font-size:14px;color:#515151;text-align:right;line-height:16px}.name-card.name-card-3col .right-col .num{font-weight:200;text-align:right;margin-top:3px;padding:0;color:#555}.name-card.name-card-3col .right-col .num .num-txt{padding:0;line-height:22px;color:#515151}.name-card.name-card-3col .right-col .order-state{font-size:13px;text-align:right}.sku-layout{background-color:#fff;-webkit-overflow-scrolling:touch}.sku-layout .line-through{display:inline-block;text-decoration:line-through;line-height:23px}.sku-layout .block-item{border:0;-webkit-border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch;-moz-border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch;border-top:2px solid #e5e5e5}@media only screen and (-webkit-min-device-pixel-ratio:1.5),only screen and (min--moz-device-pixel-ratio:1.5),only screen and (min-device-pixel-ratio:1.5){.sku-layout .block-item{border-top-width:1px}}.sku-layout .block-item:first-child{border-top:0 none}.sku-box-shadow{-webkit-box-shadow:0 -1px 14px rgba(0,0,0,.9);box-shadow:0 -1px 14px rgba(0,0,0,.9)}.sku-layout-title{-webkit-border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch;-moz-border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch;border-bottom:2px solid #e5e5e5;border-top-width:0;position:static;padding:10px 0;margin-left:10px}@media only screen and (-webkit-min-device-pixel-ratio:1.5),only screen and (min--moz-device-pixel-ratio:1.5),only screen and (min-device-pixel-ratio:1.5){.sku-layout-title{border-bottom-width:1px}}.sku-layout-title .thumb{width:50px;height:50px;border:1px solid #eee;border-radius:2px}.sku-layout-title .goods-base-info{margin-left:60px}.sku-layout-title .goods-base-info .title{padding-right:52px;margin-bottom:5px;font-size:14px;line-height:22px}.sku-layout-title .goods-base-info .goods-price{padding:0 55px 0 0}.sku-layout-title .goods-base-info .current-price{line-height:20px}.sku-layout-title .goods-base-info .current-price .price-name{padding-top:1px}.sku-layout-title .goods-base-info .current-price .price-tag{padding:0 5px;margin-left:5px;line-height:16px;font-size:10px;border-radius:2px}.sku-layout-title .goods-base-info .old-price,.sku-layout-title .goods-base-info .original-price{color:#999}.sku-layout-title .goods-base-info .original-price{display:none}.sku-layout-title .goods-base-info .old-price{display:block;margin:5px 0 0 2px;font-size:10px;line-height:12px}.sku-layout .sku-cancel{position:absolute;right:0;top:0;padding:10px}.sku-layout .sku-cancel .cancel-img{height:22px;width:22px;background-image:url(https://b.yzcdn.cn/v2/image/wap/sku/icon_close.png);background-size:22px 22px}.sku-layout .goods-models .sku-list-container{padding:10px 0 0;border:0}.sku-layout .sku-sel-title{margin-bottom:10px;font-size:13px}.sku-layout .sku-sel-list{zoom:1;padding-left:0;margin-bottom:0}.sku-layout .sku-sel-list:after{content:'';display:table;clear:both}.sku-layout .sku-tag{position:relative;margin-right:10px;min-width:32px;max-width:180px;line-height:16px;padding:5px 9px;margin-bottom:10px;color:#333;border-color:#999}.sku-layout .sku-tag.unavailable{border-color:#e5e5e5;color:#cacaca;background-color:#eee;cursor:not-allowed}.sku-layout .sku-tag.active{color:#fff;background-color:#f60;border-color:#f60}.sku-layout .sku-num{line-height:29px}.sku-layout .other-info{line-height:14px}.sku-layout .stock{display:inline-block;margin-right:10px;font-size:12px}.sku-layout .quota{display:inline-block;font-size:12px;color:#f15a0c}.sku-layout .quantity{float:right}.sku-layout .quantity .minus{border-radius:2px 0 0 2px}.sku-layout .quantity .minus.disabled{background-color:#f8f8f8;border-color:#e8e8e8 #999 #e8e8e8 #e8e8e8}.sku-layout .quantity .minus.disabled::before{background-color:#bbb}.sku-layout .quantity .plus{border-radius:0 2px 2px 0}.sku-layout .quantity .plus.disabled{background-color:#f8f8f8;border-color:#e8e8e8 #e8e8e8 #e8e8e8 #999}.sku-layout .quantity .plus.disabled::after,.sku-layout .quantity .plus.disabled::before{background-color:#bbb}.sku-layout .quantity .txt{width:33px;height:25px;padding:1px;border:1px solid #999;border-width:1px 0;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;color:#666}.sku-layout .quantity .txtCover{position:absolute;top:0;left:37px;bottom:0;right:37px}.sku-layout .quantity .minus,.sku-layout .quantity .plus{width:37px;height:29px;background-color:#fff;border:1px solid #999}.sku-layout .quantity .minus::before,.sku-layout .quantity .plus::before{height:1px;width:9px;background-color:#666}.sku-layout .quantity .minus::after,.sku-layout .quantity .plus::after{width:1px;height:9px;background-color:#666}sup.required{color:red!important}.block-list .block-item.block-item-messages{padding:0;-webkit-tap-highlight-color:transparent}.sku-message dl{-webkit-border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch;-moz-border-image:url(https://b.yzcdn.cn/v2/image/wap/border-line-2.png) 2 stretch;border-top:2px solid #e5e5e5}@media only screen and (-webkit-min-device-pixel-ratio:1.5),only screen and (min--moz-device-pixel-ratio:1.5),only screen and (min-device-pixel-ratio:1.5){.sku-message dl{border-top-width:1px}}.sku-message dl:first-child{border:0}.sku-message dt{width:70px;padding-left:10px;position:relative}.sku-message dt .required{font-size:17px;margin-left:-7px;position:absolute}.sku-message dt label{line-height:40px}.sku-message .comment-wrapper{margin-left:90px;padding-right:5px;position:relative}.sku-message .comment-wrapper .txt,.sku-message .comment-wrapper .txta{padding:9px 0 10px;line-height:20px;width:97%;border:0;outline:0;-webkit-appearance:none;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}.sku-message .comment-wrapper .txt[type=text]{padding:10px 0}.sku-message .comment-wrapper .txta{padding:0;margin-top:10px;height:50px;resize:none}.image-input-trigger{padding:0 10px;height:24px;line-height:24px;margin:8px 0;-webkit-tap-highlight-color:transparent}.image-input-trigger .select-photo::before,.image-input-trigger .take-photo::before{content:'';position:relative;display:inline-block;margin-right:5px;width:14px;height:12px;background-size:14px 12px}.image-input-trigger .take-photo{margin-right:10px}.image-input-trigger .take-photo::before{top:1px;background-image:url(https://b.yzcdn.cn/v2/image/wap/sku/icon_camera.png)}.image-input-trigger .select-photo::before{top:2px;margin-left:10px;background-image:url(https://b.yzcdn.cn/v2/image/wap/sku/icon_img.png)}.image-input-show{margin-bottom:8px;height:60px}.image-input-show img{margin-right:5px;width:60px;height:60px;vertical-align:text-bottom}.image-input-show .img-tip{font-size:10px;color:#999}.photo-input{position:absolute;opacity:0;height:40px;width:175px;left:0;top:0}.sku-layout .layout-content{overflow-y:scroll;border:1px solid #fff;line-height:20px;background-color:#fff}.sku-layout .content-foot{padding:10px;font-size:0}.sku-layout .content-foot .half-button{float:left;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;width:50%}.sku-layout .content-foot .half-button:first-child{padding-right:5px}.sku-layout .content-foot .half-button:last-child{padding-left:5px}.sku-layout .content-foot .btn-buy,.sku-layout .content-foot .btn-cart,.sku-layout .content-foot .btn-next{padding:8px;line-height:17px;font-size:14px}");
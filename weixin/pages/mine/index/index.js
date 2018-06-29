import { showModal } from '../../../static/library/wx-api';

Page({
    data: {
        isShow: false //显示遮罩
    },
    onLoad () {
    },
    goToWebHandle () {
        this.setData({
            isShow: true
        })
    },
    confirmHandel () {
        wx.showToast({
            title: '该功能未开发，敬请期待',
            icon: 'none',
            duration: 2000
        })
    },
    cancelHandle() {
        this.setData({
            isShow: false
        })
    }
});

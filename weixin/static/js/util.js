// 年月日（精确）
function formatDate(now) {
    var year = now.getFullYear();
    var month = now.getMonth() + 1;
    var date = now.getDate();
    var hour = now.getHours();
    var minute = now.getMinutes();
    var second = now.getSeconds();
    return year + "-" + (month >= 10 ? month : '0' + month) + "-" + (date >= 10 ? date : '0' + date)
        + " " + (hour >= 10 ? hour : '0' + hour) + ":" + (minute >= 10 ? minute : '0' + minute) + ":" + (second >= 10 ? second : '0' + second);
}
// 年月日
function formatYMD(now) {
    var year = now.getFullYear();
    var month = now.getMonth() + 1;
    var date = now.getDate();
    return year + "-" + (month >= 10 ? month : '0' + month) + "-" + (date >= 10 ? date : '0' + date);
}
// 时分秒
function formatHMS(now) {
    var hour = now.getHours();
    var minute = now.getMinutes();
    var second = now.getSeconds();
    return (hour >= 10 ? hour : '0' + hour) + ":" + (minute >= 10 ? minute : '0' + minute) + ":" + (second >= 10 ? second : '0' + second);
}
// 当前时间
function getCurrentTime() {
    var date = new Date();
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    m = m < 10 ? '0' + m : m;
    var d = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
    var h = date.getHours() < 10 ? '0' + date.getHours() : date.getHours();
    var f = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
    var s = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();
    keep = y + '' + m + '' + d + '' + h + '' + f + '' + s;
    return keep;
}
// 浮点型加法
function accAdd(arg1, arg2) {
    var r1, r2, m;
    try {
        r1 = arg1.toString().split(".")[1].length;
    } catch (e) {
        r1 = 0;
    }
    try {
        r2 = arg2.toString().split(".")[1].length;
    } catch (e) {
        r2 = 0;
    }
    m = Math.pow(10, Math.max(r1, r2));
    return ((arg1 * m + arg2 * m) / m).toFixed(2);
}
// 浮点型乘法
function mul(a, b) {
    var c = 0,
        d = a.toString(),
        e = b.toString();
    try {
        c += d.split(".")[1].length;
    } catch (f) {
    }
    try {
        c += e.split(".")[1].length;
    } catch (f) {
    }
    return Number(d.replace(".", "")) * Number(e.replace(".", "")) / Math.pow(10, c);
}
// 浮点型除法
function div(a, b) {
    var c, d, e = 0,
        f = 0;
    try {
        e = a.toString().split(".")[1].length;
    } catch (g) {
    }
    try {
        f = b.toString().split(".")[1].length;
    } catch (g) {
    }
    return c = Number(a.toString().replace(".", "")), d = Number(b.toString().replace(".", "")), mul(c / d, Math.pow(10, f - e));
}
// 数字落在数组的区间
function judgeWhichRegion(num, arr) {
    arr = JSON.parse(JSON.stringify(arr)).sort((a, b) => {
        return a - b
    })
    let arrLength = arr.length
    for (let i in arr) {
        if (num >= arr[i] && num < arr[i - 0 + 1]) {
            return i
        } else if (num > arr[arrLength - 1]) {
            return arrLength - 1
        } else if (num < arr[0]) {
            return 0
        }
    }
}

export {
    formatDate,
    formatYMD,
    accAdd,
    mul,
    div,
    judgeWhichRegion,
    getCurrentTime,
    formatHMS
}
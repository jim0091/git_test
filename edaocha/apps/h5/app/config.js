/**
 * ThinkSNS 配置文件
 */

// 网址根地址
//const base = '//www.aodou.com';//线上环境
const base = '__ROOT__';//本地环境
// Root URL.
export const base_url = base;

// Dist URL.
//export const dist_url = '//v3.edaocha.net/storage/app/h5/';//线上环境
export const dist_url = base + '/apps/h5/dist/';//本地环境
// H5 api build URL.
export const h5_api_url = base + '/index.php?app=h5&mod=%controller%&act=%action%';

// API URL.
export const api_url = base + '/api.php?mod=%controller%&act=%action%';

// Website title.
export const title = '奥豆';

// Website description.
export const description = '和有趣的人一起玩！';

// Website keywords.
export const keywords = '娱乐,社交';


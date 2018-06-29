
const DOMAIN = 'https://api.datashenzhen.net/jde_v3/';
// const DOMAIN2 = 'https://wecake.cc/booking/';
// const DOMAIN3 = 'https://wecake.cc';

const urls = {

  // 获取令牌
  getSession: DOMAIN + 'session.json',
  /********** 首页 **********/
  //热门舆情
  getStopic: DOMAIN + 'stopic.json',
  //舆情详情
  getStopicview: DOMAIN + 'stopicview.json',
  //公司基本信息
  getCompanyInfo: DOMAIN + 'info.json',
  //变更记录
  getLogRecord: DOMAIN + 'changes.json',
  //股东信息
  getHolders: DOMAIN + 'holders.json',
  //董监高
  getManagers: DOMAIN + 'managers.json',
  //董监高具体信息
  getManagerInfo: DOMAIN + 'managerinfo.json',
  //对外投资
  getInvest: DOMAIN + 'invests.json',
  //分支机构
  getBranches: DOMAIN + 'branchs.json',
  //专利
  getPatent: DOMAIN + 'patent.json',
  //软著
  getSofts: DOMAIN + 'softs.json',
  //网络备案
  getWebsites: DOMAIN + 'websites.json',
  //招聘信息
  getJobs: DOMAIN + 'jobs.json',
  //商标
  getBrand: DOMAIN + 'signs.json',
  //司法信息
  getWenshuList: DOMAIN + 'wenshus.json',
  //司法信息详情
  getWenshuInfo: DOMAIN + 'wenshuinfo.json',
  //招标信息
  getBids: DOMAIN + 'bids.json',
  //公告信息
  getNotice: DOMAIN + 'dynamics.json',
  //搜索
  companySearch: DOMAIN + 'search.json',
  //舆情搜索
  stopicSearch: DOMAIN + 'stopicsearch.json',
  //搜索的舆情详情
  getSearchStopic: DOMAIN + 'dynamicsinfo.json',
  //搜索的舆情详情
  getManagerNews: DOMAIN + 'managernews.json'
  /********** 我的 **********/

}
module.exports = {
  urls,
  DOMAIN
  // DOMAIN2,
  // DOMAIN3
}


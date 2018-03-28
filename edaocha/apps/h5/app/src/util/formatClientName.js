/**
 * formatClientName.
 */
const clients = {
  0: '奥豆APP',
  1: '奥豆APP',
  2: '奥豆APP',
  3: '奥豆APP',
  4: '奥豆APP',
  5: '奥豆APP',
  6: '奥豆APP',
};

const formatClientName = clientCode => clients[clientCode] || clientCode;

export default formatClientName;
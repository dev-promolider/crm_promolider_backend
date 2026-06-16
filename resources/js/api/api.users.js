import request from './api';

const apiUsers = {
  listByUser: () => request.get(`/users/api`),
  listUsers: () => request.get(`/users/api/list`),
  listMyDirects: () => request.get(`/users/api/my-directs`)
 };

export default apiUsers;

import api from './api';

const apiAccountType = {
  list: ()  => api.get(`/account-type/accountType`),
  add: accountType => api.post('/account-type/accountType', accountType),
  detail: id => api.get(`/account-type/accountType/${id}`),
  edit: accountType => api.put(`/account-type/accountType/${accountType.id}`, accountType),
  delete: (id, params) => api.deleteState(`/account-type/accountType/${id}`, params)
};

export default apiAccountType;

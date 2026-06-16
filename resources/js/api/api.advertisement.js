import api from './api';

const apiAdvertisements = {
  list: () => api.get('/config/advertisements/list'),
  add: advertisement => api.post('/config/advertisements/add', advertisement),
  detail: id => api.get(`/config/advertisements/detail/${id}`),
  edit: advertisement => api.put(`/config/advertisements/edit/${advertisement.id}`, advertisement),
  delete: (id, params) => api.deleteState(`/config/advertisements/delete/${id}`, params)
};

export default apiAdvertisements;

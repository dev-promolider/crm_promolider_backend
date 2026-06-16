import api from './api';

const apiShareLink = {
  detail: (id) => api.get(`/config/share-link/detail/${id}`),
  add: shareLink => api.post('/config/share-link/add', shareLink),
  edit: (shareLink) => api.put(`/config/share-link/edit/${shareLink.id}`, shareLink),
  delete: (id) => api.delete(`/config/share-link/delete/${id}`),
  referrals: (username) => api.get(`/config/share-link/referrals/${username}`),
};

export default apiShareLink;
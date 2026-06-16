import api from './api';

const apiPaymentMethod = {
  list: () => api.get('/config/payment-method/list'),
  add: (paymentMethod) => api.post('/config/payment-method/add', paymentMethod),
  detail: (id) => api.get(`/config/payment-method/detail/${id}`),
  edit: (paymentMethod) => api.put(`/config/payment-method/edit/${paymentMethod.id}`, paymentMethod),
  delete: (id, params) => api.deleteState(`/config/payment-method/delete/${id}`, params),
  getTypes: () => api.get('/payment/payment-methods/types'),
};

export default apiPaymentMethod;

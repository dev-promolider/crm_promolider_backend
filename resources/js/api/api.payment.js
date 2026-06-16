import api from './api';

const apiPayment = {
  list: () => api.get('/payment/list'),
};

export default apiPayment;

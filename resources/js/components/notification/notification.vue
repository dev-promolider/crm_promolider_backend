<template>
  <div class="notification-container">
    <section>
      <div class="row" v-if="listNotification.length > 0">
        <div class="col-md-9 mx-auto">
          <div class="notification-card">
            <div class="card-header">
              <div class="btn-group">
                <button type="button" class="btn btn-light">Todos</button>
              </div>
            </div>
            <div class="notification-body">
              <div v-for="(notification, index) in listNotification" :key="notification.id" class="notification-item">
                <hr v-if="index > 0" />
                <div class="media">
                  <img :src="notification.photo" alt="User Photo" class="notification-img" />
                  <div class="media-body">
                    <h5 class="notification-title">
                      {{ notification.title }}
                    </h5>
                    <p class="notification-message">
                      {{ notification.body }}
                    </p>
                    <small class="notification-date">
                      {{ formatearFecha(notification.created_at) }}
                    </small>
                  </div>
                  <div class="notification-status">
                    <img src="https://cdn-icons-png.flaticon.com/512/520/520648.png" alt="icon-image"
                      class="status-icon" data-toggle="tooltip" data-placement="bottom" title="Mensaje Recibido!" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="row">
        <div class="col-12">
          <p class="empty-message">No tiene notificaciones</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import api from '../../api/api';

export default {
  name: 'notification-view',
  data() {
    return {
      listNotification: [],
    };
  },
  mounted() {
    this.listar();
  },
  methods: {
    listar() {
      api.get('/notifications/list').then((response) => {
        this.listNotification = response;
      });
    },
    formatearFecha(fecha) {
      return fecha.split('T')[0];
    },
  },
};
</script>

<style>
.notification-container {
  padding: 20px;
  background-color: #fafafa;
}

.notification-card {
  background: white;
  border-radius: 4px;
  border: 1px solid #eaeaea;
}

.card-header {
  padding: 15px;
  border-bottom: 1px solid #eee;
  background-color: white;
}

.notification-body {
  padding: 10px 15px;
}

.notification-item {
  padding: 10px 0;
}

.notification-item hr {
  margin: 0 0 15px 0;
  border-color: #f0f0f0;
}

.media {
  display: flex;
  align-items: flex-start;
  gap: 15px;
}

.notification-img {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  object-fit: cover;
}

.media-body {
  flex: 1;
}

.notification-title {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 5px;
  color: #333;
}

.notification-message {
  font-size: 14px;
  color: #666;
  margin-bottom: 5px;
}

.notification-date {
  color: #888;
  font-size: 12px;
}

.status-icon {
  width: 20px;
  height: 20px;
}

.btn-light {
  border: 1px solid #e0e0e0;
  background-color: white;
  font-size: 14px;
  padding: 6px 12px;
}

.btn-light:hover {
  background-color: #f8f9fa;
}

.empty-message {
  text-align: center;
  color: #666;
  padding: 20px;
}

@media (max-width: 768px) {
  .notification-container {
    padding: 10px;
  }

  .notification-img {
    width: 40px;
    height: 40px;
  }

  .notification-title {
    font-size: 15px;
  }

  .notification-message {
    font-size: 13px;
  }
}
</style>

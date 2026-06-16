import Vue from "vue";


Vue.filter('formatDate', function(value) {
  if (value) {
    return moment(String(value))
      .locale('es')
      .format('D MMM YYYY');
  }
});
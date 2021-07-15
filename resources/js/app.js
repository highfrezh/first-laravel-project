require('./bootstrap');

//...
// Import Vue
import Vue from 'vue'

// Import V-form
import Form from 'vform'
window.Form = Form;

// vform component importation
import {
  AlertError,
  AlertErrors,
  HasError,
  AlertSuccess
} from 'vform/src/components/bootstrap5'

// vform build in  components
Vue.component(AlertSuccess.name, AlertSuccess)
Vue.component(AlertError.name, AlertError)
Vue.component(AlertErrors.name, AlertErrors)
Vue.component(HasError.name, HasError)

// Import VueRouter
import VueRouter from 'vue-router'

//Import moment for date formatting
import moment from 'moment';

//Import VueProgressBar
import VueProgressBar from 'vue-progressbar'

//Import Sweetalert2
import Swal from 'sweetalert2'
window.swal = Swal;

//Import Gate.js
import Gate from "./Gate";
import _ from 'lodash';
Vue.prototype.$gate = new Gate(window.user);

const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
});

//global Toast
window.toast = Toast;

//global Fire Vue
window.Fire = new Vue();

Vue.use(VueProgressBar, {
  color: 'rgb(143, 255, 199)',
  failedColor: 'red',
  height: '3px'
});

Vue.use(VueRouter)  

// vue filter to change charater to uppercase
Vue.filter('upText', function (text) {
  return text.charAt(0).toUpperCase() + text.slice(1)
});

// vue filter with moment dependence for formatting date
Vue.filter('myDate', function (created) {
  return moment().format('MMMM Do YYYY');
});

let routes = [
  { path: '/dashboard', component: require('./components/Dashboard.vue').default },
  { path: '/profile', component: require('./components/Profile.vue').default },
  { path: '/developer', component: require('./components/Developer.vue').default },
  { path: '/users', component: require('./components/Users.vue').default },
  { path: '*', component: require('./components/NotFound.vue').default }
]

//VueRouter
const router = new VueRouter({
  mode: 'history', // clearing the history of the url if vue component is called.
  routes
})


Vue.component('dashboard', require('./components/Dashboard.vue').default);

// Not found Component
Vue.component('not-found', require('./components/NotFound.vue').default);

//Pagnation component
Vue.component('pagination', require('laravel-vue-pagination'));

// creating a vue instance
const app = new Vue({
    el: '#app',
  router,
  data: {
      search: ''
  },
  methods: {   
    searchIt: _.debounce(() => {
      Fire.$emit('searching');
    },1000)
    
  }
});

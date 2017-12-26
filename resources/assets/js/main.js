import Vue from 'vue'
import BootstrapVue from 'bootstrap-vue'
import Snotify from 'vue-snotify';
import App from './App'
import router from './router'
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.use(BootstrapVue);

// Use Snotify
// You can pass {config, options} as second argument. See the next example or setConfig in [API] section
Vue.use(Snotify);

const app = new Vue({
    el: '#app',
    router,
    render: h => h(App)
});

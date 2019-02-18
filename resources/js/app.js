
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
window.Event = new Vue();

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key)))

Vue.component('tasks', require('./components/Tasks.vue'));
Vue.component('timeline', require('./components/Timeline.vue'));
Vue.component('assignees', require('./components/Assignees.vue'));
Vue.component('comments', require('./components/Comments.vue'));
Vue.component('new-task', require('./components/NewTask.vue'));
Vue.component('task-view', require('./pages/TaskView.vue'));
Vue.component('navigation', require('./components/Navigation.vue'));
Vue.component('new-comment', require('./components/NewComment.vue'));
Vue.component('task', require('./pages/Task.vue'));
Vue.component('flash', require('./components/Flash.vue'));
Vue.component('user', require('./components/User.vue'));
Vue.component('selectbox', require('./components/Selectbox.vue'));
Vue.component('notifications', require('./components/Notifications.vue'));

import VCalendar from 'v-calendar';
import 'v-calendar/lib/v-calendar.min.css';

Vue.use(VCalendar, {
    firstDayOfWeek: 2,
    formats: {
        title: 'MMMM YYYY',
        weekdays: 'W',
        navMonths: 'MMM',
        input: ['YYYY/MM/DD', 'YYYY-MM-DD'],
        dayPopover: 'L',
        data: ['YYYY/MM/DD', 'YYYY-MM-DD']
      }
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app'
});

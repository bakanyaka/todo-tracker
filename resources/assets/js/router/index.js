import Router from 'vue-router';
import Vue from 'vue';
import issues from '../components/issues/issues'

Vue.use(Router);

export default new Router({
    mode: 'history',
    routes: [
        {
            path: '/issues',
            name: 'issues.index',
            component: issues
        }
    ]
})
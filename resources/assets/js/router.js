import Router from 'vue-router';
import Vue from 'vue';
import Full from './components/layouts/Full'
import Issues from './components/issues/Issues'

Vue.use(Router);

let routes = [
    {
        path: '/',
        redirect: '/issues',
        name: 'home',
        component: Full,
        children: [
            {
                path: '/issues',
                name: 'issues.index',
                component: Issues
            }
        ]
    }
];

export default new Router({
    mode: 'history',
    linkActiveClass: 'open active',
    routes
})
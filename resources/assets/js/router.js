import Router from 'vue-router';
import Vue from 'vue';
import Full from './layouts/Full'
import Issues from './views/issues/Issues'

Vue.use(Router);

let routes = [
    {
        path: '/',
        redirect: '/issues',
        name: 'home',
        component: Full,
        meta: {
            label: 'Главная'
        },
        children: [
            {
                path: '/issues',
                name: 'issues.index',
                component: Issues,
                meta: {
                    label: 'Задачи'
                }
            }
        ]
    }
];

export default new Router({
    mode: 'history',
    linkActiveClass: 'open active',
    routes
})
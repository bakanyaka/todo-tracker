import Router from 'vue-router';
import Vue from 'vue';
import Full from './layouts/Full'
import Issues from './views/issues/Issues'
import Dashboard from './views/dashboard/Dashboard'
import Reports from './views/reports/Reports'

Vue.use(Router);

let routes = [
    {
        path: '/',
        name: 'home',
        redirect: {
            path: '/issues',
            query: {user: 'me'}
        },
        component: Full,
        meta: {
            label: 'Главная'
        },
        children: [
            {
                path: '/dashboard',
                name: 'dashboard',
                component: Dashboard,
                meta: {
                    label: 'Рабочий стол'
                }
            },
            {
                path: '/issues',
                name: 'issues.index',
                component: Issues,
                meta: {
                    label: 'Задачи'
                }
            },
            {
                path: '/reports',
                name: 'reports',
                component: Reports,
                meta: {
                    label: 'Отчеты'
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
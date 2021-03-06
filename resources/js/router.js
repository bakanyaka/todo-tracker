import Router from 'vue-router';
import Vue from 'vue';
import Full from './layouts/Full'
import Issues from './views/issues/Issues'
import Dashboard from './views/dashboard/Dashboard'
import Reports from './views/reports/Reports'
import Services from './views/services/Services'
import Synchronizations from './views/synchronizations/Synchronizations'
import AssigneeReport from './views/reports/assignees/AssigneeReport'

Vue.use(Router);

let routes = [
    {
        path: '/',
        name: 'home',
        redirect: {
            path: '/issues',
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
                    label: 'Отчеты',
                    roles: ['admin']
                }
            },
            {
                path: '/reports/assignees/:id',
                name: 'assignee_report',
                component: AssigneeReport,
                meta: {
                    label: 'Отчеты',
                    roles: ['admin']
                }
            },
            {
                path: '/services',
                name: 'services',
                component: Services,
                meta: {
                    label: 'Сервисы',
                    roles: ['admin']
                }
            },
            {
                path: '/synchronizations',
                name: 'synchronizations',
                component: Synchronizations,
                meta: {
                    label: 'Синхронизации',
                    roles: ['admin']
                }
            }
        ]
    }
];

const router = new Router({
    mode: 'history',
    linkActiveClass: 'open active',
    routes
});

router.beforeEach((to, from, next) => {
    if (to.meta.roles && to.meta.roles.includes('admin')) {
        if (config && config.user && config.user.is_admin) {
            next()
        }
    } else {
        next()
    }
});

export default router;

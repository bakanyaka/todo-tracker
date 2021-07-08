import Router from 'vue-router';
import Vue from 'vue';
import Full from './Layouts/Full'
import Issues from './Views/issues/Issues'
import Dashboard from './Views/dashboard/Dashboard'
import Reports from './Views/reports/Reports'
import Services from './Views/services/Services'
import Synchronizations from './Views/synchronizations/Synchronizations'
import AssigneeReport from './Views/reports/assignees/AssigneeReport'
import Gantt from './Views/gantt/Gantt';

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
        path: '/gantt',
        name: 'issues.index',
        component: Gantt,
        meta: {
          label: 'Диаграмма Ганта'
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

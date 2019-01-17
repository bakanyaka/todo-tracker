let items = [
    /*        {
                name: 'Рабочий стол',
                url: '/dashboard',
                icon: 'icon-speedometer'
            },*/
    {
        name: 'Задачи',
        url: '/issues?user=control',
        icon: 'icon-check'
    }
];
if (typeof config !== 'undefined' && config.user && config.user.is_admin) {
    items = items.concat([
        {
            name: 'Отчеты',
            url: '/reports',
            icon: 'icon-chart'
        },
        {
            title: true,
            name: 'Администрировать',
            class: '',
            wrapper: {
                element: '',
                attributes: {}
            }
        },
        {
            name: 'Сервисы',
            url: '/services',
            icon: 'icon-wrench'
        },
        {
            name: 'Синхронизации',
            url: '/synchronizations',
            icon: 'icon-refresh'
        }
    ])
}
export default {
    items
}
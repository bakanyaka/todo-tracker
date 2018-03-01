let items = [
    /*        {
                name: 'Рабочий стол',
                url: '/dashboard',
                icon: 'icon-speedometer'
            },*/
    {
        name: 'Задачи',
        url: '/issues?user=me',
        icon: 'icon-check'
    },
    {
        name: 'Отчеты',
        url: '/reports',
        icon: 'icon-chart'
    }
];
if (config && config.user && config.user.is_admin) {
    items = items.concat([
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
        }

    ])
}
export default {
    items
}
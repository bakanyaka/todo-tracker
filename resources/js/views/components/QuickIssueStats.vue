<template>
    <b-card-group deck class="mb-4">
        <b-card class="text-white text-center bg-primary">
            <router-link :to="{name: 'issues.index'}">
                <div class="h2 text-muted  mb-2">
                    <i class="icon-clock"></i>
                </div>
                <div class="h3 mb-0">{{stats.open}}</div>
                <span class="text-uppercase font-weight-bold">Открыто</span>
            </router-link>
        </b-card>
        <b-card class="text-white text-center bg-success">
            <router-link :to="{name: 'issues.index', query: {status: 'closed', period: 0}}">
                <div class="h2 text-muted  mb-2">
                    <i class="icon-like"></i>
                </div>
                <div class="h3 mb-0">{{stats.closed_today}}</div>
                <span class="text-uppercase font-weight-bold">Выполнено сегодня</span>
            </router-link>
        </b-card>
        <b-card class="text-white text-center bg-info">
            <router-link :to="{name: 'issues.index', query: {status: 'all', period: 0}}">
                <div class="h2 text-muted  mb-2">
                    <i class="icon-call-in"></i>
                </div>
                <div class="h3 mb-0">{{stats.created_today}}</div>
                <span class="text-uppercase font-weight-bold">Создано сегодня</span>
            </router-link>
        </b-card>
        <b-card class="text-white text-center bg-secondary">
            <router-link :to="{name: 'issues.index', query: {status: 'paused'}}">
                <div class="h2 text-muted  mb-2">
                    <i class="icon-control-pause"></i>
                </div>
                <div class="h3 mb-0">{{stats.paused}}</div>
                <span class="text-uppercase font-weight-bold">Обратная связь</span>
            </router-link>
        </b-card>
        <b-card class="text-white text-center bg-warning">
            <router-link :to="{name: 'issues.index', query: {overdue: 'soon'}}">
                <div class="h2 text-muted  mb-2">
                    <i class="icon-hourglass"></i>
                </div>
                <div class="h3 mb-0">{{stats.due_soon}}</div>
                <span class="text-uppercase font-weight-bold">Скоро крайний срок</span>
            </router-link>
        </b-card>
        <b-card class="text-white text-center bg-danger">
            <router-link :to="{name: 'issues.index', query: {overdue: 'yes'}}">
                <div class="h2 text-muted  mb-2">
                    <i class="icon-fire"></i>
                </div>
                <div class="h3 mb-0">{{stats.overdue}}</div>
                <span class="text-uppercase font-weight-bold">Просрочено</span>
            </router-link>
        </b-card>
        <b-card class="text-white text-center bg-info">
            <router-link :to="{name: 'issues.index', query: {assigned_to: 'Отдел Закупок'}}">
                <div class="h2 text-muted  mb-2">
                    <i class="icon-basket"></i>
                </div>
                <div class="h3 mb-0">{{stats.in_procurement}}</div>
                <span class="text-uppercase font-weight-bold">В закупке</span>
            </router-link>
        </b-card>

    </b-card-group>
</template>

<script>
    export default {
        name: "quick-issue-stats",
        data() {
            return {
                stats: {
                    open: 0,
                    closed_today: 0,
                    created_today: 0,
                    paused: 0,
                    due_soon: 0,
                    overdue: 0,
                    in_procurement: 0,
                }
            }
        },
        mounted() {
            this.getIssueStats();
            setInterval(() => {
                this.getIssueStats();
            }, 60000);
        },
        methods: {
            getIssueStats() {
                return axios.get(route('api.issues.stats')).then((response) => {
                    this.stats = response.data.data
                })
            }
        }
    }
</script>

<style scoped>
    a {
        color: #fff;
    }

    a:hover {
        text-decoration: none;
    }
</style>
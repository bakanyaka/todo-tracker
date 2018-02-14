<template>
    <b-row>
        <b-col sm="6" md="2">
            <router-link :to="{name: 'issues.index'}">
                <b-card class="text-white text-center bg-primary">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-clock"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.open}}</div>
                    <span class="text-uppercase font-weight-bold">Открыто</span>
                </b-card>
            </router-link>
        </b-col>
        <b-col sm="6" md="2">
            <router-link :to="{name: 'issues.index', query: {status: 'closed', period: 0}}">
                <b-card class="text-white text-center bg-success">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-like"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.closed_today}}</div>
                    <span class="text-uppercase font-weight-bold">Выполнено сегодня</span>
                </b-card>
            </router-link>
        </b-col>
        <b-col sm="6" md="2">
            <router-link :to="{name: 'issues.index', query: {status: 'all', period: 0}}">
                <b-card class="text-white text-center bg-info">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-call-in"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.created_today}}</div>
                    <span class="text-uppercase font-weight-bold">Создано сегодня</span>
                </b-card>
            </router-link>
        </b-col>
        <b-col sm="6" md="2">
            <router-link :to="{name: 'issues.index', query: {status: 'paused'}}">
                <b-card class="text-white text-center bg-secondary">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-control-pause"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.paused}}</div>
                    <span class="text-uppercase font-weight-bold">Обратная связь</span>
                </b-card>
            </router-link>
        </b-col>
        <b-col sm="6" md="2">
            <router-link :to="{name: 'issues.index', query: {overdue: 'soon'}}">
                <b-card class="text-white text-center bg-warning">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-hourglass"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.due_soon}}</div>
                    <span class="text-uppercase font-weight-bold">Скоро крайний срок</span>
                </b-card>
            </router-link>
        </b-col>
        <b-col sm="6" md="2">
            <router-link :to="{name: 'issues.index', query: {overdue: 'yes'}}">
                <b-card class="text-white text-center bg-danger">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-fire"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.overdue}}</div>
                    <span class="text-uppercase font-weight-bold">Просрочено</span>
                </b-card>
            </router-link>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        name: "quick-issue-stats",
        data() {
            return {
                stats: {
                    'open': 0,
                    'closed_today': 0,
                    'created_today': 0,
                    'paused': 0,
                    'due_soon': 0,
                    'overdue': 0
                }
            }
        },
        mounted() {
            this.getIssueStats();
            setInterval(() => {
                console.log('QuickIssueStats updated');
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
    a:hover {
        text-decoration: none;
    }
</style>
<template>
    <div>
        <project-select v-model="project_id" @input="getIssueStats" class="mb-3 w-25"></project-select>
        <b-card-group deck class="mb-4">
            <b-card class="text-white text-center bg-primary">
                <router-link :to="{name: 'issues.index', query: { ...projectQuery}}">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-clock"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.open}}</div>
                    <span class="text-uppercase font-weight-bold">Открыто</span>
                </router-link>
            </b-card>
            <b-card class="text-white text-center bg-success">
                <router-link :to="{name: 'issues.index', query: {status: 'closed', period: 0, ...projectQuery}}">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-like"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.closed_today}}</div>
                    <span class="text-uppercase font-weight-bold">Выполнено сегодня</span>
                </router-link>
            </b-card>
            <b-card class="text-white text-center bg-info">
                <router-link :to="{name: 'issues.index', query: {status: 'all', period: 0, ...projectQuery}}">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-call-in"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.created_today}}</div>
                    <span class="text-uppercase font-weight-bold">Создано сегодня</span>
                </router-link>
            </b-card>
            <b-card class="text-white text-center bg-secondary">
                <router-link :to="{name: 'issues.index', query: {status: 'paused', ...projectQuery}}">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-control-pause"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.paused}}</div>
                    <span class="text-uppercase font-weight-bold">Обратная связь</span>
                </router-link>
            </b-card>
            <b-card class="text-white text-center bg-warning">
                <router-link :to="{name: 'issues.index', query: {overdue: 'soon', ...projectQuery}}">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-hourglass"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.due_soon}}</div>
                    <span class="text-uppercase font-weight-bold">Скоро крайний срок</span>
                </router-link>
            </b-card>
            <b-card class="text-white text-center bg-danger">
                <router-link :to="{name: 'issues.index', query: {overdue: 'yes', ...projectQuery}}">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-fire"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.overdue}}</div>
                    <span class="text-uppercase font-weight-bold">Просрочено</span>
                </router-link>
            </b-card>
            <b-card class="text-white text-center bg-info">
                <router-link :to="{name: 'issues.index', query: {assigned_to: 'Отдел Закупок', ...projectQuery}}">
                    <div class="h2 text-muted  mb-2">
                        <i class="icon-basket"></i>
                    </div>
                    <div class="h3 mb-0">{{stats.in_procurement}}</div>
                    <span class="text-uppercase font-weight-bold">В закупке</span>
                </router-link>
            </b-card>
        </b-card-group>
    </div>
</template>

<script>
  import ProjectSelect from './ProjectSelect';
  export default {
    name: 'quick-issue-stats',
    components: { ProjectSelect },
    data() {
      return {
        project_id: null,
        stats: {
          open: 0,
          closed_today: 0,
          created_today: 0,
          paused: 0,
          due_soon: 0,
          overdue: 0,
          in_procurement: 0,
        },
      };
    },
    mounted() {
      this.getIssueStats();
      setInterval(() => {
        this.getIssueStats();
      }, 60000);
    },
    computed: {
      projectQuery() {
        const query = {};
        if (this.project_id) {
          query.project = this.project_id;
          query.include_subprojects = 'yes'
        }
        return query;
      },
    },
    methods: {
      getIssueStats() {
        return axios.get(route('api.issues.stats', { project_id: this.project_id })).then((response) => {
          this.stats = response.data.data;
        });
      },
    },
  };
</script>

<style scoped>
    a {
        color: #fff;
    }

    a:hover {
        text-decoration: none;
    }
</style>

<template>
    <b-card header="Задачи по проектам">
        <div class="clearfix">
            <period-filter :period="period" @change="onPeriodFilterChanged"></period-filter>
        </div>
        <b-table class="mt-3" striped bordered small
                 :items="projects"
                 :fields="fields"
        >
            <template slot="project" slot-scope="data">
                <span :style="{'margin-left': data.item.level + 'rem'}">{{data.value}}</span>
            </template>
        </b-table>
    </b-card>
</template>

<script>
    import PeriodFilter from './PeriodFilter';
    import ProjectTree from "./ProjectTree";
    export default {
        name: "issues-by-project",
        components: {
            ProjectTree,
            PeriodFilter},
        data() {
            return {
                period: 7,
                fields: [
                    {
                        key: 'project',
                        label: 'Проект'
                    },
                    {
                        key: 'created',
                        label: 'Создано'
                    },
                    {
                        key: 'closed',
                        label: 'Выполнено'
                    },
                    {
                        key: 'closed_in_time',
                        label: 'Выполнено в срок'
                    },
                    {
                        key: 'closed_overdue',
                        label: 'Выполнено не в срок'
                    },
                ],
                projects: []
            }
        },
        created() {
            this.getData();
        },
        methods: {
            onPeriodFilterChanged($period) {
                this.period = $period;
                this.getData();
            },
            flattenRecursiveProjectArray(projects, data, level = 0) {
                for (let item of data) {
                    const project = Object.assign({level},item);
                    delete project['children'];
                    projects.push(project);
                    this.flattenRecursiveProjectArray(projects, item.children, level + 1);
                }
            },
            getData() {
                return axios.get(route('api.issues.reports.projects'), {
                    params: {
                        period: this.period
                    }
                }).then((response) => {
                    let projects = [];
                    this.flattenRecursiveProjectArray(projects, response.data.data);
                    this.projects = projects;
                })
            }
        }
    }
</script>

<style scoped>

</style>
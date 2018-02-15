<template>
    <b-card header="Задачи по проектам">
        <div class="clearfix">
            <period-filter :period="period" @change="onPeriodFilterChanged"></period-filter>
        </div>
        <div>
            <b-form-checkbox :plain="true" v-model="filters.include_subprojects" :value="true" :unchecked-value="false" >
                Включая подпроекты
            </b-form-checkbox>
        </div>
        <b-table class="mt-3" striped bordered small
                 :items="filteredProjects"
                 :fields="fields"
        >
            <template slot="project" slot-scope="data">
                <span :class="{'font-weight-bold': data.item.level === 0}" :style="{'margin-left': data.item.level + 'rem'}">{{data.value}}</span>
            </template>
            <template slot="created" slot-scope="data">
                <router-link v-if="data.value !== 0" :to="{name: 'issues.index', query: {status: 'all', project: data.item.project_id, period: period, include_subprojects: 'yes'}}">
                    {{data.value}}
                </router-link>
                <span v-else>{{data.value}}</span>
            </template>
            <template slot="closed" slot-scope="data">
                <router-link v-if="data.value !== 0" :to="{name: 'issues.index', query: {status: 'closed', project: data.item.project_id, period: period, include_subprojects: 'yes'}}">
                    {{data.value}}
                </router-link>
                <span v-else>{{data.value}}</span>
            </template>
            <template slot="closed_in_time" slot-scope="data">
                <router-link v-if="data.value !== 0" :to="{name: 'issues.index', query: {status: 'closed', overdue: 'no', project: data.item.project_id, period: period, include_subprojects: 'yes'}}">
                    {{data.value}}
                </router-link>
                <span v-else>{{data.value}}</span>
            </template>
            <template slot="closed_overdue" slot-scope="data">
                <router-link v-if="data.value !== 0" :to="{name: 'issues.index', query: {status: 'closed', overdue: 'yes', project: data.item.project_id, period: period, include_subprojects: 'yes'}}">
                    {{data.value}}
                </router-link>
                <span v-else>{{data.value}}</span>
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
                        label: 'Поступило'
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
                projects: [],
                filters: {
                    include_subprojects: false
                }

            }
        },
        computed: {
            filteredProjects() {
                if (this.filters.include_subprojects === false) {
                    return this.projects.filter((project) => {
                        return project.level === 0;
                    });
                }
                return this.projects;
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
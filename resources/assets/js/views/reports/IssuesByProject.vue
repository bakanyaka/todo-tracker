<template>
    <b-card header="Задачи по проектам">
        <div class="clearfix">
            <period-filter :period="period" @change="onPeriodFilterChanged"></period-filter>
        </div>
        <b-table class="mt-3" striped bordered small
                 :items="issues"
                 :fields="fields"
        >
        </b-table>
    </b-card>
</template>

<script>
    import PeriodFilter from './PeriodFilter';
    export default {
        name: "issues-by-project",
        components: {PeriodFilter},
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
                issues: []
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
            getData() {
                return axios.get(route('api.issues.reports.projects'), {
                    params: {
                        period: this.period
                    }
                }).then((response) => {
                    this.issues = response.data.data;
                })
            }
        }
    }
</script>

<style scoped>

</style>
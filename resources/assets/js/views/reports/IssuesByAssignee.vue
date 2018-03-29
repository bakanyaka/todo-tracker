<template>
    <b-card header="Задачи по сотрудникам">
        <div class="clearfix">
            <period-filter period="7" @change="onDateRangeChanged"></period-filter>
        </div>
        <div>
            <b-form-checkbox :plain="true" v-model="filters.hide_zero_issues" :value="true" :unchecked-value="false" >
                Скрыть сотрудников с 0 задач
            </b-form-checkbox>
        </div>
        <b-table class="mt-3" striped bordered small
                 :items="filteredAssignees"
                 :fields="fields"
        >
            <template slot="fullname" slot-scope="row">
                <!--<router-link :to="{name: 'assignee_report', params: {id: row.item.id}}">{{row.item.firstname}} {{row.item.lastname}}</router-link>-->
                {{row.item.firstname}} {{row.item.lastname}}
            </template>
            <template slot="kpi" slot-scope="row">
                {{row.item.participated_issues_count ? (row.item.spent_issues_count / row.item.participated_issues_count).toFixed(3) : 0}}
            </template>
        </b-table>
    </b-card>
</template>

<script>
    import moment from 'moment'
    import PeriodFilter from '../components/PeriodFilter'
    export default {
        name: "issues-by-assignee",
        components: {
            PeriodFilter
        },
        data() {
            return {
                period: {
                    startDate: moment().subtract(7,'days').format('YYYY-MM-DD'),
                    endDate: moment().subtract(1, 'days').format('YYYY-MM-DD')
                },
                fields: [
                    {
                        key: 'fullname',
                        label: 'Сотрудник',
                    },
                    {
                        key: 'spent_issues_count',
                        label: 'Отработано задач',
                        thStyle: {
                            width: '10%',
                        }
                    },
                    {
                        key: 'participated_issues_count',
                        label: 'Участвовал в задачах',
                        thStyle: {
                            width: '10%',
                        }
                    },
                    {
                        key: 'kpi',
                        label: 'KPI',
                        thStyle: {
                            width: '10%',
                        }
                    },
                ],
                assignees: [],
                filters: {
                    hide_zero_issues: true
                }

            }
        },
        computed: {
            filteredAssignees() {
                if (this.filters.hide_zero_issues === true) {
                    return this.assignees.filter((assignee) => {
                        return assignee.participated_issues_count > 0 || assignee.spent_issues_count > 0;
                    });
                }
                return this.assignees;
            }
        },
        created() {
            this.getData();
        },
        methods: {
            onDateRangeChanged(range) {
                this.period = range;
                this.getData();
            },
            getData() {
                return axios.get(route('api.assignees.report.index'), {
                    params: {
                        period_from_date: this.period.startDate,
                        period_to_date: this.period.endDate,
                    }
                }).then((response) => {
                    this.assignees = response.data.data;
                })
            }
        }
    }
</script>

<style scoped>

</style>
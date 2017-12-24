<template>
    <div class="animated fadeIn">
            <b-row class="row">
                <b-col md="4">
                    <b-card header="Добавить задачу в отслеживаемые" class="pb-2">
                        <b-form inline  @submit.prevent="addIssue">
                            <b-input class="form-control-sm mr-2" type="text" placeholder="# Задачи" v-model="addIssueId" required></b-input>
                            <b-button variant="primary" size="sm" class="my-2" type="submit"><i class="fa fa-plus"></i>&nbsp; Отслеживать</b-button>
                        </b-form>
                    </b-card>
                </b-col>
                <b-col md="8">
                    <b-card header="Фильтры">
                        <filters></filters>
                    </b-card>
                </b-col>
            </b-row>
        <b-card header="Отслеживаемые задачи">
            <b-row class="mb-3">
                <b-col sm="6">
                    <div class="form-inline">
                        <label for="resultsPerPage" class="mr-2">Результатов на страницу:</label>
                        <b-form-select size="sm" id="resultsPerPage" v-model="pagination.perPage" :options="[10,20,50,100]"></b-form-select>
                    </div>
                </b-col>
                <b-col sm="4" offset-md="2">
                    <b-input size="sm" type="text" placeholder="Поиск" v-model="searchText" required></b-input>
                </b-col>
            </b-row>
            <b-table striped outlined small
                     :items="issues"
                     :fields="fields"
                     :filter="searchText"
                     :per-page="pagination.perPage"
                     :current-page="pagination.currentPage"
            >
                <template slot="id" slot-scope="data">
                    <a :href="`${redmineUri}/${data.value}`">{{data.value}}</a>
                </template>
            </b-table>
            <b-pagination size="md" :total-rows="pagination.totalRows" v-model="pagination.currentPage" :per-page="pagination.perPage" @input="pageChanged">
            </b-pagination>
        </b-card>
    </div>
</template>

<script>
    import filters from './Filters'
    export default {
        data () {
            return {
                redmineUri: config.redmineUri,
                addIssueId: null,
                searchText: null,
                pagination: {
                    totalRows: null,
                    perPage: 20,
                    currentPage: 1
                },
                fields: [
                    {
                        key: 'id',
                        label: '#'
                    },
                    {
                        key: 'subject',
                        label: 'Тема'
                    },
                    {
                        key: 'assigned_to',
                        label: 'Назначена'
                    },
                    {
                        key: 'department',
                        label: 'Подразделение'
                    },
                    {
                        key: 'priority',
                        label: 'Приоритет'
                    },
                    {
                        key: 'service',
                        label: 'Сервис'
                    },
                    {
                        key: 'estimated_hours',
                        label: 'Расчетное время',
                    },
                    {
                        key: 'time_left',
                        label: 'Оставшееся время'
                    },
                    {
                        key: 'created_on',
                        label: 'Дата создания'
                    },
                    {
                        key: 'due_date',
                        label: 'Плановая дата завершения'
                    },
                    {
                        key: 'closed_on',
                        label: 'Фактическая дата завершения'
                    },
                ],
                issues: [],
                meta: {},
            }
        },
        components: {
            filters,
        },
        mounted() {
            this.getIssues().then(()=>{
                this.pagination.currentPage = parseInt(this.$route.query.page) || 1
            });
        },
        methods: {
            getIssues() {
                return axios.get(route('api.issues')).then((response) => {
                    this.issues = response.data.data.map((issue) => {
                        if (issue.estimated_hours && (issue.time_left / issue.estimated_hours < 0.3)) {
                            issue._rowVariant = 'danger'
                        }
                        return issue;
                    });
                    this.pagination.totalRows = response.data.data.length;
                    this.meta = response.data.meta;
                })
            },
            async addIssue () {
                await axios.post(route('issues.track'),{issue_id: this.addIssueId});
                this.addIssueId = null;
                this.getIssues();
            },
            pageChanged() {
                this.$router.replace({
                    query: {
                        ...this.$route.query,
                        page: this.pagination.currentPage
                    }
                })
            }
        }
    }
</script>

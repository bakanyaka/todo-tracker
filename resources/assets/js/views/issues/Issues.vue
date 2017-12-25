<template>
    <div class="animated fadeIn">
        <b-row class="row">
            <b-col md="3">
                <b-card header="Добавить задачу в отслеживаемые" class="pb-1">
                    <div class="form-inline">
                        <b-input class="form-control-sm mr-2" type="text" placeholder="# Задачи" v-model="addIssueId"
                                 required></b-input>
                        <b-button @click="addIssue()" variant="primary" size="sm" class="my-2"><i class="fa fa-plus"></i>&nbsp;
                            Отслеживать
                        </b-button>

                    </div>
                </b-card>
            </b-col>
            <b-col md="2">
                <b-card header="Синхронизация с Redmine" class="text-center pb-1">
                    <b-button @click="syncIssues()" variant="primary" size="sm" class="my-2"><i class="fa fa-refresh"></i>&nbsp;
                        Синхронизировать
                    </b-button>
                </b-card>
            </b-col>
            <b-col md="7">
                <b-card header="Фильтры">
                    <filters @filters:changed="onFiltersChanged"></filters>
                </b-card>
            </b-col>
        </b-row>
        <b-card>
            <template slot="header">
                Отслеживаемые задачи <small class="pull-right">Синхронизация с Redmine выполнена {{meta.last_sync.completed_at_human}}</small>
            </template>
            <spinner v-if="loading" size="large" message="Загрузка..."></spinner>
            <div v-else-if="!issues.length">
                Ничего не найдено
            </div>
            <template v-else>
                <b-row class="mb-3">
                    <b-col sm="6">
                        <div class="form-inline">
                            <label for="resultsPerPage" class="mr-2">Результатов на страницу:</label>
                            <b-form-select size="sm" id="resultsPerPage" v-model="pagination.perPage"
                                           :options="[10,20,50,100]"></b-form-select>
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
                        <a :href="`${redmineUri}/issues/${data.value}`">{{data.value}}</a>
                    </template>

                    <template slot="actions" slot-scope="row">
                        <!-- We use @click.stop here to prevent a 'row-clicked' event from also happening -->
                        <b-button v-if="row.item.is_tracked_by_current_user === true" variant="danger" @click.stop="removeIssue(row.item.id)">
                            <i class="fa fa-chain-broken"></i>
                        </b-button>
                        <b-button v-else variant="success" @click.stop="addIssue(row.item.id)">
                            <i class="fa icon-eyeglass"></i>
                        </b-button>
                    </template>

                    <template slot="table-caption">
                        На странице показано {{visibleRows}} из {{pagination.totalRows}}
                    </template>
                </b-table>
                <b-pagination size="md" :total-rows="pagination.totalRows" v-model="pagination.currentPage"
                              :per-page="pagination.perPage" @input="pageChanged">
                </b-pagination>
            </template>
        </b-card>
    </div>
</template>

<script>
    import filters from './Filters'
    import Spinner from 'vue-simple-spinner'

    export default {
        data() {
            return {
                redmineUri: config.redmineUri,
                loading: true,
                addIssueId: null,
                searchText: null,
                pagination: {
                    totalRows: null,
                    perPage: 20,
                    currentPage: 1,
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
                    {
                        key: 'actions',
                        label: 'Действия'
                    }
                ],
                issues: [],
                meta: {},
            }
        },
        components: {
            filters,
            Spinner
        },
        computed: {
            visibleRows() {
                if (this.pagination.totalRows / (this.pagination.perPage * this.pagination.currentPage) >= 1) {
                    return this.pagination.perPage
                } else {
                    return this.pagination.totalRows - this.pagination.perPage * (this.pagination.currentPage - 1)
                }
            }
        },
        mounted() {
            this.getIssues().then(() => {
                this.pagination.currentPage = parseInt(this.$route.query.page) || 1
            });
            setInterval(() => {
                this.getIssues();
            },300000);
        },
        methods: {
            getIssues(query = this.$route.query) {
                this.loading = true;
                return axios.get(route('api.issues'), {
                    params: {
                        ...query
                    }
                }).then((response) => {
                    this.issues = response.data.data.map((issue) => {
                        if (issue.time_left && issue.time_left < 0) {
                            issue._rowVariant = 'danger'
                        } else if (issue.estimated_hours && (issue.time_left / issue.estimated_hours < 0.3)) {
                            issue._rowVariant = 'warning'
                        }
                        return issue;
                    });
                    this.pagination.totalRows = response.data.data.length;
                    this.meta = response.data.meta;
                    this.loading = false;
                }).catch((e) => {
                    this.$snotify.error('Ошибка при загрузке задач');
                    this.loading = false;
                });
            },
            async addIssue(issueId = this.addIssueId) {
                try {
                    await axios.post(route('api.issues.track'), {issue_id: issueId});
                    this.$snotify.success('Задача добавлена');
                } catch (e) {
                    this.$snotify.error('Ошибка! Задача не добавлена');
                }
                this.addIssueId = null;
                this.getIssues();
            },
            async removeIssue(id) {
                try {
                    await axios.delete(route('api.issues.untrack',{issue: id}));
                    this.$snotify.success('Задача больше не остлеживается');
                } catch (e) {
                    this.$snotify.error('Ошибка!');
                }
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
            },
            async onFiltersChanged(filters) {
                await this.$router.replace({
                    query: {
                        ...filters,
                    }
                });
                this.getIssues();
            },
            syncIssues() {
                axios.get(route('api.issues.sync')).then(() => {
                    this.$snotify.success('Задание на синхронизацию добавлено в очередь');
                    this.getIssues();
                }).catch((e) => {
                    this.$snotify.error('Ошибка при добавлении задания в очередь');
                });
            }

        }
    }
</script>

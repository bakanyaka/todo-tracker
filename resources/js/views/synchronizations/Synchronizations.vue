<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col sm="4">
                <b-card>
                    <template slot="header">
                        Синхронизировать данные из Redmine c указанной даты
                    </template>
                    <b-form-group :description="'Последняя синхронизация: ' + lastSync.assignees">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>Пользователи RM</b-input-group-text>
                            </b-input-group-prepend>
                            <b-form-input type="text" disabled></b-form-input>
                            <b-input-group-append>
                                <b-button variant="primary" @click.stop="syncUsers"><i class="fa fa-refresh"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form-group>
                    <b-form-group :description="'Последняя синхронизация: ' + lastSync.projects">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>Проекты</b-input-group-text>
                            </b-input-group-prepend>
                            <b-form-input type="text" disabled></b-form-input>
                            <b-input-group-append>
                                <b-button variant="primary" @click.stop="syncProjects"><i class="fa fa-refresh"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form-group>
                    <b-form-group :description="'Последняя синхронизация: ' + lastSync.issues">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>Задачи</b-input-group-text>
                            </b-input-group-prepend>
                            <b-form-input type="date" v-model="syncIssuesDate"></b-form-input>
                            <b-input-group-append>
                                <b-button variant="primary" @click.stop="syncIssues"><i class="fa fa-refresh"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form-group>
                    <b-form-group :description="'Последняя синхронизация: ' + lastSync.time_entries">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>Затраченное время</b-input-group-text>
                            </b-input-group-prepend>
                            <b-form-input type="date" v-model="syncTimeEntriesDate"></b-form-input>
                            <b-input-group-append>
                                <b-button variant="primary" @click.stop="syncTimeEntries"><i class="fa fa-refresh"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form-group>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        name: "synchronizations",
        data() {
            return {
                syncIssuesDate: moment().format('YYYY-MM-DD'),
                syncTimeEntriesDate: moment().format('YYYY-MM-DD'),
                lastSync: {
                    issues: 'Никогда',
                    time_entries: 'Никогда',
                    assignees: 'Никогда',
                    projects: 'Никогда'
                }
            }
        },
        created() {
            this.getLastSynchorinizations();
            setInterval(() => {
                this.getLastSynchorinizations();
            }, 300000);
        },
        methods: {
            getLastSynchorinizations() {
                return axios.get(route('api.synchronizations.index')).then((response) => {
                    this.lastSync.issues = response.data.data.issues ? response.data.data.issues.completed_at_human : 'Никогда';
                    this.lastSync.time_entries = response.data.data.time_entries ? response.data.data.time_entries.completed_at_human : 'Никогда';
                    this.lastSync.assignees = response.data.data.assignees ? response.data.data.assignees.completed_at_human : 'Никогда';
                    this.lastSync.projects = response.data.data.projects ? response.data.data.projects.completed_at_human : 'Никогда';
                }).catch((e) => {
                    console.log(e);
                    this.$snotify.error('Ошибка при загрузке синхронизаций');
                });
            },
            syncUsers() {
                return axios.get(route('api.assignees.sync')).then(() => {
                    this.$snotify.success('Пользователи синхронизированы');
                }).catch((e) => {
                    console.log(e);
                    this.$snotify.error('Ошибка при синхронизации пользователей');
                });
            },
            syncProjects() {
                return axios.get(route('api.projects.sync')).then(() => {
                    this.$snotify.success('Проекты синхронизированы');
                }).catch((e) => {
                    console.log(e);
                    this.$snotify.error('Ошибка при синхронизации проектов');
                });
            },
            syncIssues() {
                return axios.get(route('api.issues.sync'), {
                    params: {
                        updated_since: this.syncIssuesDate
                    }
                }).then(() => {
                    this.$snotify.success('Задачи синхронизированы');
                }).catch((e) => {
                    console.log(e);
                    this.$snotify.error('Ошибка при синхронизации задач');
                });
            },
            syncTimeEntries() {
                return axios.get(route('api.time-entries.sync'), {
                    params: {
                        spent_since: this.syncTimeEntriesDate
                    }
                }).then(() => {
                    this.$snotify.success('Проекты синхронизированы');
                }).catch((e) => {
                    console.log(e);
                    this.$snotify.error('Ошибка при синхронизации задач');
                });
            }
        }
    }
</script>

<style scoped>

</style>
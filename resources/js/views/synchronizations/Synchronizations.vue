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
                                <b-button variant="primary" @click.stop="syncUsers"><i class="fa fa-refresh"></i>
                                </b-button>
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
                                <b-button variant="primary" @click.stop="syncProjects"><i class="fa fa-refresh"></i>
                                </b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form-group>
                    <b-form-group :description="'Последняя синхронизация: ' + lastSync.trackers">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>Трекеры</b-input-group-text>
                            </b-input-group-prepend>
                            <b-form-input type="text" disabled></b-form-input>
                            <b-input-group-append>
                                <b-button variant="primary" @click.stop="syncTrackers"><i class="fa fa-refresh"></i>
                                </b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form-group>
                    <b-form-group :description="'Последняя синхронизация: ' + lastSync.issues">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>Задачи</b-input-group-text>
                            </b-input-group-prepend>
                            <b-input-group-prepend is-text>
                                <b-form-checkbox :value="1" :unchecked-value="null" v-model="forceUpdateAllRequests">
                                    Принудительно
                                </b-form-checkbox>
                            </b-input-group-prepend>
                            <b-form-input type="date" v-model="syncIssuesDate"></b-form-input>
                            <b-input-group-append>
                                <b-button variant="primary" @click.stop="syncIssues"><i class="fa fa-refresh"></i>
                                </b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form-group>
                    <b-form-group :description="'Последняя синхронизация: ' + lastSync.services">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>Сервисы</b-input-group-text>
                            </b-input-group-prepend>
                            <b-form-input type="text" disabled></b-form-input>
                            <b-input-group-append>
                                <b-button variant="primary" @click.stop="syncServices"><i class="fa fa-refresh"></i>
                                </b-button>
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
                                <b-button variant="primary" @click.stop="syncTimeEntries"><i class="fa fa-refresh"></i>
                                </b-button>
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
    name: 'synchronizations',
    data() {
      return {
        syncIssuesDate: moment().format('YYYY-MM-DD'),
        syncTimeEntriesDate: moment().format('YYYY-MM-DD'),
        lastSync: {
          issues: 'Никогда',
          time_entries: 'Никогда',
          assignees: 'Никогда',
          projects: 'Никогда',
          trackers: 'Никогда',
          services: 'Никогда',
        },
        syncInterval: null,
        forceUpdateAllRequests: null,
      };
    },
    created() {
      this.getLastSynchorinizations();
      this.syncInterval = setInterval(() => {
        this.getLastSynchorinizations();
      }, 300000);
    },
    methods: {
      getLastSynchorinizations() {
        return axios.get(route('api.synchronizations.index')).then((response) => {
          this.lastSync.issues = response.data.data.issues ? response.data.data.issues.completed_at_human : 'Никогда';
          this.lastSync.time_entries = response.data.data.time_entries
            ? response.data.data.time_entries.completed_at_human
            : 'Никогда';
          this.lastSync.assignees = response.data.data.assignees
            ? response.data.data.assignees.completed_at_human
            : 'Никогда';
          this.lastSync.projects = response.data.data.projects
            ? response.data.data.projects.completed_at_human
            : 'Никогда';
          this.lastSync.trackers = response.data.data.trackers
            ? response.data.data.trackers.completed_at_human
            : 'Никогда';
          this.lastSync.services = response.data.data.services
            ? response.data.data.services.completed_at_human
            : 'Никогда';
        }).catch((e) => {
          console.log(e);
          this.$snotify.error('Ошибка при загрузке синхронизаций');
        });
      },
      syncUsers() {
        return axios.get(route('api.assignees.sync')).then(() => {
          this.$snotify.success('Пользователи синхронизированы');
          this.getLastSynchorinizations();
        }).catch((e) => {
          console.log(e);
          this.$snotify.error('Ошибка при синхронизации пользователей');
        });
      },
      syncProjects() {
        return axios.get(route('api.projects.sync')).then(() => {
          this.$snotify.success('Проекты синхронизированы');
          this.getLastSynchorinizations();
        }).catch((e) => {
          console.log(e);
          this.$snotify.error('Ошибка при синхронизации проектов');
        });
      },
      syncServices() {
        return axios.get(route('api.services.sync')).then(() => {
          this.$snotify.success('Сервисы синхронизированы');
          this.getLastSynchorinizations();
        }).catch((e) => {
          console.log(e);
          this.$snotify.error('Ошибка при синхронизации сервисов');
        });
      },
      syncTrackers() {
        return axios.get(route('api.trackers.sync')).then(() => {
          this.$snotify.success('Трекеры синхронизированы');
          this.getLastSynchorinizations();
        }).catch((e) => {
          console.log(e);
          this.$snotify.error('Ошибка при синхронизации трекеров');
        });
      },
      syncIssues() {
        return axios.get(route('api.issues.sync'), {
          params: {
            updated_since: this.syncIssuesDate,
            force_update_all: this.forceUpdateAllRequests,
          },
        }).then(() => {
          this.getLastSynchorinizations();
          this.$snotify.success('Задачи синхронизированы');
        }).catch((e) => {
          console.log(e);
          this.$snotify.error('Ошибка при синхронизации задач');
        });
      },
      syncTimeEntries() {
        return axios.get(route('api.time-entries.sync'), {
          params: {
            spent_since: this.syncTimeEntriesDate,
          },
        }).then(() => {
          this.getLastSynchorinizations();
          this.$snotify.success('Проекты синхронизированы');
        }).catch((e) => {
          console.log(e);
          this.$snotify.error('Ошибка при синхронизации задач');
        });
      },
    },
    beforeDestroy() {
      clearInterval(this.syncInterval);
    },
  };
</script>

<style scoped>

</style>

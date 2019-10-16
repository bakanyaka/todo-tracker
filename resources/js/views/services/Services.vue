<template>
    <div class="animated fadeIn">
        <b-card>
            <template slot="header">
                Сервисы
            </template>
            <spinner v-if="loading" size="large" message="Загрузка..."></spinner>
            <div v-else-if="!services.length">
                Сервисы отсутствуют
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
                </b-row>
                <b-table outlined small
                         :items="services"
                         :fields="fields"
                         :per-page="pagination.perPage"
                         :current-page="pagination.currentPage"
                >
                    <template slot="table-caption">
                        На странице показано {{visibleRows}} из {{pagination.totalRows}}
                    </template>
                </b-table>
                <b-pagination size="md" :total-rows="pagination.totalRows" v-model="pagination.currentPage"
                              :per-page="pagination.perPage">
                </b-pagination>
            </template>
        </b-card>
    </div>
</template>

<script>
    import Spinner from 'vue-simple-spinner'

    export default {
        data() {
            return {
                loading: true,
                searchText: '',
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
                        key: 'name',
                        label: 'Наименование'
                    },
                    {
                        key: 'hours',
                        label: 'Часов'
                    },
                ],
                services: [],
                meta: {},
            }
        },
        components: {
            Spinner
        },
        computed: {
            visibleRows() {
                if (this.pagination.totalRows / (this.pagination.perPage * this.pagination.currentPage) >= 1) {
                    return this.pagination.perPage
                } else {
                    return this.pagination.totalRows - this.pagination.perPage * (this.pagination.currentPage - 1)
                }
            },
        },
        created() {
            this.getServices().then(() => {
                this.pagination.currentPage = parseInt(this.$route.query.page) || 1
            });
            setInterval(() => {
                this.getServices();
            }, 300000);
        },
        methods: {
            getServices() {
                this.loading = true;
                return axios.get(route('api.services')).then((response) => {
                    this.services = response.data.data;
                    this.pagination.totalRows = response.data.data.length;
                    this.meta = response.data.meta || {};
                    this.loading = false;
                }).catch((e) => {
                    console.log(e);
                    this.$snotify.error('Ошибка при загрузке сервисов');
                    this.loading = false;
                });
            },
            showAddServiceModal() {
                this.$refs.addServiceModal.show()
            },
            async deleteService(id) {
                try {
                    await axios.delete(route('api.services.destroy', id));
                    this.$snotify.success('Сервис удален');
                } catch (e) {
                    console.log(e);
                    this.$snotify.error('Ошибка при удалении сервиса!');
                }
                this.getServices();
            },
            editService(service) {
                this.$refs.editServiceModal.show(Object.assign({},service))
            },
            saveServiceChanges() {
                axios.patch(route('api.services.update', this.modals.editService.data.id),this.modals.editService.data).then(() => {
                    this.$snotify.success('Сервис изменен');
                    this.getServices();
                }).catch((e) => {
                    console.log(e);
                    this.$snotify.error('Ошибка при изменении сервиса!');
                });
                this.modals.editService.data = {};
                this.modals.editService.show = false;
            }
        }
    }
</script>

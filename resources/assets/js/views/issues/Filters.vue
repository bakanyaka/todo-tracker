<template>
    <div class="form-inline">
        <div class="col-auto">
            <label for="tracked-by">Кто отслеживает:</label>
            <b-form-select size="sm" id="tracked-by" v-model="filters.user.selected" :options="filters.user.options"></b-form-select>
        </div>
        <div class="col-auto">
            <label for="status">Статус:</label>
            <b-form-select size="sm" id="status" v-model="filters.status.selected" :options="filters.status.options"></b-form-select>
        </div>
        <div class="col-auto">
            <label for="overdue">Срок:</label>
            <b-form-select size="sm" id="overdue" v-model="filters.overdue.selected" :options="filters.overdue.options"></b-form-select>
        </div>
    </div>
</template>

<script>
    export default {
        name: "filters",
        data () {
            return {
                filters: {
                    user: {
                        selected: 'me',
                        options: [
                            { value: 'me', text: 'Только я'},
                            { value: 'all', text: 'Какой-либо пользователь'},
                            { value: 'control', text: 'Помеченные контроль'},
                            { value: null, text: 'Неважно'}
                        ]
                    },
                    status: {
                        selected: null,
                        options: [
                            { value: null, text: 'Открытые'},
                            { value: 'all', text: 'Все'},
                            { value: 'paused', text: 'Обратная связь'},
                            { value: 'closed', text: 'Закрытые'}
                        ]
                    },
                    overdue: {
                        selected: null,
                        options: [
                            { value: null, text: 'Все'},
                            { value: 'yes', text: 'Просроченные'},
                            { value: 'soon', text: 'Истекает срок'}
                        ]
                    }
                }
            }
        },
        watch: {
            'filters': {
                handler (filters) {
                    const selectedFilters = {};
                    for (let key in filters) {
                        if(filters[key].selected !== null) {
                            selectedFilters[key] = filters[key].selected
                        }
                    }
                    this.$emit('filters:changed', selectedFilters);
                },
                deep: true
            },
            '$route'() {

            },
        },
        mounted () {
            this.updateFromQuery()
        },
        methods: {
            updateFromQuery() {
                for (let param in this.$route.query) {
                    if(this.filters.hasOwnProperty(param)) {
                        this.filters[param].selected = this.$route.query[param]
                    }
                }
            }
        }
    }
</script>

<style scoped>

</style>
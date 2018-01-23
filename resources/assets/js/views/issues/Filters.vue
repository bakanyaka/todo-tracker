<template>
    <div class="form-inline">
        <div class="col-auto">
            <label for="tracked-by">Кто отслеживает:</label>
            <b-form-select size="sm" id="tracked-by" v-model="filters.user.selected" :options="filters.user.options" @change="onFiltersChanged">
            </b-form-select>
        </div>
        <div class="col-auto">
            <label for="status">Статус:</label>
            <b-form-select size="sm" id="status" v-model="filters.status.selected" :options="filters.status.options" @change="onFiltersChanged">
            </b-form-select>
        </div>
        <div class="col-auto">
            <label for="overdue">Срок:</label>
            <b-form-select size="sm" id="overdue" v-model="filters.overdue.selected" :options="filters.overdue.options" @change="onFiltersChanged">
            </b-form-select>
        </div>
        <div class="col-auto">
            <label for="period">Период:</label>
            <b-form-select size="sm" id="period" v-model="filters.period.selected" :options="filters.period.options" @change="onFiltersChanged">
            </b-form-select>
        </div>
        <div class="col-auto">
            <label>&nbsp;</label>
            <b-button :to="{name: 'issues.index', query: {user: 'me'}}" variant="primary" size="sm" active-class="" exact-active-class="">
                Сбросить
            </b-button>
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
                            { value: null, text: 'Все задачи'}
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
                    },
                    period: {
                        selected: null,
                        options: [
                            { value: null, text: 'Все время'},
                            { value: 1, text: 'Сегодня'},
                            { value: 7, text: 'За неделю'},
                            { value: 14, text: 'За 14 дней'},
                            { value: 30, text: 'За месяц'}
                        ]
                    }
                }
            }
        },
        watch: {
            '$route'() {
                this.updateFromQuery()
            },
        },
        mounted () {
            this.updateFromQuery()
        },
        methods: {
            updateFromQuery() {
                for (let filter in this.filters) {
                    if (this.$route.query.hasOwnProperty(filter)) {
                        this.filters[filter].selected = this.$route.query[filter];
                    } else {
                        this.filters[filter].selected = null;
                    }
                }
            },
            onFiltersChanged() {
                // $nextTick is workaround for event being triggered before v-model variable is updated
                // Can't use watchers because need to react only on changes being made by user
                this.$nextTick(() => {
                    const selectedFilters = {};
                    for (let key in this.filters) {
                        if(this.filters[key].selected !== null) {
                            selectedFilters[key] = this.filters[key].selected
                        }
                    }
                    this.$emit('filters:changed', selectedFilters);
                });
            }
        }
    }
</script>

<style scoped>

</style>
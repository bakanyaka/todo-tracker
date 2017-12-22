<template>
    <div class="container my-2">
        <div class="row form-inline">
            <div class="col-auto">
                <label for="tracked-by">Кто отслеживает:</label>
                <b-form-select id="tracked-by" v-model="filters.user.selected" :options="filters.user.options"></b-form-select>
            </div>
            <div class="col-auto">
                <label for="checkbox1-by">Статус:</label>
                <b-form-select id="tracked-by" v-model="filters.status.selected" :options="filters.status.options"></b-form-select>
            </div>
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
                        selected: null,
                        options: [
                            { value: null, text: 'Только я'},
                            { value: 'all', text: 'Любой пользователь'}
                        ]
                    },
                    status: {
                        selected: 'open',
                        options: [
                            { value: 'open', text: 'Открытые'},
                            { value: null, text: 'Все'},
                            { value: 'closed', text: 'Закрытые'}
                        ]
                    }
                }
            }
        },
        watch: {
            'filters': {
                handler (filters) {
                    const selectedFilters = {};
                    this.$emit('filters:changed', filters)
                },
                deep: true
            }
        }
    }
</script>

<style scoped>

</style>